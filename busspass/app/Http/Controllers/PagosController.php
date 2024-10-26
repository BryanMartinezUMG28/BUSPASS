<?php

namespace App\Http\Controllers;

use App\Models\Pagos;
use Illuminate\Http\Request;

class PagosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $pagos = Pagos::all();
        return view('pagos.index', compact('pagos'));
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
