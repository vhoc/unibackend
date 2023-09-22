<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;


class AuthController extends Controller
{
    
    public function register( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);

        // Create user
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt( $fields['password'] ),
        ]);

        // Send verification email.
        event(new Registered($user));

        // Generate response.
        // $token = $user->createToken('myapptoken')->plainTextToken;

        // Generate and send response.
        $response = [
            'user' => $user,
            // 'token' => $token,
        ];

        return response( $response, 201 );

    }

    public function login( Request $request ) {

        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check user existence and password
        if ( !$user || !Hash::check($fields['password'], $user->password) ) {
            return response([
                'message' => 'Authentication failed.'
            ], 401);
        }

        // Check if user's email has been verified.
        if (!$user->email_verified_at) {
            return response([
                'message' => 'User email has not been verified.'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
        ];

        return response( $response, 200 );

    }

    public function logout( Request $request ) {

        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Logged out.'
        ];
    }

    public function verifyEmail( Request $request ) {

        $user = User::find($request->route('id'));

        if ($request->route('id') != $user->getKey()) {
            throw new AuthorizationException;
        }

        if ($user->email_verified_at) {
            return view('failed', ['name' => $user->name]);
        }

        if ($user->markEmailAsVerified())
            event(new Verified($user));

        // return redirect()->route('verification.success')->with('name', $user->name);
        return view('success', ['name' => $user->name]);

    }

    public function resendVerificationEmail( Request $request ) {

        // $request->user()->sendEmailVerificationNotification();
        $fields = $request->validate([
            'email' => 'required|string',
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Resend verification email
        $user->sendEmailVerificationNotification();

        $response = [
            'message' => 'Verification link sent!',
        ];

        return response( $response, 200 );
    }

}
