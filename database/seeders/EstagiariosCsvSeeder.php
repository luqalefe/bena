<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Estagiario;
use App\Models\Supervisor;
use App\Support\CsvDateParser;
use App\Support\NomeNormalizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Carga inicial dos estagiários a partir do CSV institucional.
 *
 * Estratégia em duas passadas:
 *   1. Extrai supervisores distintos do CSV e os cria por nome (firstOrCreate).
 *   2. Para cada estagiário, faz updateOrCreate por SEI (chave natural).
 *
 * Idempotente: rodar duas vezes não duplica.
 *
 * Usa parser tolerante (App\Support\CsvDateParser) para datas em formatos
 * misturados (US/BR) e contaminadas com texto. Linhas com data inválida
 * recebem warning no log mas não bloqueiam o seed.
 */
class EstagiariosCsvSeeder extends Seeder
{
    private const CSV_PATH = __DIR__.'/data/Lista Total de Estagiarios de 2026 - ligados ATUALIZADA - Plan3.csv';

    public function __construct(
        private readonly CsvDateParser $dataParser = new CsvDateParser,
        private readonly NomeNormalizer $nomeNormalizer = new NomeNormalizer,
    ) {}

    public function run(): void
    {
        $linhas = $this->lerCsv();

        $supervisoresPorNome = $this->semearSupervisores($linhas);

        foreach ($linhas as $linha) {
            $this->semearEstagiario($linha, $supervisoresPorNome);
        }
    }

    /**
     * @return list<array<string, string>>
     */
    private function lerCsv(): array
    {
        if (! is_file(self::CSV_PATH)) {
            throw new RuntimeException('CSV de estagiários não encontrado: '.self::CSV_PATH);
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('Não foi possível abrir o CSV: '.self::CSV_PATH);
        }

        $cabecalho = fgetcsv($handle);
        if ($cabecalho === false) {
            fclose($handle);
            throw new RuntimeException('CSV vazio.');
        }

        // Strip BOM e normaliza chaves do header.
        $cabecalho = array_map(
            fn (string $col): string => trim(str_replace("\u{FEFF}", '', $col)),
            $cabecalho,
        );

        $linhas = [];
        while (($colunas = fgetcsv($handle)) !== false) {
            // Linhas vazias (último \n do arquivo) — pula.
            if (count(array_filter($colunas, static fn ($v): bool => trim((string) $v) !== '')) === 0) {
                continue;
            }

            // Pode haver linhas com menos colunas que o header — completa com vazio.
            $colunas = array_pad($colunas, count($cabecalho), '');

            /** @var array<string, string> $linha */
            $linha = array_combine($cabecalho, $colunas);

            if (trim($linha['NOME'] ?? '') === '') {
                continue;
            }

            $linhas[] = $linha;
        }

        fclose($handle);

        return $linhas;
    }

    /**
     * @param  list<array<string, string>>  $linhas
     * @return array<string, Supervisor>
     */
    private function semearSupervisores(array $linhas): array
    {
        $porNome = [];

        foreach ($linhas as $linha) {
            $nomeBruto = trim($linha['SUPERVISOR'] ?? '');
            if ($nomeBruto === '') {
                continue;
            }

            $nome = $this->nomeNormalizer->normalizar($nomeBruto);

            if (isset($porNome[$nome])) {
                continue;
            }

            $porNome[$nome] = Supervisor::firstOrCreate(
                ['nome' => $nome],
                ['ativo' => true],
            );
        }

        return $porNome;
    }

    /**
     * @param  array<string, string>  $linha
     * @param  array<string, Supervisor>  $supervisoresPorNome
     */
    private function semearEstagiario(array $linha, array $supervisoresPorNome): void
    {
        $sei = trim($linha['N° SEI'] ?? '');
        if ($sei === '') {
            Log::warning('[EstagiariosCsvSeeder] linha sem SEI ignorada', ['nome' => $linha['NOME'] ?? '']);

            return;
        }

        $email = trim($linha['EMAIL'] ?? '') ?: null;
        $username = $email !== null ? explode('@', $email, 2)[0] : null;

        $supervisorNomeBruto = trim($linha['SUPERVISOR'] ?? '');
        $supervisor = null;
        if ($supervisorNomeBruto !== '') {
            $supervisor = $supervisoresPorNome[$this->nomeNormalizer->normalizar($supervisorNomeBruto)] ?? null;
        }

        $inicio = $this->dataParser->parse($linha['INICIO DE ESTÁGIO'] ?? '');
        $fim = $this->dataParser->parse($linha['FIM DE CONTRATO'] ?? '');
        $prorrogacao = $this->dataParser->parseIntervalo($linha['PRORROGAÇÃO'] ?? '');

        if ($inicio === null && trim($linha['INICIO DE ESTÁGIO'] ?? '') !== '') {
            Log::warning('[EstagiariosCsvSeeder] data de início inválida', [
                'sei' => $sei,
                'valor' => $linha['INICIO DE ESTÁGIO'],
            ]);
        }
        if ($fim === null && trim($linha['FIM DE CONTRATO'] ?? '') !== '') {
            Log::warning('[EstagiariosCsvSeeder] data de fim inválida', [
                'sei' => $sei,
                'valor' => $linha['FIM DE CONTRATO'],
            ]);
        }

        Estagiario::updateOrCreate(
            ['sei' => $sei],
            [
                'nome' => $this->nomeNormalizer->normalizar($linha['NOME'] ?? ''),
                'email' => $email,
                'username' => $username,
                'lotacao' => trim($linha['SETOR'] ?? '') ?: null,
                'instituicao_ensino' => trim($linha['INSTITUIÇÃO DE ENSINO'] ?? '') ?: null,
                'inicio_estagio' => $inicio?->format('Y-m-d'),
                'fim_estagio' => $fim?->format('Y-m-d'),
                'prorrogacao_inicio' => $prorrogacao !== null ? $prorrogacao['inicio']->format('Y-m-d') : null,
                'prorrogacao_fim' => $prorrogacao !== null ? $prorrogacao['fim']->format('Y-m-d') : null,
                'horas_diarias' => 5.00,
                'ativo' => true,
                'supervisor_id' => $supervisor?->id,
                'supervisor_nome' => $supervisor?->nome,
                'supervisor_username' => $supervisor?->username,
            ],
        );
    }
}
