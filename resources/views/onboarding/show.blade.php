@extends('layouts.app')

@section('title', 'Bem-vindo ao Bena')

@push('styles')
<style>
    .bena-buddy-slot {
        display: none;
        flex-direction: column;
        align-items: center;
        gap: 0.85rem;
        padding: 1.75rem;
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        border: 2px solid rgba(250, 204, 21, 0.35);
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
        margin: 1.25rem auto 0;
        max-width: 280px;
    }
    .bena-buddy-reveal[data-buddy-reveal="rolling"] .bena-buddy-reveal__trigger,
    .bena-buddy-reveal[data-buddy-reveal="rolling"] .bena-buddy-reveal__intro {
        display: none;
    }
    .bena-buddy-reveal[data-buddy-reveal="rolling"] .bena-buddy-slot {
        display: flex;
        animation: bena-slot-shake 0.18s infinite;
    }
    .bena-buddy-slot__frame {
        width: 160px;
        height: 160px;
        border: 4px solid #fde68a;
        border-radius: 12px;
        background: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: inset 0 0 16px rgba(0, 0, 0, 0.5);
    }
    .bena-buddy-slot__avatar {
        width: 128px;
        height: 128px;
        image-rendering: pixelated;
    }
    .bena-buddy-slot__label {
        color: #fde68a;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-size: 0.85rem;
        margin: 0;
        animation: bena-slot-pulse 0.8s ease-in-out infinite;
    }
    @keyframes bena-slot-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-3px) rotate(-0.5deg); }
        75% { transform: translateX(3px) rotate(0.5deg); }
    }
    @keyframes bena-slot-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.55; }
    }
    @media (prefers-reduced-motion: reduce) {
        .bena-buddy-reveal[data-buddy-reveal="rolling"] .bena-buddy-slot {
            animation: none;
        }
        .bena-buddy-slot__label {
            animation: none;
        }
    }
</style>
@endpush

