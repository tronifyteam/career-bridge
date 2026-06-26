<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequestResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only log API routes to avoid cluttering admin panel / web pages
        $isApi = $request->is('api/*');

        if ($isApi) {
            $method = $request->getMethod();
            $url = $request->fullUrl();
            $ip = $request->ip();
            $payload = $request->all();

            // Mask sensitive fields for security
            if (isset($payload['password'])) {
                $payload['password'] = '********';
            }
            if (isset($payload['password_confirmation'])) {
                $payload['password_confirmation'] = '********';
            }

            Log::info("=== [API REQUEST] ===", [
                'method' => $method,
                'url' => $url,
                'ip' => $ip,
                'payload' => $payload,
            ]);
        }

        $response = $next($request);

        if ($isApi) {
            $status = $response->getStatusCode();
            $content = $response->getContent();
            $jsonDecoded = json_decode($content, true);

            Log::info("=== [API RESPONSE] ===", [
                'status' => $status,
                'response' => $jsonDecoded ?? $content,
            ]);
        }

        return $response;
    }
}
