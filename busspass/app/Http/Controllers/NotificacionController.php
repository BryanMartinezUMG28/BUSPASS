<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getNotificaciones($userId)
    {
        $notificacionesUser = DB::table('notificaciones as n')
            ->join('usuarios as u', 'n.id_usuario', '=', 'u.user_id')
            ->where('u.user_id', '=', $userId)
            ->select(
                'n.id_notificacion',
                'n.fecha_envio',
                'n.tipo_notificacion',
                'n.mensaje',
                'n.notificacion_leida'
            )
            ->get();

        return view('notificaciones.shownotificaciones', compact('notificacionesUser'));
    }

    public function marcarLeidas(Request $request)
{
    // Obtener las IDs de las notificaciones seleccionadas
    $notificacionesIds = $request->input('notificaciones');

    if ($notificacionesIds) {
        // Marcar las notificaciones como leídas
        Notificacion::whereIn('id_notificacion', $notificacionesIds)->update(['notificacion_leida' => true]);
    }

    // Redirigir de vuelta con un mensaje de éxito
    return response()->json([
        'success' => true,
        'message' => 'Notificaciones marcadas como leídas',
        'redirect' => route('bus.index')
    ]);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
