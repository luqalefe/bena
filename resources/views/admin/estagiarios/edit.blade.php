@extends('layouts.app')

@section('title', "Editar {$estagiario->nome} — Estagiários")

@section('content')
    <div class="bena-page-header">
        <a href="{{ route('admin.estagiarios.index') }}" class="bena-page-header__back">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para estagiários
        </a>
        <h1 class="bena-page-header__title">Editar estagiário</h1>
        <p class="bena-page-header__subtitle">
            {{ $estagiario->nome }}
        </p>
    </div>

    <section class="bena-readonly-info" aria-label="Identidade vinda do SSO">
        <h2 class="bena-readonly-info__title">
            <i class="fas fa-id-badge" aria-hidden="true"></i>
            Identidade · vinda do SSO, não editável
        </h2>
        <dl class="bena-readonly-info__list">
            <dt>Username</dt>
            <dd><code>{{ $estagiario->username }}</code></dd>
            <dt>Nome</dt>
            <dd>{{ $estagiario->nome }}</dd>
            <dt>E-mail</dt>
            <dd>{{ $estagiario->email }}</dd>
        </dl>
    </section>

    @if ($errors->any())
        <div class="bena-error-summary" role="alert">
            <p class="bena-error-summary__title">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                Corrija os erros abaixo:
            </p>
            <ul class="bena-error-summary__list">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bena-card">
        <form method="POST" action="{{ route('admin.estagiarios.update', $estagiario) }}" enctype="multipart/form-data" class="bena-form">
            @csrf
            @method('PUT')

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="matricula" class="bena-form__label">Matrícula</label>
                    <input type="text" id="matricula" name="matricula" value="{{ old('matricula', $estagiario->matricula) }}" maxlength="30" class="bena-form__input">
                </div>

                <div class="bena-form__field">
                    <label for="sei" class="bena-form__label">Processo SEI</label>
                    <input type="text" id="sei" name="sei" value="{{ old('sei', $estagiario->sei) }}" maxlength="50" class="bena-form__input" placeholder="0000.000000/0000-00">
                </div>
            </div>

            <div class="bena-form__field">
                <label for="lotacao" class="bena-form__label">Lotação</label>
                <input type="text" id="lotacao" name="lotacao" value="{{ old('lotacao', $estagiario->lotacao) }}" maxlength="100" class="bena-form__input" placeholder="Ex: STI / SDBD">
            </div>

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="supervisor_nome" class="bena-form__label">Supervisor (nome)</label>
                    <input type="text" id="supervisor_nome" name="supervisor_nome" value="{{ old('supervisor_nome', $estagiario->supervisor_nome) }}" maxlength="200" class="bena-form__input">
                </div>

                <div class="bena-form__field">
                    <label for="supervisor_username" class="bena-form__label">Supervisor (username)</label>
                    <input type="text" id="supervisor_username" name="supervisor_username" value="{{ old('supervisor_username', $estagiario->supervisor_username) }}" maxlength="100" placeholder="ex: marco.supervisor" class="bena-form__input">
                    <p class="bena-form__help">
                        Quem pode contra-assinar a folha. Deve bater com o login do supervisor no Authelia.
                    </p>
                </div>
            </div>

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="inicio_estagio" class="bena-form__label">Início do estágio</label>
                    <input type="date" id="inicio_estagio" name="inicio_estagio" value="{{ old('inicio_estagio', optional($estagiario->inicio_estagio)->format('Y-m-d')) }}" class="bena-form__input">
                </div>

                <div class="bena-form__field">
                    <label for="fim_estagio" class="bena-form__label">Fim do estágio</label>
                    <input type="date" id="fim_estagio" name="fim_estagio" value="{{ old('fim_estagio', optional($estagiario->fim_estagio)->format('Y-m-d')) }}" class="bena-form__input">
                </div>
            </div>

            <div class="bena-form__field" style="max-width: 240px;">
                <label for="horas_diarias" class="bena-form__label">
                    Horas diárias <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="number" id="horas_diarias" name="horas_diarias" value="{{ old('horas_diarias', $estagiario->horas_diarias) }}" step="0.25" min="0.25" max="24" required class="bena-form__input">
                <p class="bena-form__help">Jornada diária prevista (em horas, ex: 5 ou 6).</p>
            </div>

            <label class="bena-form__checkbox">
                <input type="checkbox" name="ativo" value="1" @checked(old('ativo', $estagiario->ativo))>
                <span>Estágio ativo</span>
            </label>

            <div class="bena-form__field">
                <label for="contrato" class="bena-form__label">Contrato (PDF, máx 5 MB)</label>
                <input type="file" id="contrato" name="contrato" accept="application/pdf" class="bena-form__file">
                @if ($estagiario->contrato_path)
                    <p class="bena-form__help">
                        Contrato atual:
                        <a href="{{ route('admin.estagiarios.contrato', $estagiario) }}">
                            <i class="fas fa-file-pdf" aria-hidden="true"></i> baixar
                        </a>
                        · enviar novo arquivo substitui o anterior.
                    </p>
                @endif
            </div>

            <div class="bena-form__actions">
                <a href="{{ route('admin.estagiarios.index') }}" class="br-button secondary">Cancelar</a>
                <button type="submit" class="br-button primary">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>
@endsection
