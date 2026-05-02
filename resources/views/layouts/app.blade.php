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

    <style>
        /* ─────────────────────────────────────────────────────────────
           Header Bena — branco frosted glass com texto navy (cor da
           logo). Escopado em .bena-header / .bena-* pra não conflitar
           com classes do gov.br DS.
           ───────────────────────────────────────────────────────────── */

        .skip-link {
            position: absolute;
            top: -100px;
            left: 1rem;
            padding: 0.5rem 1rem;
            background: #003366;
            color: #fff;
            border-radius: 6px;
            font-weight: 600;
            z-index: 1000;
            text-decoration: none;
            transition: top 0.2s ease;
            box-shadow: 0 6px 18px rgba(0, 51, 102, 0.25);
        }
        .skip-link:focus {
            top: 0.5rem;
            outline: 2px solid #60a5fa;
            outline-offset: 2px;
        }

        .bena-header {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03), 0 4px 12px rgba(15, 23, 42, 0.04);
            color: #0f172a;
            position: sticky;
            top: 0;
            z-index: 100;
            animation: benaHeaderSlideDown 0.35s ease-out both;
        }

        @keyframes benaHeaderSlideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .bena-header__inner {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 0.875rem 1rem;
        }

        /* Brand: logo + textos */
        .bena-brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            color: inherit;
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            transition: background 0.2s ease;
        }
        .bena-brand:hover {
            background: rgba(0, 51, 102, 0.04);
        }
        .bena-brand__logo {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06),
                        0 4px 14px rgba(0, 51, 102, 0.08),
                        0 0 0 1px rgba(0, 51, 102, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .bena-brand:hover .bena-brand__logo {
            transform: scale(1.04);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08),
                        0 8px 22px rgba(0, 51, 102, 0.14),
                        0 0 0 1px rgba(0, 51, 102, 0.08);
        }
        .bena-brand__logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .bena-brand__title {
            font-family: 'Raleway', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: #003366;
        }
        .bena-brand__subtitle {
            font-size: 0.78rem;
            font-weight: 500;
            color: #475569;
            margin-top: 0.15rem;
            letter-spacing: 0.02em;
        }
        .bena-brand__meta {
            font-size: 0.68rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: 0.2rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* Navegação por grupo */
        .bena-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-left: auto;
        }
        .bena-nav a {
            color: #475569;
            text-decoration: none;
            padding: 0.5rem 0.9rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .bena-nav a:hover {
            background: rgba(0, 51, 102, 0.06);
            color: #003366;
        }

        /* Bloco do usuário (avatar + nome + cargo) */
        .bena-user {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.4rem 0.6rem 0.4rem 0.4rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.03);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }
        .bena-user__avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003366 0%, #00528c 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 51, 102, 0.22),
                        inset 0 0 0 2px rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
            letter-spacing: 0.02em;
        }
        .bena-user__name {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.1;
        }
        .bena-user__role {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        /* Focus visible — a11y obrigatória pra órgão público (WCAG 2.1 AA) */
        .bena-brand:focus-visible,
        .bena-nav a:focus-visible {
            outline: 2px solid #003366;
            outline-offset: 2px;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .bena-header__inner {
                gap: 0.75rem;
                padding: 0.625rem 0.75rem;
            }
            .bena-brand__subtitle,
            .bena-brand__meta,
            .bena-user__info,
            .bena-nav {
                display: none;
            }
            .bena-brand__logo {
                width: 44px; height: 44px;
            }
            .bena-brand__logo img {
                width: 36px; height: 36px;
            }
            .bena-brand__title {
                font-size: 1.05rem;
            }
            .bena-user__avatar {
                width: 34px; height: 34px;
            }
            .bena-user {
                padding: 0.3rem;
            }
        }
    </style>

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
    <a href="#main-content" class="skip-link">Pular para o conteúdo</a>

    @if (config('authelia.dev_bypass') && ! app()->environment('production'))
        <div style="background: var(--accent-tre-ac); color: var(--accent-tre-ac-contrast); padding: 0.5rem 1rem; font-size: 0.875rem; text-align: center;">
            <i class="fas fa-flask" aria-hidden="true"></i>
            <strong>Modo dev</strong> — usuário simulado.
            <a href="{{ route('dev.sessao.form') }}" style="color: var(--accent-tre-ac-contrast); text-decoration: underline;">
                Trocar usuário
            </a>
        </div>
    @endif

    <header class="bena-header" id="header">
        <div class="container-lg bena-header__inner">
            <a href="{{ route('dashboard') }}" class="bena-brand" aria-label="Bena — ir para o dashboard">
                <div class="bena-brand__logo">
                    <img src="{{ asset('img/bena.png') }}" alt="">
                </div>
                <div class="bena-brand__text">
                    <div class="bena-brand__title">@yield('header-title', 'Bena')</div>
                    <div class="bena-brand__subtitle">@yield('header-subtitle', 'Controle de Frequência de Estagiários')</div>
                    <div class="bena-brand__meta">
                        Tribunal Regional Eleitoral do Acre · {{ now()->translatedFormat('F / Y') }}
                    </div>
                </div>
            </a>

            @auth
                @php
                    $grupo = session('grupodeacesso');
                @endphp

                @if ($grupo === '0')
                    <nav class="bena-nav" aria-label="Navegação administrativa">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <a href="{{ route('admin.estagiarios.index') }}">Estagiários</a>
                        <a href="{{ route('admin.feriados.index') }}">Feriados</a>
                    </nav>
                @elseif ($grupo === 'S')
                    <nav class="bena-nav" aria-label="Navegação supervisor">
                        <a href="{{ route('supervisor.dashboard') }}">Meus estagiários</a>
                    </nav>
                @else
                    <div style="margin-left: auto;"></div>
                @endif

                @php
                    $nomeCompleto = auth()->user()->nome ?? auth()->user()->username;
                    $partes = preg_split('/\s+/', trim((string) $nomeCompleto)) ?: [(string) $nomeCompleto];
                    $iniciais = strtoupper(
                        substr($partes[0] ?? '?', 0, 1)
                        .(count($partes) > 1 ? substr((string) end($partes), 0, 1) : '')
                    );
                    $cargo = match($grupo) {
                        '0' => 'Admin / RH',
                        'S' => 'Supervisor',
                        'E' => 'Estagiário',
                        default => 'Usuário',
                    };
                @endphp
                <div class="bena-user">
                    <div class="bena-user__avatar" aria-hidden="true">{{ $iniciais }}</div>
                    <div class="bena-user__info">
                        <div class="bena-user__name">{{ $nomeCompleto }}</div>
                        <div class="bena-user__role">{{ $cargo }}</div>
                    </div>
                </div>
            @endauth
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
            Tribunal Regional Eleitoral do Acre · Bena — Controle de Frequência · v1.0
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
