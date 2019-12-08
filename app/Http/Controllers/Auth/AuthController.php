<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\BlockAttempsUsers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @var int
     */
    protected $maxAttempts = 3; // Default is 5

    /**
     * Login function
     *
     * @param Request $request
     * @return void
     *
     * api/v1/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if ($this->hasTooManyLoginAttempts($request)) {
            // aqui cerramos al usuario permanentemente
            dd("test");
            event(new BlockAttempsUsers($request));
        }

        // Esto no deberia estar aquì pero lo dejamos por conveniencia
        $checkStatus = User::where('email', $request->email)->first();
        if ($checkStatus->status == false) {
            return \response()->json('La Cuenta esta bloqueada, solicite procedimiento de desbloqueo al administrador del sistema', 401);
        }



        if (!Auth::attempt($credentials))
            return response()->json(['message' => 'Unauthorized'], 401);
        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
        ]);
    }

    /**
     * Register function
     *
     * @param Request $request
     * @return void
     *
     * api/v1/register
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => false,
        ]);

        return response()->json($user, 200);
    }

    /**
     * User function
     *
     * @param Request $request
     * @return void
     *
     * api/v1/user
     */
    public function user(Request $request)
    {
        return $request->user();
    }


    /**
     * Logout function
     *
     * @return void
     *
     * api/v1/logout
     */
    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json('logout', 200);
    }


}
