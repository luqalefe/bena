@extends('layouts.app')

@section('title', "Remover feriado — {$feriado->descricao}")

@section('content')
    <div class="bena-page-header">
        <a href="{{ route('calendario.mes', ['ano' => $feriado->data->year, 'mes' => $feriado->data->month]) }}" class="bena-page-header__back">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para o calendário
        </a>
        <h1 class="bena-page-header__title">Remover feriado</h1>
        <p class="bena-page-header__subtitle">
            Confirme a remoção. Esta ação afeta a classificação retroativa da folha mensal.
        </p>
    </div>

    <div class="bena-warning-card">
        <h2 class="bena-warning-card__title">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            Atenção
        </h2>
        <p>Você está prestes a remover o feriado:</p>
        <p>
            <strong>{{ $feriado->descricao }}</strong>
            · {{ $feriado->data->format('d/m/Y') }}
            · <em>{{ ucfirst($feriado->tipo) }}</em>
        </p>
        <p>
            Após a remoção, dias que eram "feriado" voltam a ser "dia útil sem registro"
            na classificação retroativa da folha mensal.
        </p>

        @if ($assinaturasImpactadas > 0)
            <div class="bena-warning-card__highlight">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                <span>
                    <strong>{{ $assinaturasImpactadas }} folhas</strong>
                    assinadas no mês deste feriado terão o hash <strong>invalidado</strong>
                    na próxima verificação. As folhas continuam visíveis, mas mostrarão
                    "alterada".
                </span>
            </div>
        @endif
    </div>

    <div class="bena-card">
        <form method="POST" action="{{ route('admin.feriados.destroy', $feriado) }}" class="bena-form">
            @csrf
            @method('DELETE')
            <div class="bena-form__actions">
                <a href="{{ route('calendario.mes', ['ano' => $feriado->data->year, 'mes' => $feriado->data->month]) }}" class="br-button secondary">Cancelar</a>
                <button type="submit" class="br-button danger">
                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                    Confirmar remoção
                </button>
            </div>
        </form>
    </div>
@endsection
