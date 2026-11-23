<?php

namespace App\Http\Requests;

class UpdateLeagueRequest extends StoreLeagueRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit leagues') ?? false;
    }
}
