@extends('layouts.app')

@section('title', 'Dashboard admin — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin: 0 0 0.25rem;">Dashboard administrativo</h1>
    <p style="color: var(--color-secondary-07); margin: 0 0 1.5rem; text-transform: capitalize;">{{ $mesAnoExtenso }}</p>

    <form method="GET" action="{{ url('/admin') }}" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Lotação</span>
            <select name="lotacao" onchange="this.form.submit()" style="padding: 0.4rem 0.6rem;">
                <option value="">Todas</option>
                @foreach ($lotacoes as $opt)
                    <option value="{{ $opt }}" @selected($lotacao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Ano</span>
            <input type="number" name="ano" value="{{ $ano }}" min="2000" max="2100" style="padding: 0.4rem 0.6rem; width: 100px;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Mês</span>
            <select name="mes" onchange="this.form.submit()" style="padding: 0.4rem 0.6rem;">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($mes === $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="liberadas" value="1" onchange="this.form.submit()" @checked($apenasLiberadas)>
            <span>Apenas liberadas para RH</span>
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Alerta</span>
            <select name="alerta" onchange="this.form.submit()" style="padding: 0.4rem 0.6rem;">
                <option value="">Todos</option>
                <option value="tce_vencendo" @selected($alerta === 'tce_vencendo')>TCE vencendo</option>
                <option value="sem_recesso" @selected($alerta === 'sem_recesso')>Sem recesso</option>
                <option value="jornada_excedida" @selected($alerta === 'jornada_excedida')>Jornada excedida</option>
            </select>
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
    </form>

    @if ($linhas->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum estagiário ativo encontrado.</p>
    @else
        <div class="bena-form__field" style="margin-bottom: 1rem; max-width: 480px;">
            <label for="busca-tabela" class="bena-form__label">
                <i class="fas fa-search" aria-hidden="true"></i> Busca rápida
            </label>
            <input type="text" id="busca-tabela" class="bena-form__input"
                   placeholder="Buscar estagiário…"
                   autocomplete="off">
        </div>
        <table id="tabela-principal" class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Nome</th>
                    <th style="text-align: left; padding: 0.5rem;">Lotação</th>
                    <th style="text-align: right; padding: 0.5rem;">Horas no mês</th>
                    <th style="text-align: right; padding: 0.5rem;">Dias batidos</th>
                    <th style="text-align: left; padding: 0.5rem;">Assinatura</th>
                    <th style="text-align: left; padding: 0.5rem;">Alertas</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $linha->estagiario->nome }}</td>
                        <td style="padding: 0.5rem;">{{ $linha->estagiario->lotacao ?? '—' }}</td>
                        <td style="padding: 0.5rem; text-align: right;">{{ number_format($linha->horasMes, 2, ',', '.') }}</td>
                        <td style="padding: 0.5rem; text-align: right;">{{ $linha->diasBatidos }} dias</td>
                        <td style="padding: 0.5rem; font-size: 0.85rem;">
                            <div>estagiário {!! $linha->assinadoEstagiario ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                            <div>supervisor {!! $linha->assinadoSupervisor ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                        </td>
                        <td style="padding: 0.5rem; font-size: 0.85rem;">
                            @if (count($linha->alertas) === 0)
                                <span style="color: #166534;" title="Sem alertas">✓</span>
                            @else
                                @foreach ($linha->alertas as $codigo)
                                    <span title="{{ $descricaoAlerta($codigo) }}"
                                          style="display: inline-block; padding: 0.15rem 0.5rem; margin: 0.1rem 0.2rem 0.1rem 0; background: #fef3c7; color: #92400e; border-radius: 3px; font-size: 0.75rem; cursor: help;">
                                        ⚠ {{ $descricaoAlerta($codigo) }}
                                    </span>
                                @endforeach
                            @endif
                        </td>
                        <td style="padding: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button primary small">Ver folha</a>
                            @if ($linha->liberadaParaRh())
                                <a href="{{ route('frequencia.pdf', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button small">Baixar PDF</a>
                            @endif
                            <a href="{{ route('admin.estagiarios.edit', $linha->estagiario) }}" class="br-button secondary small">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('busca-tabela');
        const tabela = document.getElementById('tabela-principal');
        if (!input || !tabela) return;

        const normalizar = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

        input.addEventListener('input', function () {
            const termo = normalizar(this.value);
            tabela.querySelectorAll('tbody tr').forEach((tr) => {
                tr.style.display = normalizar(tr.textContent).includes(termo) ? '' : 'none';
            });
        });
    })();
</script>
@endpush
