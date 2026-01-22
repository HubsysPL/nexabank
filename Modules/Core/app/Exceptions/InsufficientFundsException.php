<?php

namespace Modules\Core\Exceptions;

use Exception;

class InsufficientFundsException extends Exception
{
    protected $message = 'Niewystarczające środki na rachunku do wykonania tej operacji.';
}
