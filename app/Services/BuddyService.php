<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Support\BuddySprite;
use Carbon\CarbonImmutable;

class BuddyService
{
    public function __construct(private readonly BuddySprite $sprites) {}

    public function garantirBuddy(Estagiario $estagiario, ?string $grupo = null): void
    {
        if ($estagiario->buddy_tipo !== null) {
            return;
        }

        $tipos = $this->poolPara($estagiario, $grupo);
        if ($tipos === []) {
            return;
        }

        $estagiario->buddy_tipo = $tipos[array_rand($tipos)];
        $estagiario->save();
    }

    /**
     * @return array<int, string>
     */
    private function poolPara(Estagiario $estagiario, ?string $grupo): array
    {
        // Estagiários das lotações listadas em `buddies.lotacoes_lendarias`
        // (STI e SSEC, hoje) recebem o pool lendário, inspirado em personagens
        // da casa. Servidores e admin dessas seções continuam no pool sênior.
        $lendarias = (array) config('buddies.lotacoes_lendarias', []);
        if ($grupo === 'E' && in_array($estagiario->lotacao, $lendarias, true)) {
            return (array) config('buddies.tipos_lendarios', []);
        }

        $key = match ($grupo) {
            '0', 'S' => 'tipos_supervisores',
            default => 'tipos',
        };

        return (array) config("buddies.{$key}", []);
    }

    public function montar(Estagiario $estagiario, string $statusPonto): BuddyData
    {
        $tipo = (string) $estagiario->buddy_tipo;
        $perfil = (array) config("buddies.perfis.{$tipo}", ['emoji' => '🐾', 'nome' => 'Buddy']);

        $agora = CarbonImmutable::now();
        $diaSemana = $this->diaSemanaChave($agora);

        $frases = (array) config("buddies.frases.{$tipo}.{$diaSemana}.{$statusPonto}", []);

        if ($frases === []) {
            $frase = $this->fraseGenerica($tipo);
        } else {
            // Determinístico por (dia-do-mês, bloco de 12h): a frase é estável
            // dentro de um mesmo bloco (manhã 0-11h ou tarde-noite 12-23h) e
            // varia entre blocos e entre dias.
            $indice = ($agora->day + intdiv($agora->hour, 12)) % count($frases);
            $frase = $frases[$indice];
        }

        return new BuddyData(
            tipo: $tipo,
            emoji: (string) ($perfil['emoji'] ?? '🐾'),
            nome: (string) ($perfil['nome'] ?? 'Buddy'),
            frase: $frase,
            sprite: $this->sprites->caminho($tipo),
        );
    }

    private function diaSemanaChave(CarbonImmutable $data): string
    {
        return match ($data->dayOfWeek) {
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            default => 'segunda',
        };
    }

    private function fraseGenerica(string $tipo): string
    {
        $genericas = (array) config("buddies.frases.{$tipo}.generica", ['Bom dia! 👋']);

        return $genericas[array_rand($genericas)];
    }

    public function boasVindas(Estagiario $estagiario): BuddyData
    {
        $tipo = (string) $estagiario->buddy_tipo;
        $perfil = (array) config("buddies.perfis.{$tipo}", ['emoji' => '🐾', 'nome' => 'Buddy']);
        $frases = (array) config("buddies.frases.{$tipo}.boas_vindas", []);

        $frase = $frases === []
            ? $this->fraseGenerica($tipo)
            : $frases[array_rand($frases)];

        return new BuddyData(
            tipo: $tipo,
            emoji: (string) ($perfil['emoji'] ?? '🐾'),
            nome: (string) ($perfil['nome'] ?? 'Buddy'),
            frase: $frase,
            sprite: $this->sprites->caminho($tipo),
        );
    }
}
