<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\UnauthorizedException;

class SecurityMiddleware
{
    public function handle($request, Closure $next)
    {
        $value = $request->header("X-Security");
        if (strcmp($value, "myvalue") !== 0) {
            throw new UnauthorizedException("Unauthorized user");
        }

        return $next($request);
    }
}