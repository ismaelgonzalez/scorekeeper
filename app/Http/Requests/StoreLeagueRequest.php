<?php

namespace App\Http\Requests;

use App\Models\League;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeagueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create leagues') ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:16000'],
            'status' => ['required', Rule::in(League::STATUSES)],
        ];
    }
}
