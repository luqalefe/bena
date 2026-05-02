@extends('layouts.app')

@section('title', 'Painel do supervisor — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin: 0 0 1rem;">Estagiários sob sua responsabilidade</h1>

    @if ($estagiarios->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum estagiário sob sua responsabilidade. Se isso parece errado, peça ao RH para preencher o campo "Supervisor (username Authelia)" no cadastro.</p>
    @else
        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Nome</th>
                    <th style="text-align: left; padding: 0.5rem;">Lotação</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estagiarios as $estagiario)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $estagiario->nome }}</td>
                        <td style="padding: 0.5rem;">{{ $estagiario->lotacao ?? '—' }}</td>
                        <td style="padding: 0.5rem;">
                            <a href="{{ route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $estagiario->username]) }}" class="br-button primary small">Ver folha</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
