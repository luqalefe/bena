<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assinatura;
use App\Models\Feriado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeriadoController extends Controller
{
    public function create(): View
    {
        return view('admin.feriados.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        Feriado::create($this->payload($dados));

        $redirectTo = $request->input('redirect_to');
        // Whitelist: aceita /calendario exato OU /calendario seguido de /
        // ou ?. Rejeita /calendariofake, //evil.com, javascript:, etc.
        if (is_string($redirectTo) && preg_match('#^/calendario(/|\?|$)#', $redirectTo) === 1) {
            return redirect($redirectTo)->with('sucesso', 'Feriado cadastrado com sucesso.');
        }

        return redirect()
            ->route('calendario.index')
            ->with('sucesso', 'Feriado cadastrado com sucesso.');
    }

    public function edit(Feriado $feriado): View
    {
        return view('admin.feriados.edit', ['feriado' => $feriado]);
    }

    public function update(Request $request, Feriado $feriado): RedirectResponse
    {
        $dados = $this->validar($request, ignorarFeriadoId: $feriado->id);

        $feriado->update($this->payload($dados));

        return redirect()
            ->route('calendario.mes', [
                'ano' => $feriado->data->year,
                'mes' => $feriado->data->month,
            ])
            ->with('sucesso', 'Feriado atualizado com sucesso.');
    }

    public function confirmDestroy(Feriado $feriado): View
    {
        return view('admin.feriados.confirm-destroy', [
            'feriado' => $feriado,
            'assinaturasImpactadas' => $this->contarAssinaturasNoMesDoFeriado($feriado),
        ]);
    }

    public function destroy(Feriado $feriado): RedirectResponse
    {
        $feriado->delete();

        return redirect()
            ->route('calendario.index')
            ->with('sucesso', 'Feriado removido. Folhas afetadas terão o hash invalidado na próxima verificação.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validar(Request $request, ?int $ignorarFeriadoId = null): array
    {
        return $request->validate([
            'data' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $ignorarFeriadoId): void {
                    $duplicado = Feriado::query()
                        ->whereDate('data', $value)
                        ->where('tipo', $request->input('tipo'))
                        ->when($ignorarFeriadoId, fn ($q) => $q->where('id', '!=', $ignorarFeriadoId))
                        ->exists();

                    if ($duplicado) {
                        $fail('Já existe feriado nesta data.');
                    }
                },
            ],
            'descricao' => ['required', 'string', 'max:200'],
            'tipo' => ['required', 'in:nacional,estadual,municipal,recesso'],
            'uf' => ['nullable', 'string', 'size:2'],
            'recorrente' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function payload(array $dados): array
    {
        return [
            'data' => $dados['data'],
            'descricao' => $dados['descricao'],
            'tipo' => $dados['tipo'],
            'uf' => $dados['uf'] ?? null,
            'recorrente' => (bool) ($dados['recorrente'] ?? false),
        ];
    }

    private function contarAssinaturasNoMesDoFeriado(Feriado $feriado): int
    {
        $data = $feriado->data;

        return Assinatura::query()
            ->where('ano', $data->year)
            ->where('mes', $data->month)
            ->count();
    }
}
