<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        // $tickets = Ticket::where('id_usuario', $id)->get(); // Esto devuelve una colección de tickets
        // El de arriba pero con el estado que sea diferente a 'cancelado'

        //         SELECT r.horario,  CONCAT(c1.nombre, ', ', c1.departamento) as 'origen',
        //        CONCAT(c2.nombre, ', ', c2.departamento) as 'destino'  FROM reservaciones r
        // JOIN viajes v ON r.id_viaje = v.id_viaje
        //                  JOIN ciudades c1 ON v.id_origen = c1.id_ciudad
        //                     JOIN ciudades c2 ON v.id_destino = c2.id_ciudad
        //                                                               JOIN usuarios u ON r.id_usuario = u.user_id
        // WHERE u.user_id = 1 and r.estado = 'confirmado';

        $tickets = DB::table('reservaciones as r')
            ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
            ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
            ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
            ->join('usuarios as u', 'r.id_usuario', '=', 'u.user_id')
            ->select(
                'r.id_reservacion',
                'r.estado',
                'v.horario',
                'r.numero_asiento',
                DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino")
            )
            ->where('u.user_id', $id)
            ->get();

        if ($tickets->isEmpty()) {
            return view('tickets.show', ['tickets' => null]);
        }
        return view('tickets.show', compact('tickets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function cancel(string $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            Log::error("Ticket not found with id: $id");
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        // Actualizar el estado si el ticket no está ya cancelado
        if ($ticket->estado === 'cancelado') {
            Log::warning("Ticket already canceled: $id");
            return response()->json([
                'message' => 'Ticket already canceled'
            ], 400);
        }

        // Intentar guardar el estado cancelado
        try {
            $ticket->estado = 'cancelado';
            $ticket->save();

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Se ha cancelado la reservación con éxito';
            $notificacion->save();

            Log::info("Ticket canceled successfully: $id");
            return response()->json([
                'message' => 'Ticket canceled successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update ticket: " . $e->getMessage());
            return response()->json([
                'message' => 'Error updating ticket'
            ], 500);
        }
    }


    public function showTicket(string $id)
    {
        // $ticket = Ticket::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;

        $ticket = DB::table('reservaciones_detalle as rd')
            ->join('reservaciones as r', 'rd.id_reservacion', '=', 'r.id_reservacion')
            ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
            ->join('tipo_pasajero as tp', 'rd.id_tipo_pasajero', '=', 'tp.id_tipo_pasajero')
            ->select(
                'rd.id_reservacion',
                'v.precio_asiento',
                'rd.cantidad_tipo_pasajero',
                'tp.nombre'
            )
            ->where('r.id_reservacion', $id)
            ->get();

        if (!$ticket) {
            Log::error("Ticket not found with id: $id");
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        //         SELECT v.precio_asiento FROM viajes v
        // JOIN reservaciones r ON v.id_viaje = r.id_viaje
        // WHERE r.id_reservacion = 1;
        $ticketPrice =  DB::table('viajes as v')
            ->join('reservaciones as r', 'v.id_viaje', '=', 'r.id_viaje')
            ->select('v.precio_asiento')
            ->where('r.id_reservacion', $id)
            ->first();
        // dd($ticketPrice);
        return view('tickets.detalles', compact('ticket', 'ticketPrice', 'id'));
    }

    public function payTarjeta(Request $request)
    {
        $id_reservacion = $request->id_reservacion;
        $total = $request->total;
        return view('tickets.tarjeta', compact('total', 'id_reservacion'));
    }

    public function pay(string $id)
    {
        dd($id);
        $ticket = Ticket::find($id);

        if (!$ticket) {
            Log::error("Ticket not found with id: $id");
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        // Actualizar el estado si el ticket no está ya cancelado
        if ($ticket->estado === 'pagado') {
            Log::warning("Ticket already paid: $id");
            return response()->json([
                'message' => 'Ticket already paid'
            ], 400);
        }

        // Intentar guardar el estado pagado
        try {
            $ticket->estado = 'pagado';
            $ticket->save();

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Se ha pagado la reservación con éxito';
            $notificacion->save();

            Log::info("Ticket paid successfully: $id");
            return response()->json([
                'message' => 'Ticket paid successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update ticket: " . $e->getMessage());
            return response()->json([
                'message' => 'Error updating ticket'
            ], 500);
        }
    }

    public function editTicket(string $id)
    {
        // $ticket = Ticket::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;

        $ticket = DB::table('reservaciones_detalle as rd')
            ->join('reservaciones as r', 'rd.id_reservacion', '=', 'r.id_reservacion')
            ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
            ->join('tipo_pasajero as tp', 'rd.id_tipo_pasajero', '=', 'tp.id_tipo_pasajero')
            ->select(
                'rd.id_reservacion',
                'v.precio_asiento',
                'rd.cantidad_tipo_pasajero',
                'tp.nombre'
            )
            ->where('r.id_reservacion', $id)
            ->get();

        if (!$ticket) {
            Log::error("Ticket not found with id: $id");
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        //         SELECT v.precio_asiento FROM viajes v
        // JOIN reservaciones r ON v.id_viaje = r.id_viaje
        // WHERE r.id_reservacion = 1;
        $ticketPrice =  DB::table('viajes as v')
            ->join('reservaciones as r', 'v.id_viaje', '=', 'r.id_viaje')
            ->select('v.precio_asiento')
            ->where('r.id_reservacion', $id)
            ->first();
        // dd($ticketPrice);
        // return view('tickets.edit', compact('ticket', 'ticketPrice'));
        return view('tickets.edit', compact('id'));
    }


    public function changeTicket(Request $request, string $id)
    {

        // dd($request->all());
        // dd($id);
        // $ticket = Ticket::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;
        $ticket = Ticket::find($id);
        // dd($ticket);

        // SELECT c1.id_ciudad as 'c1', c1.nombre as 'origen', c2.id_ciudad as 'c2', c2.nombre as 'destino'
        // FROM viajes v
        //          JOIN ciudades c1 ON v.id_origen = c1.id_ciudad
        //          JOIN ciudades c2 ON v.id_destino = c2.id_ciudad
        // WHERE v.id_viaje = 64261;

        $ciudades = DB::table('viajes as v')
            ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
            ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
            ->select(
                'c1.id_ciudad as c1',
                'c1.nombre as origen',
                'c2.id_ciudad as c2',
                'c2.nombre as destino'
            )
            ->where('v.id_viaje', $ticket->id_viaje)
            ->first();

        //dd($ciudades);

        $viajes = DB::table('viajes as v')
            ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
            ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
            ->select(
                'v.id_viaje',
                'v.horario',
                'v.precio_asiento',
                DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino")
            )
            ->where('v.id_origen', $ciudades->c1)
            ->where('v.id_destino', $ciudades->c2)
            ->where('v.fecha_viaje', $request->fecha)
            ->get();


        //dd($viajes);

        return view('tickets.change', compact('id', 'viajes', 'ciudades'));
    }

    public function updateTicket(Request $request, string $id)
    {
        // dd($request->all());

        $ticket = Ticket::findorFail($id);

        if(!$ticket){
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        }

        $ticket->id_viaje = $request->id_viaje;
        $ticket->save();

         //Notificacion
         $notificacion = new Notificacion();
         $notificacion->id_usuario = Auth::user()->user_id;
         $notificacion->tipo_notificacion = 'recordatorio';
         $notificacion->mensaje = 'Se ha actualizado la reservación con éxito';
         $notificacion->save();


        return response()->json([
            'success' => true,
            'message' => 'Ticket actualizado exitosamente',
            'redirect' => route('tickets.show', ['id' => $ticket->id_usuario])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
