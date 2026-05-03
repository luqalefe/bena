<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Auditoria;
use Carbon\CarbonImmutable;

class AuditoriaService
{
    /**
     * Registra uma entrada append-only na tabela `auditoria`. Idempotência
     * NÃO é garantida — duas chamadas iguais geram duas linhas. Cabe ao
     * caller só chamar quando a ação realmente aconteceu.
     *
     * @param  array<string, mixed>  $payload
     */
    public function registrar(
        string $usuario,
        string $acao,
        string $entidade,
        ?string $entidadeId = null,
        array $payload = [],
        ?string $ip = null,
    ): Auditoria {
        return Auditoria::create([
            'usuario_username' => $usuario,
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'payload' => $payload === [] ? null : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip' => $ip,
            'created_at' => CarbonImmutable::now(),
        ]);
    }
}
