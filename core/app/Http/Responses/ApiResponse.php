<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Create a success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function success($data = null, $message = 'Success', $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Create an error response
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function error($message = 'Error', $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // Add validation errors if they exist
        if (request()->hasErrors()) {
            $response['errors'] = request()->errors();
        }

        return response()->json($response, $code);
    }

    /**
     * Create a validation error response
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError($errors, $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }

    /**
     * Create an unauthorized response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized($message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Create a forbidden response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden($message = 'Forbidden'): JsonResponse
    {
        return self::error($message, 403);
    }

    /**
     * Create a not found response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound($message = 'Resource not found'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Create a server error response
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function serverError($message = 'Internal server error'): JsonResponse
    {
        return self::error($message, 500);
    }

    /**
     * Create a paginated response
     *
     * @param $data
     * @param $pagination
     * @param string $message
     * @return JsonResponse
     */
    public static function paginated($data, $pagination, $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $pagination->currentPage(),
                'last_page' => $pagination->lastPage(),
                'per_page' => $pagination->perPage(),
                'total' => $pagination->total(),
                'from' => $pagination->firstItem(),
                'to' => $pagination->lastItem(),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
