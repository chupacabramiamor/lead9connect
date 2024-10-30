<?php
namespace Chupacabramiamor\Lead9Connect\Exceptions;

use Exception;

class Lead9Exception extends Exception
{
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        $message = empty($message)
            ? 'Невдається закінчити закінчити цю операцію.'
            : $message;

        parent::__construct($message, $code, $previous);
    }
}
