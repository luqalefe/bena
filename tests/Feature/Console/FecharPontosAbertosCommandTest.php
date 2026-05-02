<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FecharPontosAbertosCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fecha_pontos_abertos_e_loga_quantidade(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');
        $estagiario = Estagiario::factory()->create(['horas_diarias' => 5.00]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
        ]);

        $this->artisan('ponto:fechar-abertos')
            ->expectsOutputToContain('Fechados: 1 ponto(s) aberto(s).')
            ->assertSuccessful();

        $f = Frequencia::first();
        $this->assertSame('14:00:00', $f->saida->format('H:i:s'));
        $this->assertTrue($f->saida_automatica);
    }

    public function test_command_sem_pontos_abertos_loga_zero(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');

        $this->artisan('ponto:fechar-abertos')
            ->expectsOutputToContain('Fechados: 0 ponto(s) aberto(s).')
            ->assertSuccessful();
    }

    public function test_schedule_executa_diariamente_as_00_05(): void
    {
        $events = collect(app(Schedule::class)->events())
            ->filter(fn ($e) => str_contains($e->command ?? '', 'ponto:fechar-abertos'));

        $this->assertCount(1, $events);
        $this->assertSame('5 0 * * *', $events->first()->expression);
    }
}
