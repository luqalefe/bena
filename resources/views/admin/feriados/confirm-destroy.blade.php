@extends('layouts.app')

@section('title', "Remover feriado — {$feriado->descricao}")

@section('content')
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.feriados.index') }}" style="color: var(--color-secondary-07); text-decoration: none;">← Voltar</a>
        <h1 style="color: var(--color-primary-default); margin: 0.5rem 0 0;">Remover feriado</h1>
    </div>

    <div style="background: #fff7ed; border: 1px solid #fdba74; padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1rem;">
        <p>Você está prestes a remover o feriado:</p>
        <p><strong>{{ $feriado->descricao }}</strong> — {{ $feriado->data->format('d/m/Y') }} ({{ $feriado->tipo }})</p>
        <p>Após a remoção, dias que eram "feriado" voltam a ser "dia útil sem registro" na classificação retroativa da folha mensal.</p>

        @if ($assinaturasImpactadas > 0)
            <p style="background: #fee2e2; color: #991b1b; padding: 0.5rem; border-radius: 4px;">
                ⚠ <strong>{{ $assinaturasImpactadas }} folhas</strong> assinadas no mês deste feriado terão seu
                hash <strong>invalidado</strong> na próxima verificação. As folhas continuam visíveis,
                mas mostrarão "⚠ alterada".
            </p>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.feriados.destroy', $feriado) }}">
        @csrf
        @method('DELETE')
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="br-button" style="background: #b91c1c; color: white;">Confirmar remoção</button>
            <a href="{{ route('admin.feriados.index') }}" class="br-button secondary">Cancelar</a>
        </div>
    </form>
@endsection
