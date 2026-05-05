<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Roda diariamente às 00:05 — pega pontos esquecidos do dia anterior e
// fecha com saida=entrada+horas_diarias. Idempotente.
Schedule::command('ponto:fechar-abertos')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->onOneServer();

// Sincroniza setores com as APIs do TRE-AC (/unidades/ e /lotacao/).
Schedule::command('setores:sincronizar')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();
