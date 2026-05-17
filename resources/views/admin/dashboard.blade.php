@extends('layouts.app')

@section('title', 'Dashboard admin — Bena')

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Dashboard administrativo</h1>
            <p class="bena-listing__subtitle" style="text-transform: capitalize;">{{ $mesAnoExtenso }}</p>
        </div>
    </header>

    <form method="GET" action="{{ url('/admin') }}" class="bena-filters">
        <label class="bena-filters__field">
            <span class="bena-filters__label">Setor</span>
            <select name="setor" onchange="this.form.submit()" class="bena-filters__control">
                <option value="">Todos</option>
                @foreach ($setores as $opt)
                    <option value="{{ $opt }}" @selected($setor === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">Ano</span>
            <input type="number" name="ano" value="{{ $ano }}" min="2000" max="2100" class="bena-filters__control">
        </label>
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">Mês</span>
            <select name="mes" onchange="this.form.submit()" class="bena-filters__control">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($mes === $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
        </label>
        <label class="bena-filters__field">
            <span class="bena-filters__label">Alerta</span>
            <select name="alerta" onchange="this.form.submit()" class="bena-filters__control">
                <option value="">Todos</option>
                <option value="tce_vencendo" @selected($alerta === 'tce_vencendo')>TCE vencendo</option>
                <option value="sem_recesso" @selected($alerta === 'sem_recesso')>Sem recesso</option>
                <option value="jornada_excedida" @selected($alerta === 'jornada_excedida')>Jornada excedida</option>
            </select>
        </label>
        <label class="bena-filters__check">
            <input type="checkbox" name="liberadas" value="1" onchange="this.form.submit()" @checked($apenasLiberadas)>
            <span>Apenas liberadas para RH</span>
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
    </form>

    @if ($linhas->isEmpty())
        <div class="bena-empty">
            <i class="fas fa-folder-open" aria-hidden="true"></i>
            Nenhum estagiário ativo encontrado para os filtros aplicados.
        </div>
    @else
        <div class="bena-search">
            <i class="fas fa-search bena-search__icon" aria-hidden="true"></i>
            <input type="text" id="busca-tabela" class="bena-search__input"
                   placeholder="Buscar estagiário…"
                   autocomplete="off">
        </div>

        <div class="bena-table-wrap bena-table-wrap--scroll">
            <table id="tabela-principal" class="bena-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Setor</th>
                        <th class="is-num">Horas no mês</th>
                        <th class="is-num">Dias batidos</th>
                        <th>Assinatura</th>
                        <th>Alertas</th>
                        <th class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($linhas as $linha)
                        <tr>
                            <td>
                                <span class="bena-table__name">{{ $linha->estagiario->nome }}</span>
                                @if ($linha->estagiario->username)
                                    <code class="bena-table__sub">{{ $linha->estagiario->username }}</code>
                                @endif
                            </td>
                            <td>{{ $linha->estagiario->setor?->sigla ?? '—' }}</td>
                            <td class="is-num">{{ number_format($linha->horasMes, 2, ',', '.') }}</td>
                            <td class="is-num">{{ $linha->diasBatidos }} dias</td>
                            <td style="font-size: 0.82rem; line-height: 1.5;">
                                <div>estagiário {!! $linha->assinadoEstagiario ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                                <div>supervisor {!! $linha->assinadoSupervisor ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                            </td>
                            <td>
                                @if (count($linha->alertas) === 0)
                                    <span class="muted" title="Sem alertas">—</span>
                                @else
                                    @foreach ($linha->alertas as $codigo)
                                        <span class="bena-pill bena-pill--alerta" title="{{ $descricaoAlerta($codigo) }}" style="margin: 0.1rem 0.2rem 0.1rem 0;">
                                            ⚠ {{ $descricaoAlerta($codigo) }}
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="is-actions">
                                <div style="display: inline-flex; gap: 0.4rem; flex-wrap: wrap; justify-content: flex-end;">
                                    <a href="{{ route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button primary small">Ver folha</a>
                                    @if ($linha->liberadaParaRh())
                                        <a href="{{ route('frequencia.pdf', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button small" data-turbo="false">PDF</a>
                                    @endif
                                    <a href="{{ route('admin.estagiarios.edit', $linha->estagiario) }}" class="br-button secondary small">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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
