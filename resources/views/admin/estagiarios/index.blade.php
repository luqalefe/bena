@extends('layouts.app')

@section('title', 'Estagiários — Bena')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h1 style="color: var(--color-primary-default); margin: 0;">Estagiários</h1>
    </div>

    @if (session('sucesso'))
        <div style="background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            {{ session('sucesso') }}
        </div>
    @endif

    <form method="GET" action="{{ url('/admin/estagiarios') }}" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Lotação</span>
            <select name="lotacao" onchange="this.form.submit()" style="padding: 0.4rem 0.6rem;">
                <option value="">Todas</option>
                @foreach ($lotacoes as $opt)
                    <option value="{{ $opt }}" @selected($lotacao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <noscript>
            <button type="submit" class="br-button primary">Filtrar</button>
        </noscript>
    </form>

    @if ($estagiarios->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum estagiário encontrado.</p>
    @else
        <div class="bena-form__field" style="margin-bottom: 1rem; max-width: 480px;">
            <label for="busca-tabela" class="bena-form__label">
                <i class="fas fa-search" aria-hidden="true"></i> Busca rápida
            </label>
            <input type="text" id="busca-tabela" class="bena-form__input"
                   placeholder="Buscar por nome, username ou lotação…"
                   autocomplete="off">
        </div>
        <table id="tabela-principal" class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Nome</th>
                    <th style="text-align: left; padding: 0.5rem;">Username</th>
                    <th style="text-align: left; padding: 0.5rem;">Lotação</th>
                    <th style="text-align: left; padding: 0.5rem;">Matrícula</th>
                    <th style="text-align: left; padding: 0.5rem;">Status</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estagiarios as $estagiario)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $estagiario->nome }}</td>
                        <td style="padding: 0.5rem;"><code>{{ $estagiario->username }}</code></td>
                        <td style="padding: 0.5rem;">{{ $estagiario->lotacao ?? '—' }}</td>
                        <td style="padding: 0.5rem;">{{ $estagiario->matricula ?? '—' }}</td>
                        <td style="padding: 0.5rem;">
                            @if ($estagiario->ativo)
                                <span class="badge-tre-ac">ativo</span>
                            @else
                                <span class="badge-tre-ac is-pendente">inativo</span>
                            @endif
                        </td>
                        <td style="padding: 0.5rem;">
                            <a href="{{ route('admin.estagiarios.edit', $estagiario) }}" class="br-button secondary small">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('busca-tabela');
        const tabela = document.getElementById('tabela-principal');
        if (!input || !tabela) return;

        const normalizar = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

        input.addEventListener('input', function () {
            const termo = normalizar(this.value);
            tabela.querySelectorAll('tbody tr').forEach((tr) => {
                tr.style.display = normalizar(tr.textContent).includes(termo) ? '' : 'none';
            });
        });
    })();
</script>
@endpush
