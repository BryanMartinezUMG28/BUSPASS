<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery\Matcher\Not;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmEmail;
use App\Models\User;
use Illuminate\Support\Str;

class IndexController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return view('index');
    }

    // public function sendEmail(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:usuarios,correo_electronico'
    //     ]);

    //     $user = User::where('correo_electronico', $request->email)->first();

    //     if(!$user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'El email no está registrado',
    //         ], 404);
    //     }

    //     //Send email confirmation
    //     Mail::to($user->correo_electronico)->send(new ConfirmEmail($user->correo_electronico));

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Email enviado exitosamente',
    //     ]);
    // }

    public function indexAdmin()
    {
        //

        return view('admin.index');
    }

    public function sendEmail()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'El email no está registrado',
            ], 404);
        }

        // Generar un token de verificación
        $token = Str::random(60);

        // Guardar el token en el usuario
        if ($user->saveConfirmEmailToken($token)) {
            // Enviar el correo electrónico con el token
            Mail::to($user->correo_electronico)->send(new ConfirmEmail($user, $token));

            return response()->json([
                'success' => true,
                'message' => 'Email enviado exitosamente',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo enviar el correo electrónico.',
        ]);
    }


    public function verifyEmail($token)
    {
        $user = User::where('email_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Token inválido o expirado.');
        }

        $user->email_confirmado = 1;
        $user->email_token = null;  // Remover el token después de la confirmación
        $user->save();

        // Si todo es exitoso, devolver la vista con el mensaje
        return view('usuarios.confirm-email', [
            'success' => true,
            'message' => '¡Correo electrónico confirmado exitosamente!',
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
