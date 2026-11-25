<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('media', 'title')
                    ->where(fn (Builder $query) => $query->where('type', $this->input('type'))),
            ],
            'type' => ['required', Rule::in(['anime', 'drama', 'manga', 'light_novel'])],
            'year' => ['required', 'integer', 'between:1900,2100'],
            'score' => ['required', 'numeric', 'between:0,10'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['required', 'string', 'max:60', 'distinct:ignore_case'],
            'status' => ['required', Rule::in(['finished', 'current', 'upcoming'])],
            'total_units' => ['required', 'integer', 'between:1,100000'],
            'creator' => ['nullable', 'string', 'max:255'],
        ];
    }
}
