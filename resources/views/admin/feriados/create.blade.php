@extends('layouts.app')

@section('title', 'Novo feriado — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin-bottom: 1.5rem;">
        Novo feriado
    </h1>

    @if ($errors->any())
        <div style="background: #fde8e8; color: #b91c1c; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.feriados.store') }}" style="display: flex; flex-direction: column; gap: 1rem; max-width: 480px;">
        @csrf

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Data</span>
            <input type="date" name="data" value="{{ old('data') }}" required>
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Descrição</span>
            <input type="text" name="descricao" value="{{ old('descricao') }}" maxlength="200" required>
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Tipo</span>
            <select name="tipo" required>
                <option value="">Selecione…</option>
                <option value="nacional"  @selected(old('tipo') === 'nacional')>nacional</option>
                <option value="estadual"  @selected(old('tipo') === 'estadual')>estadual</option>
                <option value="municipal" @selected(old('tipo') === 'municipal')>municipal</option>
                <option value="recesso"   @selected(old('tipo') === 'recesso')>recesso</option>
            </select>
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>UF (apenas para tipo estadual)</span>
            <input type="text" name="uf" value="{{ old('uf') }}" maxlength="2" minlength="2" pattern="[A-Za-z]{2}" placeholder="AC">
        </label>

        <label style="display: flex; gap: 0.5rem; align-items: center;">
            <input type="checkbox" name="recorrente" value="1" @checked(old('recorrente'))>
            <span>Recorrente (todo ano)</span>
        </label>

        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="br-button primary">Salvar</button>
            <a href="{{ route('admin.feriados.index') }}" class="br-button">Cancelar</a>
        </div>
    </form>
@endsection
