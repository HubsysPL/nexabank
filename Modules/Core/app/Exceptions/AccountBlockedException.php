<?php

namespace Modules\Core\Exceptions;

use Exception;

class AccountBlockedException extends Exception
{
    protected $message = 'Konto jest zablokowane. Operacja nie może zostać wykonana.';
}
