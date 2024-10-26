<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boleto;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Validaciones;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoletoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $boletos = Boleto::all();
        return view('boletos.index', compact('boletos'));
    }

    public function showTickets(string $id)
    {
        $tickets = $this->validarBoletos(Auth::user()->user_id);

        //dd($tickets);

        // Validar los tickets por fecha y hora


        return view('tickets.show', compact('tickets'));
    }

    public function validarBoletos($userId)
    {
        $boletos = DB::table('boletos as b')
            ->join('reservaciones as r', 'b.id_reservacion', '=', 'r.id_reservacion')
            ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
            ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
            ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
            ->join('usuarios as u', 'r.id_usuario', '=', 'u.user_id')
            ->where('u.user_id', '=', $userId)
            ->select(
                'b.id_boleto',
                'b.fecha_compra',
                'b.estado_pago',
                'b.boleto_cancelado',
                DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino"),
                'v.fecha_viaje',
                'v.horario',
                'r.numero_asiento'
            )
            ->get();

        $now = Carbon::now(); // Obtener la fecha y hora actual
        $resultados = [];

        foreach ($boletos as $boleto) {
            if($boleto->boleto_cancelado){
                $boleto->estado_validez = 'cancelado';
                $resultados[] = $boleto;
                continue;
            }
            // Combinar la fecha del viaje y el horario en un solo objeto Carbon
            $fechaViajeCompleta = Carbon::parse($boleto->fecha_viaje . ' ' . $boleto->horario);

            // Verificar si el boleto está caducado o válido
            if ($now->greaterThan($fechaViajeCompleta)) {
                $boleto->estado_validez = 'caducado';
                // $validacion = new Validaciones();
                // $validacion->id_boleto = $boleto->id_boleto;
                // $validacion->fecha_validacion = $now;
                // $validacion->estado_ticket = 'caducado';
                // $validacion->save();
            } else {
                $boleto->estado_validez = 'válido';
                // $validacion = new Validaciones();
                // $validacion->id_boleto = $boleto->id_boleto;
                // $validacion->fecha_validacion = $now;
                // $validacion->estado_ticket = 'válido';
                // $validacion->save();
            }

            $resultados[] = $boleto;
        }

        return $resultados; // Puedes retornar los resultados o hacer algo con ellos
    }

    public function cancelTicket(string $id)
    {
        $ticket = Boleto::find($id);

        if (!$ticket) {
            return response()->json([
                'message' => 'Ticket no encontrado'
            ], 404);
        }

        if ($ticket->boleto_cancelado) {
            return response()->json([
                'message' => 'El ticket ya ha sido cancelado'
            ], 400);
        }

        try {
            $ticket->boleto_cancelado = true;
            $ticket->save();

            //Notificacion
            $notificacion = new Notificacion();
            $notificacion->id_usuario = Auth::user()->user_id;
            $notificacion->tipo_notificacion = 'recordatorio';
            $notificacion->mensaje = 'Se ha cancelado con éxito el boleto';
            $notificacion->save();

            Log::info("Ticket canceled successfully: $id");
            return response()->json([
                'message' => 'Ticket cancelada con éxito'
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Error al cancelar el ticket'
            ], 500);
        }
    }

    public function generateQR($id) {
        $ticket = Boleto::find($id);
        $ticketUrl = route('tickets.showDetails', ['id' => $ticket->id_boleto]); // URL del detalle del ticket
        $qrCode = QrCode::size(200)->generate($ticketUrl); // Genera el QR
        
        return response()->json(['qrCode' => base64_encode($qrCode)]);
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
//         SELECT
//     b.id_boleto,
//     b.fecha_compra,
//     b.estado_pago,
//     b.boleto_cancelado,
//     CONCAT(c1.nombre, ', ', c1.departamento) AS origen,
//     CONCAT(c2.nombre, ', ', c2.departamento) AS destino,
//     v.fecha_viaje,
//     v.horario,
//     r.numero_asiento
// FROM boletos b
// JOIN reservaciones r ON b.id_reservacion = r.id_reservacion
// JOIN viajes v ON r.id_viaje = v.id_viaje
// JOIN ciudades c1 ON v.id_origen = c1.id_ciudad
// JOIN ciudades c2 ON v.id_destino = c2.id_ciudad
// JOIN usuarios u ON r.id_usuario = u.user_id
// WHERE b.id_boleto = 1;
        $ticket = DB::table('boletos as b')
            ->join('reservaciones as r', 'b.id_reservacion', '=', 'r.id_reservacion')
            ->join('viajes as v', 'r.id_viaje', '=', 'v.id_viaje')
            ->join('ciudades as c1', 'v.id_origen', '=', 'c1.id_ciudad')
            ->join('ciudades as c2', 'v.id_destino', '=', 'c2.id_ciudad')
            ->join('usuarios as u', 'r.id_usuario', '=', 'u.user_id')
            ->where('b.id_boleto', '=', $id)
            ->select(
                'b.id_boleto',
                'b.fecha_compra',
                'b.estado_pago',
                'b.boleto_cancelado',
                DB::raw("CONCAT(c1.nombre, ', ', c1.departamento) as origen"),
                DB::raw("CONCAT(c2.nombre, ', ', c2.departamento) as destino"),
                'v.fecha_viaje',
                'v.horario',
                'r.numero_asiento'
            )
            ->first();

        if (!$ticket) {
            return response()->json([
                'message' => 'Boleto no encontrado'
            ], 404);
        }

        return view('tickets.ticketview', compact('ticket'));
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
