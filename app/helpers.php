<?php

// Validate required environment variables.
function validEcwidConfig() {
    if ( !env('ECWID_API_SECRET_TOKEN') && !env('ECWID_API_PUBLIC_TOKEN') ) {
        return [
            "status" => false,
            "message" => "Missing ECWID access token (ECWID_API_SECRET_TOKEN or ECWID_API_PUBLIC_TOKEN).",
        ];
    }

    if ( !env('ECWID_API_BASE_URL') ) {
        return [
            "status" => false,
            "message" => "Missing ECWID base URL (ECWID_API_BASE_URL).",
        ];
    }

    if ( !env('ECWID_STORE_ID') ) {
        return [
            "status" => false,
            "message" => "Missing ECWID store ID (ECWID_STORE_ID).",
        ];
    }

    return [
        "status" => true,
    ];
}