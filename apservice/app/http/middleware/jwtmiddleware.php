<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\DB;

class JWTMiddleware
{
    public function handle($request, Closure $next)
    {
        // List of routes that should be accessible without authentication
        $publicRoutes = [
            'auth', 'send_email', 'check_token', 'check_db', 'z_download', 'dashboard/dashboardguests'
        ];

        // If accessing a public route, skip authentication
        if (in_array($request->path(), $publicRoutes)) {
            return $next($request);
        }

        if (!$request->header('Authorization')) {
            return response()->json([
                'success' => 'false',
                'message' => 'Authorization Token not found'
            ], 400);
        }

        try {
            // Extract Bearer token
            $token = $request->header('Authorization');
            if (strpos($token, 'Bearer ') === 0) {
                $token = str_replace('Bearer ', '', $token);
            } else {
                return response()->json([
                    'success' => 'false',
                    'message' => 'Authorization Token format is invalid'
                ], 400);
            }

            // Validate token
            $payload = JWTAuth::parseToken()->getPayload()->toArray();

            if (!isset($payload['sub'])) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'User not found in token'
                ], 404);
            }

            // Validate user from database
            $user = DB::table('a_user')
                ->where('useractive', 'YES')
                ->where('userlogin', $payload['sub'])
                ->first();

            if (empty($user)) {
                return response()->json([
                    'success' => 'false',
                    'message' => 'User not found'
                ], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Token has expired'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Token is invalid'
            ], 401);
        }

        return $next($request);
    }
}
