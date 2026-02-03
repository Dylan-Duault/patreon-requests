<?php

namespace App\Http\Requests;

use App\Models\VideoRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequestContextRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $videoRequest = $this->route('videoRequest');

        // Ensure the request belongs to the user and is pending
        return $videoRequest instanceof VideoRequest
            && $videoRequest->user_id === $this->user()->id
            && $videoRequest->isPending();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'context' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'context.max' => 'The context cannot exceed 500 characters.',
        ];
    }
}
