<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->query('usuario');
        $acao = $request->query('acao');
        $de = $request->query('de');
        $ate = $request->query('ate');

        $query = Auditoria::query()
            ->when($usuario, fn ($q, $v) => $q->where('usuario_username', 'like', '%'.$v.'%'))
            ->when($acao, fn ($q, $v) => $q->where('acao', $v))
            ->when($de, fn ($q, $v) => $q->where('created_at', '>=', CarbonImmutable::parse($v)->startOfDay()))
            ->when($ate, fn ($q, $v) => $q->where('created_at', '<=', CarbonImmutable::parse($v)->endOfDay()))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(500);

        $linhas = $query->get();
        $acoesDisponiveis = Auditoria::query()->distinct()->orderBy('acao')->pluck('acao');

        return view('admin.auditoria.index', [
            'linhas' => $linhas,
            'usuario' => $usuario,
            'acao' => $acao,
            'de' => $de,
            'ate' => $ate,
            'acoesDisponiveis' => $acoesDisponiveis,
        ]);
    }
}
