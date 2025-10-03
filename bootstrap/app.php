<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Register API routes if they exist
            if (file_exists(base_path('routes/api.php'))) {
                require base_path('routes/api.php');
            }
            
            // Register MCP routes
            if (file_exists(base_path('routes/ai.php'))) {
                require base_path('routes/ai.php');
            }
            
            // Register authenticated MCP routes if file exists
            if (file_exists(base_path('routes/auth-ai.php'))) {
                require base_path('routes/auth-ai.php');
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom MCP middleware
        $middleware->alias([
            'auth.custom.token' => \App\Http\Middleware\CustomTokenAuth::class,
            'subscription.active' => \App\Http\Middleware\SubscriptionActive::class,
            'throttle:mcp' => [\Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1'],
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();