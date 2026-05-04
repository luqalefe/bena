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
        .bena-nav a.bena-nav__about {
            color: #64748b;
        }
        .bena-nav a.bena-nav__about i {
            margin-right: 0.35rem;
            font-size: 0.95rem;
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

        /* ─────────────────────────────────────────────────────────────
           Páginas de formulário e edição (admin) — namespace .bena-form-*
           Cards brancos, inputs modernos, focus ring navy. Mantém o
           gov.br DS para botões (.br-button primary) e adiciona estados
           visuais consistentes.
           ───────────────────────────────────────────────────────────── */

        .bena-page-header {
            margin-bottom: 1.5rem;
        }
        .bena-page-header__back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.35rem 0.65rem;
            margin-left: -0.65rem;
            border-radius: 6px;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .bena-page-header__back:hover {
            background: rgba(0, 51, 102, 0.06);
            color: #003366;
        }
        .bena-page-header__back i {
            font-size: 0.7rem;
        }
        .bena-page-header__title {
            color: #003366;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.01em;
            margin: 0.6rem 0 0.25rem;
        }
        .bena-page-header__subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.5;
        }

        .bena-card {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 12px;
            padding: 1.75rem 2rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04),
                        0 4px 14px rgba(0, 51, 102, 0.04);
            max-width: 720px;
        }
        @media (max-width: 600px) {
            .bena-card {
                padding: 1.25rem 1.1rem;
                border-radius: 10px;
            }
        }

        .bena-readonly-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-left: 3px solid #94a3b8;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            max-width: 720px;
        }
        .bena-readonly-info__title {
            color: #475569;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin: 0 0 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }
        .bena-readonly-info__list {
            margin: 0;
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 0.35rem 1rem;
            font-size: 0.9rem;
        }
        .bena-readonly-info__list dt {
            color: #64748b;
            font-weight: 500;
        }
        .bena-readonly-info__list dd {
            color: #0f172a;
            margin: 0;
        }
        .bena-readonly-info__list code {
            background: rgba(15, 23, 42, 0.05);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-family: 'SF Mono', Menlo, monospace;
        }

        .bena-error-summary {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #dc2626;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            color: #991b1b;
            max-width: 720px;
        }
        .bena-error-summary__title {
            font-weight: 700;
            font-size: 0.875rem;
            margin: 0 0 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .bena-error-summary__list {
            margin: 0;
            padding-left: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .bena-form {
            display: grid;
            gap: 1.1rem;
        }
        .bena-form__row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem;
        }
        @media (max-width: 600px) {
            .bena-form__row {
                grid-template-columns: 1fr;
            }
        }

        .bena-form__field {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .bena-form__label {
            color: #334155;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .bena-form__label .required {
            color: #dc2626;
            margin-left: 0.2rem;
        }

        .bena-form__input,
        .bena-form__select,
        .bena-form__textarea {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.6rem 0.8rem;
            font-size: 0.95rem;
            font-family: inherit;
            color: #0f172a;
            line-height: 1.4;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
        }
        .bena-form__input:hover,
        .bena-form__select:hover,
        .bena-form__textarea:hover {
            border-color: #94a3b8;
        }
        .bena-form__input:focus,
        .bena-form__select:focus,
        .bena-form__textarea:focus {
            outline: none;
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.12);
        }
        .bena-form__input::placeholder {
            color: #94a3b8;
        }
        .bena-form__select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            padding-right: 2rem;
        }
        .bena-form__input[type="date"] {
            padding-right: 0.5rem;
        }

        .bena-form__help {
            color: #64748b;
            font-size: 0.825rem;
            line-height: 1.5;
        }
        .bena-form__help a {
            color: #0066cc;
            font-weight: 500;
        }

        .bena-form__checkbox {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.4rem 0;
            cursor: pointer;
            user-select: none;
        }
        .bena-form__checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #003366;
            cursor: pointer;
            flex-shrink: 0;
            margin: 0;
        }
        .bena-form__checkbox span {
            color: #334155;
            font-size: 0.95rem;
        }

        .bena-form__file {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 0.7rem 0.85rem;
            font-size: 0.9rem;
            color: #475569;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
            width: 100%;
        }
        .bena-form__file:hover {
            border-color: #003366;
            background: #f0f9ff;
        }
        .bena-form__file:focus {
            outline: 2px solid #003366;
            outline-offset: 2px;
        }

        .bena-form__actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 0.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(15, 23, 42, 0.06);
        }
        .bena-form__actions--has-extra {
            justify-content: space-between;
            align-items: center;
        }
        .bena-form__actions__primary {
            display: flex;
            gap: 0.75rem;
        }
        @media (max-width: 600px) {
            .bena-form__actions,
            .bena-form__actions--has-extra,
            .bena-form__actions__primary {
                flex-direction: column-reverse;
                align-items: stretch;
            }
            .bena-form__actions .br-button {
                width: 100%;
                justify-content: center;
            }
        }

        .br-button.danger {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
        }
        .br-button.danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }
        .br-button.danger:focus-visible {
            outline: 2px solid #fca5a5;
            outline-offset: 2px;
        }
        .bena-link-danger {
            color: #b91c1c;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            transition: background 0.2s ease;
        }
        .bena-link-danger:hover {
            background: #fee2e2;
            color: #991b1b;
        }

        .bena-warning-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-left: 4px solid #d97706;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(217, 119, 6, 0.06);
            max-width: 720px;
        }
        .bena-warning-card__title {
            color: #78350f;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin: 0 0 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .bena-warning-card p {
            color: #422006;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0 0 0.75rem;
        }
        .bena-warning-card p:last-child {
            margin-bottom: 0;
        }
        .bena-warning-card__highlight {
            background: #fee2e2;
            color: #991b1b;
            padding: 0.7rem 0.9rem;
            border-radius: 6px;
            font-size: 0.9rem;
            line-height: 1.55;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-top: 0.75rem;
        }
        .bena-warning-card__highlight i {
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        /* ─────────────────────────────────────────────────────────────
           Buddy / mascote do estagiário (H28)
           Card exibido na dashboard quando o usuário é do grupo 'E'.
           ───────────────────────────────────────────────────────────── */

        .bena-buddy-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid rgba(0, 51, 102, 0.08);
            border-left: 4px solid #003366;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 51, 102, 0.04),
                        0 4px 14px rgba(0, 51, 102, 0.05);
            animation: bena-buddy-enter 0.5s ease-out both;
        }
        @keyframes bena-buddy-enter {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bena-buddy-card__avatar {
            font-size: 2.5rem;
            line-height: 1;
            flex-shrink: 0;
            animation: bena-buddy-bounce 2.4s ease-in-out infinite;
            transform-origin: 50% 90%;
        }
        @keyframes bena-buddy-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .bena-buddy-card__content {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .bena-buddy-card__name {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #003366;
        }
        .bena-buddy-card__frase {
            margin: 0.25rem 0 0;
            color: #334155;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        @media (prefers-reduced-motion: reduce) {
            .bena-buddy-card,
            .bena-buddy-card__avatar {
                animation: none;
            }
        }
        @media (max-width: 600px) {
            .bena-buddy-card {
                padding: 0.85rem 1rem;
            }
            .bena-buddy-card__avatar {
                font-size: 2rem;
            }
            .bena-buddy-card__frase {
                font-size: 0.9rem;
            }
        }

        /* Variante "apresentação" — usada no onboarding (mascote maior + rodapé) */
        .bena-buddy-card--apresentacao {
            margin-bottom: 2rem;
            padding: 1.4rem 1.5rem;
            gap: 1.25rem;
        }
        .bena-buddy-card__avatar--grande {
            font-size: 3.5rem;
        }
        .bena-buddy-card__rodape {
            margin: 0.6rem 0 0;
            color: #64748b;
            font-size: 0.82rem;
            line-height: 1.5;
        }
        @media (max-width: 600px) {
            .bena-buddy-card--apresentacao {
                flex-direction: column;
                text-align: center;
            }
            .bena-buddy-card__avatar--grande {
                font-size: 2.75rem;
            }
        }

        /* Twemoji: emojis do Twitter (SVG) substituem os do SO. Garante
           que glifos novos (🦫) e ZWJ-sequences (🧑‍🚒, 🧑‍🔬) renderizem
           igual em qualquer SO/navegador, em vez de cair em fallback do
           Segoe UI Emoji. */
        img.emoji {
            height: 1em;
            width: 1em;
            margin: 0 0.05em 0 0.1em;
            vertical-align: -0.1em;
            display: inline-block;
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

                <nav class="bena-nav" aria-label="Navegação principal">
                    @if ($grupo === '0')
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        <a href="{{ route('admin.estagiarios.index') }}">Estagiários</a>
                        <a href="{{ route('admin.supervisores.index') }}">Supervisores</a>
                        <a href="{{ route('admin.auditoria.index') }}" title="Log de ações sensíveis">Auditoria</a>
                    @elseif ($grupo === 'S')
                        <a href="{{ route('supervisor.dashboard') }}">Meus estagiários</a>
                    @endif
                    <a href="{{ route('calendario.index') }}" title="Calendário · feriados">
                        <i class="fas fa-calendar" aria-hidden="true"></i> Calendário
                    </a>
                    <a href="{{ route('onboarding.show') }}" class="bena-nav__about" title="Tutorial e história do sistema">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>Sobre
                    </a>
                </nav>

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

    {{-- Twemoji parser: troca emojis Unicode por <img> SVG do Twitter. --}}
    <script src="https://cdn.jsdelivr.net/npm/@twemoji/api@15.1.0/dist/twemoji.min.js" crossorigin="anonymous"></script>
    <script>
        (function () {
            if (typeof twemoji === 'undefined') return;
            twemoji.parse(document.body, {
                folder: 'svg',
                ext: '.svg',
                base: 'https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/',
            });
        })();
    </script>
</body>
</html>
