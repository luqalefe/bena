<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PontoService;
use Illuminate\Console\Command;

class FecharPontosAbertosCommand extends Command
{
    protected $signature = 'ponto:fechar-abertos';

    protected $description = 'Fecha pontos esquecidos (entrada sem saida em dias passados) usando horas_diarias do estagiario';

    public function handle(PontoService $ponto): int
    {
        $fechados = $ponto->fecharPontosAbertos();

        $this->info("Fechados: {$fechados} ponto(s) aberto(s).");

        return self::SUCCESS;
    }
}
