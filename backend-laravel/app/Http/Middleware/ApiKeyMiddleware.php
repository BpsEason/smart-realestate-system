<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        try {
            // Check if the API key exists and is active in the database
            if (!$apiKey || !DB::table('api_keys')->where('key', $apiKey)->where('is_active', true)->exists()) {
                Log::warning('Unauthorized access attempt with invalid or missing API key.');
                return response()->json(['error' => '無效或停用的 API 金鑰。'], 401);
            }
        } catch (\Exception $e) {
            // Handle database connection errors
            Log::error('Database error during API key validation: ' . $e->getMessage());
            return response()->json(['error' => '伺服器內部錯誤，無法驗證 API 金鑰。'], 500);
        }

        return $next($request);
    }
}
