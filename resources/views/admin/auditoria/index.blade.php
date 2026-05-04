@extends('layouts.app')

@section('title', 'Auditoria — Bena')

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Auditoria de ações</h1>
            <p class="bena-listing__subtitle">
                Log append-only de ações sensíveis: bater ponto, assinar, editar
                feriado, editar estagiário. Limite de 500 entradas por consulta.
            </p>
        </div>
    </header>

    <form method="GET" action="{{ route('admin.auditoria.index') }}" class="bena-filters">
        <label class="bena-filters__field">
            <span class="bena-filters__label">Usuário</span>
            <input type="text" name="usuario" value="{{ $usuario }}" placeholder="lucas.dev" class="bena-filters__control">
        </label>
        <label class="bena-filters__field">
            <span class="bena-filters__label">Ação</span>
            <select name="acao" onchange="this.form.submit()" class="bena-filters__control">
                <option value="">Todas</option>
                @foreach ($acoesDisponiveis as $opt)
                    <option value="{{ $opt }}" @selected($acao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">De</span>
            <input type="date" name="de" value="{{ $de }}" onchange="this.form.submit()" class="bena-filters__control">
        </label>
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">Até</span>
            <input type="date" name="ate" value="{{ $ate }}" onchange="this.form.submit()" class="bena-filters__control">
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
        @if ($usuario || $acao || $de || $ate)
            <a href="{{ route('admin.auditoria.index') }}" class="br-button secondary">Limpar</a>
        @endif
    </form>

    @if ($linhas->isEmpty())
        <div class="bena-empty">
            <i class="fas fa-clipboard-list" aria-hidden="true"></i>
            Nenhum registro encontrado para os filtros aplicados.
        </div>
    @else
        <p style="color: #64748b; font-size: 0.875rem; margin: 0 0 0.75rem;">
            {{ $linhas->count() }} {{ $linhas->count() === 1 ? 'entrada' : 'entradas' }}@if ($linhas->count() === 500) (limite atingido — refine os filtros)@endif.
        </p>

        <div class="bena-table-wrap bena-table-wrap--scroll">
            <table class="bena-table">
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Entidade</th>
                        <th>ID</th>
                        <th>IP</th>
                        <th>Payload</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($linhas as $linha)
                        <tr>
                            <td style="white-space: nowrap;">
                                <span title="{{ $linha->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $linha->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td><code>{{ $linha->usuario_username }}</code></td>
                            <td>{{ $linha->acao }}</td>
                            <td>{{ $linha->entidade }}</td>
                            <td>{{ $linha->entidade_id ?? '—' }}</td>
                            <td style="font-family: 'SF Mono', Menlo, monospace; font-size: 0.78rem;">{{ $linha->ip ?? '—' }}</td>
                            <td>
                                @if ($linha->payload)
                                    <details>
                                        <summary style="cursor: pointer; color: #003366; font-size: 0.85rem;">ver</summary>
                                        <pre style="background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.75rem; max-width: 400px; overflow: auto; margin: 0.4rem 0 0;">{{ json_encode(json_decode($linha->payload, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
