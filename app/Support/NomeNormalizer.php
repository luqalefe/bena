<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normaliza nomes vindos do CSV institucional (UPPERCASE) para Title Case
 * com partículas portuguesas (de, da, do, dos, das, e) em lowercase.
 */
class NomeNormalizer
{
    private const PARTICULAS = ['de', 'da', 'do', 'dos', 'das', 'e'];

    public function normalizar(string $valor): string
    {
        $colapsado = (string) preg_replace('/\s+/u', ' ', trim($valor));
        if ($colapsado === '') {
            return '';
        }

        $palavras = explode(' ', $colapsado);
        $resultado = [];

        foreach ($palavras as $i => $palavra) {
            $minuscula = mb_strtolower($palavra);

            if ($i > 0 && in_array($minuscula, self::PARTICULAS, true)) {
                $resultado[] = $minuscula;

                continue;
            }

            $resultado[] = mb_convert_case($palavra, MB_CASE_TITLE);
        }

        return implode(' ', $resultado);
    }
}
