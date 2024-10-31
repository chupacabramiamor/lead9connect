<?php
namespace Chupacabramiamor\Lead9Connect;

abstract class AbstractCommand
{
    protected string $method = 'POST';

    public static function getErrorMessage($contents = null): ?string
    {
        return $contents->message ?? $contents->error ?? null;
    }

    /**
     * Повертає назву ключа який відповідає за кешування даних.
     *
     * @return ?string
     */
    public function configCacheKey(): ?string
    {
        return null;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCommandName(): string
    {
        $segments = explode('\\', static::class);
        return lcfirst(end($segments));
    }

	public function hasFailure($contents): bool
	{
		return empty($contents->success);
	}
}
