@extends('layouts.app')

@section('title', 'Supervisores — Bena')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h1 style="color: var(--color-primary-default); margin: 0;">Supervisores</h1>
        <a href="{{ route('admin.supervisores.create') }}" class="br-button primary">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Novo supervisor
        </a>
    </div>

    @if (session('sucesso'))
        <div style="background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            {{ session('sucesso') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bena-error-summary" role="alert" style="margin-bottom: 1rem;">
            <p class="bena-error-summary__title">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                Não foi possível concluir a operação:
            </p>
            <ul class="bena-error-summary__list">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($supervisores->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum supervisor cadastrado ainda.</p>
    @else
        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Nome</th>
                    <th style="text-align: left; padding: 0.5rem;">Username</th>
                    <th style="text-align: left; padding: 0.5rem;">E-mail</th>
                    <th style="text-align: left; padding: 0.5rem;">Lotação</th>
                    <th style="text-align: left; padding: 0.5rem;">Estagiários</th>
                    <th style="text-align: left; padding: 0.5rem;">Status</th>
                    <th style="text-align: right; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supervisores as $supervisor)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $supervisor->nome }}</td>
                        <td style="padding: 0.5rem;">
                            @if ($supervisor->username)
                                <code>{{ $supervisor->username }}</code>
                            @else
                                <span style="color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="padding: 0.5rem;">{{ $supervisor->email ?? '—' }}</td>
                        <td style="padding: 0.5rem;">{{ $supervisor->lotacao ?? '—' }}</td>
                        <td style="padding: 0.5rem;">{{ $supervisor->estagiarios_count }}</td>
                        <td style="padding: 0.5rem;">
                            @if ($supervisor->ativo)
                                <span class="badge-tre-ac">ativo</span>
                            @else
                                <span class="badge-tre-ac is-pendente">inativo</span>
                            @endif
                        </td>
                        <td style="padding: 0.5rem; text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.supervisores.edit', $supervisor) }}" class="br-button secondary small">Editar</a>
                            <form method="POST" action="{{ route('admin.supervisores.destroy', $supervisor) }}"
                                  style="display: inline;"
                                  onsubmit="return confirm('Remover {{ addslashes($supervisor->nome) }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="br-button danger small">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
