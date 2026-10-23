<?php

namespace App\Exceptions;

use RuntimeException;

/** Thrown when something tries to change data while platform support is viewing as another account. */
class ViewOnlyException extends RuntimeException
{
    public function __construct(string $message = 'View only: nothing can be changed while viewing as another account.')
    {
        parent::__construct($message);
    }
}
