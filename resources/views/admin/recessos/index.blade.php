@extends('layouts.app')

@section('title', 'Recessos — ' . $estagiario->nome)

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: var(--color-primary-default); margin: 0;">Recessos</h1>
            <p style="margin: 0.25rem 0 0; color: var(--color-secondary-07);">
                {{ $estagiario->nome }}
                @if ($estagiario->lotacao)
                    — {{ $estagiario->lotacao }}
                @endif
            </p>
        </div>
        <a href="{{ route('admin.estagiarios.edit', $estagiario) }}" class="br-button">
            ← Voltar para edição
        </a>
    </div>

    @if (session('sucesso'))
        <div style="background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            {{ session('sucesso') }}
        </div>
    @endif

    <section style="margin-bottom: 2rem; padding: 1rem; background: #f9fafb; border-radius: 4px;">
        <h2 style="font-size: 1.1rem; margin: 0 0 1rem;">Cadastrar novo recesso</h2>
        <form method="POST" action="{{ route('admin.estagiarios.recessos.store', $estagiario) }}" style="display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
            @csrf
            <label style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Início</span>
                <input type="date" name="inicio" value="{{ old('inicio') }}" required style="padding: 0.4rem 0.6rem;">
            </label>
            <label style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Fim</span>
                <input type="date" name="fim" value="{{ old('fim') }}" required style="padding: 0.4rem 0.6rem;">
            </label>
            <label style="display: flex; flex-direction: column; gap: 0.25rem; flex: 1; min-width: 200px;">
                <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Observação</span>
                <input type="text" name="observacao" value="{{ old('observacao') }}" maxlength="255" style="padding: 0.4rem 0.6rem;">
            </label>
            <button type="submit" class="br-button primary">Cadastrar</button>
        </form>
        @error('inicio')
            <p style="color: #b91c1c; margin: 0.5rem 0 0; font-size: 0.875rem;">{{ $message }}</p>
        @enderror
        @error('fim')
            <p style="color: #b91c1c; margin: 0.5rem 0 0; font-size: 0.875rem;">{{ $message }}</p>
        @enderror
    </section>

    @if ($recessos->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum recesso cadastrado.</p>
    @else
        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Início</th>
                    <th style="text-align: left; padding: 0.5rem;">Fim</th>
                    <th style="text-align: left; padding: 0.5rem;">Observação</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recessos as $recesso)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $recesso->inicio->format('d/m/Y') }}</td>
                        <td style="padding: 0.5rem;">{{ $recesso->fim->format('d/m/Y') }}</td>
                        <td style="padding: 0.5rem;">{{ $recesso->observacao ?? '—' }}</td>
                        <td style="padding: 0.5rem;">
                            <form method="POST" action="{{ route('admin.estagiarios.recessos.destroy', [$estagiario, $recesso]) }}"
                                  onsubmit="return confirm('Remover este recesso?')" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="br-button" style="color: #b91c1c;">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
