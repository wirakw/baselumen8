<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['login']]);
    }
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role' => 'required',
            'phone_number' => 'required|min:10|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|same:password',
        ]);
        $input = $request->all();
        // unset($input['c_password']);
        $input['password'] = Hash::make($input['password']);
        $data = User::create($input);
        $credentials = $request->only(['email', 'password', 'role']);
        $token = Auth::attempt($credentials);
        //return successful response
        return response()->json([
            'data' => $data,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL()
        ], 200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'email' => 'required|email|string',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth::refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL()
        ]);
    }

    /**
     * Request an email verification email to be sent.
     *
     * @param  Request  $request
     * @return Response
     */
    public function emailRequestVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('Email address is already verified.');
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            "success" => true,
            "message" => 'Email request verification sent to ' . Auth::user()->email,
        ]);
    }

    /**
     * Verify an email using email and token from email.
     *
     * @param  Request  $request
     * @return Response
     */
    public function emailVerify(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
        ]);

        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return response()->json('Fail Verify Email, Token has expired');
            // do whatever you want to do if a token is expired
        } catch (TokenInvalidException $e) {
            return response()->json('Fail Verify Email, Token is invalid', 401);
            // do whatever you want to do if a token is invalid
        } catch (JWTException $e) {
            return response()->json('Fail Verify Email, Token not found', 401);
        }

        if (!$request->user()) {
            return response()->json('Invalid token', 401);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('Email address ' . $request->user()->getEmailForVerification() . ' is already verified.');
        }
        $request->user()->markEmailAsVerified();

        return response()->json('Email address ' . $request->user()->email . ' successfully verified.');
    }
}
