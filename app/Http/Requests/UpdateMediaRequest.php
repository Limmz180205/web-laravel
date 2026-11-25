<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $media = $this->route('media');
        $type = $this->input('type', $media->type);

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('media', 'title')
                    ->where(fn (Builder $query) => $query->where('type', $type))
                    ->ignore($media->getKey()),
            ],
            'type' => ['sometimes', Rule::in(['anime', 'drama', 'manga', 'light_novel'])],
            'year' => ['sometimes', 'required', 'integer', 'between:1900,2100'],
            'score' => ['sometimes', 'required', 'numeric', 'between:0,10'],
            'genres' => ['sometimes', 'required', 'array', 'min:1'],
            'genres.*' => ['required', 'string', 'max:60', 'distinct:ignore_case'],
            'status' => ['sometimes', Rule::in(['finished', 'current', 'upcoming'])],
            'total_units' => ['sometimes', 'required', 'integer', 'between:1,100000'],
            'creator' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
