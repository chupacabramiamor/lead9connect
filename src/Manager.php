<?php

namespace Chupacabramiamor\Lead9Connect;

use Chupacabramiamor\Lead9Connect\Contracts\ReplaceResponseData;
use Chupacabramiamor\Lead9Connect\Contracts\UseCache;
use Chupacabramiamor\Lead9Connect\Contracts\UsePointer;
use Chupacabramiamor\Lead9Connect\Exceptions\Lead9Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;

class Manager
{
    const DROP_CACHE = 1;

    public function __construct(
        private string $endpoint
    ) {}

    /**
     * @param string $class
     * @param array $payload
     * @param int $flags
     * @return mixed
     * @throws Lead9Exception
     */
    public function execute(string $class, array $payload = [], int $flags = 0): mixed
    {
        if (!class_exists($class)) {
            throw new Lead9Exception('trying_to_execute_wrong_command');
        }

        $contracts = class_implements($class);

        /** @var AbstractCommand|ReplaceResponseData|UseCache|UsePointer */
        $command = new $class($payload);

        /** @var array|object|null */
        $contents = null;

        if (in_array(UseCache::class, $contracts)) {
            if (($flags & self::DROP_CACHE) === self::DROP_CACHE ) {
                Cache::forget($command->getCacheKey());
            } else {
                $contents = Cache::get($command->getCacheKey());
            }
        }

        if (!$contents) {
            $client = new Client([
                'timeout'  => 15,
                'base_uri' => $this->endpoint,
                'verify'   => false,
            ]);

            $response = $client->send($this->makeRequest($command, $payload));

            if ($response->getStatusCode() >= 400) {
                throw new Lead9Exception();
            }

            $contents = json_decode(trim($response->getBody()->getContents()));

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Lead9Exception('incorrect_data_received');
            }

            if ($command->hasFailure($contents)) {
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
            }
        }

        return $contents;
    }

    private function makeRequest(AbstractCommand $command, array $payload = []): Request
    {
        $query = array_merge($payload, [
            'command' => $command->getCommandName(),
            'ip' => static::getRemoteIp() ?: 'UNKNOWN',
            'comment' => $payload['os'] ?? '',
        ]);

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        return new Request($command->getMethod(), '', $headers, http_build_query($query));
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
