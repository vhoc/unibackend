<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MlaContactMethod;
use Illuminate\Auth\Events\Registered;

class MlaUserController extends Controller
{
    public function register( Request $request ) {

        // Validate request data
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'whatsapp' => 'string',
            'facebook' => 'string',
            'contact_email' => 'email',
            'phone' => 'string'
            
        ]);

        $newUser = [
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt( $fields['password'] ),
            'type' => 'Otro'
        ];

        // Create local user
        $user = User::create($newUser);

        if ( $fields['whatsapp'] ) {
            $newWhatsapp = [
                "user_id" => $user->id,
                "type" => "whatsapp",
                "value" => $fields['whatsapp'],
            ];

            $fb = MlaContactMethod::create($newWhatsapp);
        }

        if ( $fields['facebook'] ) {
            $newFacebook = [
                "user_id" => $user->id,
                "type" => "facebook",
                "value" => $fields['facebook'],
            ];

            $fb = MlaContactMethod::create($newFacebook);
        }

        if ( $fields['phone'] ) {
            $newPhone = [
                "user_id" => $user->id,
                "type" => "phone",
                "value" => $fields['phone'],
            ];

            $ph = MlaContactMethod::create($newPhone);
        }

        if ( $fields['contact_email'] ) {
            $newContactEmail = [
                "user_id" => $user->id,
                "type" => "contact_email",
                "value" => $fields['contact_email'],
            ];

            $ce = MlaContactMethod::create($newContactEmail);
        }
                
        // Send verification email.
        event(new Registered($user));

        // Generate and send response with created local user.
        $response = [
            'user' => $user,
        ];

        // Return a sucess response.
        return response( $response, 201 );

        // return response($request);

    }
}
