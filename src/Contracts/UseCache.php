<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface UseCache
{
    /**
     * Повертає назву ключа який відповідає за кешування даних.
     *
     * @return ?string
     */
    public function getCacheKey(): string;

    /**
     * Повертає TTL-значення кешування.
     *
     * @return int
     */
    public function getCacheTtl(): ?int;
}
