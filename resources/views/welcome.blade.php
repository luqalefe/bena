@extends('layouts.app')

@section('title', 'Início — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin-bottom: 1rem;">
        Bem-vindo ao Bena
    </h1>

    <p style="color: var(--color-secondary-07); margin-bottom: 2rem;">
        Controle de Frequência de Estagiários do TRE-AC — substitui o
        preenchimento manual da Ficha de Controle de Frequência (FCF) em
        papel.
    </p>

    {{-- Demo dos cards do dashboard (H4) usando os tokens TRE-AC --}}
    <div class="row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="tre-ac-card">
            <h3>Status hoje</h3>
            <div class="metric" style="font-size: 1.25rem;">
                <span class="badge-tre-ac is-pendente">Aguardando entrada</span>
            </div>
        </div>

        <div class="tre-ac-card">
            <h3>Horas no mês</h3>
            <div class="metric">0,00 h</div>
            <small style="color: var(--color-secondary-06);">Maio / 2026</small>
        </div>

        <div class="tre-ac-card">
            <h3>Dias batidos</h3>
            <div class="metric">0</div>
            <small style="color: var(--color-secondary-06);">de 21 dias úteis</small>
        </div>
    </div>

    {{-- Botões primários e secundários da paleta --}}
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <button class="br-button primary" type="button">
            <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
            Bater entrada
        </button>

        <button class="br-button secondary" type="button" disabled>
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            Bater saída
        </button>

        <a href="#" class="br-button" style="text-decoration: none;">
            Ver folha do mês
        </a>
    </div>

    {{-- Demonstração dos badges (folha mensal) --}}
    <div style="margin-top: 2rem;">
        <h3 style="color: var(--color-primary-default);">Convenções de status</h3>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem;">
            <span class="badge-tre-ac is-completo">Completo</span>
            <span class="badge-tre-ac is-entrada">Em andamento</span>
            <span class="badge-tre-ac is-pendente">Não batido</span>
            <span class="badge-tre-ac is-feriado">Feriado</span>
            <span class="badge-tre-ac is-fimdesemana">Fim de semana</span>
        </div>
    </div>
@endsection
