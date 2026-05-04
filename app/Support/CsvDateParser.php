<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Parser tolerante para datas vindas do CSV institucional.
 *
 * O CSV mistura formato US (M/D/Y) — predominante — com BR (D/M/Y) onde o
 * dia ultrapassa 12. Também aceita ISO (Y-M-D), texto parentético colado
 * (ex: "21/07/2025 (PRORROGADO)") e espaços ao redor.
 *
 * A heurística é simples: se o primeiro segmento > 12, só pode ser BR.
 * Caso contrário, tenta US; se inválido, cai pra BR.
 */
class CsvDateParser
{
    public function parse(string $valor): ?CarbonImmutable
    {
        $limpo = $this->limpar($valor);
        if ($limpo === '') {
            return null;
        }

        // ISO Y-M-D — se está em ISO, parse direto.
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $limpo, $m) === 1) {
            return $this->tentar((int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // Formato com barras.
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $limpo, $m) !== 1) {
            return null;
        }

        $a = (int) $m[1];
        $b = (int) $m[2];
        $ano = (int) $m[3];

        // Se primeiro segmento > 12, só pode ser D/M/Y (BR).
        if ($a > 12) {
            return $this->tentar($ano, $b, $a);
        }

        // Caso ambíguo: tenta US (M/D/Y) primeiro.
        $usa = $this->tentar($ano, $a, $b);
        if ($usa !== null) {
            return $usa;
        }

        return $this->tentar($ano, $b, $a);
    }

    /**
     * Parse de intervalo "DD/MM/YYYY à DD/MM/YYYY" (com ou sem acento no "à").
     *
     * @return array{inicio: CarbonImmutable, fim: CarbonImmutable}|null
     */
    public function parseIntervalo(string $valor): ?array
    {
        $limpo = $this->limpar($valor);
        if ($limpo === '') {
            return null;
        }

        $partes = preg_split('/\s+(à|a)\s+/u', $limpo, 2);
        if ($partes === false || count($partes) !== 2) {
            return null;
        }

        $inicio = $this->parse($partes[0]);
        $fim = $this->parse($partes[1]);
        if ($inicio === null || $fim === null) {
            return null;
        }

        return ['inicio' => $inicio, 'fim' => $fim];
    }

    private function limpar(string $valor): string
    {
        // Remove texto parentético colado (ex: "(PRORROGADO)") e normaliza espaços.
        $sem_parenteses = (string) preg_replace('/\s*\([^)]*\)\s*/u', '', $valor);

        return trim($sem_parenteses);
    }

    private function tentar(int $ano, int $mes, int $dia): ?CarbonImmutable
    {
        if (! checkdate($mes, $dia, $ano)) {
            return null;
        }

        return CarbonImmutable::create($ano, $mes, $dia, 0, 0, 0);
    }
}
