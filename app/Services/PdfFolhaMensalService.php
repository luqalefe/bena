<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;

class PdfFolhaMensalService
{
    public function __construct(
        private readonly FolhaMensalService $folha,
        private readonly AssinaturaService $assinaturas,
    ) {}

    public function gerar(Estagiario $estagiario, int $ano, int $mes): DomPDF
    {
        $folha = $this->folha->montar($estagiario, $ano, $mes);
        $verificacoes = $this->assinaturas->verificar($estagiario, $ano, $mes);

        return Pdf::loadView('frequencia.pdf', [
            'folha' => $folha,
            'estagiario' => $estagiario,
            'verificacoes' => $verificacoes,
        ])->setPaper('a4', 'portrait');
    }

    public function nomeArquivo(Estagiario $estagiario, int $ano, int $mes): string
    {
        return sprintf(
            'frequencia_%s_%04d-%02d.pdf',
            $estagiario->username,
            $ano,
            $mes
        );
    }
}
