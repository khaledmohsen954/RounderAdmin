<?php
use Illuminate\Support\Facades\Route;

// Laravel يضيف /api مرة واحدة عبر bootstrap/app.php.
foreach (glob(app_path('Modules/*/Routes/api.php')) as $moduleRoutes) {
    require $moduleRoutes;
}
Route::get('/v1/health', fn () => response()->json(['status' => 'ok', 'app' => 'Rounder']));
