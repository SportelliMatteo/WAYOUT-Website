<?php

namespace App\Exceptions;

use RuntimeException;

class PhoneVerificationException extends RuntimeException
{
    // Intentionally generic: Firebase details must not be exposed to the browser.
}
