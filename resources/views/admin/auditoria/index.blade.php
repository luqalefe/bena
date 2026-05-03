@extends('layouts.app')

@section('title', 'Auditoria — Bena')

@section('content')
    <div class="bena-page-header">
        <h1 class="bena-page-header__title">Auditoria de ações</h1>
        <p class="bena-page-header__subtitle">
            Log append-only de ações sensíveis: bater ponto, assinar, editar
            feriado, editar estagiário. Limite de 500 entradas por consulta.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.auditoria.index') }}"
          style="margin-bottom: 1.25rem; display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.78rem; color: #334155; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">Usuário</span>
            <input type="text" name="usuario" value="{{ $usuario }}"
                   placeholder="lucas.dev"
                   class="bena-form__input" style="min-width: 180px;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.78rem; color: #334155; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">Ação</span>
            <select name="acao" class="bena-form__select" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach ($acoesDisponiveis as $opt)
                    <option value="{{ $opt }}" @selected($acao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.78rem; color: #334155; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">De</span>
            <input type="date" name="de" value="{{ $de }}" class="bena-form__input" onchange="this.form.submit()">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.78rem; color: #334155; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;">Até</span>
            <input type="date" name="ate" value="{{ $ate }}" class="bena-form__input" onchange="this.form.submit()">
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
        @if ($usuario || $acao || $de || $ate)
            <a href="{{ route('admin.auditoria.index') }}" class="br-button secondary" style="align-self: end;">Limpar</a>
        @endif
    </form>

    @if ($linhas->isEmpty())
        <p style="color: #64748b;">Nenhum registro encontrado para os filtros aplicados.</p>
    @else
        <p style="color: #64748b; font-size: 0.875rem; margin: 0 0 0.75rem;">
            {{ $linhas->count() }} {{ $linhas->count() === 1 ? 'entrada' : 'entradas' }}
            @if ($linhas->count() === 500) (limite atingido — refine os filtros) @endif.
        </p>

        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="background: rgba(0, 51, 102, 0.04);">
                    <th style="text-align: left; padding: 0.5rem;">Quando</th>
                    <th style="text-align: left; padding: 0.5rem;">Usuário</th>
                    <th style="text-align: left; padding: 0.5rem;">Ação</th>
                    <th style="text-align: left; padding: 0.5rem;">Entidade</th>
                    <th style="text-align: left; padding: 0.5rem;">ID</th>
                    <th style="text-align: left; padding: 0.5rem;">IP</th>
                    <th style="text-align: left; padding: 0.5rem;">Payload</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr>
                        <td style="padding: 0.5rem; white-space: nowrap;">
                            <span title="{{ $linha->created_at->format('Y-m-d H:i:s') }}">
                                {{ $linha->created_at->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td style="padding: 0.5rem;"><code>{{ $linha->usuario_username }}</code></td>
                        <td style="padding: 0.5rem;">{{ $linha->acao }}</td>
                        <td style="padding: 0.5rem;">{{ $linha->entidade }}</td>
                        <td style="padding: 0.5rem;">{{ $linha->entidade_id ?? '—' }}</td>
                        <td style="padding: 0.5rem; font-family: monospace; font-size: 0.78rem;">{{ $linha->ip ?? '—' }}</td>
                        <td style="padding: 0.5rem;">
                            @if ($linha->payload)
                                <details>
                                    <summary style="cursor: pointer; color: #003366; font-size: 0.8rem;">ver</summary>
                                    <pre style="background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.75rem; max-width: 400px; overflow: auto; margin: 0.4rem 0 0;">{{ json_encode(json_decode($linha->payload, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
