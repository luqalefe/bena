@php
    $modoEdicao = isset($supervisor) && $supervisor !== null;
    $titulo = $modoEdicao ? 'Editar supervisor' : 'Novo supervisor';
    $action = $modoEdicao
        ? route('admin.supervisores.update', $supervisor)
        : route('admin.supervisores.store');
@endphp

@extends('layouts.app')

@section('title', $titulo.' — Bena')

@section('content')
    <div class="bena-page-header">
        <a href="{{ route('admin.supervisores.index') }}" class="bena-page-header__back">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para a lista
        </a>
        <h1 class="bena-page-header__title">{{ $titulo }}</h1>
        <p class="bena-page-header__subtitle">
            Supervisores aparecem como opção ao vincular um estagiário. Apenas o
            <strong>nome</strong> é obrigatório — username e e-mail podem ser
            preenchidos depois.
        </p>
    </div>

    @if ($errors->any())
        <div class="bena-error-summary" role="alert">
            <p class="bena-error-summary__title">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                Corrija os erros abaixo:
            </p>
            <ul class="bena-error-summary__list">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bena-card">
        <form method="POST" action="{{ $action }}" class="bena-form">
            @csrf
            @if ($modoEdicao)
                @method('PUT')
            @endif

            <div class="bena-form__field">
                <label for="nome" class="bena-form__label">
                    Nome <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" id="nome" name="nome"
                       value="{{ old('nome', $supervisor->nome ?? '') }}"
                       maxlength="200" required class="bena-form__input"
                       placeholder="Ex: Daniele Carlos de Oliveira Nunes">
            </div>

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="username" class="bena-form__label">Username (Authelia)</label>
                    <input type="text" id="username" name="username"
                           value="{{ old('username', $supervisor->username ?? '') }}"
                           maxlength="100" class="bena-form__input"
                           placeholder="Ex: daniele.nunes">
                    <p class="bena-form__help">Identificador único no SSO. Deixe vazio se ainda não souber.</p>
                </div>

                <div class="bena-form__field">
                    <label for="email" class="bena-form__label">E-mail</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email', $supervisor->email ?? '') }}"
                           maxlength="200" class="bena-form__input"
                           placeholder="exemplo@tre-ac.jus.br">
                </div>
            </div>

            <div class="bena-form__field">
                <label for="lotacao" class="bena-form__label">Lotação</label>
                <input type="text" id="lotacao" name="lotacao"
                       value="{{ old('lotacao', $supervisor->lotacao ?? '') }}"
                       maxlength="100" class="bena-form__input"
                       placeholder="Ex: ASCOM, SECEP, 9ª ZONA">
            </div>

            <label class="bena-form__checkbox">
                <input type="checkbox" name="ativo" value="1"
                       @checked(old('ativo', $supervisor->ativo ?? true))>
                <span>Supervisor ativo (aparece na lista ao vincular estagiários)</span>
            </label>

            <div class="bena-form__actions">
                <a href="{{ route('admin.supervisores.index') }}" class="br-button secondary">Cancelar</a>
                <button type="submit" class="br-button primary">
                    <i class="fas fa-check" aria-hidden="true"></i>
                    {{ $modoEdicao ? 'Salvar alterações' : 'Cadastrar supervisor' }}
                </button>
            </div>
        </form>
    </div>
@endsection
