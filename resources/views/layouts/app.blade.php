<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    {{-- Fontes oficiais do gov.br DS (Rawline + Raleway) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Ícones do gov.br DS (Font Awesome 5 ships com o pacote) --}}
    <link href="https://cdn.jsdelivr.net/gh/fortawesome/Font-Awesome@5.15.4/css/all.min.css" rel="stylesheet">

    {{-- gov.br Design System v3 --}}
    <link href="https://cdn.jsdelivr.net/npm/@govbr-ds/core@3.7.0/dist/core.min.css" rel="stylesheet">

    {{-- Tema institucional TRE-AC (carregado DEPOIS pra sobrescrever tokens) --}}
    <link href="{{ asset('css/tre-ac-theme.css') }}?v={{ filemtime(public_path('css/tre-ac-theme.css')) }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    {{--
        Cabeçalho institucional. Usa .br-header (component do gov.br) com
        modificador .tre-ac (definido em tre-ac-theme.css) que pinta o fundo
        com a navy do tribunal.
    --}}
    {{--
        Banner de modo de desenvolvimento.
        Renderizado apenas quando AUTHELIA_DEV_BYPASS está ativo (ou seja,
        a app está usando usuário simulado em vez do Authelia real).
        Em produção, config('authelia.dev_bypass') é false → banner some.
    --}}
    @if (config('authelia.dev_bypass') && ! app()->environment('production'))
        <div style="background: var(--accent-tre-ac); color: var(--accent-tre-ac-contrast); padding: 0.5rem 1rem; font-size: 0.875rem; text-align: center;">
            <i class="fas fa-flask" aria-hidden="true"></i>
            <strong>Modo dev</strong> — usuário simulado.
            <a href="{{ route('dev.sessao.form') }}" style="color: var(--accent-tre-ac-contrast); text-decoration: underline;">
                Trocar usuário
            </a>
        </div>
    @endif

    <header class="br-header tre-ac" id="header">
        <div class="container-lg">
            <div class="header-top">
                <div class="header-logo">
                    <span class="header-sign">Tribunal Regional Eleitoral do Acre</span>
                </div>
                <div class="header-actions">
                    @auth
                        <span class="header-functions" style="margin-right: 1rem;">
                            <i class="fas fa-user-circle"></i>
                            {{ auth()->user()->nome ?? auth()->user()->username }}
                        </span>
                    @endauth
                </div>
            </div>
            <div class="header-bottom">
                <div class="header-menu">
                    <div class="header-info">
                        <div class="header-title">@yield('header-title', 'Controle de Frequência de Estagiários')</div>
                        <div class="header-subtitle">@yield('header-subtitle', 'Sistema interno')</div>
                    </div>
                    @if (session('grupodeacesso') === '0')
                        <nav aria-label="Navegação administrativa" style="margin-left: auto; display: flex; gap: 1rem; align-items: center;">
                            <a href="{{ route('admin.dashboard') }}" style="color: var(--brand-tre-ac-contrast, #fff); text-decoration: none;">Dashboard</a>
                            <a href="{{ route('admin.estagiarios.index') }}" style="color: var(--brand-tre-ac-contrast, #fff); text-decoration: none;">Estagiários</a>
                            <a href="{{ route('admin.feriados.index') }}" style="color: var(--brand-tre-ac-contrast, #fff); text-decoration: none;">Feriados</a>
                        </nav>
                    @elseif (session('grupodeacesso') === 'S')
                        <nav aria-label="Navegação supervisor" style="margin-left: auto; display: flex; gap: 1rem; align-items: center;">
                            <a href="{{ route('supervisor.dashboard') }}" style="color: var(--brand-tre-ac-contrast, #fff); text-decoration: none;">Meus estagiários</a>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main id="main-content" class="d-flex flex-fill flex-column" style="min-height: calc(100vh - 200px);">
        <div class="container-lg" style="padding: 2rem 1rem;">
            @if (session('sucesso') || session('status'))
                <div class="br-message success" role="alert" style="margin-bottom: 1rem;">
                    <div class="content">{{ session('sucesso') ?? session('status') }}</div>
                </div>
            @endif

            @if (session('erro') || session('error'))
                <div class="br-message danger" role="alert" style="margin-bottom: 1rem; background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 4px;">
                    <div class="content">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ session('erro') ?? session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="br-footer" style="background: var(--color-primary-darken-01); color: var(--brand-tre-ac-contrast); padding: 1.5rem 0; margin-top: auto;">
        <div class="container-lg" style="text-align: center; font-size: 0.875rem;">
            Tribunal Regional Eleitoral do Acre · Controle de Frequência · v1.0
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
