@extends('layouts.app')

@section('title', 'Configurar usuário simulado')

@section('header-title', 'Configurar usuário simulado')
@section('header-subtitle', 'Modo de desenvolvimento — substitui o login Authelia')

@section('content')
    <div class="tre-ac-card" style="max-width: 640px; margin-bottom: 1.5rem;">
        <h2 style="color: var(--color-primary-default); margin-top: 0;">Atalhos — perfis de teste</h2>
        <p style="color: var(--color-secondary-07); margin-bottom: 1rem; font-size: 0.875rem;">
            Pré-configurado pelo seeder <code>DevPerfisSeeder</code>. O
            estagiário <code>lucas.dev</code> é supervisor de si mesmo
            (<code>supervisor_username = lucas.dev</code>), então o
            preset 🪞 vê o próprio registro na lista de
            <code>/supervisor</code>.
        </p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem;">
            @foreach ([
                ['username' => 'lucas.dev',       'groups' => 'estagiarios',  'nome' => 'Lucas Dev',        'email' => 'lucas.dev@example.local',        'rotulo' => '👤 Eu como estagiário'],
                ['username' => 'lucas.dev',       'groups' => 'supervisores', 'nome' => 'Lucas Dev',        'email' => 'lucas.dev@example.local',        'rotulo' => '🪞 Supervisor de mim mesmo'],
                ['username' => 'lucas.rh',       'groups' => 'admin',         'nome' => 'Lucas RH',         'email' => 'lucas.rh@example.local',         'rotulo' => '👑 RH/Admin'],
                ['username' => 'lucas.supervisor','groups' => 'supervisores', 'nome' => 'Lucas Supervisor', 'email' => 'lucas.supervisor@example.local', 'rotulo' => '🛡️ Outro supervisor (ninguém sob ele)'],
            ] as $preset)
                <form method="POST" action="{{ route('dev.sessao.set') }}">
                    @csrf
                    <input type="hidden" name="username" value="{{ $preset['username'] }}">
                    <input type="hidden" name="groups" value="{{ $preset['groups'] }}">
                    <input type="hidden" name="nome" value="{{ $preset['nome'] }}">
                    <input type="hidden" name="email" value="{{ $preset['email'] }}">
                    <button type="submit" class="br-button" style="width: 100%; text-align: left;">
                        <strong>{{ $preset['rotulo'] }}</strong><br>
                        <small style="color: var(--color-secondary-07);">{{ $preset['username'] }}</small>
                    </button>
                </form>
            @endforeach
        </div>
    </div>

    <div class="tre-ac-card" style="max-width: 640px;">
        <h2 style="color: var(--color-primary-default); margin-top: 0;">
            Configurar usuário simulado
        </h2>

        <p style="color: var(--color-secondary-07); margin-bottom: 1rem;">
            Em produção, o Authelia faz login + 2FA e injeta os headers
            <code>Remote-User/Groups/Name/Email</code>. Em dev, esta tela
            simula esses headers e guarda na sessão. Os defaults vêm do
            <code>.env</code>; o que você definir aqui prevalece.
        </p>

        <form method="POST" action="{{ route('dev.sessao.set') }}">
            @csrf

            <div class="br-input" style="margin-bottom: 1rem;">
                <label for="username">Username (Remote-User)</label>
                <input type="text" id="username" name="username"
                       value="{{ $atual['username'] ?? $defaults['username'] }}"
                       placeholder="ex: lucas.dev" required>
            </div>

            <div class="br-input" style="margin-bottom: 1rem;">
                <label for="groups">Grupos (Remote-Groups, separados por vírgula)</label>
                <input type="text" id="groups" name="groups"
                       value="{{ $atual['groups'] ?? $defaults['groups'] }}"
                       placeholder="estagiarios" required>
                <small style="color: var(--color-secondary-06);">
                    Aceitos: <code>estagiarios</code>, <code>supervisores</code>, <code>admin</code>.
                </small>
            </div>

            <div class="br-input" style="margin-bottom: 1rem;">
                <label for="nome">Nome (Remote-Name)</label>
                <input type="text" id="nome" name="nome"
                       value="{{ $atual['nome'] ?? $defaults['nome'] }}" required>
            </div>

            <div class="br-input" style="margin-bottom: 1.5rem;">
                <label for="email">E-mail (Remote-Email)</label>
                <input type="email" id="email" name="email"
                       value="{{ $atual['email'] ?? $defaults['email'] }}" required>
            </div>

            @if ($errors->any())
                <div class="br-message danger" role="alert" style="margin-bottom: 1rem;">
                    <ul style="margin: 0; padding-left: 1rem;">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="br-button primary">
                    <i class="fas fa-save" aria-hidden="true"></i>
                    Aplicar
                </button>
                <a href="/" class="br-button secondary" style="text-decoration: none;">
                    Cancelar
                </a>
            </div>
        </form>

        @if (! empty($atual))
            <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid var(--color-secondary-03);">

            <h3 style="color: var(--color-primary-default); font-size: 1rem;">
                Sessão atual sobrepondo o .env
            </h3>
            <pre style="background: var(--color-secondary-02); padding: 0.75rem; border-radius: 4px; font-size: 0.875rem; overflow-x: auto;">{{ json_encode($atual, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>

            <form method="POST" action="{{ route('dev.sessao.reset') }}">
                @csrf
                <button type="submit" class="br-button" style="margin-top: 0.5rem;">
                    <i class="fas fa-undo" aria-hidden="true"></i>
                    Resetar para defaults do .env
                </button>
            </form>
        @endif
    </div>
@endsection
