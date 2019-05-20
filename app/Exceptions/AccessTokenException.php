<?php

namespace App\Exceptions;

use Exception;

class AccessTokenException extends Exception            //TODO::删了这个没用的东西
{
    //
    protected $message = 'Receive accessToken failed.';
}
