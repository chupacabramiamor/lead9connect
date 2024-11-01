<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface UseCache
{
    const DROP_CACHE = 1;

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
    public function getCacheTtl(): ?int;
}
