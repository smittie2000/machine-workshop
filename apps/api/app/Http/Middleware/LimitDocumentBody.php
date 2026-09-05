<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LimitDocumentBody
{
    public function handle(Request $request, Closure $next): Response
    {
        if (strlen($request->getContent()) > 256 * 1024) {
            throw ValidationException::withMessages(['document' => 'Request body must not exceed 256 KiB.']);
        }

        return $next($request);
    }
}
