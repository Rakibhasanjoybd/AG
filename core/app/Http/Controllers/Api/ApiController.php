<?php

namespace App\Http\Controllers\Api;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * The authenticated user.
     *
     * @var \App\Models\User|null
     */
    protected $user;

    /**
     * The authenticated admin.
     *
     * @var \App\Models\Admin|null
     */
    protected $admin;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth('api')->check()) {
                $this->user = auth('api')->user();
            }

            if (auth('admin')->check()) {
                $this->admin = auth('admin')->user();
            }

            return $next($request);
        });
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return ApiResponse::validationError($validator->errors());
        }

        return $validator->validated();
    }

    /**
     * Get success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, $message = 'Success', $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Get error response
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message = 'Error', $code = 400, $errors = null)
    {
        return ApiResponse::error($message, $code, $errors);
    }

    /**
     * Get paginated response
     *
     * @param $data
     * @param $pagination
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function paginated($data, $pagination, $message = 'Success')
    {
        return ApiResponse::paginated($data, $pagination, $message);
    }

    /**
     * Check if user has sufficient balance
     *
     * @param float $amount
     * @param \App\Models\User|null $user
     * @return bool
     */
    protected function hasSufficientBalance($amount, $user = null)
    {
        $user = $user ?: $this->user;
        return $user && $user->balance >= $amount;
    }

    /**
     * Generate unique transaction ID
     *
     * @return string
     */
    protected function generateTransactionId()
    {
        return 'TXN' . strtoupper(uniqid()) . time();
    }

    /**
     * Log security event
     *
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function logSecurityEvent($event, array $data = [])
    {
        \Log::channel('security')->warning($event, array_merge([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $data));
    }

    /**
     * Get authenticated user with fresh data
     *
     * @return \App\Models\User|null
     */
    protected function getFreshUser()
    {
        return $this->user ? $this->user->fresh() : null;
    }

    /**
     * Get authenticated admin with fresh data
     *
     * @return \App\Models\Admin|null
     */
    protected function getFreshAdmin()
    {
        return $this->admin ? $this->admin->fresh() : null;
    }
}
