@extends('layouts.app')

@section('title', 'Estagiários — Bena')

@section('content')
    <header class="bena-listing__header">
        <div class="bena-listing__header-text">
            <h1 class="bena-listing__title">Estagiários</h1>
            <p class="bena-listing__subtitle">
                Cadastro completo. Use o filtro por lotação para focar num setor.
            </p>
        </div>
    </header>

    @if (session('sucesso'))
        <div class="bena-flash bena-flash--sucesso">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            {{ session('sucesso') }}
        </div>
    @endif

    <form method="GET" action="{{ url('/admin/estagiarios') }}" class="bena-filters">
        <label class="bena-filters__field">
            <span class="bena-filters__label">Lotação</span>
            <select name="lotacao" onchange="this.form.submit()" class="bena-filters__control">
                <option value="">Todas</option>
                @foreach ($lotacoes as $opt)
                    <option value="{{ $opt }}" @selected($lotacao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
        @if ($lotacao)
            <a href="{{ url('/admin/estagiarios') }}" class="br-button secondary">Limpar</a>
        @endif
    </form>

    @if ($estagiarios->isEmpty())
        <div class="bena-empty">
            <i class="fas fa-users" aria-hidden="true"></i>
            Nenhum estagiário encontrado.
        </div>
    @else
        <div class="bena-search">
            <i class="fas fa-search bena-search__icon" aria-hidden="true"></i>
            <input type="text" id="busca-tabela" class="bena-search__input"
                   placeholder="Buscar por nome, username ou lotação…"
                   autocomplete="off">
        </div>

        <div class="bena-table-wrap bena-table-wrap--scroll">
            <table id="tabela-principal" class="bena-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Username</th>
                        <th>Lotação</th>
                        <th>Matrícula</th>
                        <th>Status</th>
                        <th class="is-actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estagiarios as $estagiario)
                        <tr>
                            <td>
                                <span class="bena-table__name">{{ $estagiario->nome }}</span>
                                @if ($estagiario->email)
                                    <span class="bena-table__sub">{{ $estagiario->email }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($estagiario->username)
                                    <code>{{ $estagiario->username }}</code>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>{{ $estagiario->lotacao ?? '—' }}</td>
                            <td>{{ $estagiario->matricula ?? '—' }}</td>
                            <td>
                                @if ($estagiario->ativo)
                                    <span class="bena-pill bena-pill--ativo">ativo</span>
                                @else
                                    <span class="bena-pill bena-pill--inativo">inativo</span>
                                @endif
                            </td>
                            <td class="is-actions">
                                <a href="{{ route('admin.estagiarios.edit', $estagiario) }}" class="br-button secondary small">Editar</a>
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
