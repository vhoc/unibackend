<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\EcwidUserController;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    
    public function register( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'type' => 'required|string'
        ]);

        /**
         * Sync with ECWID database
         * Acquires the user from ECWID or creates it if it doesn't exist.
         * Requires: all ECWID env variables to be set through the validEcwidConfig helper method used in EcwidUserController
         */
         $ecwidUser = EcwidUserController::get( $fields['email'] );

         

        // If the user's email exists on Ecwid, get user's data
        if ( $ecwidUser && $ecwidUser["status"] === 200 ) {
            
            $newUser = [
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt( $fields['password'] ),
                'ecwidUserId' => $ecwidUser["user"]["id"],
                'type' => $request->type,
                'phone' => $ecwidUser["user"]["billingPerson"]["phone"] ? $ecwidUser["user"]["billingPerson"]["phone"] : $ecwidUser["user"]["shippingAddresses"][0]["phone"],
            ];

            // If ECWID user was successfully acquired, save the user in the local database.
            if ( $newUser['ecwidUserId'] ) {

                // Create local user
                $user = User::create($newUser);
                
                // Send verification email.
                event(new Registered($user));

                // Generate and send response with created local user.
                $response = [
                    'user' => $user,
                ];

                // Return a sucess response.
                return response( $response, 201 );

            }
        }
        
        // If the user doesnt exist on Ecwid API:
        // Create customer on Ecwid, with tier (customer group) 0
        $newEcwidUser = EcwidUserController::create( $fields['email'], $fields['password'], 0, ["name" => $fields['name']] );

        if ( $newEcwidUser ) {
            
            $newUser = [
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt( $fields['password'] ),
                'ecwidUserId' => $newEcwidUser["id"], 
                'type' => $request->type,
            ];

            // If ECWID user was successfully created, save the user in the local database.
            if ( $newUser['ecwidUserId'] ) {

                // Create local user
                $user = User::create($newUser);
                
                // Send verification email.
                event(new Registered($user));

                // Generate and send response with created local user.
                $response = [
                    'user' => $user,
                ];

                // Return a sucess response.
                return response( $response, 201 );

            }

        }

        return response( [
            "status" => 500,
            "message" => "Hubo un error al crear el usuario.",
        ], 500 );

    }

    /**
     * Login function
     */
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
                'status' => 401,
                'message' => 'Authentication failed.'
            ], 401);
        }

        // Check if user's email has been verified.
        if (!$user->email_verified_at) {
            return response([
                'status' => 401,
                'message' => 'User email has not been verified.'
            ], 401);
        }

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'status' => 200,
            'message' => 'Autenticación exitosa.',
            'userId' => $user->id,
            'ecwidUserId' => $user->ecwidUserId,
            'type' => $user->type,
            'email' => $user->email,
            'phone' => $user->phone,
            'name' => $user->name,
            'active' => $user->email_verified_at ? true : false,
            'user' => $user,
            'accessToken' => $token,
            'refreshToken' => ''// NOT NEEDED
        ];

        return response( $response, 200 );

    }

    /**
     * Logout function
     */
    public function logout( Request $request ) {

        $request->user()->currentAccessToken()->delete();

        return [
            'message' => 'Logged out.'
        ];
    }

    /**
     * E-mail verification function
     */
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

        return view('success', ['name' => $user->name]);

    }

    /**
     * Resend E-mail verification function
     */
    public function resendVerificationEmail( Request $request ) {

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

    /**
     * Reset Password function
     */
    public function resetPassword( Request $request ) {

        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ( $status === Password::RESET_LINK_SENT ) {
            return $status;
        }

        return response( [
            "status" => 500,
            "message" => "Hubo un error al intentar enviar el correo de reseteo del password.",
        ], 500 );

    }

    /**
     * Process password reset function
     */
    public function processPasswordReset( Request $request ) {

        

    }

}
