@extends('layouts.app')

@section('title', 'Painel do supervisor — Bena')

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Estagiários sob sua responsabilidade</h1>
            <p class="bena-listing__subtitle">
                Acesse a folha mensal de cada um para revisar e contra-assinar.
            </p>
        </div>
    </header>

    @if ($estagiarios->isEmpty())
        <div class="bena-empty">
            <i class="fas fa-user-friends" aria-hidden="true"></i>
            Nenhum estagiário sob sua responsabilidade. Se isso parece errado,
            peça ao admin para preencher o supervisor no cadastro.
        </div>
    @else
        <div class="bena-table-wrap">
            <table class="bena-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Lotação</th>
                        <th class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estagiarios as $estagiario)
                        <tr>
                            <td>
                                <span class="bena-table__name">{{ $estagiario->nome }}</span>
                                @if ($estagiario->username)
                                    <code class="bena-table__sub">{{ $estagiario->username }}</code>
                                @endif
                            </td>
                            <td>{{ $estagiario->setor?->sigla ?? '—' }}</td>
                            <td class="is-actions">
                                <a href="{{ route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $estagiario->username]) }}" class="br-button primary small">Ver folha</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
