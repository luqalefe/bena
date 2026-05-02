<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Estagiario;
use App\Models\Feriado;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolhaMensalTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return [
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
            'Remote-Name' => 'Lucas Estagiário',
            'Remote-Email' => 'lucas.dev@example.local',
        ];
    }

    public function test_estagiario_autenticado_acessa_folha_de_2026_04_com_200(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/frequencia/2026/04')
            ->assertStatus(200);
    }

    public function test_get_frequencia_sem_params_redireciona_para_mes_corrente(): void
    {
        Carbon::setTestNow('2026-07-15 10:00:00');

        $this->withHeaders($this->estagiarioHeaders())
            ->get('/frequencia')
            ->assertRedirect('/frequencia/2026/7');
    }

    public function test_view_mostra_titulo_extenso_e_links_de_navegacao(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00'); // mês corrente: maio/2026

        $this->withHeaders($this->estagiarioHeaders())
            ->get('/frequencia/2026/04')
            ->assertStatus(200)
            ->assertSee('Abril / 2026')          // título extenso
            ->assertSee('/frequencia/2026/3', false)  // anterior
            ->assertSee('/frequencia/2026/5', false); // próximo (maio = corrente, permitido)
    }

    public function test_view_desabilita_proximo_quando_destino_eh_futuro(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00'); // corrente: maio/2026

        // Visualizando maio: próximo seria junho (futuro) → desabilitado
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/frequencia/2026/05')
            ->assertStatus(200)
            ->assertSee('/frequencia/2026/4', false)
            ->assertDontSee('/frequencia/2026/6', false);
    }

    public function test_view_renderiza_dia_batido_feriado_fds_e_total(): void
    {
        // O middleware cria o Estagiário com o username do header. Precisamos
        // ter o estagiário em mãos para criar Frequencia ANTES da request.
        $estagiario = Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'email' => 'lucas.dev@example.local',
            'nome' => 'Lucas Estagiário',
        ]);

        // Dia útil batido — 06/04/2026 (segunda)
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-06',
            'entrada' => '09:00:00',
            'saida' => '13:30:00',
            'horas' => 4.5,
        ]);

        // Feriado — 21/04/2026 (Tiradentes)
        Feriado::create([
            'data' => '2026-04-21',
            'descricao' => 'Tiradentes',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $this->withHeaders($this->estagiarioHeaders())
            ->get('/frequencia/2026/04')
            ->assertStatus(200)
            ->assertSee('06/04')      // dia batido visível
            ->assertSee('09:00')      // entrada
            ->assertSee('13:30')      // saída
            ->assertSee('Tiradentes') // descrição do feriado
            ->assertSee('Sábado')     // 04/04 é sábado
            ->assertSee('Domingo')    // 05/04 é domingo
            ->assertSee('não batido') // dias úteis sem registro
            ->assertSee('4,50');      // total de horas no rodapé (pt-BR)
    }
}
