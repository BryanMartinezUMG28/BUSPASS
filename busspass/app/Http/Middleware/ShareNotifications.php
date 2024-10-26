<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacion;

class ShareNotifications
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            // $notificaciones = Notificacion::where('id_usuario', Auth::user()->user_id)->get();
            $notificaciones = Notificacion::where('id_usuario', Auth::user()->user_id)->where('notificacion_leida', false)->get();
            view()->share('notificaciones', $notificaciones);
        }

        return $next($request);
    }
}
