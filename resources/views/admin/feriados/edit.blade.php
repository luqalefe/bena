@extends('layouts.app')

@section('title', "Editar feriado — {$feriado->descricao}")

@section('content')
    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.feriados.index') }}" style="color: var(--color-secondary-07); text-decoration: none;">← Voltar</a>
        <h1 style="color: var(--color-primary-default); margin: 0.5rem 0 0;">Editar feriado</h1>
    </div>

    @if ($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <ul style="margin: 0 0 0 1rem;">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.feriados.update', $feriado) }}" style="display: grid; gap: 1rem; max-width: 480px;">
        @csrf
        @method('PUT')
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Data</span>
            <input type="date" name="data" value="{{ old('data', $feriado->data->format('Y-m-d')) }}" required style="padding: 0.4rem 0.6rem;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Descrição</span>
            <input type="text" name="descricao" value="{{ old('descricao', $feriado->descricao) }}" maxlength="200" required style="padding: 0.4rem 0.6rem;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Tipo</span>
            <select name="tipo" required style="padding: 0.4rem 0.6rem;">
                @foreach (['nacional','estadual','municipal','recesso'] as $t)
                    <option value="{{ $t }}" @selected(old('tipo', $feriado->tipo) === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>UF (opcional)</span>
            <input type="text" name="uf" value="{{ old('uf', $feriado->uf) }}" maxlength="2" style="padding: 0.4rem 0.6rem;">
        </label>
        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="recorrente" value="1" @checked(old('recorrente', $feriado->recorrente))>
            <span>Recorrente (todo ano)</span>
        </label>
        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="br-button primary">Salvar</button>
            <a href="{{ route('admin.feriados.confirmDestroy', $feriado) }}" class="br-button" style="background: #fee2e2; color: #991b1b;">Remover</a>
            <a href="{{ route('admin.feriados.index') }}" class="br-button secondary">Cancelar</a>
        </div>
    </form>
@endsection
