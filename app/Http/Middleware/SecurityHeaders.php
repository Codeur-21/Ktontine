<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        return $this->apply($request, $next($request));
    }

    public function apply(Request $request, Response $response): Response
    {
        // Alpine expressions and the existing inline scripts still require these CSP exceptions.
        $scriptSources = "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com";
        $styleSources = "'self' 'unsafe-inline'";
        $connectSources = "'self'";
        if (app()->environment('local')) {
            $scriptSources .= ' http: https:';
            $styleSources .= ' http: https:';
            $connectSources .= ' http: https: ws: wss:';
        }
        $policy = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            'script-src '.$scriptSources,
            'style-src '.$styleSources,
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            'connect-src '.$connectSources,
            "worker-src 'self' blob:",
            "manifest-src 'self'",
        ];
        if (app()->isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
            $policy[] = 'upgrade-insecure-requests';
        }
        $response->headers->set('Content-Security-Policy', implode('; ', $policy));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
