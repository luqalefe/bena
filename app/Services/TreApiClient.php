<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class TreApiClient
{
    /**
     * @return array<int, string>
     */
    public function unidades(): array
    {
        return Cache::remember(
            'tre_ac.unidades',
            $this->cacheTtl(),
            fn () => $this->fetchUnidades(),
        );
    }

    /**
     * @return array<string, int>
     */
    public function lotacoes(): array
    {
        return Cache::remember(
            'tre_ac.lotacoes',
            $this->cacheTtl(),
            fn () => $this->fetchLotacoes(),
        );
    }

    /**
     * @return array<int, string>
     */
    private function fetchUnidades(): array
    {
        $rows = $this->get('/unidades/');

        $siglas = [];
        foreach ($rows as $row) {
            $sigla = $this->siglaFrom($row);
            if ($sigla !== null) {
                $siglas[] = $sigla;
            }
        }

        return $siglas;
    }

    /**
     * @return array<string, int>
     */
    private function fetchLotacoes(): array
    {
        $rows = $this->get('/lotacao/');

        $lotacoes = [];
        foreach ($rows as $row) {
            $sigla = $this->siglaFrom($row);
            if ($sigla === null) {
                continue;
            }
            $lotacoes[$sigla] = (int) ($row['QUANTIDADE'] ?? 0);
        }

        return $lotacoes;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get(string $path): array
    {
        $url = rtrim((string) config('services.tre_ac.base_url'), '/').$path;
        $timeout = (int) config('services.tre_ac.timeout', 8);

        try {
            $response = Http::timeout($timeout)->get($url);
        } catch (ConnectionException $e) {
            throw new RuntimeException("Falha ao conectar em {$url}: {$e->getMessage()}", 0, $e);
        }

        if (! $response->successful()) {
            throw new RuntimeException("API TRE-AC retornou HTTP {$response->status()} para {$url}");
        }

        try {
            $data = $response->json();
        } catch (Throwable $e) {
            throw new RuntimeException("Resposta JSON inválida de {$url}: {$e->getMessage()}", 0, $e);
        }

        if (! is_array($data) || (! empty($data) && ! array_is_list($data))) {
            throw new RuntimeException("Resposta de {$url} não é uma lista JSON.");
        }

        return $data;
    }

    private function siglaFrom(mixed $row): ?string
    {
        if (! is_array($row)) {
            return null;
        }
        $sigla = trim((string) ($row['SIGLA_UNID_TSE'] ?? ''));

        return $sigla === '' ? null : $sigla;
    }

    private function cacheTtl(): int
    {
        return (int) config('services.tre_ac.cache_ttl', 3600);
    }
}
