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
use Illuminate\Validation\Rules\Password as Pass;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * New user registration
     */
    public function register( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'type' => 'in:Arquitecto,Interiorista,Otro'
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
     * Update user profile (Name, email(disabled) phone, type)
     */
    public function updateUser( Request $request ) {

        // Validate request data
        try {
            $fields = $request->validate([
                'name' => 'string',
                'email' => 'email',
                'type' => 'in:Arquitecto,Interiorista,Otro',
                'phone' => 'string',
            ]);
        } catch ( \Throwable $th ) {
            return response([
                "status" => 422,
                "message" => $th->getMessage()
            ], 422);
        }

        // Locate the user by the email.
        $user = User::where('email', $fields['email'])->first();

        if (!$user) {
            return response([
                "status" => 404,
                "message" => "No se encontró un usuario con ese email."
            ], 404);
        }

        // Update all the fields on ECWID
        $ecwidResponse = EcwidUserController::update( $user->ecwidUserId, $fields["name"], $fields["email"], $fields["phone"] );
        // return $ecwidResponse;
        if ( $ecwidResponse["updateCount"] !== 1 ) {
            return response([
                "status" => 502,
                "message" => "Error actualizando al usuario en la plataforma. No se guardaron cambios."
            ], 502);
        }

        // Update all the fields on local database
        try {
            $user->name = $fields['name'] ? $fields['name'] : $user->name;
            $user->email = $fields['email'] ? $fields['email'] : $user->email;
            $user->type = $fields['type'] ? $fields['type'] : $user->type;
            $user->phone = $fields['phone'] ? $fields['phone'] : $user->phone;
            $user->save();

            return response([
                "status" => 200,
                "message" => "Los cambios al usuario han sido aplicados."
            ], 200);
        } catch ( \Throwable $th ) {
            return response([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }

    }

    /**
     * Delete user.
     */
    public function deleteUser( Request $request ) {
        
        $request->validate([
            'email' => 'required|email',
        ]);

        // Select the user
        try {
            $user = User::where('email', $request->email)->first();
        } catch (\Throwable $th) {
            return response([
                "status" => 404,
                "message" => $th->getMessage()
            ], 404);
        }

        // Delete the user from ECWID
        $ecwidResponse = EcwidUserController::delete( $user->ecwidUserId );

        if ( $ecwidResponse["deleteCount"] !== 1 ) {
            return response([
                "status" => 502,
                "message" => "Error al intentar eliminar al usuario en la plataforma."
            ], 502);
        }

        // Delete current token (logging user out)
        $request->user()->currentAccessToken()->delete();

        // Delete the user from the local database.
        try {
            $user->delete();

            return response([
                "status" => 200,
                "message" => "El usuario y los datos ligados han sido eliminados del sistema."
            ]);

        } catch (\Throwable $th) {
            return response([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }

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

        return response([
            'status' => 200,
            'message' => 'Sesión cerrada.'
        ], 200);
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
            return response([
                "status" => 200,
                "message" => "El correo para restablecer tu contraseña ha sido enviado."
            ], 200);
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

        $fields = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Pass::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        // Locate the user by email.
        $user = User::where('email', $request->email)->first();

        // Change the password on ECWID platform. (UNTESTED)
        $ecwidResponse = EcwidUserController::updatePassword( $user->ecwidUserId, $fields['password'] );
        // return $ecwidResponse;
        if ( $ecwidResponse["updateCount"] !== 1 ) {
            return response([
                "status" => 502,
                "message" => "Error cambiando la contraseña en la plataforma. No se guardaron cambios."
            ], 502);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );

        if ( $status === Password::PASSWORD_RESET ) {
            return response( [
                "status" => 200,
                "message" => "La nueva contraseña fue establecida con éxito.",
            ], 200 );
        }

        return response( [
            "status" => 422,
            "message" => "El enlace ha expirado o ya fue utilizado",
        ], 422 );

    }

    /**
     * Change password function
     */
    public function changePassword( Request $request ) {

        // Validate request inputs.
        try {
            $fields = $request->validate([
                'email' => 'required|email',
                'password' => ['required', 'confirmed', Pass::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        } catch ( \Throwable $th ) {

            return response([
                "status" => 422,
                "message" => $th->getMessage()
            ], 422);

        }
        
        // Locate the user by email.
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response([
                "status" => 404,
                "message" => "No se encontró un usuario con ese email."
            ], 404);
        }

        // Change the password on ECWID platform. (UNTESTED)
        $ecwidResponse = EcwidUserController::updatePassword( $user->ecwidUserId, $fields['password'] );
        // return $ecwidResponse;
        if ( $ecwidResponse["updateCount"] !== 1 ) {
            return response([
                "status" => 502,
                "message" => "Error cambiando la contraseña en la plataforma. No se guardaron cambios."
            ], 502);
        }
    
        // Change the password.
        try {
            $user->password = Hash::make($request->password);
    
            $user->save();

            return response([
                "status" => 200,
                "message" => "La contraseña ha sido cambiada."
            ], 200);
        } catch ( \Throwable $th ) {
            return response([
                "status" => 500,
                "message" => $th->getMessage()
            ], 500);
        }

    }

}

