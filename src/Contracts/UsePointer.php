<?php

namespace Chupacabramiamor\Lead9Connect\Contracts;

interface UsePointer
{
    /**
     * Формування даних на основі поінтера - ключа, значення якого
     * містить корисні дані у відповіді з сервера
     *
     * @return string
     */
    public function pointer(): string;
}