@section('content')
    <div style="max-width: 720px; margin: 1rem auto;">
        <header class="bena-onboarding-hero">
            <img src="{{ asset('img/bena.png') }}" alt="Bena" class="bena-onboarding-hero__logo">
            <h1 class="bena-onboarding-hero__title">Bem-vindo ao Bena</h1>
            <section class="bena-buddy-card bena-buddy-card--apresentacao bena-onboarding-hero__narrator-card" role="status">
                <div class="bena-buddy-card__avatar bena-buddy-card__avatar--grande" aria-hidden="true">
                    @if (! empty($lucanderSprite))
                        <img src="{{ $lucanderSprite }}" alt="" class="bena-buddy-card__sprite" style="image-rendering: pixelated; width: 96px; height: 96px;">
                    @else
                        🧙‍♂️
                    @endif
                </div>
                <div class="bena-buddy-card__content">
                    <span class="bena-buddy-card__name">Lucander, o Improvisador</span>
                    <p class="bena-buddy-card__frase">
                        Olá! Sou o <strong>Lucander, o Improvisador</strong> — criador do Bena.
                        Deixa eu te mostrar como funciona em 30 segundos.
                    </p>
                </div>
            </section>
        </header>

        <section aria-labelledby="por-que-bena" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 4px solid #d97706; border-radius: 8px; padding: 1.5rem 1.75rem; margin-bottom: 2.5rem; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.08);">
            <h2 id="por-que-bena" style="color: #78350f; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin: 0 0 0.75rem;">
                Por que Bena
            </h2>
            <p style="color: #422006; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                <strong>Bena</strong> é uma palavra em <em>Hãtxa Kuĩ</em>, língua
                do povo Huni Kuin, que vive no Acre. Significa <strong>"novo"</strong>.
                Ela vem da expressão <em>Xinã Bena</em>, ou "novo tempo", usada
                pelos Huni Kuin para falar do momento atual de renovação cultural.
                É o nome certo para o sistema que substitui a folha de ponto em
                papel pelo registro digital.
            </p>
            <p style="color: #422006; font-size: 0.95rem; line-height: 1.65; margin: 0 0 1.25rem;">
                O Bena nasceu de uma observação simples: se a entrega da folha
                de ponto vira uma pequena novela mensal, com adiamento,
                promessa de oração e reza pelo bom senso de quem vai entregar
                por último, talvez o problema não seja quem assina. Seja a
                folha. Esse sistema resolve isso de uma vez, para que
                estagiários e supervisores possam investir o tempo onde ele
                rende: no aprendizado e no trabalho que importa.
            </p>

        </section>

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

        @isset($buddy)
            @php
                $spritesService = app(\App\Support\BuddySprite::class);
                $tiposParaSlot = collect(array_merge(
                    (array) config('buddies.tipos', []),
                    (array) config('buddies.tipos_supervisores', []),
                    (array) config('buddies.tipos_lendarios', []),
                ))
                    ->map(fn ($t) => $spritesService->caminho($t))
                    ->filter()
                    ->values()
                    ->all();
            @endphp
            <div class="bena-buddy-reveal" data-buddy-reveal="false">
                <p class="bena-buddy-reveal__intro">
                    Antes de você entrar pra valer, eu sorteei um mascote pra te
                    acompanhar — clica pra revelar.
                </p>
                <button type="button" class="br-button primary bena-buddy-reveal__trigger" data-buddy-trigger>
                    <i class="fas fa-gift" aria-hidden="true"></i>
                    Descobrir meu mascote
                </button>
                <div class="bena-buddy-slot" data-buddy-slot>
                    <div class="bena-buddy-slot__frame">
                        {{-- src vazio no carregamento: o JS popula com sprite aleatório
                             no clique pra evitar spoiler. --}}
                        <img class="bena-buddy-slot__avatar" alt="" src="">
                    </div>
                    <p class="bena-buddy-slot__label">Sorteando…</p>
                </div>
                <section aria-labelledby="buddy-titulo" class="bena-buddy-card bena-buddy-card--apresentacao bena-buddy-reveal__card">
                    <div class="bena-buddy-card__avatar bena-buddy-card__avatar--grande" aria-hidden="true">
                        @if ($buddy->sprite)
                            <img src="{{ $buddy->sprite }}" alt="" class="bena-buddy-card__sprite" style="image-rendering: pixelated; width: 96px; height: 96px;">
                        @else
                            {{ $buddy->emoji }}
                        @endif
                    </div>
                    <div class="bena-buddy-card__content">
                        <span id="buddy-titulo" class="bena-buddy-card__name">Conheça seu mascote · {{ $buddy->nome }}</span>
                        <p class="bena-buddy-card__frase">{{ $buddy->frase }}</p>
                        <p class="bena-buddy-card__rodape">
                            Cada usuário recebe um mascote sorteado no primeiro acesso.
                            Ele te recebe na dashboard com uma frase diferente a cada dia.
                        </p>
                    </div>
                </section>
                <div class="bena-buddy-reveal__after">
                    <a href="{{ route('mascotes.index') }}?autoplay=1" class="br-button secondary">
                        <i class="fas fa-paw" aria-hidden="true"></i>
                        Conhecer todos os mascotes
                    </a>
                </div>
            </div>
        @endisset

        <section aria-labelledby="bastidores" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-left: 4px solid #d97706; border-radius: 8px; padding: 1.5rem 1.75rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(217, 119, 6, 0.08);">
            <h2 id="bastidores" style="color: #78350f; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin: 0 0 0.75rem;">
                Bastidores
            </h2>
            <p style="color: #422006; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                Para quem está curioso: o Bena foi escrito em um único feriadão.
                Três dias, mais ou menos 17 horas por dia, 51 horas no total,
                começando em um <strong>1º de Maio</strong>. Sim: um sistema para
                tirar a folha de papel das mãos dos trabalhadores foi escrito
                justamente no Dia do Trabalhador. A ironia é parte do projeto.
            </p>
            <p style="color: #422006; font-size: 0.95rem; line-height: 1.65; margin: 0;">
                O combustível foi uma dose generosa de teimosia: alguns
                comentários irônicos de colegas estagiários sobre a tal
                "novela mensal" da folha de ponto serviram de empurrão.
                O Bena não chega nem perto da complexidade do
                <strong>Git</strong>, guardadas as devidíssimas
                proporções, mas a indignação foi parecida com a do
                Linus Torvalds quando escreveu o dele e, no primeiro
                commit, batizou o projeto de "gerenciador de arquivos
                do inferno". Sou neurodivergente, convivo com o que se
                chama de hiperfoco, esse traço de cair de cabeça em
                tarefas que amo, e engenharia de software entra
                exatamente nessa lista. No fim, foi mais fácil escrever
                o sistema do que continuar reclamando dele.
            </p>
        </section>

        <section aria-labelledby="agradecimentos" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-left: 4px solid #003366; border-radius: 8px; padding: 1.5rem 1.75rem; margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0, 51, 102, 0.08);">
            <h2 id="agradecimentos" style="color: #003366; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin: 0 0 0.75rem;">
                Quem me ensinou a amar isso aqui
            </h2>
            <p style="color: #0f172a; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                O TRE-AC, e em especial a <strong>STI</strong>, foi quem me
                ensinou a amar essa profissão. Em quase dois anos de estágio
                aprendi com gente que faz esse setor existir todo dia, e devo
                este sistema, e muito do que sei, a essas pessoas.
            </p>
            <p style="color: #0f172a; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                Ao <strong>mestre Edcley</strong>, que sempre acreditou no meu
                potencial e me entregou projetos desafiadores que me fizeram
                crescer. Engenheiro de raiz, daqueles que preferem construir
                tudo do zero, bem no estilo do próprio Linus Torvalds, que
                gostava de resumir: <em>"não sou um visionário, sou um
                engenheiro"</em>. Menos plano de cinco anos, mais código
                rodando. Ao <strong>Lucir</strong>, um dos primeiros analistas
                de TI do tribunal, que também apostou em mim e me passou ideias
                "malucas". Tem quem ache o Lucir doido, mas, como cantam Os
                Mutantes na <em>Balada do Louco</em>, "louco é quem me diz e
                não é feliz"; e, no fim das contas, para ser engenheiro de
                software também é preciso ter um certo grau de loucura. Entre
                essas ideias estava o sistema de cadastro de magistrados,
                feriadão solo bem parecido com este, feito a quatro mãos com o
                <strong>Keith</strong>, também um dos servidores mais antigos
                da casa, que conduziu comigo a análise de requisitos e me
                ajudou a entender o domínio do começo ao fim. Ao
                <strong>Bortoli</strong>, talvez o amigo com mais anos de TI em
                toda a Justiça Eleitoral, e referência viva.
            </p>
            <p style="color: #0f172a; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                À paciência do <strong>Ilis</strong>, que insistiu em me
                ensinar algumas tarefas no Plone. Confesso, não estão entre
                as minhas favoritas, mas sempre que precisam de uma mão por
                lá eu tomo a frente e tento resolver. Aprender com quem domina
                a ferramenta vale mais do que gostar dela.
            </p>
            <p style="color: #0f172a; font-size: 0.95rem; line-height: 1.65; margin: 0;">
                E aos colegas que chegaram um pouco depois e viraram parceria
                de verdade: <strong>Felipe, Thallys, Jair e Jonatan</strong>,
                que abriram espaço para eu contribuir no projeto AGRECOM
                cuidando de todo o módulo de relatórios. Esse trecho da estrada
                foi decisivo na minha evolução.
            </p>
        </section>

        <section aria-labelledby="para-quem-vier" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 4px solid #059669; border-radius: 8px; padding: 1.5rem 1.75rem; margin-bottom: 2.5rem; box-shadow: 0 1px 3px rgba(5, 150, 105, 0.08);">
            <h2 id="para-quem-vier" style="color: #064e3b; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin: 0 0 0.75rem;">
                Para quem vier depois
            </h2>
            <p style="color: #022c22; font-size: 0.95rem; line-height: 1.65; margin: 0 0 0.75rem;">
                Quero que o Bena seja útil para o próximo estagiário da SDBD,
                e para os seguintes, assim como os outros sistemas deixados
                aqui têm sido úteis para mim. Que esses projetos durem pelas
                próximas gerações de estagiários do tribunal. Martin Fowler,
                no artigo <em>MonolithFirst</em>, defende que
                <em>"você não deveria começar um novo projeto com
                microserviços, nem que tenha certeza de que a aplicação vai
                ficar grande o bastante para justificar"</em>. Foi exatamente
                esse o gesto aqui: comecei o Bena pequeno, monolítico,
                propositalmente simples, para que quem herdar encontre um
                terreno entendível e só evolua a complexidade quando o
                domínio realmente pedir. Nada de microserviço prematuro só
                para parecer moderno.
            </p>
            <p style="color: #022c22; font-size: 0.95rem; line-height: 1.65; margin: 0 0 1.25rem;">
                E que prevaleça o foco no que importa: aprender e evoluir
                naquilo a que cada um se propôs, seja na engenharia de
                software, no suporte ou na área jurídica. Burocracia
                administrativa não pode roubar esse tempo. Este lugar é
                terreno fértil para quem quer crescer, e eu sou testemunha
                disso.
            </p>
            <div style="border-top: 1px dashed rgba(5, 150, 105, 0.35); padding-top: 1rem;">
                <p style="color: #022c22; font-size: 0.92rem; line-height: 1.6; font-style: italic; margin: 0 0 0.5rem;">
                    Esta é a marca que quero deixar neste tribunal: um sistema
                    feito por um estagiário, para estagiários.
                </p>
                <p style="color: #064e3b; font-size: 0.88rem; font-weight: 700; margin: 0; letter-spacing: 0.02em;">
                    Lucas Alefe
                </p>
            </div>
        </section>

        <form method="POST" action="{{ route('onboarding.concluir') }}" style="text-align: center;">
            @csrf
            <button type="submit" class="br-button primary" style="font-size: 1rem; padding: 0.75rem 2rem;">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                Entendi, vamos começar
            </button>
            <p style="margin: 1rem 0 0; font-size: 0.8rem; color: #94a3b8;">
                Você sempre pode rever este tutorial pelo link <strong>Sobre</strong> no topo, ou em <code>/bem-vindo</code>.
            </p>
        </form>
    </div>

    <script>
        (function () {
            var reveal = document.querySelector('[data-buddy-reveal]');
            if (!reveal) return;
            var trigger = reveal.querySelector('[data-buddy-trigger]');
            if (!trigger) return;
            var slot = reveal.querySelector('[data-buddy-slot]');
            var slotAvatar = slot ? slot.querySelector('.bena-buddy-slot__avatar') : null;

            @isset($buddy)
                var allSprites = @json($tiposParaSlot ?? []);
                var finalSprite = @json($buddy->sprite ?? null);
                var finalArtistName = @json($buddy->nome ?? '');
            @else
                var allSprites = [];
                var finalSprite = null;
                var finalArtistName = '';
            @endisset

            // Pré-cache de todos os sprites do slot machine. Cada troca de
            // src durante a animação encontra a imagem já em cache, evitando
            // flicker de loading e o "atraso" do mascote no mini player.
            var preloadCache = [];
            allSprites.forEach(function (url) {
                var img = new Image();
                img.src = url;
                preloadCache.push(img);
            });
            if (finalSprite) {
                var preloadFinal = new Image();
                preloadFinal.src = finalSprite;
                preloadCache.push(preloadFinal);
            }

            // Trilha sonora do sorteio: começa do zero a cada clique no
            // descobrir mascote. Reusa o elemento <audio> do player global
            // (em vez de criar um Audio() à parte) — sem isso teríamos duas
            // instâncias tocando em paralelo. Estado é salvo no
            // sessionStorage pelos listeners do próprio player global.
            function iniciaTrilhaDoZero() {
                var globalPlayer = document.getElementById('bena-player');
                var globalAudio = document.getElementById('bena-player-audio');
                if (!globalAudio) return;

                // Se o usuário tinha fechado no X, reaparecer e desmarcar
                // dismissed pras próximas views.
                if (globalPlayer && globalPlayer.hidden) globalPlayer.hidden = false;
                try { sessionStorage.removeItem('bena-player-dismissed'); } catch (e) {}

                try { globalAudio.pause(); } catch (e) {}
                try { globalAudio.currentTime = 0; } catch (e) {}
                globalAudio.play().catch(function () {});
            }

            function revelaCarta() {
                reveal.setAttribute('data-buddy-reveal', 'true');
                var card = reveal.querySelector('.bena-buddy-reveal__card');
                if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            trigger.addEventListener('click', function () {
                // Música começa exatamente no momento do clique — mesmo no
                // caminho de fallback (sem sprites pra animar).
                iniciaTrilhaDoZero();

                if (!slot || !slotAvatar || allSprites.length === 0) {
                    revelaCarta();
                    return;
                }

                // Queries do mini player no momento do clique (e não no parse
                // do <script>): o script desta view é injetado dentro de
                // <main>, mas o <div id="bena-player"> vem depois de </main>
                // no layout. No parse time, querySelector retornaria null;
                // no clique humano, o DOM inteiro já está pronto.
                var playerCoverWrap = document.querySelector('#bena-player .bena-player__cover');
                var playerArtistEl  = document.querySelector('#bena-player .bena-player__artist');

                function atualizaPlayerCover(spriteUrl) {
                    if (!playerCoverWrap || !spriteUrl) return;
                    var img = playerCoverWrap.querySelector('img');
                    if (!img) return;
                    img.src = spriteUrl;
                    img.removeAttribute('hidden');
                    var ph = playerCoverWrap.querySelector('.bena-player__cover-placeholder');
                    if (ph) ph.style.display = 'none';
                }
                function atualizaPlayerArtista(nome) {
                    if (playerArtistEl && nome) playerArtistEl.textContent = nome;
                }

                // Pré-carrega slot e player no mesmo sprite aleatório ANTES
                // de ativar o estado rolling — garante que ambos comecem a
                // animação no mesmo frame, sem flash do sprite anterior.
                var spriteInicial = allSprites[Math.floor(Math.random() * allSprites.length)];
                slotAvatar.src = spriteInicial;
                atualizaPlayerCover(spriteInicial);

                reveal.setAttribute('data-buddy-reveal', 'rolling');

                var duration = 4000;
                var start = performance.now();
                // lastSwap começa no passado pra forçar swap imediato no
                // primeiro tick — slot e player não ficam parados nos
                // primeiros 60ms da animação.
                var lastSwap = start - 1000;

                function intervaloSwap(progresso) {
                    // Desacelera com o tempo: 60ms (rápido) → 400ms (suspense).
                    return 60 + 340 * Math.pow(progresso, 2.5);
                }

                function tick(now) {
                    var elapsed = now - start;
                    var progresso = Math.min(elapsed / duration, 1);

                    if (now - lastSwap >= intervaloSwap(progresso)) {
                        var sprite = allSprites[Math.floor(Math.random() * allSprites.length)];
                        slotAvatar.src = sprite;
                        atualizaPlayerCover(sprite);
                        lastSwap = now;
                    }

                    if (progresso >= 1) {
                        // Tudo trava no mesmo frame: slot, mini player e carta.
                        // Sem setTimeout — qualquer delay seria "antecipação"
                        // do player em relação à animação que ainda não acabou.
                        if (finalSprite) {
                            slotAvatar.src = finalSprite;
                            atualizaPlayerCover(finalSprite);
                        }
                        atualizaPlayerArtista(finalArtistName);
                        revelaCarta();
                        return;
                    }

                    requestAnimationFrame(tick);
                }

                requestAnimationFrame(tick);
            });
        })();
    </script>
@endsection
