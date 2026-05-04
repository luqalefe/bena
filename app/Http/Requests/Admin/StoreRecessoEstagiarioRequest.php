<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Estagiario;
use App\Models\RecessoEstagiario;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecessoEstagiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorização via $adminOnlyRouteNames em ConfigureUserSession
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'inicio' => ['required', 'date'],
            'fim' => [
                'required',
                'date',
                'after_or_equal:inicio',
                $this->semSobreposicao(),
            ],
            'observacao' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function semSobreposicao(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $inicio = $this->input('inicio');
            if (! is_string($inicio) || ! is_string($value)) {
                return;
            }

            $estagiario = $this->route('estagiario');
            if (! $estagiario instanceof Estagiario) {
                return;
            }

            // Sobreposição: inicio_existente <= novo_fim AND fim_existente >= novo_inicio
            $existe = RecessoEstagiario::query()
                ->where('estagiario_id', $estagiario->id)
                ->whereDate('inicio', '<=', $value)
                ->whereDate('fim', '>=', $inicio)
                ->exists();

            if ($existe) {
                $fail('Período se sobrepõe a outro recesso já cadastrado.');
            }
        };
    }
}
