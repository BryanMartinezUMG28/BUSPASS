<?php

namespace App\Http\Controllers;

use App\Models\Ciudades;
use App\Models\Notificacion;
use App\Models\Reservacion;
use App\Models\ReservacionDetalle;
use Illuminate\Http\Request;
use App\Models\Viaje;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $viajes = Viaje::all();
        return view('viajes.index', compact('viajes'));
    }

    public function consultar()
    {
        //
        $ciudades = Ciudades::all();
        return view('viajes.consultar', compact('ciudades'));
    }

    public function resultado(Request $request)
    {
        //

        // $viajes = Viaje::where('id_origen', $request->origen)
        //     ->where('id_destino', $request->destino)
        //     ->get();

        //         SELECT v.horario, v.precio_asiento, CONCAT( c1.nombre, ', ', c1.departamento ) as 'origen', CONCAT( c2.nombre, ', ', c2.departamento ) as 'destino' FROM viajes v
        // JOIN ciudades c1 ON v.id_origen = c1.id_ciudad
        // JOIN ciudades c2 ON v.id_destino = c2.id_ciudad
        // WHERE v.id_origen=1 AND v.id_destino=15;

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
            ->where('v.id_origen', $request->origen)
            ->where('v.id_destino', $request->destino)
            ->where('v.fecha_viaje', $request->fecha)
            ->get();
        return view('viajes.resultado', compact('viajes'));
    }

    // public function reservar(string $id)
    // {
    //     //
    //     $viaje = Viaje::find($id);
    //     $tipo_pasajero = ['Adulto', 'Niño', 'Adulto Mayor'];

    //     return view('viajes.reservar', compact('viaje', 'tipo_pasajero'));
    // }

    

    public function viaje(string $id)
    {
        //
        $viaje = Viaje::find($id);
        // return view('viajes.show', compact('viaje'));
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
