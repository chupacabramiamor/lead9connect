<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface UseCache
{
    /**
     * Повертає TTL-значення кешування.
     *
     * @return int
     */
    public function getCacheTtl(): ?int;
}
