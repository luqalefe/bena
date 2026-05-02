@extends('layouts.app')

@section('title', 'Bem-vindo — Bena')

@section('content')
    @php
        $passos = [
            [
                'icone' => 'fa-clock',
                'titulo' => 'Bater ponto pelo navegador',
                'texto' => 'Entrada e saída diretamente da tela inicial. Sem planilha, sem papel — o sistema já calcula as horas trabalhadas.',
            ],
            [
                'icone' => 'fa-calendar-alt',
                'titulo' => 'Folha mensal automática',
                'texto' => 'Veja o mês inteiro com horas calculadas, feriados destacados e fins de semana classificados. Adicione observações em dias específicos.',
            ],
            [
                'icone' => 'fa-pen-fancy',
                'titulo' => 'Assinatura digital ao final do mês',
                'texto' => 'Quando o mês fechar, você assina sua folha eletronicamente. O sistema gera um hash SHA-256 do conteúdo + carimbo de tempo — modelo do SEI, sem necessidade de certificado físico.',
            ],
            [
                'icone' => 'fa-user-check',
                'titulo' => 'Supervisor e RH no mesmo fluxo',
                'texto' => 'Após você assinar, seu supervisor contra-assina pelo sistema. O RH baixa o PDF assinado e anexa direto no processo SEI.',
            ],
            [
                'icone' => 'fa-magic',
                'titulo' => 'Esqueceu de bater saída?',
                'texto' => 'O sistema fecha automaticamente após sua jornada (5h por padrão). Aparece marcado como "auto" pra você saber que foi auto-fechamento.',
            ],
        ];
    @endphp

    <div style="max-width: 720px; margin: 1rem auto;">
        <header style="text-align: center; margin-bottom: 2.5rem;">
            <img src="{{ asset('img/bena.png') }}" alt="Bena" style="width: 96px; height: 96px; object-fit: contain; margin-bottom: 1rem;">
            <h1 style="color: #003366; font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem; letter-spacing: -0.02em;">
                Bem-vindo ao Bena
            </h1>
            <p style="color: #475569; font-size: 1rem; margin: 0;">
                Sistema de Controle de Frequência de Estagiários do TRE-AC.
                Veja em 30 segundos como vai ser o seu dia a dia.
            </p>
        </header>

        <ol style="list-style: none; padding: 0; margin: 0 0 2.5rem;">
            @foreach ($passos as $i => $passo)
                <li style="display: flex; gap: 1.25rem; padding: 1.25rem; background: #fff; border: 1px solid rgba(15, 23, 42, 0.08); border-radius: 12px; margin-bottom: 0.75rem; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);">
                    <div style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #003366 0%, #00528c 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0, 51, 102, 0.18);">
                        <i class="fas {{ $passo['icone'] }}" aria-hidden="true"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span style="color: #94a3b8; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.06em;">
                                PASSO {{ $i + 1 }}
                            </span>
                        </div>
                        <h2 style="color: #0f172a; font-size: 1.05rem; font-weight: 600; margin: 0 0 0.4rem;">
                            {{ $passo['titulo'] }}
                        </h2>
                        <p style="color: #475569; font-size: 0.9rem; margin: 0; line-height: 1.5;">
                            {{ $passo['texto'] }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>

        <form method="POST" action="{{ route('onboarding.concluir') }}" style="text-align: center;">
            @csrf
            <button type="submit" class="br-button primary" style="font-size: 1rem; padding: 0.75rem 2rem;">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                Entendi, vamos começar
            </button>
            <p style="margin: 1rem 0 0; font-size: 0.8rem; color: #94a3b8;">
                Você sempre pode rever este tutorial em <code>/bem-vindo</code>.
            </p>
        </form>
    </div>
@endsection
