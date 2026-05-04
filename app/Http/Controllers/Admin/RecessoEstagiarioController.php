<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecessoEstagiarioRequest;
use App\Models\Estagiario;
use App\Models\RecessoEstagiario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecessoEstagiarioController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(Estagiario $estagiario): View
    {
        $recessos = $estagiario->recessos()
            ->orderByDesc('inicio')
            ->get();

        return view('admin.recessos.index', [
            'estagiario' => $estagiario,
            'recessos' => $recessos,
        ]);
    }

    public function store(StoreRecessoEstagiarioRequest $request, Estagiario $estagiario): RedirectResponse
    {
        $dados = $request->validated();

        $recesso = RecessoEstagiario::create([
            'estagiario_id' => $estagiario->id,
            'inicio' => $dados['inicio'],
            'fim' => $dados['fim'],
            'observacao' => $dados['observacao'] ?? null,
        ]);

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'recesso.criar',
            entidade: 'recesso',
            entidadeId: (string) $recesso->id,
            payload: [
                'estagiario_username' => $estagiario->username,
                'inicio' => $dados['inicio'],
                'fim' => $dados['fim'],
            ],
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.estagiarios.recessos.index', $estagiario)
            ->with('sucesso', 'Recesso cadastrado.');
    }

    public function destroy(Request $request, Estagiario $estagiario, RecessoEstagiario $recesso): RedirectResponse
    {
        if ($recesso->estagiario_id !== $estagiario->id) {
            abort(404);
        }

        $recesso->delete();

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'recesso.remover',
            entidade: 'recesso',
            entidadeId: (string) $recesso->id,
            payload: [
                'estagiario_username' => $estagiario->username,
                'inicio' => $recesso->inicio->format('Y-m-d'),
                'fim' => $recesso->fim->format('Y-m-d'),
            ],
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.estagiarios.recessos.index', $estagiario)
            ->with('sucesso', 'Recesso removido.');
    }
}
