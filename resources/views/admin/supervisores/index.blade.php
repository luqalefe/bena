@extends('layouts.app')

@section('title', 'Supervisores — Bena')

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Supervisores</h1>
            <p class="bena-listing__subtitle">
                Cadastre quem aparece como opção ao vincular um estagiário.
                Apenas supervisores ativos aparecem no dropdown de edição.
            </p>
        </div>
        <a href="{{ route('admin.supervisores.create') }}" class="br-button primary">
            <i class="fas fa-plus" aria-hidden="true"></i>
            Novo supervisor
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
        <div class="bena-empty">
            <i class="fas fa-user-tie" aria-hidden="true"></i>
            Nenhum supervisor cadastrado ainda.
        </div>
    @else
        <div class="bena-search">
            <i class="fas fa-search bena-search__icon" aria-hidden="true"></i>
            <input type="text" id="busca-tabela" class="bena-search__input"
                   placeholder="Buscar por nome, username, lotação…"
                   autocomplete="off">
        </div>

        <div class="bena-table-wrap bena-table-wrap--scroll">
            <table id="tabela-principal" class="bena-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Username</th>
                        <th>E-mail</th>
                        <th>Lotação</th>
                        <th class="is-num">Estagiários</th>
                        <th>Status</th>
                        <th class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supervisores as $supervisor)
                        <tr>
                            <td><span class="bena-table__name">{{ $supervisor->nome }}</span></td>
                            <td>
                                @if ($supervisor->username)
                                    <code>{{ $supervisor->username }}</code>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>{{ $supervisor->email ?? '—' }}</td>
                            <td>{{ $supervisor->lotacao ?? '—' }}</td>
                            <td class="is-num">{{ $supervisor->estagiarios_count }}</td>
                            <td>
                                @if ($supervisor->ativo)
                                    <span class="bena-pill bena-pill--ativo">ativo</span>
                                @else
                                    <span class="bena-pill bena-pill--inativo">inativo</span>
                                @endif
                            </td>
                            <td class="is-actions">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="{{ route('admin.supervisores.edit', $supervisor) }}" class="br-button secondary small">Editar</a>
                                    <form method="POST" action="{{ route('admin.supervisores.destroy', $supervisor) }}"
                                          style="display: inline;"
                                          onsubmit="return confirm('Remover {{ addslashes($supervisor->nome) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="br-button danger small">Remover</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('busca-tabela');
        const tabela = document.getElementById('tabela-principal');
        if (!input || !tabela) return;

        const normalizar = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

        input.addEventListener('input', function () {
            const termo = normalizar(this.value);
            tabela.querySelectorAll('tbody tr').forEach((tr) => {
                tr.style.display = normalizar(tr.textContent).includes(termo) ? '' : 'none';
            });
        });
    })();
</script>
@endpush
