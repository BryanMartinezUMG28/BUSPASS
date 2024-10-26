<?php

namespace App\Http\Controllers;

use App\Mail\TicketPurchased;
use App\Models\Boleto;
use Illuminate\Http\Request;
use App\Models\Reservacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Notificacion;
use App\Models\Pagos;
use App\Models\ReservacionDetalle;
use App\Models\Viaje;
use Illuminate\Support\Facades\Mail;

class ReservacionController extends Controller
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
        // $reservacions = Reservación::where('id_usuario', $id)->get(); // Esto devuelve una colección de Reservacións
        // El de arriba pero con el estado que sea diferente a 'cancelado'

        //         SELECT r.horario,  CONCAT(c1.nombre, ', ', c1.departamento) as 'origen',
        //        CONCAT(c2.nombre, ', ', c2.departamento) as 'destino'  FROM reservaciones r
        // JOIN viajes v ON r.id_viaje = v.id_viaje
        //                  JOIN ciudades c1 ON v.id_origen = c1.id_ciudad
        //                     JOIN ciudades c2 ON v.id_destino = c2.id_ciudad
        //                                                               JOIN usuarios u ON r.id_usuario = u.user_id
        // WHERE u.user_id = 1 and r.estado = 'confirmado';

        $reservaciones = DB::table('reservaciones as r')
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

        if ($reservaciones->isEmpty()) {
            return view('reservaciones.show', ['reservaciones' => null]);
        }
        return view('reservaciones.show', compact('reservaciones'));
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
        $reservacion = Reservacion::find($id);

        if (!$reservacion) {
            Log::error("Reservación not found with id: $id");
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        // Actualizar el estado si el Reservación no está ya cancelado
        if ($reservacion->estado === 'cancelado') {
            Log::warning("Reservación already canceled: $id");
            return response()->json([
                'message' => 'Reservación ya cancelada'
            ], 400);
        }

        // Intentar guardar el estado cancelado
        try {
            $reservacion->estado = 'cancelado';
            $reservacion->save();

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Se ha cancelado la reservación con éxito';
            $notificacion->save();

            Log::info("Reservación canceled successfully: $id");
            return response()->json([
                'message' => 'Reservación cancelada con éxito'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update Reservación: " . $e->getMessage());
            return response()->json([
                'message' => 'Error actualizando reservación'
            ], 500);
        }
    }


    public function showReservacion(string $id)
    {
        // $reservacion = Reservación::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;

        $reservacion = DB::table('reservaciones_detalle as rd')
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

        if (!$reservacion) {
            Log::error("Reservación not found with id: $id");
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        //         SELECT v.precio_asiento FROM viajes v
        // JOIN reservaciones r ON v.id_viaje = r.id_viaje
        // WHERE r.id_reservacion = 1;
        $reservacionPrice =  DB::table('viajes as v')
            ->join('reservaciones as r', 'v.id_viaje', '=', 'r.id_viaje')
            ->select('v.precio_asiento')
            ->where('r.id_reservacion', $id)
            ->first();
        // dd($reservacionPrice);
        return view('reservaciones.detalles', compact('reservacion', 'reservacionPrice', 'id'));
    }

    public function payTarjeta(Request $request)
    {
        $id_reservacion = $request->id_reservacion;
        $total = $request->total;
        return view('reservaciones.tarjeta', compact('total', 'id_reservacion'));
    }

    public function payDetails(Request $request, string $id)
    {
        // dd($request->all());
        $reservacion = Reservacion::find($id);

        if (!$reservacion) {
            Log::error("Reservación not found with id: $id");
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        // Actualizar el estado si el Reservación no está ya cancelado
        if ($reservacion->estado === 'pagado') {
            Log::warning("Reservación already paid: $id");
            return response()->json([
                'message' => 'Reservación ya pagada'
            ], 400);
        }

        // Intentar guardar el estado pagado
        try {
            $reservacion->estado = 'confirmado';
            $reservacion->save();

            // Registrar el boleto
            $boleto = new Boleto();
            //             `id_reservacion` int DEFAULT NULL,
            //   `fecha_compra` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            //   `metodo_pago` enum('tarjeta','sucursal') NOT NULL,
            //   `estado_pago` enum('pagado','pendiente') DEFAULT 'pendiente',

            $boleto->id_reservacion = $id;
            $boleto->metodo_pago = 'tarjeta';
            $boleto->estado_pago = 'pagado';
            $boleto->save();

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Boleto comprado con éxito';
            $notificacion->save();


            // Registrar los pagos
            $pagos = new Pagos();
            //             `id_boleto` int DEFAULT NULL,
            //   `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            //   `cantidad` decimal(10,2) DEFAULT NULL,
            //   `metodo_pago` enum('tarjeta','sucursal') DEFAULT NULL,
            //   `estado` enum('confirmado','pendiente') DEFAULT 'pendiente',

            $pagos->id_boleto = $boleto->id_boleto;
            $pagos->cantidad = $request->total;
            $pagos->metodo_pago = 'tarjeta';
            $pagos->estado = 'confirmado';
            $pagos->save();

            // Enviar boleto por email
            $boleto = DB::table('boletos as b')
                ->join('reservaciones as r', 'b.id_reservacion', '=', 'r.id_reservacion')
                ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
                ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
                ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
                ->join('usuarios as u', 'r.id_usuario', '=', 'u.user_id')
                ->where('b.id_boleto', '=', $boleto->id_boleto)
                ->select(
                    'b.id_boleto',
                    'b.fecha_compra',
                    'b.estado_pago',
                    'b.boleto_cancelado',
                    DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                    DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino"),
                    'v.fecha_viaje',
                    'v.horario',
                    'r.numero_asiento',
                    'u.user_id',
                    'u.nombre',
                    'u.correo_electronico'
                )
                ->first();

            $this->sendTicket($boleto);

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Pago realizado con éxito';
            $notificacion->save();




            Log::info("Reservación paid successfully: $id");
            return response()->json([
                'success' => true,
                'message' => 'Reservación pagada con éxito',
                'redirect' => route('bus.index')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update Reservación: " . $e->getMessage());
            return response()->json([
                'message' => 'Error actualizando reservación'
            ], 500);
        }
    }


    public function payCash(Request $request, string $id)
    {
        //dd($request->all());
        $reservacion = Reservacion::find($id);

        if (!$reservacion) {
            Log::error("Reservación not found with id: $id");
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        // Actualizar el estado si el Reservación no está ya cancelado
        if ($reservacion->estado === 'pagado') {
            Log::warning("Reservación already paid: $id");
            return response()->json([
                'message' => 'Reservación ya pagada'
            ], 400);
        }

        // Intentar guardar el estado pagado
        try {
            $reservacion->estado = 'confirmado';
            $reservacion->save();

            // Registrar el boleto
            $boleto = new Boleto();
            //             `id_reservacion` int DEFAULT NULL,
            //   `fecha_compra` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            //   `metodo_pago` enum('tarjeta','sucursal') NOT NULL,
            //   `estado_pago` enum('pagado','pendiente') DEFAULT 'pendiente',

            $boleto->id_reservacion = $id;
            $boleto->metodo_pago = 'sucursal';
            $boleto->estado_pago = 'pendiente';
            $boleto->save();



            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Boleto comprado con éxito';
            $notificacion->save();


            // Registrar los pagos
            $pagos = new Pagos();
            //             `id_boleto` int DEFAULT NULL,
            //   `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            //   `cantidad` decimal(10,2) DEFAULT NULL,
            //   `metodo_pago` enum('tarjeta','sucursal') DEFAULT NULL,
            //   `estado` enum('confirmado','pendiente') DEFAULT 'pendiente',

            $pagos->id_boleto = $boleto->id_boleto;
            $pagos->cantidad = $request->total;
            $pagos->metodo_pago = 'sucursal';
            $pagos->estado = 'pendiente';
            $pagos->save();

            // Enviar boleto por email
            $boleto = DB::table('boletos as b')
                ->join('reservaciones as r', 'b.id_reservacion', '=', 'r.id_reservacion')
                ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
                ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
                ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
                ->join('usuarios as u', 'r.id_usuario', '=', 'u.user_id')
                ->where('b.id_boleto', '=', $boleto->id_boleto)
                ->select(
                    'b.id_boleto',
                    'b.fecha_compra',
                    'b.estado_pago',
                    'b.boleto_cancelado',
                    DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                    DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino"),
                    'v.fecha_viaje',
                    'v.horario',
                    'r.numero_asiento',
                    'u.user_id',
                    'u.nombre',
                    'u.correo_electronico'
                )
                ->first();

            $this->sendTicket($boleto);



            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Boleto realizado con éxito, se pagará en sucursal';
            $notificacion->save();


            Log::info("Reservación paid successfully: $id");
            return response()->json([
                'success' => true,
                'message' => 'Boleto realizado con éxito, se pagará en sucursal',
                'redirect' => route('bus.index')
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update Reservación: " . $e->getMessage());
            return response()->json([
                'message' => 'Error actualizando reservación'
            ], 500);
        }
    }

    public function sendTicket($ticket)
    {
        if (!empty($ticket->correo_electronico)) {
            Log::info("Enviando ticket a: {$ticket->correo_electronico}");

            try {
                Mail::to($ticket->correo_electronico)->send(new TicketPurchased($ticket));
                Log::info("Correo enviado a {$ticket->correo_electronico}");
            } catch (\Exception $e) {
                Log::error('Error enviando el correo: ' . $e->getMessage());
            }
        } else {
            Log::warning("El usuario con ID {$ticket->user_id} no tiene un correo electrónico registrado.");
        }
    }




    public function editReservacion(string $id)
    {
        // $reservacion = Reservación::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;

        $reservacion = DB::table('reservaciones_detalle as rd')
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

        if (!$reservacion) {
            Log::error("Reservación not found with id: $id");
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        //         SELECT v.precio_asiento FROM viajes v
        // JOIN reservaciones r ON v.id_viaje = r.id_viaje
        // WHERE r.id_reservacion = 1;
        $reservacionPrice =  DB::table('viajes as v')
            ->join('reservaciones as r', 'v.id_viaje', '=', 'r.id_viaje')
            ->select('v.precio_asiento')
            ->where('r.id_reservacion', $id)
            ->first();
        // dd($reservacionPrice);
        // return view('Reservacións.edit', compact('Reservación', 'ReservaciónPrice'));
        return view('reservaciones.edit', compact('id'));
    }


    public function changeReservacion(Request $request, string $id)
    {

        // dd($request->all());
        // dd($id);
        // $reservacion = Reservacion::find($id);
        //         SELECT rd.id_reservacion, v.precio_asiento, rd.cantidad_tipo_pasajero, tp.nombre
        // FROM reservaciones_detalle rd
        //          JOIN reservaciones r ON rd.id_reservacion = r.id_reservacion
        //          JOIN viajes v ON r.id_viaje = v.id_viaje
        //          JOIN tipo_pasajero tp on rd.id_tipo_pasajero = tp.id_tipo_pasajero
        // where r.id_reservacion = 1;
        $reservacion = Reservacion::find($id);

        if (!$reservacion) {
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }
        // dd($reservacion);

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
            ->where('v.id_viaje', $reservacion->id_viaje)
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

        return view('reservaciones.change', compact('id', 'viajes', 'ciudades'));
    }


    public function updateReservacion(Request $request, string $id)
    {
        // dd($request->all());

        $reservacion = Reservacion::findorFail($id);

        if (!$reservacion) {
            return response()->json([
                'message' => 'Reservación no encontrada'
            ], 404);
        }

        $reservacion->id_viaje = $request->id_viaje;
        $reservacion->save();

        //Notificacion
        $notificacion = new Notificacion();
        $notificacion->id_usuario = Auth::user()->user_id;
        $notificacion->tipo_notificacion = 'recordatorio';
        $notificacion->mensaje = 'Se ha actualizado la reservación con éxito';
        $notificacion->save();


        return response()->json([
            'success' => true,
            'message' => 'Reservación actualizada con éxito',
            'redirect' => route('reservaciones.show', ['id' => $reservacion->id_usuario])
        ]);
    }

    public function saveReservacion(Request $request)
    {
        //
        //    dd($request->all());

        $request->validate([
            'horario' => 'required',
            'id_viaje' => 'required',
        ]);
        // Inicializamos las cantidades
        $cantidad_adultos = 0;
        $cantidad_niños = 0;
        $cantidad_tercera_edad = 0;

        // Obtén los tipos de pasajero y sus cantidades
        $tipos = $request->input('tipo');
        $cantidades = $request->input('cantidad');

        // Itera sobre los tipos y las cantidades para asignarlas
        foreach ($tipos as $index => $tipo) {
            $cantidad = $cantidades[$index];

            // Clasificamos la cantidad según el tipo de pasajero
            if ($tipo == 'Adulto') {
                $cantidad_adultos += $cantidad;
            } elseif ($tipo == 'Niño') {
                $cantidad_niños += $cantidad;
            } elseif ($tipo == 'Adulto Mayor') {
                $cantidad_tercera_edad += $cantidad;
            }
        }
        try {
            // Crear una nueva reservación y asignar los valores procesados
            $reservacion = new Reservacion();
            $reservacion->id_usuario = Auth::user()->user_id; // Asume que tienes un campo 'user_id' en tu tabla de usuarios
            $reservacion->fecha_reservacion = now(); // Lo mismo para este campo
            $reservacion->numero_asiento = '1, 2, 3'; // Y este también
            $reservacion->codigo_qr = '123456'; // Puedes generar un código QR único si lo prefieres
            $reservacion->id_viaje = $request->id_viaje; // Este campo debería venir del formulario
            $reservacion->save();

            $detalle = new ReservacionDetalle();
            $detalle->id_reservacion = $reservacion->id_reservacion;
            $detalle->id_tipo_pasajero = 1; // Asume que el tipo de pasajero 'Adulto' tiene un ID de 1
            $detalle->cantidad_tipo_pasajero = $cantidad_adultos;
            $detalle->save();

            $detalle = new ReservacionDetalle();
            $detalle->id_reservacion = $reservacion->id_reservacion;
            $detalle->id_tipo_pasajero = 2; // Asume que el tipo de pasajero 'Niño' tiene un ID de 2
            $detalle->cantidad_tipo_pasajero = $cantidad_niños;
            $detalle->save();

            $detalle = new ReservacionDetalle();
            $detalle->id_reservacion = $reservacion->id_reservacion;
            $detalle->id_tipo_pasajero = 3; // Asume que el tipo de pasajero 'Adulto Mayor' tiene un ID de 3
            $detalle->cantidad_tipo_pasajero = $cantidad_tercera_edad;
            $detalle->save();

            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Reservación realizada con éxito';
            $notificacion->save();

            // Puedes redirigir o mostrar un mensaje de éxito aquí
            return response()->json([
                'success' => true,
                'message' => 'Reservación realizada con éxito',
                'redirect' => route('bus.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo realizar la reservación',
            ], 401);
        }
    }

    public function reservar(string $id)
    {
        //
        $viaje = Viaje::find($id);
        $tipo_pasajero = ['Adulto', 'Niño', 'Adulto Mayor'];

        return view('viajes.reservar', compact('viaje', 'tipo_pasajero'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
