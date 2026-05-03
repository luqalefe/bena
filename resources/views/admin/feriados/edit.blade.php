@extends('layouts.app')

@section('title', "Editar feriado — {$feriado->descricao}")

@section('content')
    <div class="bena-page-header">
        <a href="{{ route('calendario.mes', ['ano' => $feriado->data->year, 'mes' => $feriado->data->month]) }}" class="bena-page-header__back">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para o calendário
        </a>
        <h1 class="bena-page-header__title">Editar feriado</h1>
        <p class="bena-page-header__subtitle">
            {{ $feriado->descricao }} · {{ $feriado->data->format('d/m/Y') }}
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
        <form method="POST" action="{{ route('admin.feriados.update', $feriado) }}" class="bena-form">
            @csrf
            @method('PUT')

            <div class="bena-form__field">
                <label for="data" class="bena-form__label">
                    Data <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="date" id="data" name="data" value="{{ old('data', $feriado->data->format('Y-m-d')) }}" required class="bena-form__input">
            </div>

            <div class="bena-form__field">
                <label for="descricao" class="bena-form__label">
                    Descrição <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" id="descricao" name="descricao" value="{{ old('descricao', $feriado->descricao) }}" maxlength="200" required class="bena-form__input">
            </div>

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="tipo" class="bena-form__label">
                        Tipo <span class="required" aria-hidden="true">*</span>
                    </label>
                    <select id="tipo" name="tipo" required class="bena-form__select">
                        @foreach (['nacional','estadual','municipal','recesso'] as $t)
                            <option value="{{ $t }}" @selected(old('tipo', $feriado->tipo) === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bena-form__field">
                    <label for="uf" class="bena-form__label">UF</label>
                    <input type="text" id="uf" name="uf" value="{{ old('uf', $feriado->uf) }}" maxlength="2" class="bena-form__input" style="text-transform: uppercase;">
                    <p class="bena-form__help">Apenas para feriados estaduais.</p>
                </div>
            </div>

            <label class="bena-form__checkbox">
                <input type="checkbox" name="recorrente" value="1" @checked(old('recorrente', $feriado->recorrente))>
                <span>Recorrente — repete todo ano nesta data</span>
            </label>

            <div class="bena-form__actions bena-form__actions--has-extra">
                <a href="{{ route('admin.feriados.confirmDestroy', $feriado) }}" class="bena-link-danger">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                    Remover feriado
                </a>
                <div class="bena-form__actions__primary">
                    <a href="{{ route('calendario.mes', ['ano' => $feriado->data->year, 'mes' => $feriado->data->month]) }}" class="br-button secondary">Cancelar</a>
                    <button type="submit" class="br-button primary">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        Salvar alterações
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
