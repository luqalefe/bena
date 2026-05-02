<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estagiario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstagiarioController extends Controller
{
    public function index(Request $request): View
    {
        $lotacao = $request->query('lotacao');

        $estagiarios = Estagiario::query()
            ->when($lotacao, fn ($q) => $q->where('lotacao', $lotacao))
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        $lotacoes = Estagiario::query()
            ->whereNotNull('lotacao')
            ->distinct()
            ->orderBy('lotacao')
            ->pluck('lotacao');

        return view('admin.estagiarios.index', [
            'estagiarios' => $estagiarios,
            'lotacoes' => $lotacoes,
            'lotacao' => $lotacao,
        ]);
    }

    public function edit(Estagiario $estagiario): View
    {
        return view('admin.estagiarios.edit', ['estagiario' => $estagiario]);
    }

    public function update(Request $request, Estagiario $estagiario): RedirectResponse
    {
        $dados = $request->validate([
            'matricula' => ['nullable', 'string', 'max:30'],
            'lotacao' => ['nullable', 'string', 'max:100'],
            'supervisor_nome' => ['nullable', 'string', 'max:200'],
            'supervisor_username' => ['nullable', 'string', 'max:100'],
            'sei' => ['nullable', 'string', 'max:50'],
            'inicio_estagio' => ['nullable', 'date'],
            'fim_estagio' => ['nullable', 'date', 'after:inicio_estagio'],
            'horas_diarias' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $estagiario->fill([
            'matricula' => $dados['matricula'] ?? null,
            'lotacao' => $dados['lotacao'] ?? null,
            'supervisor_nome' => $dados['supervisor_nome'] ?? null,
            'supervisor_username' => $dados['supervisor_username'] ?? null,
            'sei' => $dados['sei'] ?? null,
            'inicio_estagio' => $dados['inicio_estagio'] ?? null,
            'fim_estagio' => $dados['fim_estagio'] ?? null,
            'horas_diarias' => $dados['horas_diarias'],
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);
        $estagiario->save();

        return redirect()
            ->route('admin.estagiarios.index')
            ->with('sucesso', "Dados de {$estagiario->nome} atualizados.");
    }
}
