<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for insufficient stock
 */
class InsufficientStockException extends Exception
{
    protected $code = 422;

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient Stock',
            'error' => $this->getMessage(),
        ], $this->code);
    }
}
