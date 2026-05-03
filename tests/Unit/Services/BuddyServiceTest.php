<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Services\BuddyData;
use App\Services\BuddyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuddyServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_garantir_buddy_atribui_tipo_quando_null(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario);

        $this->assertNotNull($estagiario->fresh()->buddy_tipo);
        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos'),
        );
    }

    public function test_garantir_buddy_nao_altera_tipo_existente(): void
    {
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        app(BuddyService::class)->garantirBuddy($estagiario);

        $this->assertSame('coruja', $estagiario->fresh()->buddy_tipo);
    }

    public function test_montar_retorna_buddy_data_com_frase(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00'); // segunda

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertInstanceOf(BuddyData::class, $data);
        $this->assertSame('coruja', $data->tipo);
        $this->assertSame('Coruinha', $data->nome);
        $this->assertSame('🦉', $data->emoji);
        $this->assertNotEmpty($data->frase);
    }

    public function test_montar_usa_frase_deterministica_por_dia(): void
    {
        Carbon::setTestNow('2026-05-05 09:00:00'); // terça

        $estagiario = Estagiario::factory()->comBuddy('cachorro')->create();
        $service = app(BuddyService::class);

        $primeira = $service->montar($estagiario, 'em_andamento');

        // Mesmo dia, mesmo status, mesma hora-do-dia → mesma frase.
        Carbon::setTestNow('2026-05-05 11:00:00');
        $segunda = $service->montar($estagiario, 'em_andamento');

        $this->assertSame($primeira->frase, $segunda->frase);
    }

    public function test_montar_retorna_frase_generica_quando_contexto_vazio(): void
    {
        // Configura buddy 'coruja' com frases.tipo.dia.status vazio,
        // mas frases.coruja.generica preenchido.
        config([
            'buddies.frases.coruja.segunda.aguardando_entrada' => [],
            'buddies.frases.coruja.generica' => ['Frase de fallback da coruja.'],
        ]);

        Carbon::setTestNow('2026-05-04 10:00:00'); // segunda

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertSame('Frase de fallback da coruja.', $data->frase);
    }

    public function test_boas_vindas_retorna_buddy_com_frase_de_apresentacao(): void
    {
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->boasVindas($estagiario);

        $this->assertInstanceOf(BuddyData::class, $data);
        $this->assertSame('coruja', $data->tipo);
        $this->assertSame('Coruinha', $data->nome);
        $this->assertSame('🦉', $data->emoji);
        $this->assertNotEmpty($data->frase);
        $this->assertContains(
            $data->frase,
            config('buddies.frases.coruja.boas_vindas'),
        );
    }

    public function test_garantir_buddy_de_supervisor_usa_pool_senior(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'S');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_supervisores'),
        );
    }

    public function test_garantir_buddy_de_admin_usa_pool_senior(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario, '0');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_supervisores'),
        );
    }

    public function test_garantir_buddy_de_estagiario_usa_pool_padrao(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'E');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos'),
        );
    }
}
