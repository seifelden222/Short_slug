<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $linkId = $this->route('link'); // For update requests

        return [
            'slug' => [
                'nullable',
                'string',
                'regex:/^[a-z0-9-]{4,30}$/',
                'unique:links,slug' . ($linkId ? ",$linkId" : '')
            ],
            'target_url' => 'required|url|max:2048',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ];
    }
}
