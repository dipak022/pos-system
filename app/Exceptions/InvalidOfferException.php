<?php

namespace App\Exceptions;

use Exception;



/**
 * Exception for invalid offer configuration
 */
class InvalidOfferException extends Exception
{
    protected $code = 422;

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Invalid Offer Configuration',
            'error' => $this->getMessage(),
        ], $this->code);
    }
}

