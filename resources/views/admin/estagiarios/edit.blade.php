@extends('layouts.app')

@section('title', "Editar {$estagiario->nome} — Estagiários")

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.estagiarios.index') }}" style="color: var(--color-secondary-07); text-decoration: none;">← Voltar para lista</a>
        <h1 style="color: var(--color-primary-default); margin: 0.5rem 0 0;">Editar estagiário</h1>
    </div>

    <section style="background: var(--color-secondary-01); padding: 1rem 1.25rem; border-radius: 4px; margin-bottom: 1.5rem;">
        <h2 style="margin: 0 0 0.5rem; font-size: 1rem; color: var(--color-secondary-07);">Identidade (vinda do SSO, não editável)</h2>
        <dl style="margin: 0; display: grid; grid-template-columns: max-content 1fr; gap: 0.25rem 1rem;">
            <dt><strong>Username:</strong></dt>
            <dd><code>{{ $estagiario->username }}</code></dd>
            <dt><strong>Nome:</strong></dt>
            <dd>{{ $estagiario->nome }}</dd>
            <dt><strong>E-mail:</strong></dt>
            <dd>{{ $estagiario->email }}</dd>
        </dl>
    </section>

    @if ($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <strong>Erros de validação:</strong>
            <ul style="margin: 0.5rem 0 0 1.25rem;">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.estagiarios.update', $estagiario) }}" enctype="multipart/form-data" style="display: grid; gap: 1rem; max-width: 640px;">
        @csrf
        @method('PUT')

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Matrícula</span>
            <input type="text" name="matricula" value="{{ old('matricula', $estagiario->matricula) }}" maxlength="30" style="padding: 0.4rem 0.6rem;">
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Lotação</span>
            <input type="text" name="lotacao" value="{{ old('lotacao', $estagiario->lotacao) }}" maxlength="100" style="padding: 0.4rem 0.6rem;">
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Supervisor (nome)</span>
            <input type="text" name="supervisor_nome" value="{{ old('supervisor_nome', $estagiario->supervisor_nome) }}" maxlength="200" style="padding: 0.4rem 0.6rem;">
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Supervisor (username Authelia)</span>
            <input type="text" name="supervisor_username" value="{{ old('supervisor_username', $estagiario->supervisor_username) }}" maxlength="100" placeholder="ex: marco.supervisor" style="padding: 0.4rem 0.6rem;">
            <small style="color: var(--color-secondary-07);">Quem pode contra-assinar a folha desse estagiário. Deve bater com o login do supervisor no Authelia.</small>
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>SEI</span>
            <input type="text" name="sei" value="{{ old('sei', $estagiario->sei) }}" maxlength="50" style="padding: 0.4rem 0.6rem;">
        </label>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <label style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span>Início do estágio</span>
                <input type="date" name="inicio_estagio" value="{{ old('inicio_estagio', optional($estagiario->inicio_estagio)->format('Y-m-d')) }}" style="padding: 0.4rem 0.6rem;">
            </label>
            <label style="display: flex; flex-direction: column; gap: 0.25rem;">
                <span>Fim do estágio</span>
                <input type="date" name="fim_estagio" value="{{ old('fim_estagio', optional($estagiario->fim_estagio)->format('Y-m-d')) }}" style="padding: 0.4rem 0.6rem;">
            </label>
        </div>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Horas diárias</span>
            <input type="number" name="horas_diarias" value="{{ old('horas_diarias', $estagiario->horas_diarias) }}" step="0.25" min="0.25" max="24" required style="padding: 0.4rem 0.6rem;">
        </label>

        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $estagiario->ativo))>
            <span>Estágio ativo</span>
        </label>

        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span>Contrato (PDF, máx 5 MB)</span>
            <input type="file" name="contrato" accept="application/pdf" style="padding: 0.4rem 0.6rem;">
            @if ($estagiario->contrato_path)
                <small style="color: var(--color-secondary-07);">
                    Contrato atual:
                    <a href="{{ route('admin.estagiarios.contrato', $estagiario) }}">baixar</a>
                    — enviar novo arquivo substitui o anterior.
                </small>
            @endif
        </label>

        <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
            <button type="submit" class="br-button primary">Salvar</button>
            <a href="{{ route('admin.estagiarios.index') }}" class="br-button secondary">Cancelar</a>
        </div>
    </form>
@endsection
