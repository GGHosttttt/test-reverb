<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('HandleCors Middleware', [
            'path' => $request->path(),
            'method' => $request->method(),
        ]);

        $corsConfig = Config::get('cors');
        $paths = $corsConfig['paths'] ?? [];
        $requestPath = trim($request->path(), '/');
        $shouldApplyCors = false;

        foreach ($paths as $path) {
            if ($path === '*' || preg_match('#^' . str_replace('*', '.*', $path) . '$#', $requestPath)) {
                $shouldApplyCors = true;
                break;
            }
        }

        if (!$shouldApplyCors) {
            return $next($request);
        }

        $response = $request->isMethod('OPTIONS') ? response()->json([], 200) : $next($request);

        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', implode(',', $corsConfig['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']));
        $response->headers->set('Access-Control-Allow-Headers', implode(',', $corsConfig['allowed_headers'] ?? ['Content-Type', 'Authorization', '*']));
        $response->headers->set('Access-Control-Max-Age', $corsConfig['max_age'] ?? 0);

        if (!empty($corsConfig['exposed_headers'])) {
            $response->headers->set('Access-Control-Expose-Headers', implode(',', $corsConfig['exposed_headers']));
        }

        return $response;
    }
}
