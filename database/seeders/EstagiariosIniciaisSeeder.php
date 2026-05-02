<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Estagiario;
use Illuminate\Database\Seeder;
use RuntimeException;

class EstagiariosIniciaisSeeder extends Seeder
{
    private const CSV_PATH = __DIR__.'/data/estagiarios_iniciais.csv';

    public function run(): void
    {
        $linhas = $this->lerCsv();

        foreach ($linhas as $linha) {
            $username = $this->gerarUsername($linha['nome'], $linha['sei']);

            Estagiario::updateOrCreate(
                ['sei' => $linha['sei']],
                [
                    'username' => $username,
                    'nome' => $linha['nome'],
                    'email' => $username.'@tre-ac.jus.br',
                    'lotacao' => $linha['lotacao'],
                    'supervisor_nome' => $linha['supervisor'],
                    'horas_diarias' => 5.00,
                    'ativo' => true,
                ]
            );
        }
    }

    /**
     * @return list<array{nome: string, lotacao: string, supervisor: string, sei: string}>
     */
    private function lerCsv(): array
    {
        if (! is_file(self::CSV_PATH)) {
            throw new RuntimeException('CSV de estagiários iniciais não encontrado: '.self::CSV_PATH);
        }

        $handle = fopen(self::CSV_PATH, 'r');
        if ($handle === false) {
            throw new RuntimeException('Não foi possível abrir o CSV: '.self::CSV_PATH);
        }

        $cabecalho = fgetcsv($handle, separator: ';');
        if ($cabecalho === false) {
            fclose($handle);
            throw new RuntimeException('CSV vazio: '.self::CSV_PATH);
        }

        $registros = [];
        while (($colunas = fgetcsv($handle, separator: ';')) !== false) {
            $linha = array_combine($cabecalho, $colunas);
            $registros[] = [
                'nome' => trim($linha['nome']),
                'lotacao' => trim($linha['lotacao']),
                'supervisor' => trim($linha['supervisor']),
                'sei' => trim($linha['sei']),
            ];
        }
        fclose($handle);

        return $registros;
    }

    private function gerarUsername(string $nome, string $sei): string
    {
        $semAcento = $this->removerAcentos($nome);
        $partes = preg_split('/\s+/', strtolower(trim($semAcento))) ?: [];

        $stop = ['de', 'da', 'do', 'das', 'dos'];
        $partes = array_values(array_filter($partes, fn ($p) => ! in_array($p, $stop, true) && $p !== ''));

        if (count($partes) < 2) {
            return preg_replace('/[^a-z0-9]/', '', $sei) ?? $sei;
        }

        return $partes[0].'.'.end($partes);
    }

    private function removerAcentos(string $texto): string
    {
        $de = ['á', 'à', 'ã', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'õ', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ',
            'Á', 'À', 'Ã', 'Â', 'Ä', 'É', 'È', 'Ê', 'Ë', 'Í', 'Ì', 'Î', 'Ï', 'Ó', 'Ò', 'Õ', 'Ô', 'Ö', 'Ú', 'Ù', 'Û', 'Ü', 'Ç', 'Ñ'];
        $para = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n',
            'A', 'A', 'A', 'A', 'A', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'C', 'N'];

        return str_replace($de, $para, $texto);
    }
}
