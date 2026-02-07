<?php
namespace Chupacabramiamor\Lead9Connect;

abstract class AbstractCommand
{
    protected string $method = 'POST';

    protected array $data = [];

    public function __construct(array $data = []) {
        $this->data = array_merge($this->initialData(), $data);
    }

    public static function initialData(): array
    {
        return [];
    }

    public static function hasFailed($contents): bool
    {
        return empty($contents->success);
    }

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

    public function getData(): array
    {
        return $this->data;
    }

    public function verify(): bool
    {
        return true;
    }

    /** @deprecated */
	public function hasFailure($contents): bool
	{
		return empty($contents->success);
	}
}
