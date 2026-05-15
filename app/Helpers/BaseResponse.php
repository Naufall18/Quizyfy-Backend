<?php 

namespace App\Helpers;
class BaseResponse
{
    public static function OK($data = null, string $message){
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    public static function Created($data = null, string $message = 'Created'){
        return response()->json([
            'success' => true,
            'data' => $data,
            'status' => 201,
            'message' => $message,
        ], 201);
    }

    public static function NoContent(){
        return response()->json(null, 204);
    }

    public static function BadRequest(string $message = 'Bad Request'){
        return response()->json([
            'success' => false,
            'status' => 400,
            'message' => $message,
        ], 400);
    }

    public static function Unauthorized(string $message = 'Unauthorized'){
        return response()->json([
            'success' => false,
            'status' => 401,
            'message' => $message,
        ], 401);
    }

    public static function Forbidden(string $message = 'Forbidden'){
        return response()->json([
            'success' => false,
            'status' => 403,
            'message' => $message,
        ], 403);
    }

    public static function NotFound(string $message = 'Not Found'){
        return response()->json([
            'success' => false,
            'status' => 404,
            'message' => $message,
        ], 404);
    }

    public static function UnProcessable(string $message = 'Unprocessable Entity'){
        return response()->json([
            'success' => false,
            'status' => 422,
            'message' => $message,
        ], 422);
    }

    public static function ServerError(string $message = 'Internal Server Error'){
        return response()->json([
            'success' => false,
            'status' => 500,
            'message' => $message,
        ], 500);
    }   
}