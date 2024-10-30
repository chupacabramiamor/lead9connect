<?php

namespace Chupacabramiamor\Lead9Connect;

use Chupacabramiamor\Lead9Connect\Contracts\ReplaceResponseData;
use Chupacabramiamor\Lead9Connect\Contracts\UseCache;
use Chupacabramiamor\Lead9Connect\Exceptions\Lead9Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;

class Manager
{
    public function __construct(
        private string $endpoint
    ) {}

    /**
     * @param string $class
     * @param array $params
     * @return mixed
     * @throws Lead9Exception
     */
    public function execute(string $class, array $params = []): mixed
    {
        if (!class_exists($class)) {
            throw new Lead9Exception('executing_the_wrong_command');
        }

        $contracts = class_implements($class);

        $data = null;

        if (in_array(UseCache::class, $contracts)) {
            $data = Cache::get($class::configCacheKey());
        }

        if (!$data) {
            /** @var AbstractCommand|ReplaceResponseData|UseCache */
            $command = new $class();

            $client = new Client([
                'timeout'  => 15,
                'base_uri' => $this->endpoint,
                'verify'   => false,
            ]);

            $response = $client->send($this->makeRequest($command, $params));

            if ($response->getStatusCode() >= 400) {
                throw new Lead9Exception();
            }

            $contents = json_decode(trim($response->getBody()->getContents()));

            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Lead9Exception('incorrect_data_received');
            }

            if ($command->hasFailure($contents)) {
                throw new Lead9Exception($contents->message ?? $contents->error ?? '');
            }

            if (in_array(ReplaceResponseData::class, $contracts)) {
                $data = $command->replace($contents);
            }

            if (in_array(UseCache::class, $contracts)) {
                Cache::put($class::configCacheKey(), $data, $class::configCacheTll());
            }
        }

        return $data;
    }

    private function makeRequest(AbstractCommand $command, array $params = []): Request
    {
        $query = array_merge($params, [
            'command' => $command->getCommandName(),
            'ip' => static::getRemoteIp() ?: 'UNKNOWN',
            'comment' => $params['os'] ?? '',
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
