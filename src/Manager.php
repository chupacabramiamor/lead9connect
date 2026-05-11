<?php

namespace Chupacabramiamor\Lead9Connect;

use Chupacabramiamor\Lead9Connect\Contracts\ReplaceResponseData;
use Chupacabramiamor\Lead9Connect\Contracts\UseCache;
use Chupacabramiamor\Lead9Connect\Contracts\UsePointer;
use Chupacabramiamor\Lead9Connect\Exceptions\Lead9Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Support\Arrayable;

class Manager
{
    const DROP_CACHE = 1;

    public function __construct(
        private string $endpoint
    ) {}

    public function flushCache(UseCache $command)
    {
        Cache::forget($command->getCacheKey());
    }

    /**
     * @param string $class
     * @param Arrayable|array $payload
     * @param int $flags
     * @return mixed
     * @throws Lead9Exception
     */
    public function execute(string $class, Arrayable|array $payload = [], int $flags = 0): mixed
    {
        if ($payload instanceof Arrayable) {
            $payload = $payload->toArray();
        }

        if (!class_exists($class)) {
            Log::warning('Command class does not exist', ['class' => $class]);
            throw new Lead9Exception('trying_to_execute_wrong_command');
        }

        Log::debug('Executing command', ['class' => $class, 'payload' => $payload]);

        /** @var AbstractCommand|ReplaceResponseData|UseCache|UsePointer */
        $command = new $class($payload);

        if (!$command->verify()) {
            Log::warning('Command verification failed', ['class' => $class, 'payload' => $payload]);
            throw new Lead9Exception('not_allowed_to_execute');
        }

        $contracts = class_implements($class);

        /** @var array|object|null */
        $contents = null;

        if (in_array(UseCache::class, $contracts)) {
            if (($flags & self::DROP_CACHE) === self::DROP_CACHE ) {
                Log::info('Flushing cache', ['cache_key' => $command->getCacheKey()]);
                $this->flushCache($command);
            } else {
                $contents = Cache::get($command->getCacheKey());
                if ($contents) {
                    Log::info('Cache hit', ['class' => $class, 'cache_key' => $command->getCacheKey()]);
                } else {
                    Log::debug('Cache miss', ['class' => $class, 'cache_key' => $command->getCacheKey()]);
                }
            }
        }

        if (!$contents) {
            Log::debug('Making API request', ['class' => $class, 'command' => $command->getCommandName()]);

            $client = new Client([
                'timeout'  => 15,
                'base_uri' => $this->endpoint,
                'verify'   => false,
            ]);

            $response = $client->send($this->makeRequest($command, $command->getData()));

            if ($response->getStatusCode() >= 400) {
                Log::error('API request failed', ['class' => $class, 'status' => $response->getStatusCode()]);
                throw new Lead9Exception();
            }

            $contents = json_decode(trim($response->getBody()->getContents()));

            if (json_last_error() != JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'class' => $class,
                    'error' => json_last_error_msg(),
                    'response' => $response->getBody()->getContents()
                ]);
                throw new Lead9Exception('incorrect_data_received');
            }

            if ($command::hasFailed($contents)) {
                Log::error("message", (array) ['payload' => $command->getData(), 'contents' => (array) $contents ]);
                throw new Lead9Exception($class::getErrorMessage($contents) ?: '');
            }

            if (in_array(UsePointer::class, $contracts)) {
                $contents = $contents->{$command->pointer()} ?? null;
            }

            if (in_array(ReplaceResponseData::class, $contracts)) {
                $contents = $command->replace($contents);
            }

            if (in_array(UseCache::class, $contracts)) {
                Cache::put($command->getCacheKey(), $contents, $command->getCacheTtl());
                Log::debug('Cached response', ['class' => $class, 'cache_key' => $command->getCacheKey(), 'ttl' => $command->getCacheTtl()]);
            }
        }

        Log::info('Command executed successfully', ['class' => $class]);
        return $contents;
    }

    private function makeRequest(AbstractCommand $command, array $payload = []): Request
    {
        $query = array_merge($payload, [
            'command' => $command->getCommandName(),
            'ip' => static::getRemoteIp() ?: 'UNKNOWN',
            'comment' => $payload['comment'] ?? $payload['os'] ?? '',
        ]);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        return new Request($command->getMethod(), '', $headers, urldecode(http_build_query($query)));
    }

    private static function getRemoteIp(): ?string
    {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $a) {
            if ($ip = getenv($a)) {
                return $ip;
            }
        }

        return null;
    }
}
