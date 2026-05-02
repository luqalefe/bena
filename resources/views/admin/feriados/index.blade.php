@extends('layouts.app')

@section('title', 'Feriados — Bena')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h1 style="color: var(--color-primary-default); margin: 0;">
            Feriados {{ $ano }}
        </h1>
        <a href="{{ route('admin.feriados.create') }}" class="br-button primary">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Novo feriado
        </a>
    </div>

    @if (session('sucesso'))
        <div style="background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            {{ session('sucesso') }}
        </div>
    @endif

    <form method="GET" action="{{ url('/admin/feriados') }}" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Ano</span>
            <input type="number" name="ano" value="{{ $ano }}" min="2000" max="2100" style="padding: 0.4rem 0.6rem;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Tipo</span>
            <select name="tipo" style="padding: 0.4rem 0.6rem;">
                <option value="">Todos</option>
                <option value="nacional"   @selected($tipo === 'nacional')>nacional</option>
                <option value="estadual"   @selected($tipo === 'estadual')>estadual</option>
                <option value="municipal"  @selected($tipo === 'municipal')>municipal</option>
                <option value="recesso"    @selected($tipo === 'recesso')>recesso</option>
            </select>
        </label>
        <button type="submit" class="br-button primary">Filtrar</button>
    </form>

    @if ($feriados->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum feriado cadastrado para {{ $ano }}.</p>
    @else
        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Data</th>
                    <th style="text-align: left; padding: 0.5rem;">Descrição</th>
                    <th style="text-align: left; padding: 0.5rem;">Tipo</th>
                    <th style="text-align: left; padding: 0.5rem;">UF</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($feriados as $feriado)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $feriado->data->format('d/m/Y') }}</td>
                        <td style="padding: 0.5rem;">{{ $feriado->descricao }}</td>
                        <td style="padding: 0.5rem;">{{ $feriado->tipo }}</td>
                        <td style="padding: 0.5rem;">{{ $feriado->uf }}</td>
                        <td style="padding: 0.5rem;">
                            @if ($feriado->recorrente)
                                <span class="badge-tre-ac is-pendente">recorrente</span>
                            @endif
                        </td>
                        <td style="padding: 0.5rem; display: flex; gap: 0.5rem;">
                            <a href="{{ route('admin.feriados.edit', $feriado) }}" class="br-button secondary small">Editar</a>
                            <a href="{{ route('admin.feriados.confirmDestroy', $feriado) }}" class="br-button small" style="background:#fee2e2; color:#991b1b;">Remover</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
