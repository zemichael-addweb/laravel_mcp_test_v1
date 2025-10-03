<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use App\Models\User;

class CustomTokenAuth
{
    /**
     * Handle an incoming request for custom token authentication.
     * 
     * This middleware demonstrates custom authentication for MCP servers
     * where you might have your own token system.
     */
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('Authorization header missing or invalid.');
        }
        
        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
        
        // Custom token validation logic
        $user = $this->validateCustomToken($token);
        
        if (!$user) {
            throw new AuthenticationException('Invalid token provided.');
        }
        
        // Set the authenticated user
        Auth::login($user);
        
        return $next($request);
    }
    
    /**
     * Validate custom token and return associated user.
     * 
     * In a real application, this would validate against your token storage
     * (database, Redis, etc.) and return the associated user.
     */
    private function validateCustomToken(string $token): ?User
    {
        // Example custom token validation
        // In reality, you'd check your token storage system
        
        if ($token === 'test-token-123') {
            // Return a test user or create one
            return User::firstOrCreate([
                'email' => 'test@example.com'
            ], [
                'name' => 'Test User',
                'password' => bcrypt('password')
            ]);
        }
        
        // You might also decode JWT tokens, validate API keys, etc.
        if ($this->isValidJWT($token)) {
            $payload = $this->decodeJWT($token);
            return User::find($payload['user_id'] ?? null);
        }
        
        return null;
    }
    
    /**
     * Check if token is a valid JWT format.
     */
    private function isValidJWT(string $token): bool
    {
        return substr_count($token, '.') === 2;
    }
    
    /**
     * Decode JWT token (simplified example).
     * In production, use a proper JWT library like firebase/php-jwt
     */
    private function decodeJWT(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }
            
            $payload = base64_decode($parts[1]);
            return json_decode($payload, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}

