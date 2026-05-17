@extends('layouts.app')

@section('title', 'Folha mensal — Bena')

@php
    $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    $rotuloFimDeSemana = ['sabado' => 'Sábado', 'domingo' => 'Domingo'];
    $coresLinha = [
        'sabado' => 'background: #f3f4f6;',
        'domingo' => 'background: #f3f4f6;',
        'feriado' => 'background: #fef9c3;',
        'recesso' => 'background: #e0f2fe;',
    ];
@endphp

@section('content')
    @php
        $modoAdmin = $modoAdmin ?? false;
        $queryNavegacao = $queryNavegacao ?? [];
        $anterior = array_merge($folha->mesAnterior(), $queryNavegacao);
        $proximo = array_merge($folha->proximoMes(), $queryNavegacao);
        $podeAvancar = $folha->podeNavegarParaProximo();
    @endphp

    @if ($modoAdmin)
        <div style="background: var(--color-primary-lighten-02, #e6f0ff); color: var(--color-primary-darken-01); padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
            <span>
                <strong>Visualizando folha de {{ $estagiario->nome }}</strong>
                @if ($estagiario->setor)
                    — {{ $estagiario->setor->sigla }}
                @endif
            </span>
            <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: underline; font-size: 0.875rem;">← Dashboard admin</a>
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('frequencia.show', $anterior) }}" class="br-button">
            ← Anterior
        </a>

        <h1 style="color: var(--color-primary-default); margin: 0; font-size: 1.6rem;">
            {{ $folha->tituloExtenso() }}
        </h1>

        @if ($podeAvancar)
            <a href="{{ route('frequencia.show', $proximo) }}" class="br-button">
                Próximo →
            </a>
        @else
            <span class="br-button" style="opacity: 0.4; cursor: not-allowed;" aria-disabled="true">
                Próximo →
            </span>
        @endif
    </div>

    <div style="margin-bottom: 1rem; display: flex; justify-content: flex-end; gap: 0.5rem; flex-wrap: wrap;">
        @if (($podeAssinarComoEstagiario ?? false))
            <form method="POST" action="{{ route('frequencia.assinar', ['ano' => $folha->ano, 'mes' => $folha->mes]) }}" onsubmit="return confirm('Após assinar, alterações nos registros invalidarão o hash. Confirmar?');">
                @csrf
                <button type="submit" class="br-button primary"><i class="fas fa-pen" aria-hidden="true"></i> Assinar como estagiário</button>
            </form>
        @endif
        @if (($podeContraAssinarComoSupervisor ?? false))
            <form method="POST" action="{{ route('frequencia.contra-assinar', ['ano' => $folha->ano, 'mes' => $folha->mes, 'estagiario' => $estagiario->username]) }}" onsubmit="return confirm('Após contra-assinar, alterações nos registros invalidarão o hash. Confirmar?');">
                @csrf
                <button type="submit" class="br-button primary"><i class="fas fa-stamp" aria-hidden="true"></i> Contra-assinar como supervisor</button>
            </form>
        @endif
        <a href="{{ route('frequencia.pdf', array_merge(['ano' => $folha->ano, 'mes' => $folha->mes], $queryNavegacao)) }}" class="br-button" data-turbo="false">
            <i class="fas fa-file-pdf" aria-hidden="true"></i>
            Baixar PDF
        </a>
    </div>

    @php
        $verificacoes = $verificacoes ?? [];
        $souProprioEstagiario = $souProprioEstagiario ?? false;
        $souSupervisorResponsavel = $souSupervisorResponsavel ?? false;
    @endphp
    @if ($verificacoes)
        <section style="background: var(--color-secondary-01); border: 1px solid var(--color-secondary-04); padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1rem; color: var(--color-primary-default); margin: 0 0 0.5rem;">Assinaturas eletrônicas</h2>
            @foreach ($verificacoes as $v)
                @php
                    $rotuloPapel = $v['papel'] === 'supervisor' ? 'Contra-assinada pelo supervisor' : 'Assinada como estagiário';
                    $podeReassinar = ! $v['integro'] && (
                        ($v['papel'] === 'estagiario' && $souProprioEstagiario)
                        || ($v['papel'] === 'supervisor' && $souSupervisorResponsavel)
                    );
                    $rotaReassinar = $v['papel'] === 'supervisor'
                        ? route('frequencia.re-contra-assinar', ['ano' => $folha->ano, 'mes' => $folha->mes, 'estagiario' => $estagiario->username])
                        : route('frequencia.reassinar', ['ano' => $folha->ano, 'mes' => $folha->mes]);
                @endphp
                <div style="padding: 0.4rem 0; border-bottom: 1px dashed var(--color-secondary-03);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <strong>{{ $rotuloPapel }}</strong>
                            — {{ $v['assinatura']->assinante_username }}
                            em {{ $v['assinatura']->assinado_em->format('d/m/Y H:i:s') }}
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                            <code style="font-size: 0.8rem;">{{ $v['assinatura']->hash_truncado }}</code>
                            @if ($v['integro'])
                                <span class="badge-tre-ac is-completo">✓ íntegra</span>
                            @else
                                <span class="badge-tre-ac is-pendente" style="background: #fee2e2; color: #991b1b;">⚠ alterada</span>
                                @if ($podeReassinar)
                                    <form method="POST" action="{{ $rotaReassinar }}" onsubmit="return confirm('A assinatura anterior será marcada como substituída e o histórico será preservado. Confirmar re-assinatura da versão atual?');">
                                        @csrf
                                        <button type="submit" class="br-button" style="font-size: 0.8rem; padding: 0.25rem 0.6rem; background: #b45309; color: #fff;">
                                            <i class="fas fa-redo" aria-hidden="true"></i> Re-assinar versão atual
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if (! $v['integro'] && ! empty($v['diff']))
                        <details style="margin-top: 0.5rem; background: #fef2f2; border-left: 3px solid #dc2626; border-radius: 6px; padding: 0.6rem 0.85rem;">
                            <summary style="cursor: pointer; font-size: 0.875rem; color: #991b1b; font-weight: 600;">
                                Ver o que mudou desde a assinatura ({{ count($v['diff']) }})
                            </summary>
                            <ul style="margin: 0.6rem 0 0; padding-left: 1.25rem; font-size: 0.875rem; color: #7f1d1d; line-height: 1.55;">
                                @foreach ($v['diff'] as $m)
                                    @php
                                        $diaFmt = \Carbon\Carbon::parse($m['data'])->format('d/m/Y');
                                    @endphp
                                    @if ($m['tipo'] === 'dia_adicionado')
                                        <li><strong>{{ $diaFmt }}</strong> — dia novo registrado após a assinatura.</li>
                                    @elseif ($m['tipo'] === 'dia_removido')
                                        <li><strong>{{ $diaFmt }}</strong> — registro removido após a assinatura.</li>
                                    @else
                                        <li>
                                            <strong>{{ $diaFmt }}</strong> — campo <code>{{ $m['campo'] }}</code>:
                                            de <code>{{ $m['antes'] ?? '∅' }}</code>
                                            para <code>{{ $m['depois'] ?? '∅' }}</code>.
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align: left; padding: 0.5rem;">Data</th>
                <th style="text-align: left; padding: 0.5rem;">Dia</th>
                <th style="text-align: left; padding: 0.5rem;">Entrada</th>
                <th style="text-align: left; padding: 0.5rem;">Saída</th>
                <th style="text-align: left; padding: 0.5rem;">Horas</th>
                <th style="text-align: left; padding: 0.5rem;">Status</th>
                <th style="text-align: left; padding: 0.5rem;">Observação</th>
            </tr>
        </thead>
        <tbody>
            @php $podeEditarObservacao = $podeEditarObservacao ?? false; @endphp
            @foreach ($folha->dias as $dia)
                <tr style="{{ $coresLinha[$dia->tipo] ?? '' }}">
                    <td style="padding: 0.5rem;">{{ $dia->data->format('d/m') }}</td>
                    <td style="padding: 0.5rem;">{{ $diasSemana[$dia->data->dayOfWeek] }}</td>

                    @if ($dia->tipo === 'feriado')
                        <td colspan="3" style="padding: 0.5rem; font-style: italic;">
                            {{ $dia->descricaoFeriado }}
                        </td>
                        <td style="padding: 0.5rem;">
                            <span class="badge-tre-ac is-pendente">feriado</span>
                        </td>
                        <td style="padding: 0.5rem;">—</td>
                    @elseif (in_array($dia->tipo, ['sabado', 'domingo'], true))
                        <td colspan="3" style="padding: 0.5rem; color: var(--color-secondary-07);">—</td>
                        <td style="padding: 0.5rem;">
                            <span class="badge-tre-ac">{{ $rotuloFimDeSemana[$dia->tipo] }}</span>
                        </td>
                        <td style="padding: 0.5rem;">—</td>
                    @elseif ($dia->tipo === 'recesso')
                        <td colspan="3" style="padding: 0.5rem; font-style: italic; color: var(--color-secondary-07);">Recesso</td>
                        <td style="padding: 0.5rem;">
                            <span class="badge-tre-ac">recesso</span>
                        </td>
                        <td style="padding: 0.5rem;">—</td>
                    @else
                        @if ($dia->frequencia !== null && ($dia->frequencia->entrada !== null || $dia->frequencia->saida !== null))
                            <td style="padding: 0.5rem;">{{ $dia->frequencia->entrada?->format('H:i') ?? '—' }}</td>
                            <td style="padding: 0.5rem;">
                                {{ $dia->frequencia->saida?->format('H:i') ?? '—' }}
                                @if ($dia->frequencia->saida_automatica)
                                    <span title="Saída registrada automaticamente após {{ number_format((float) $dia->frequencia->horas, 2, ',', '') }}h sem batida (cron diário 00:05)" style="font-size: 0.7rem; color: #b45309; margin-left: 0.25rem; cursor: help;">⚠ auto</span>
                                @endif
                            </td>
                            <td style="padding: 0.5rem;">{{ $dia->frequencia->horas !== null ? number_format((float) $dia->frequencia->horas, 2, ',', '') : '—' }}</td>
                            <td style="padding: 0.5rem;">
                                @if ($dia->frequencia->saida === null)
                                    <span class="badge-tre-ac is-entrada">em andamento</span>
                                @elseif ($dia->frequencia->saida_automatica)
                                    <span class="badge-tre-ac" style="background: #fef3c7; color: #92400e;">batido (auto)</span>
                                @else
                                    <span class="badge-tre-ac is-completo">batido</span>
                                @endif
                            </td>
                        @else
                            <td style="padding: 0.5rem;">—</td>
                            <td style="padding: 0.5rem;">—</td>
                            <td style="padding: 0.5rem;">—</td>
                            <td style="padding: 0.5rem;">
                                <span class="badge-tre-ac is-pendente">não batido</span>
                            </td>
                        @endif
                        <td style="padding: 0.5rem;">
                            @if ($dia->frequencia?->observacao)
                                <small style="display: block; margin-bottom: 0.25rem;">{{ $dia->frequencia->observacao }}</small>
                            @endif
                            @if ($podeEditarObservacao)
                                <details>
                                    <summary style="cursor: pointer; color: var(--color-secondary-07); font-size: 0.85rem;" title="Adicionar/editar observação">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                    </summary>
                                    <form method="POST" action="{{ route('frequencia.observacao', ['ano' => $folha->ano, 'mes' => $folha->mes, 'dia' => (int) $dia->data->day]) }}" style="margin-top: 0.4rem;">
                                        @csrf
                                        <textarea name="texto" maxlength="500" rows="3" style="width: 100%; padding: 0.3rem; font-size: 0.85rem;">{{ $dia->frequencia?->observacao }}</textarea>
                                        <button type="submit" class="br-button primary" style="font-size: 0.8rem; padding: 0.25rem 0.6rem;">Salvar</button>
                                    </form>
                                </details>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="padding: 0.75rem 0.5rem; text-align: right; font-weight: 600;">
                    Total de horas:
                </td>
                <td colspan="3" style="padding: 0.75rem 0.5rem; font-weight: 600;">
                    {{ number_format($folha->totalHoras, 2, ',', '') }} h
                </td>
            </tr>
        </tfoot>
    </table>
@endsection
