<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for product not found
 */
class ProductNotFoundException extends Exception
{
    protected $code = 404;

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Product Not Found',
            'error' => $this->getMessage(),
        ], $this->code);
    }
}
