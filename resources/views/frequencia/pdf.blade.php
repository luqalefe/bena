<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Folha de Frequência — {{ $estagiario->nome }} — {{ $folha->tituloExtenso() }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; }
        h1 { font-size: 14pt; color: #003366; margin: 0 0 0.5rem; }
        h2 { font-size: 11pt; color: #003366; margin: 1rem 0 0.4rem; }
        .header { border-bottom: 2px solid #003366; padding-bottom: 0.5rem; margin-bottom: 1rem; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .header td.logo { width: 70px; }
        .header td.logo img { width: 60px; height: 60px; }
        .header .titulo { font-size: 9pt; color: #555; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 9pt; }
        .meta td { padding: 0.2rem 0.4rem; border: 1px solid #d4d4d4; }
        .meta td.label { background: #f3f4f6; font-weight: bold; width: 22%; }
        table.dias { width: 100%; border-collapse: collapse; font-size: 9pt; }
        table.dias th, table.dias td { border: 1px solid #999; padding: 0.25rem 0.4rem; text-align: left; }
        table.dias th { background: #003366; color: #fff; }
        table.dias tr.fds td, table.dias tr.feriado td, table.dias tr.recesso td { font-style: italic; color: #555; }
        table.dias tr.fds { background: #f3f4f6; }
        table.dias tr.feriado { background: #fef9c3; }
        table.dias tr.recesso { background: #e0f2fe; }
        .total { text-align: right; font-weight: bold; padding: 0.5rem 0.4rem; }
        .assinaturas { margin-top: 1.5rem; display: table; width: 100%; }
        .assinaturas .col { display: table-cell; width: 50%; padding: 0.5rem; vertical-align: top; }
        .bloco-assinatura { border: 1px dashed #999; padding: 0.5rem; min-height: 70px; font-size: 9pt; }
        .bloco-assinatura .label { font-weight: bold; color: #003366; }
        .bloco-assinatura.placeholder { color: #888; font-style: italic; }
        .footer { margin-top: 1rem; font-size: 8pt; color: #666; border-top: 1px solid #d4d4d4; padding-top: 0.4rem; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    <img src="{{ public_path('img/bena.png') }}" alt="Bena">
                </td>
                <td>
                    <div class="titulo">Tribunal Regional Eleitoral do Acre — Bena · Controle de Frequência de Estagiários</div>
                    <h1>Folha de Frequência — {{ $folha->tituloExtenso() }}</h1>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Estagiário</td><td>{{ $estagiario->nome }}</td>
            <td class="label">Matrícula</td><td>{{ $estagiario->matricula ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Lotação</td><td>{{ $estagiario->setor?->sigla ?? '—' }}</td>
            <td class="label">SEI</td><td>{{ $estagiario->sei ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Supervisor</td><td>{{ $estagiario->supervisor_nome ?? '—' }}</td>
            <td class="label">Início / Fim</td>
            <td>
                {{ optional($estagiario->inicio_estagio)->format('d/m/Y') ?? '—' }}
                a
                {{ optional($estagiario->fim_estagio)->format('d/m/Y') ?? '—' }}
            </td>
        </tr>
    </table>

    <table class="dias">
        <thead>
            <tr>
                <th>Data</th>
                <th>Dia</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Horas</th>
                <th>Status</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            @php
                $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                $rotuloFds = ['sabado' => 'Sábado', 'domingo' => 'Domingo'];
            @endphp
            @foreach ($folha->dias as $dia)
                @php
                    $cls = match (true) {
                        $dia->tipo === 'feriado' => 'feriado',
                        $dia->tipo === 'recesso' => 'recesso',
                        in_array($dia->tipo, ['sabado', 'domingo'], true) => 'fds',
                        default => '',
                    };
                @endphp
                <tr class="{{ $cls }}">
                    <td>{{ $dia->data->format('d/m') }}</td>
                    <td>{{ $diasSemana[$dia->data->dayOfWeek] }}</td>
                    @if ($dia->tipo === 'feriado')
                        <td colspan="3">{{ $dia->descricaoFeriado }}</td>
                        <td>feriado</td>
                        <td>—</td>
                    @elseif (in_array($dia->tipo, ['sabado', 'domingo'], true))
                        <td colspan="3">—</td>
                        <td>{{ $rotuloFds[$dia->tipo] }}</td>
                        <td>—</td>
                    @elseif ($dia->tipo === 'recesso')
                        <td colspan="3">Recesso</td>
                        <td>recesso</td>
                        <td>—</td>
                    @elseif ($dia->frequencia !== null && ($dia->frequencia->entrada !== null || $dia->frequencia->saida !== null))
                        <td>{{ $dia->frequencia->entrada?->format('H:i') ?? '—' }}</td>
                        <td>
                            {{ $dia->frequencia->saida?->format('H:i') ?? '—' }}
                            @if ($dia->frequencia->saida_automatica) *@endif
                        </td>
                        <td>{{ $dia->frequencia->horas !== null ? number_format((float) $dia->frequencia->horas, 2, ',', '') : '—' }}</td>
                        <td>{{ $dia->frequencia->saida === null ? 'em andamento' : ($dia->frequencia->saida_automatica ? 'batido (auto)' : 'batido') }}</td>
                        <td>{{ $dia->frequencia->observacao ?? '—' }}</td>
                    @elseif ($dia->frequencia?->observacao)
                        <td>—</td><td>—</td><td>—</td><td>justificado</td>
                        <td>{{ $dia->frequencia->observacao }}</td>
                    @else
                        <td>—</td><td>—</td><td>—</td><td>não batido</td>
                        <td>—</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">Total de horas no mês:</td>
                <td colspan="3" class="total">{{ number_format($folha->totalHoras, 2, ',', '') }} h</td>
            </tr>
        </tfoot>
    </table>

    @php
        $assinaturas = collect($verificacoes ?? [])->keyBy('papel');
        $blocoEstagiario = $assinaturas->get('estagiario');
        $blocoSupervisor = $assinaturas->get('supervisor');
    @endphp

    <div class="assinaturas">
        <div class="col">
            @if ($blocoEstagiario)
                <div class="bloco-assinatura">
                    <div class="label">Assinatura do estagiário</div>
                    <div>{{ $blocoEstagiario['assinatura']->assinante_username }}</div>
                    <div>Em {{ $blocoEstagiario['assinatura']->assinado_em->format('d/m/Y H:i:s') }}</div>
                    <div>Hash: <code>{{ $blocoEstagiario['assinatura']->hash_truncado }}</code></div>
                    <div>{{ $blocoEstagiario['integro'] ? '✓ íntegra' : '⚠ alterada após assinatura' }}</div>
                </div>
            @else
                <div class="bloco-assinatura placeholder">
                    <div class="label">Assinatura do estagiário</div>
                    Aguardando assinatura eletrônica.
                </div>
            @endif
        </div>
        <div class="col">
            @if ($blocoSupervisor)
                <div class="bloco-assinatura">
                    <div class="label">Contra-assinatura do supervisor</div>
                    <div>{{ $blocoSupervisor['assinatura']->assinante_username }}</div>
                    <div>Em {{ $blocoSupervisor['assinatura']->assinado_em->format('d/m/Y H:i:s') }}</div>
                    <div>Hash: <code>{{ $blocoSupervisor['assinatura']->hash_truncado }}</code></div>
                    <div>{{ $blocoSupervisor['integro'] ? '✓ íntegra' : '⚠ alterada após assinatura' }}</div>
                </div>
            @else
                <div class="bloco-assinatura placeholder">
                    <div class="label">Contra-assinatura do supervisor</div>
                    Aguardando contra-assinatura eletrônica.
                </div>
            @endif
        </div>
    </div>

    <div class="footer">
        Documento gerado em {{ now()->format('d/m/Y H:i:s') }}.
        Hash baseado em SHA-256 do conjunto canônico de registros do mês.
        A integridade pode ser reverificada a qualquer momento na tela da
        folha mensal.
    </div>
</body>
</html>
