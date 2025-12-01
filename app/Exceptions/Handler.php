<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{

    /**
     * Handle unauthenticated requests.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {
            $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'errors' => method_exists($exception, 'errors') ? $exception->errors() : []
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
