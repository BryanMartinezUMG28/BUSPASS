<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use App\Mail\RecuperarContrasena;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\RecuperacionContrasena;
use App\Models\Rol;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function login(Request $request)
    {
        Log::info('Login attempt started');
        try {
            Log::info('Validating request');
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);

            Log::info('Checking if user exists');
            $user = User::where('correo_electronico', $request->email)->first();


            if (!$user) {
                Log::info('User not found');
                return response()->json([
                    'success' => false,
                    'message' => 'El email no está registrado',
                ], 404);
            }

            Log::info('Attempt authentication');
            if (Auth::attempt(['correo_electronico' => $request->email, 'password' => $request->password])) {
                Log::info('Authentication successful');
                $request->session()->regenerate();
                Log::info('Authenticated user: ' . Auth::user()->nombre);
                // return response()->json([
                //     Auth::user()
                // ]);

                if(Auth::user()->id_rol == 1){
                    return response()->json([
                        'success' => true,
                        // 'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user->nombre,
                        'message' => 'Inicio de sesión exitoso. Bienvenido ' . Auth::user()->nombre,
                        'redirect' => route('bus.admin')
                    ]);
                }else if(Auth::user()->id_rol == 3){
                    return response()->json([
                        'success' => true,
                        // 'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user->nombre,
                        'message' => 'Inicio de sesión exitoso. Bienvenido ' . Auth::user()->nombre,
                        'redirect' => route('bus.index')
                    ]);
                }

                // return response()->json([
                //     'success' => true,
                //     // 'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user->nombre,
                //     'message' => 'Inicio de sesión exitoso. Bienvenido ' . Auth::user()->nombre,
                //     'redirect' => route('bus.index')
                // ]);
            }

            Log::info('Authentication failed');
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta',
            ], 401);
        } catch (\Exception $e) {
            Log::error('Error during login attempt: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'No se pudo iniciar sesión',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    // Logout
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response()->json([
                'success' => true,
                'message' => 'Cierre de sesión exitoso',
                'redirect' => route('login')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cerrar sesión: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'No se pudo cerrar sesión',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     // Rol pero si un id 1
    //     $roles = Rol::where('id_rol', '!=', 3)->get();
    //     return view('usuarios.register', compact('roles'));
    // }

   


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // dd($request->all());

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:usuarios,correo_electronico',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
            'phone' => 'required|string',
            'role' => 'required|exists:roles,id_rol'
        ]);

        $user = new User();
        $user->nombre = $request->name;
        $user->correo_electronico = $request->email;
        $user->contrasena = Hash::make($request->password);
        $user->direccion = $request->address;
        $user->numero_celular = $request->phone;
        $user->id_rol = $request->role;

        $user->save();

        return response()->json([
            'success' => true,
            // 'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user->nombre,
            'message' => 'Usuario registrado exitosamente',
            'redirect' => route('login')
        ]);
    }


    public function storeCliente(Request $request)
    {
        //
        // dd($request->all());

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:usuarios,correo_electronico',
            'password' => 'required|string|min:6',
            'address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $user = new User();
        $user->nombre = $request->name;
        $user->correo_electronico = $request->email;
        $user->contrasena = Hash::make($request->password);
        $user->direccion = $request->address;
        $user->numero_celular = $request->phone;
        $user->id_rol = 3;

        $user->save();

        return response()->json([
            'success' => true,
            // 'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user->nombre,
            'message' => 'Usuario registrado exitosamente',
            'redirect' => route('login')
        ]);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function perfil(string $id)
    {
        //
        $user = User::find($id);
        return view('usuarios.perfil', compact('user'));
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

    public function recuperarContrasena(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $user = User::where('correo_electronico', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'El email no está registrado',
                ], 404);
            }

            $token = Str::random(60);
            $recuperarContrasena = new RecuperacionContrasena();
            $recuperarContrasena->id_usuario = $user->user_id;
            $recuperarContrasena->token = $token;
            $recuperarContrasena->save();

            Mail::to($user->correo_electronico)->send(new RecuperarContrasena($token));

            return response()->json([
                'success' => true,
                'message' => 'Se ha enviado un correo para recuperar la contraseña',
                'redirect' => route('inicio')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo recuperar la contraseña',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:6',
                'password_confirmation' => 'required|string|same:password'
            ]);

            $recuperarContrasena = RecuperacionContrasena::where('token', $request->token)->first();

            if (!$recuperarContrasena) {
                return response()->json([
                    'success' => false,
                    'message' => 'El token no es válido',
                ], 404);
            }

            $user = User::find($recuperarContrasena->id_usuario);
            $user->contrasena = Hash::make($request->password);
            $user->save();

            $recuperarContrasena->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente',
                'redirect' => route('inicio')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar la contraseña',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
