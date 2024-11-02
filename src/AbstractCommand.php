<?php
namespace Chupacabramiamor\Lead9Connect;

abstract class AbstractCommand
{
    protected string $method = 'POST';

    public function __construct(
        private array $data = []
    ) {}

    public static function getErrorMessage($contents = null): ?string
    {
        return $contents->message ?? $contents->error ?? null;
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
