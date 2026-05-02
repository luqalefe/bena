<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\PontoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PontoController extends Controller
{
    public function __construct(private readonly PontoService $ponto) {}

    public function entrada(Request $request): JsonResponse
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        $frequencia = $this->ponto->baterEntrada($estagiario, $request->ip());

        return response()->json($frequencia, 201);
    }

    public function saida(Request $request): JsonResponse
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        $frequencia = $this->ponto->baterSaida($estagiario, $request->ip());

        return response()->json($frequencia);
    }
}
