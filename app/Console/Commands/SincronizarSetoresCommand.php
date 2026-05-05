<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setor;
use App\Services\TreApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SincronizarSetoresCommand extends Command
{
    protected $signature = 'setores:sincronizar';

    protected $description = 'Sincroniza a tabela de setores a partir das APIs /unidades/ e /lotacao/ do TRE-AC.';

    public function handle(TreApiClient $api): int
    {
        Cache::forget('tre_ac.unidades');
        Cache::forget('tre_ac.lotacoes');

        try {
            $unidades = $api->unidades();
            $lotacoes = $api->lotacoes();
        } catch (Throwable $e) {
            $this->error("Falha ao consultar APIs do TRE-AC: {$e->getMessage()}");

            return self::FAILURE;
        }

        $siglasApi = array_unique(array_merge($unidades, array_keys($lotacoes)));
        sort($siglasApi);

        $existentes = Setor::all()->keyBy('sigla');
        $agora = now();

        $criados = 0;
        $atualizados = 0;
        $inativados = 0;

        foreach ($siglasApi as $sigla) {
            $quantidade = $lotacoes[$sigla] ?? null;
            $setor = $existentes->get($sigla);

            if ($setor === null) {
                Setor::create([
                    'sigla' => $sigla,
                    'quantidade_servidores' => $quantidade,
                    'ativo' => true,
                    'sincronizado_em' => $agora,
                ]);
                $criados++;

                continue;
            }

            $mudou = $setor->quantidade_servidores !== $quantidade || $setor->ativo === false;

            $setor->fill([
                'quantidade_servidores' => $quantidade,
                'ativo' => true,
                'sincronizado_em' => $agora,
            ])->save();

            if ($mudou) {
                $atualizados++;
            }
        }

        $apiSet = array_flip($siglasApi);
        foreach ($existentes as $sigla => $setor) {
            if (! isset($apiSet[$sigla]) && $setor->ativo) {
                $setor->update(['ativo' => false]);
                $inativados++;
            }
        }

        $this->info("Sincronização concluída: {$criados} criados, {$atualizados} atualizados, {$inativados} inativados.");

        return self::SUCCESS;
    }
}
