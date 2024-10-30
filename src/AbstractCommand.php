<?php
namespace Chupacabramiamor\Lead9Connect;

abstract class AbstractCommand
{
    protected string $method = 'POST';

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