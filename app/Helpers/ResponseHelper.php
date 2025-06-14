<?php

namespace App\Helpers;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ResponseHelper
{

    public static function SendSuccess($message, $data = null)
    {
        $response = response()->json([
            "message" => $message,
            "data" => $data
        ]);

        return $response;
    }

    public static function SendErrorMessage($message, $code = 400)
    {
        $response = response()->json([
            "message" => $message
        ], $code);

        return $response;
    }

    public static function SendInternalServerError($error)
    {
        Log::debug($error->getMessage());
        $response = response()->json([
            "message" => "Internal server error"
        ], 500);
        return $response;
    }

    public static function SendValidationError($error) {
        $response = response()->json([
            "message" => "Validation error",
            "errors" => $error
        ], 422);

        return $response;
    }

}
