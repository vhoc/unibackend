<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MlaContactMethod;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MlaContactMethodController extends Controller
{
    
    public function index( $userId ) {

        try {
            $contactMethods = MlaContactMethod::where('user_id', $userId)->get();

            return response([
                "status" => 200,
                "contact_methods" => $contactMethods,
            ]);
        } catch (\Throwable $e) {
            return response([
                "status" => $e->getcode(),
                "message" => $e->getMessage(),
            ]);
        }

    }

    public function store( Request $request ) {

        // Get user
        $user = Auth::user();
        $userId = $user->id;
        $userObject = User::find($userId);

        $fields = $request->validate([
            'whatsapp' => 'string|nullable',
            'facebook' => 'string|nullable',
            'contact_email' => 'string|nullable',
            'phone' => 'string|nullable',
        ]);

        try {

            // if ( $fields["whatsapp"] ) {
                DB::table('mla_contact_methods')->updateOrInsert(
                    [ 'user_id' => $userObject->id, 'type' => 'whatsapp' ],
                    [ 'value' => $fields["whatsapp"] ? $fields["whatsapp"] : "" ]
                );
            // }
    
            // if ( $fields["facebook"] ) {
                DB::table('mla_contact_methods')->updateOrInsert(
                    [ 'user_id' => $userObject->id, 'type' => 'facebook' ],
                    [ 'value' => $fields["facebook"] ? $fields["facebook"] : "" ]
                );
            // }
    
            // if ( $fields["contact_email"] ) {
                DB::table('mla_contact_methods')->updateOrInsert(
                    [ 'user_id' => $userObject->id, 'type' => 'contact_email' ],
                    [ 'value' => $fields["contact_email"] ? $fields["contact_email"] : "" ]
                );
            // }
    
            // if ( $fields["phone"] ) {
                DB::table('mla_contact_methods')->updateOrInsert(
                    [ 'user_id' => $userObject->id, 'type' => 'phone' ],
                    [ 'value' => $fields["phone"] ? $fields["phone"] : "" ]
                );
            // }

            $updatedMethods = MlaContactMethod::where('user_id', $userObject->id)->get();

            return response([
                "status" => 200,
                "contact_methods" => $updatedMethods,
            ]);

        } catch (\Throwable $e) {
            return response([
                "status" => $e->getCode(),
                "message" => $e->getMessage(),
            ]);
        }

        

    }

}
