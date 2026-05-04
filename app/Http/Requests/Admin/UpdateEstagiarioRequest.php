<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEstagiarioRequest extends FormRequest
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
        return [
            'matricula' => ['nullable', 'string', 'max:30'],
            'lotacao' => ['nullable', 'string', 'max:100'],
            'supervisor_nome' => ['nullable', 'string', 'max:200'],
            'supervisor_username' => ['nullable', 'string', 'max:100'],
            'sei' => ['nullable', 'string', 'max:50'],
            'inicio_estagio' => ['nullable', 'date'],
            'fim_estagio' => [
                'nullable',
                'date',
                'after:inicio_estagio',
                $this->duracaoMaximaDoisAnos(),
            ],
            'horas_diarias' => ['required', 'numeric', 'min:0.25', 'max:8'],
            'ativo' => ['nullable', 'boolean'],
            'contrato' => ['nullable', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'horas_diarias.max' => 'Jornada máxima do estagiário é 8h/dia (Lei 11.788, art. 10).',
        ];
    }

    private function duracaoMaximaDoisAnos(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $inicio = $this->input('inicio_estagio');
            if (! is_string($inicio) || ! is_string($value)) {
                return;
            }

            $inicioData = CarbonImmutable::parse($inicio)->startOfDay();
            $fimData = CarbonImmutable::parse($value)->startOfDay();

            if ($fimData->gt($inicioData->addYears(2))) {
                $fail('Duração máxima do estágio é 2 anos (Lei 11.788, art. 11).');
            }
        };
    }
}
