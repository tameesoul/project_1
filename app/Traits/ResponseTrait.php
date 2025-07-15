<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait ResponseTrait
{
    public function success($body, int $code = 200, array $extra = []): JsonResponse
    {
        return $this->base($body, $code, $extra);
    }

    public function error($errors, int $code = 400, array $extra = []): JsonResponse
    {
        $formattedErrors = $this->formatErrors($errors);
        return response()->json(['errors' => $formattedErrors], $code);
    }

    public function executed(): JsonResponse
    {
        return $this->success(__('Request executed successfully'));
    }

    public function failed(): JsonResponse
    {
        return $this->error(__('Request failed to be executed'));
    }

    /**
     * Override the failedValidation method to handle validation errors.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $errors = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                $errors[] = [
                    'field' => $field,
                    'message' => $messages[0]
                ];
            }

            throw new HttpResponseException(
                response()->json(['errors' => $errors], 422)
            );
        } else {
            throw new HttpResponseException(
                redirect()->back()->withErrors($validator->errors())->withInput()
            );
        }
    }

    private function formatErrors($errors): array
    {
        $formattedErrors = [];
        
        if (is_array($errors)) {
            foreach ($errors as $field => $messages) {
                // Ensure messages is an array
                $messages = is_array($messages) ? $messages : [$messages];
                // Take the first message from the array
                $formattedErrors[] = [
                    'field' => $field,
                    'message' => $messages[0] // Only the first message
                ];
            }
        } else {
            $formattedErrors[] = [
                'field' => 'general',
                'message' => (string) $errors
            ];
        }

        return $formattedErrors;
    }

    private function base($body, int $code, array $extra, bool $status = true): JsonResponse
    {
        $bodyAttribute = $status ? 'data' : 'message';
        $response = [
            $bodyAttribute => $body
        ];

        if (count($extra) > 0) {
            $response['extra'] = $extra;
        }

        return response()->json($response, $code);
    }
}