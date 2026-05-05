@extends('layouts.app')

@section('title', 'Recessos — ' . $estagiario->nome)

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Recessos</h1>
            <p class="bena-listing__subtitle">
                {{ $estagiario->nome }}@if ($estagiario->setor) · {{ $estagiario->setor->sigla }}@endif
            </p>
        </div>
        <a href="{{ route('admin.estagiarios.edit', $estagiario) }}" class="br-button secondary">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para edição
        </a>
    </header>

    @if (session('sucesso'))
        <div class="bena-flash bena-flash--sucesso">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('sucesso') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bena-error-summary" role="alert">
            <p class="bena-error-summary__title">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                Corrija os erros:
            </p>
            <ul class="bena-error-summary__list">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.estagiarios.recessos.store', $estagiario) }}" class="bena-filters" style="margin-bottom: 1.5rem;">
        @csrf
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">Início</span>
            <input type="date" name="inicio" value="{{ old('inicio') }}" required class="bena-filters__control">
        </label>
        <label class="bena-filters__field bena-filters__field--narrow">
            <span class="bena-filters__label">Fim</span>
            <input type="date" name="fim" value="{{ old('fim') }}" required class="bena-filters__control">
        </label>
        <label class="bena-filters__field" style="flex: 1; min-width: 220px;">
            <span class="bena-filters__label">Observação</span>
            <input type="text" name="observacao" value="{{ old('observacao') }}" maxlength="255" class="bena-filters__control">
        </label>
        <button type="submit" class="br-button primary">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Cadastrar recesso
        </button>
    </form>

    @if ($recessos->isEmpty())
        <div class="bena-empty">
            <i class="fas fa-umbrella-beach" aria-hidden="true"></i>
            Nenhum recesso cadastrado para este estagiário.
        </div>
    @else
        <div class="bena-table-wrap">
            <table class="bena-table">
                <thead>
                    <tr>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Observação</th>
                        <th class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recessos as $recesso)
                        <tr>
                            <td>{{ $recesso->inicio->format('d/m/Y') }}</td>
                            <td>{{ $recesso->fim->format('d/m/Y') }}</td>
                            <td>{{ $recesso->observacao ?? '—' }}</td>
                            <td class="is-actions">
                                <form method="POST" action="{{ route('admin.estagiarios.recessos.destroy', [$estagiario, $recesso]) }}"
                                      onsubmit="return confirm('Remover este recesso?')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="br-button danger small">Remover</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
