
protected $middleware = [
    // ... middleware lainnya
    \Illuminate\Http\Middleware\HandleCors::class,
];

protected $middlewareGroups = [
    'web' => [
        // ... middleware web lainnya
    ],

    'api' => [
        \Illuminate\Http\Middleware\HandleCors::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];

protected $middlewareAliases = [
    // ... middleware aliases lainnya
    'role' => \App\Http\Middleware\RoleMiddleware::class, 
];