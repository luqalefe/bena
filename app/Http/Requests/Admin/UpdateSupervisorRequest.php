<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Supervisor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupervisorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorização via middleware EnsureGroup('admin')
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $supervisor = $this->route('supervisor');
        $id = $supervisor instanceof Supervisor ? $supervisor->id : null;

        return [
            'nome' => ['required', 'string', 'max:200'],
            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('supervisores', 'username')->ignore($id),
            ],
            'email' => ['nullable', 'email', 'max:200'],
            'lotacao' => ['nullable', 'string', 'max:100'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }
}
