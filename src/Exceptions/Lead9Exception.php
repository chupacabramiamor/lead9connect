<?php
namespace Chupacabramiamor\Lead9Connect\Exceptions;

use Exception;

class Lead9Exception extends Exception
{
    public function __construct($message = 'Невдається закінчити закінчити цю операцію.', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
