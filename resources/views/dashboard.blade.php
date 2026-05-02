@extends('layouts.app')

@section('title', 'Dashboard — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin-bottom: 0.5rem;">
        Olá, {{ explode(' ', $estagiario->nome)[0] ?? $estagiario->username }}
    </h1>
    <p style="color: var(--color-secondary-07); margin-bottom: 2rem;">
        {{ $resumo->mesAnoExtenso }}
    </p>

    {{-- Cards principais --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="tre-ac-card">
            <h3>Status hoje</h3>
            @if ($resumo->statusHoje === 'aguardando_entrada')
                <span class="badge-tre-ac is-pendente">Aguardando entrada</span>
            @elseif ($resumo->statusHoje === 'em_andamento')
                <span class="badge-tre-ac is-entrada">{{ $resumo->statusDescricao }}</span>
            @else
                <span class="badge-tre-ac is-completo">{{ $resumo->statusDescricao }}</span>
            @endif
        </div>

        <div class="tre-ac-card">
            <h3>Horas no mês</h3>
            <div class="metric">{{ number_format($resumo->horasMes, 2, ',', '.') }} h</div>
        </div>

        <div class="tre-ac-card">
            <h3>Dias batidos</h3>
            <div class="metric">{{ $resumo->diasBatidos }}</div>
        </div>
    </div>

    {{-- Botão de bater ponto - varia conforme o status --}}
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        @if ($resumo->statusHoje === 'aguardando_entrada')
            <form method="POST" action="{{ route('ponto.entrada') }}">
                @csrf
                <button type="submit" class="br-button primary">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    Bater entrada
                </button>
            </form>
        @elseif ($resumo->statusHoje === 'em_andamento')
            <form method="POST" action="{{ route('ponto.saida') }}">
                @csrf
                <button type="submit" class="br-button primary">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    Bater saída
                </button>
            </form>
        @else
            <span class="br-button" style="cursor: default; opacity: 0.7;">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                Frequência do dia concluída
            </span>
        @endif

        <a href="{{ route('frequencia.atual') }}" class="br-button secondary">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            Ver folha mensal
        </a>
    </div>
@endsection
