<?php
namespace Chupacabramiamor\Lead9Connect;

abstract class AbstractCommand
{
    protected string $method = 'POST';

    public function __construct(
        protected int $flags = 0
    ) {}

    public static function getErrorMessage(): ?string
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
