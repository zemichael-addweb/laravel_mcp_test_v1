<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionActive
{
    /**
     * Handle an incoming request to check subscription status.
     * 
     * This middleware ensures the user has an active subscription
     * before accessing premium MCP features.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Authentication required to access premium features.'
            ], Response::HTTP_UNAUTHORIZED);
        }
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'error' => 'Active subscription required to access this MCP server.',
                'upgrade_url' => '/subscription/upgrade'
            ], Response::HTTP_PAYMENT_REQUIRED);
        }
        
        return $next($request);
    }
    
    /**
     * Check if user has active subscription.
     * 
     * In a real application, this would check your billing system,
     * subscription database, or third-party service like Stripe.
     */
    private function hasActiveSubscription($user): bool
    {
        // Example subscription check
        // In reality, you'd check against your billing system
        
        // Check if user has subscription_status property/column
        if (property_exists($user, 'subscription_status')) {
            return in_array($user->subscription_status, ['active', 'premium', 'pro']);
        }
        
        // Check subscription relationship (if using Cashier or similar)
        if (method_exists($user, 'subscribed')) {
            return $user->subscribed('default') || $user->subscribed('premium');
        }
        
        // Fallback: check for any subscription-related field
        return $user->is_premium ?? false;
    }
}

