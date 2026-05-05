<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMenuAccess
{
    public function handle(Request $request, Closure $next)
    {
        $menu = config('adminlte.menu');
        
        $filteredMenu = array_filter($menu, function($item) {
            if (isset($item['restricted_to'])) {
                return auth()->check() && auth()->user()->email === $item['restricted_to'];
            }
            return true;
        });

        config(['adminlte.menu' => array_values($filteredMenu)]);
        
        return $next($request);
    }
}