@extends('layouts.app')

@section('title', 'Bem-vindo ao Bena')

@section('content')
    @php
        $passos = [
            [
                'icone' => 'fa-clock',
                'titulo' => 'Bater ponto pelo navegador',
                'texto' => 'Entrada e saída diretamente da tela inicial. Sem planilha, sem papel. O sistema já calcula as horas trabalhadas.',
            ],
            [
                'icone' => 'fa-calendar-alt',
                'titulo' => 'Folha mensal automática',
                'texto' => 'Veja o mês inteiro com horas calculadas, feriados destacados e fins de semana classificados. Adicione observações em dias específicos.',
            ],
            [
                'icone' => 'fa-pen-fancy',
                'titulo' => 'Assinatura digital ao final do mês',
                'texto' => 'Quando o mês fechar, você assina sua folha eletronicamente. O sistema gera um hash SHA-256 do conteúdo + carimbo de tempo, no modelo do SEI, sem necessidade de certificado físico.',
            ],
            [
                'icone' => 'fa-user-check',
                'titulo' => 'Supervisor e RH no mesmo fluxo',
                'texto' => 'Após você assinar, seu supervisor contra-assina pelo sistema. O RH baixa o PDF assinado e anexa direto no processo SEI.',
            ],
            [
                'icone' => 'fa-magic',
                'titulo' => 'Esqueceu de bater saída?',
                'texto' => 'O sistema fecha automaticamente após sua jornada (5h por padrão). Aparece marcado como "auto" para você saber que foi auto-fechamento.',
            ],
        ];
    @endphp

    <div style="max-width: 720px; margin: 1rem auto;">
        <header style="text-align: center; margin-bottom: 2rem;">
            <img src="{{ asset('img/bena.png') }}" alt="Bena" style="width: 96px; height: 96px; object-fit: contain; margin-bottom: 1rem;">
            <h1 style="color: #003366; font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem; letter-spacing: -0.02em;">
                Bem-vindo ao Bena
            </h1>
            <p style="color: #475569; font-size: 1rem; margin: 0;">
                Sistema de Controle de Frequência de Estagiários do TRE-AC.
                Veja em 30 segundos como vai ser o seu dia a dia.
            </p>
        </header>

        @isset($buddy)
            <section aria-labelledby="buddy-titulo" class="bena-buddy-card bena-buddy-card--apresentacao">
                <div class="bena-buddy-card__avatar bena-buddy-card__avatar--grande" aria-hidden="true">{{ $buddy->emoji }}</div>
                <div class="bena-buddy-card__content">
                    <span id="buddy-titulo" class="bena-buddy-card__name">Conheça seu mascote · {{ $buddy->nome }}</span>
                    <p class="bena-buddy-card__frase">{{ $buddy->frase }}</p>
                    <p class="bena-buddy-card__rodape">
                        Cada estagiário recebe um mascote sorteado no primeiro acesso.
                        Ele te recebe na dashboard com uma frase diferente a cada dia.
                    </p>
                </div>
            </section>
        @endisset

        <p style="text-align: center; margin: -1rem 0 2.5rem;">
            <a href="{{ route('mascotes.index') }}" class="br-button secondary">
                <i class="fas fa-paw" aria-hidden="true"></i>
                Conhecer todos os mascotes
            </a>
        </p>


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
@endsection
