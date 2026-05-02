@extends('layouts.app')

@section('title', 'Ponto registrado — Controle de Frequência')

@section('content')
    @php
        $titulos = [
            'entrada' => 'Entrada registrada',
            'saida' => 'Saída registrada',
        ];
        $icones = [
            'entrada' => 'fa-sign-in-alt',
            'saida' => 'fa-sign-out-alt',
        ];
        $titulo = $titulos[$acao] ?? 'Ponto registrado';
        $icone = $icones[$acao] ?? 'fa-check-circle';
    @endphp

    <div style="max-width: 480px; margin: 3rem auto 0; text-align: center;">
        <div style="width: 88px; height: 88px; margin: 0 auto 1.5rem; border-radius: 50%; background: var(--color-success-lighten-02, #d1fae5); color: var(--color-success-darken-01, #065f46); display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
            <i class="fas {{ $icone }}" aria-hidden="true"></i>
        </div>

        <h1 style="color: var(--color-primary-default); margin: 0 0 0.5rem; font-size: 1.6rem;">
            {{ $titulo }}
        </h1>

        <p style="color: var(--color-secondary-07); margin: 0 0 0.25rem; font-size: 1rem;">
            às <strong style="color: var(--color-primary-default); font-size: 1.4rem;">{{ $horario }}</strong>
        </p>

        @if ($acao === 'saida' && ! empty($horas))
            <p style="color: var(--color-secondary-07); margin: 0 0 1.5rem;">
                Total do dia: <strong>{{ $horas }} h</strong>
            </p>
        @else
            <div style="margin-bottom: 1.5rem;"></div>
        @endif

        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('dashboard') }}" class="br-button primary">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                Voltar ao dashboard
            </a>
            <a href="{{ route('frequencia.atual') }}" class="br-button secondary">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                Ver folha mensal
            </a>
        </div>
    </div>
@endsection
