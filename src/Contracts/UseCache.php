<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface UseCache
{
    /**
     * Повертає назву ключа який відповідає за кешування даних.
     *
     * @return string
     */
    public static function configCacheKey(): string;

    /**
     * Повертає TTL-значення кешування.
     *
     * @return int
     */
    public static function configCacheTll(): int;
}
