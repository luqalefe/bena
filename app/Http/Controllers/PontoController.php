<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\PontoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PontoController extends Controller
{
    public function __construct(private readonly PontoService $ponto) {}

    public function entrada(Request $request): JsonResponse|RedirectResponse
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        $frequencia = $this->ponto->baterEntrada($estagiario, $request->ip());

        if ($request->wantsJson()) {
            return response()->json($frequencia, 201);
        }

        return redirect()->route('ponto.sucesso')->with([
            'ponto_acao' => 'entrada',
            'ponto_horario' => $frequencia->entrada->format('H:i'),
        ]);
    }

    public function saida(Request $request): JsonResponse|RedirectResponse
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        $frequencia = $this->ponto->baterSaida($estagiario, $request->ip());

        if ($request->wantsJson()) {
            return response()->json($frequencia);
        }

        return redirect()->route('ponto.sucesso')->with([
            'ponto_acao' => 'saida',
            'ponto_horario' => $frequencia->saida->format('H:i'),
            'ponto_horas' => number_format((float) $frequencia->horas, 2, ',', ''),
        ]);
    }

    public function sucesso(Request $request): View|RedirectResponse
    {
        // Se entrou direto sem flash, volta pro dashboard.
        if (! $request->session()->has('ponto_acao')) {
            return redirect()->route('dashboard');
        }

        return view('ponto.sucesso', [
            'acao' => $request->session()->get('ponto_acao'),
            'horario' => $request->session()->get('ponto_horario'),
            'horas' => $request->session()->get('ponto_horas'),
        ]);
    }
}
