<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
use App\Models\MlaContactMethod;
use App\Models\MlaImage;
use App\Models\MlaProduct;

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
            'type' => 'in:Arquitecto,Interiorista,Otro',
            'phone' => 'string'
        ]);

        /**
         * Sync with ECWID database
         * Acquires the user from ECWID or creates it if it doesn't exist.
         * Requires: all ECWID env variables to be set through the validEcwidConfig helper method used in EcwidUserController
         */
        if( env('PLATFORM') === "ecwid" ) {

            $ecwidUser = EcwidUserController::get( $fields['email'] );
            
            if( $ecwidUser['status'] === 500 ) {
                return response([
                    "status" => 500,
                    "remote_status" => $ecwidUser['remote_status'],
                    "remote_body" => $ecwidUser["remote_body"],
                    // "message" => "No fue posible conectar a la plataforma remota. Verifique tokens de acceso."
                    "message" => $ecwidUser['message'],
                ], 500);
            }

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
            $newEcwidUser = EcwidUserController::create( $fields['email'], $fields['password'], 0, ["name" => $fields['name'], "phone" => $fields['phone']] );

            if ( $newEcwidUser ) {
                
                $newUser = [
                    'name' => $fields['name'],
                    'email' => $fields['email'],
                    'password' => bcrypt( $fields['password'] ),
                    'ecwidUserId' => $newEcwidUser["id"], 
                    'type' => $request->type,
                    'phone' => $fields['phone'],
                ];

                // If ECWID user was successfully created, save the user in the local database.
                if ( $newUser['ecwidUserId'] ) {

                    // Create local user
                    $user = User::create($newUser);
                    
                    // Send verification email.
                    try {
                        event(new Registered($user));
                    } catch (\Throwable $e) {
                        // If verification mail send fails, delete user from ecwid and local database.
                        EcwidUserController::delete( $newUser['ecwidUserId'] );
                        $user->delete();

                        return response( [
                            "status" => 500,
                            "message" => $e->getMessage(),
                        ], 500 );
                    }
                    

                    // Generate and send response with created local user.
                    $response = [
                        'user' => $user,
                    ];

                    // Return a sucess response.
                    return response( $response, 201 );

                }

            }

        } else {// NO ECWID
            $newUser = [
                'name' => $fields['name'],
                'email' => $fields['email'],
                'password' => bcrypt( $fields['password'] ),
            ];

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
                // 'type' => 'in:Arquitecto,Interiorista,Otro',
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
            $user->name = $fields['name'];
            $user->email = $fields['email'] ? $fields['email'] : $user->email;
            // $user->type = $fields['type'] ? $fields['type'] : "Otro";
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
        // $request->validate([
        //     'email' => 'required|email',
        // ]);

        // Select the user
        try {
            // $user = User::where('email', $request->email)->first();
            $user = Auth::user();
            $userId = $user->id;
            $userObject = User::find($userId);
        } catch (\Throwable $th) {
            return response([
                "status" => 404,
                "message" => $th->getMessage()
            ], 404);
        }

        if ( env("PLATFORM") === "ecwid" ) {
            // Delete the user from ECWID
            $ecwidResponse = EcwidUserController::delete( $user->ecwidUserId );

            if ( $ecwidResponse["deleteCount"] !== 1 ) {
                return response([
                    "status" => 502,
                    "message" => "Error al intentar eliminar al usuario en la plataforma."
                ], 502);
            }
        }        

        // Delete current token (logging user out)
        $request->user()->currentAccessToken()->delete();

        if ( env("PLATFORM") === "milistapp" ) {

            // Delete the user from the local database.
            try {
                // Delete the user's upload folder and all its images.
                if ( Storage::disk('public')->exists('uploads/' . strval($userObject->id)) ) {
                    Storage::disk('public')->deleteDirectory('uploads/' . strval($userObject->id));
                }

                // Get all product id's belonging to the user
                $userProducts = MlaProduct::where('user_id', $userObject->id)->get();
                $userProductsIds = [];
                foreach ( $userProducts as $userProduct ) {
                    array_push($userProductsIds, $userProduct->id);
                }

                // Get all images id's belonging to the user's products.
                $imagesToDelete = MlaImage::whereIn('mla_product_id', $userProductsIds)->get();
                $imagesIDsToDelete = [];
                foreach ( $imagesToDelete as $imageToDelete ) {
                    array_push($imagesIDsToDelete, $imageToDelete->id);
                }

                // Delete the user's products and their images from the DB.
                MlaProduct::whereIn('id', $userProductsIds)->delete();
                MlaImage::whereIn('id', $imagesIDsToDelete)->delete();

                // Delete all the user's contact methods
                MlaContactMethod::where('user_id', $userObject->id)->delete();

                // Finally delete the user. Bye bye :')
                $userObject->delete();

                return response([
                    "status" => 200,
                    "message" => "El usuario y los datos ligados han sido eliminados del sistema."
                ]);

            } catch (\Throwable $th) {
                return response([
                    "status" => $th->getCode(),
                    "message" => $th->getMessage()
                ], $th->getCode());
            }

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
            'hexId' => $user->hexId,
            'ecwidUserId' => $user->ecwidUserId,
            'type' => $user->type,
            'email' => $user->email,
            'phone' => $user->phone,
            'name' => $user->name,
            'active' => $user->email_verified_at ? true : false,
            // 'user' => $user,
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
            'status' => 200,
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

        if ( env("PLATFORM") === "ecwid" ) {

            // Change the password on ECWID platform. (UNTESTED)
            $ecwidResponse = EcwidUserController::updatePassword( $user->ecwidUserId, $fields['password'] );
            // return $ecwidResponse;
            if ( $ecwidResponse["updateCount"] !== 1 ) {
                return response([
                    "status" => 502,
                    "message" => "Error cambiando la contraseña en la plataforma. No se guardaron cambios."
                ], 502);
            }

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
            return view('passwordResetSuccess');
        }

        return view('passwordResetFailed');

    }

    /**
     * Change password function
     */
    public function changePassword( Request $request ) {

        // Validate request inputs.
        try {
            $fields = $request->validate([
                'email' => 'required|email',
                'password' => ['required', 'confirmed', Pass::min(8)],
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

        if ( env("PLATFORM") === "ecwid" ) {

            // Change the password on ECWID platform. (UNTESTED)
            $ecwidResponse = EcwidUserController::updatePassword( $user->ecwidUserId, $fields['password'] );
            // return $ecwidResponse;
            if ( $ecwidResponse["updateCount"] !== 1 ) {
                return response([
                    "status" => 502,
                    "message" => "Error cambiando la contraseña en la plataforma. No se guardaron cambios."
                ], 502);
            }

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

    /**
     * Register user (MLA)
     */
    public function register_mla( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'whatsapp' => 'string',
            'facebook' => 'string',
            'phone' => 'string',
            'contact_email' => 'email',
        ]);

        $newUser = [
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt( $fields['password'] ),
        ];

        try {
            // Create local user
            $user = User::create($newUser);
                    
            // Send verification email.
            event(new Registered($user));

            // Create the contact methods for the user;
            if ( $fields["whatsapp"] ) {
                MlaContactMethod::create([
                    "user_id" => $user->id,
                    "type" => "whatsapp",
                    "value" => $fields["whatsapp"],
                ]);
            }

            if ( $fields["facebook"] ) {
                MlaContactMethod::create([
                    "user_id" => $user->id,
                    "type" => "facebook",
                    "value" => $fields["facebook"],
                ]);
            }

            if ( $fields["contact_email"] ) {
                MlaContactMethod::create([
                    "user_id" => $user->id,
                    "type" => "contact_email",
                    "value" => $fields["contact_email"],
                ]);
            }

            if ( $fields["phone"] ) {
                MlaContactMethod::create([
                    "user_id" => $user->id,
                    "type" => "phone",
                    "value" => $fields["phone"],
                ]);
            }

            // Generate and send response with created local user.
            $response = [
                'user' => $user,
            ];

            // Return a sucess response.
            return response( $response, 201 );
        } catch (\Throwable $e) {
            return response([
                "status" => $e->code,
                "message" => $e->message,
            ]);
        }

        

    }

    /**
     * Get user data
     */
    public function show( $userId ) {

        try {
            $user = User::find( $userId );
            return response([
                "status" => 200,
                "user" => $user,
            ], 200);
        } catch (\Throwable $e) {
            return response([
                "status" => $e->code,
                "message" => $e->message,
            ], $e->code);
        }

    }

    /**
     * Update user that has no external platform (like ECWID)
     */
    public function update( Request $request ) {
        // Validate request data
        try {
            $fields = $request->validate([
                'id' => 'numeric',
                'name' => 'string',
                // 'email' => 'email',
            ]);
        } catch ( \Throwable $th ) {
            return response([
                "status" => 422,
                "message" => $th->getMessage()
            ], 422);
        }

        try {
            $user = User::find( $fields['id'] );

            $user->name = $fields['name'];
            // $user->email = $fields['email'];

            $user->save();

            return response([
                "status" => 200,
                "message" => "El usuario ha sido actualizado.",
                "user" => $user,
            ], 200);
        } catch (\Throwable $e) {
            return response([
                "status" => $e->code,
                "message" => $e->message,
            ], $e->code);
        }
    }

    /**
     * Update user's password that has no external platform (like ECWID)
     */
    public function updatePassword( Request $request ) {
        // Validate request inputs.
        try {
            $fields = $request->validate([
                'id' => 'required|numeric',
                'password' => ['required', 'confirmed', Pass::min(8)],
            ]);
        } catch ( \Throwable $th ) {

            return response([
                "status" => 422,
                "message" => $th->getMessage()
            ], 422);

        }
        
        // Locate the user by email.
        $user = User::find( $fields['id'] );

        if ( $user ) {
            $user->password = Hash::make($request->password);
    
            $user->save();

            return response([
                "status" => 200,
                "message" => "La contraseña ha sido cambiada."
            ], 200);
        } else {
            return response([
                "status" => 404,
                "message" => "No se encontró al usuario."
            ], 404);
        }
    }

}

