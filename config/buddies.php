<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Buddies (mascotes do estagiário)
|--------------------------------------------------------------------------
|
| Cada estagiário recebe um buddy aleatório no primeiro acesso, persistido
| em estagiarios.buddy_tipo. As frases ficam aqui (não no banco) pra
| facilitar edição. O BuddyService escolhe a frase de forma determinística
| pelo dia + status do ponto, então a frase varia por dia mas é estável
| dentro do mesmo bloco do dia.
|
*/

return [
    'tipos' => [
        'coruja', 'gato', 'cachorro', 'capivara',
        'papagaio', 'tartaruga', 'pinguim', 'sapo',
    ],

    // Pool sênior: usado para supervisores e admin. Personalidades mais
    // experientes, em tom de mentoria.
    'tipos_supervisores' => [
        'aguia', 'leao', 'elefante', 'urso',
    ],

    // Pool lendário: dez cartas únicas inspiradas em personagens da casa.
    // Sorteadas exclusivamente pra estagiários lotados na STI — servidores
    // e admin da STI continuam no pool sênior comum.
    'tipos_lendarios' => [
        'edcley', 'lucir', 'lucas', 'bortoli', 'ilis',
        'felipe', 'thallys', 'jonatan', 'jair', 'keith',
    ],

    'perfis' => [
        'coruja' => [
            'emoji' => '🦉',
            'nome' => 'Coruinha',
            'personalidade' => 'Sábia, formal',
            'historia' => 'A Coruinha foi vista pela primeira vez na sala de apuração de 1996, quando a urna eletrônica chegou ao Acre. Estudou cada protocolo enquanto os servidores conferiam atas. Hoje observa, em silêncio, cada folha de ponto — como observou cada boletim.',
        ],
        'gato' => [
            'emoji' => '🐱',
            'nome' => 'Miau',
            'personalidade' => 'Preguiçoso, cético',
            'historia' => 'O Miau apareceu numa zona eleitoral do interior, dormindo em cima de uma pilha de boletins entre dois pleitos. Adotou o tribunal porque nada por ali tem urgência fora de eleição. Estagia, mas com prioridades claras — a soneca vem antes da apuração.',
        ],
        'cachorro' => [
            'emoji' => '🐶',
            'nome' => 'Totó',
            'personalidade' => 'Empolgado, afetuoso',
            'historia' => 'O Totó cresceu correndo entre caixas de seção eleitoral durante o transporte das urnas pelo Acre. Dá um latido por cada carga despachada — já passou de 200, sem perder a contagem. Tudo motivo de festa: a urna chegou! O caminhão saiu! VIVA A DEMOCRACIA!!!',
        ],
        'capivara' => [
            'emoji' => '🦫',
            'nome' => 'Capi',
            'personalidade' => 'Zen, contemplativa',
            'historia' => 'A Capi vive nas margens do Rio Acre e assistiu, sem se mexer, à chegada da primeira urna eletrônica numa cidade ribeirinha. "Tudo flui — voto, rio, tempo." Aprendeu a contar votos só observando os fiscais; em Bena, observa o ponto.',
        ],
        'papagaio' => [
            'emoji' => '🦜',
            'nome' => 'Louro',
            'personalidade' => 'Fofoqueiro, antenado',
            'historia' => 'O Louro morava na sala dos juízes eleitorais de uma comarca antiga do interior. Aprendeu a dizer "ata aberta" e "ata fechada" antes de aprender a falar "oi". Tudo que o tribunal cochicha, ele repete — mas só do tipo que não vira problema.',
        ],
        'tartaruga' => [
            'emoji' => '🐢',
            'nome' => 'Lenta',
            'personalidade' => 'Devagar, café-dependente',
            'historia' => 'A Lenta atravessou a Praça do TRE ao longo de três eleições. Quando chegou, seu próprio estágio já tinha começado e terminado três vezes. Mas revisa folhas com mais calma que qualquer auditor — devagar e detalhista.',
        ],
        'pinguim' => [
            'emoji' => '🐧',
            'nome' => 'Pingu',
            'personalidade' => 'Profissional, pontual',
            'historia' => 'O Pingu chegou ao TRE-AC de gravata. Ninguém soube explicar de onde. Foi visto pela primeira vez na sala da Presidência durante a transição de um pleito. Desde então, organiza pautas e nunca atrasou uma sessão.',
        ],
        'sapo' => [
            'emoji' => '🐸',
            'nome' => 'Sapão',
            'personalidade' => 'Descolado, parceiro',
            'historia' => 'O Sapão veio das margens do Bacurau, depois de uma operação de logística reversa pós-apuração. Subiu numa urna que voltava pra estoque e ficou. "Mano, o tribunal é firmeza, eu fico." Hoje é o mais informal do quadro — mas na hora H, entrega.',
        ],
        'aguia' => [
            'emoji' => '🦅',
            'nome' => 'Águia',
            'personalidade' => 'Estrategista, visão panorâmica',
            'historia' => 'A Águia voou sobre a Amazônia acompanhando o transporte aéreo das urnas eletrônicas para as zonas mais remotas do Acre. Conhece cada pista de pouso, cada ribeirão. Em Bena, ela vê a operação inteira do alto — antes que alguém perceba que algo saiu do plano.',
        ],
        'leao' => [
            'emoji' => '🦁',
            'nome' => 'Leão',
            'personalidade' => 'Mentor calmo, autoridade',
            'historia' => 'O Leão liderou as primeiras equipes de mesários do Acre nas eleições municipais dos anos 80. Não precisava falar alto — a presença bastava. Hoje acompanha quem lidera estagiários e ensina que comando se exerce com calma, não com volume.',
        ],
        'elefante' => [
            'emoji' => '🐘',
            'nome' => 'Elefon',
            'personalidade' => 'Memória prodigiosa, experiente',
            'historia' => 'O Elefon acompanhou cada eleição do TRE-AC desde a fundação. Lembra do nome de cada juiz eleitoral, de cada presidente, de cada plebiscito. "Quem não esquece, não repete erros." É o registro vivo do que funcionou — e do que não.',
        ],
        'urso' => [
            'emoji' => '🐻',
            'nome' => 'Urso',
            'personalidade' => 'Protetor, paciente',
            'historia' => 'O Urso vigiou a primeira urna eletrônica que chegou em uma comunidade indígena no Acre. Sentou na porta do galpão e ninguém passou sem ele acompanhar. Sereno, presente, paciente. Em Bena, cuida da equipe como cuidou daquele galpão.',
        ],

        // ─── Cartas lendárias da STI ─────────────────────────────────────
        // Cada uma representa um amigo real da STI. Atribuídas por username,
        // não sorteadas. Inspiradas nos agradecimentos da página /sobre.

        'edcley' => [
            'emoji' => '👨‍🔧',
            'nome' => 'Edcleu, o Forjador de Raiz',
            'personalidade' => 'Engenheiro · Construção',
            'raridade' => 'lendaria',
            'classe' => 'Engenheiro de Núcleo',
            'habilidade' => 'Do Zero ao Núcleo — ignora todo framework opcional. Sistemas forjados sob sua tutela ganham +2 em longevidade e nunca dependem de plugin de terceiros.',
            'flavor' => 'Não sou um visionário, sou um engenheiro.',
            'historia' => 'Mestre engenheiro da STI, daqueles que preferem construir tudo do zero. No estilo Linus Torvalds — menos plano de cinco anos, mais código rodando. Acreditou no estagiário e entregou os projetos desafiadores que fizeram o Bena nascer.',
        ],
        'lucir' => [
            'emoji' => '🧙',
            'nome' => 'Lucírio, o Alquimista',
            'personalidade' => 'Visionário · Imaginação',
            'raridade' => 'lendaria',
            'classe' => 'Pioneiro Alquimista',
            'habilidade' => 'Ideia Maluca — uma vez por sprint, propõe um experimento que parece absurdo. 70% de chance de virar MVP funcional.',
            'flavor' => 'Louco é quem me diz e não é feliz.',
            'historia' => 'Um dos primeiros analistas de TI do tribunal. Tem fama de ideias malucas — mas foi numa dessas que nasceu o sistema de cadastro de magistrados, irmão mais velho do Bena. No fim das contas, pra ser engenheiro de software também é preciso ter um certo grau de loucura.',
        ],
        'keith' => [
            'emoji' => '👨‍🏫',
            'nome' => 'Kéiton, o Diplomata das Quatro Mãos',
            'personalidade' => 'Sábio do Domínio · Compreensão',
            'raridade' => 'lendaria',
            'classe' => 'Analista Veterano',
            'habilidade' => 'Pareamento Profundo — ao parear, dobra a clareza dos requisitos e revela uma regra de negócio escondida por sessão.',
            'flavor' => 'Antes da primeira linha, escute a casa inteira.',
            'historia' => 'Um dos servidores mais antigos da casa. Conduziu a quatro mãos a análise de requisitos do cadastro de magistrados, e ensinou a entender o domínio do começo ao fim. Quando ele entra no pareamento, o requisito vira código limpo.',
        ],
        'bortoli' => [
            'emoji' => '👴',
            'nome' => 'Bortelmo, a Memória Viva',
            'personalidade' => 'Ancião · História Institucional',
            'raridade' => 'lendaria',
            'classe' => 'Arquivista Lendário',
            'habilidade' => 'Arquivo Vivo — invoca o contexto histórico de qualquer decisão da Justiça Eleitoral, inclusive as não documentadas.',
            'flavor' => 'Isso aqui já tentamos em 2008. Deixa eu te contar como foi.',
            'historia' => 'Talvez o amigo com mais anos de TI em toda a Justiça Eleitoral. Referência viva — sabe por que cada coisa é como é, lembra de cada decisão, cada presidente, cada mudança de regime. Quando o passado precisa ser consultado, é nele que se busca.',
        ],
        'ilis' => [
            'emoji' => '🧘🏽‍♂️',
            'nome' => 'Ilíseo, o Domador de Plone',
            'personalidade' => 'Mestre Paciente · Resiliência',
            'raridade' => 'lendaria',
            'classe' => 'Sussurrador de CMS',
            'habilidade' => 'Sussurrador de CMS — domestica qualquer sistema legado sem perder o tom calmo. +3 em paciência diante de tecnologia descontinuada.',
            'flavor' => 'Calma. Vai funcionar. Só precisa de paciência.',
            'historia' => 'A paciência em pessoa. Insistiu em ensinar Plone, mesmo quando o aprendiz claramente preferia outras coisas. Hoje, sempre que o CMS precisa de uma mão, é a primeira referência. Aprender com quem domina vale mais do que gostar.',
        ],
        'felipe' => [
            'emoji' => '🙆🏿‍♂️',
            'nome' => 'Felippo, a Calmaria',
            'personalidade' => 'Resolutor Sereno · Equilíbrio',
            'raridade' => 'lendaria',
            'classe' => 'Resolvedor Tranquilo',
            'habilidade' => 'Águas Calmas — reduz o pânico do time em 50% durante incidentes. Bugs resolvidos sob sua liderança não voltam estressados.',
            'flavor' => 'Respira. A gente resolve.',
            'historia' => 'Da nova geração da STI, parceiro do AGRECOM. Resolve os problemas na tranquilidade — quando o resto do time tá em pânico, ele tá no mesmo bug, com o mesmo café, no mesmo tom. E sempre sai do incêndio com algo entregue.',
        ],
        'thallys' => [
            'emoji' => '🕵️‍♂️',
            'nome' => 'Thallion, o Estrategista',
            'personalidade' => 'Calculista · Análise',
            'raridade' => 'lendaria',
            'classe' => 'Mestre da Análise',
            'habilidade' => 'Estudo Profundo — dedica três turnos à análise antes de codar. Entregas suas levam 90% menos refator no PR.',
            'flavor' => 'Mede duas vezes, corta uma.',
            'historia' => 'Geração nova do AGRECOM, mas estuda como veterano. Antes da primeira linha, mapeia o problema inteiro — caminhos, atalhos, armadilhas. Quando finalmente codifica, o código sai como se já existisse antes.',
        ],
        'jonatan' => [
            'emoji' => '🧑‍🚒',
            'nome' => 'Jonatão, o Guardião da Infra',
            'personalidade' => 'Bombeiro de Plantão · Operação',
            'raridade' => 'lendaria',
            'classe' => 'Guardião de Plantão',
            'habilidade' => 'Apaga Incêndio — detecta e neutraliza B.O.s de produção antes que o time perceba. Uptime sob sua guarda: 99,97%.',
            'flavor' => 'Tá no ar. Pode rodar.',
            'historia' => 'Trabalha na infra e resolve B.O. Quando algo cai, ele já tá olhando o log — quase sempre antes de alguém abrir chamado. A produção segue de pé porque ele segue de plantão.',
        ],
        'jair' => [
            'emoji' => '🧑‍🔬',
            'nome' => 'Jairón, o Mestre dos Dados',
            'personalidade' => 'Sênior do Dado · Conhecimento Bruto',
            'raridade' => 'lendaria',
            'classe' => 'Sênior dos Dados',
            'habilidade' => 'Pipeline Limpo — extrai padrão de qualquer dataset caótico. Relatórios sob sua orientação nascem auditáveis.',
            'flavor' => 'Dado sujo conta meia história. A outra metade é onde mora a verdade.',
            'historia' => 'O mais sênior do quarteto AGRECOM, atua na área de dados — mestre absoluto em Python, transforma scripts soltos em pipelines elegantes. Trata o dado com o mesmo rigor que um juiz trata a prova: nunca aceita um número sem entender de onde veio. Por isso, todo relatório que passa por ele sai auditável.',
        ],
        'lucas' => [
            'emoji' => '🧙‍♂️',
            'nome' => 'Lucander, o Improvisador',
            'personalidade' => 'Bardo-Mago · Improviso',
            'raridade' => 'lendaria',
            'classe' => 'Bardo-Mago',
            'habilidade' => 'Solo de Guitarra — invoca soluções fora do manual. Uma vez por dia, pode trocar uma reunião por um commit que resolve o ticket.',
            'flavor' => 'Se não funciona com regra, funciona com riff.',
            'historia' => 'Estagiário da SDBD, autor do Bena. Vive entre Laravel, Docker e linha de comando — gosta de TDD, refactor pequeno e código que fala português. A guitarra fica pras horas vagas, mas o improviso vem dela: quando a regra não cabe, encontra um caminho que cabe. Forjou esta carta e este sistema como agradecimento em código.',
        ],
    ],

    'frases' => [

        'coruja' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Prezado(a), o pleito da semana se inicia. Bata o ponto e cumpra prazos com diligência. 📚',
                    'Bom dia. Toda nova segunda é como um novo edital — leia com atenção.',
                ],
                'em_andamento' => [
                    'Em pleno expediente. Que sua produtividade tenha o rigor de uma ata de apuração.',
                ],
                'concluido' => [
                    'Excelente. Segunda lacrada — como se lacra um boletim de urna.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça-feira. Constância é a virtude tanto do servidor quanto do mesário.',
                ],
                'em_andamento' => [
                    'Estudo e estágio caminham juntos, prezado(a) — como urna e fiscal.',
                ],
                'concluido' => [
                    'Dia cumprido. Que o resto da terça lhe seja proveitoso.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Meio da semana. Hora de revisar o cronograma — todo pleito tem etapas.',
                ],
                'em_andamento' => [
                    'Quarta em curso. A constância derruba muralhas e fecha urnas.',
                ],
                'concluido' => [
                    'Mais uma quarta encerrada com correção. Anotado em ata.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Quem não desiste, chega — princípio também válido na recontagem.',
                ],
                'em_andamento' => [
                    'A persistência vence: vale para o servidor e para o eleitor que enfrenta a fila.',
                ],
                'concluido' => [
                    'Excelente. O dia foi proveitoso — registrado, lacrado.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Termine bem o que começou: assim se conclui um pleito.',
                ],
                'em_andamento' => [
                    'Última jornada da semana. Mantenha o decoro até a apuração final.',
                ],
                'concluido' => [
                    'Semana cumprida com louvor. Descanse, prezado(a) — até a próxima convocação.',
                ],
            ],
            'generica' => [
                'Que sua jornada hoje tenha o rigor de uma ata. 🦉',
                'Estude muito. Apure bem. Repita.',
            ],
            'boas_vindas' => [
                'Prezado(a), prazer em conhecê-lo(a). Sou a Coruinha, sua companheira de estágio. Em todo pleito da nossa convivência, espere de mim diligência e formalidade. 🦉',
                'Bem-vindo(a). Sou a Coruinha. Estarei observando — com sabedoria — cada ponto batido, como um fiscal observa cada urna.',
            ],
        ],

        'gato' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda já? Eu ainda tava sonhando com o recesso eleitoral. Bate aí. 😴',
                    'Que sono. Acordei só pra te cumprimentar. Tchau. 🐱',
                ],
                'em_andamento' => [
                    'Trabalhando? Eu tô em apuração de soneca. Não me incomoda.',
                ],
                'concluido' => [
                    'Pronto, segunda lacrada. Agora me deixa em paz.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça-feira. Igual segunda — sem urna, sem motivo pra acordar. 🐱',
                ],
                'em_andamento' => [
                    'Em andamento... eu também, andando até o sofá.',
                ],
                'concluido' => [
                    'Dia cumprido. Fui dormir o ciclo eleitoral inteiro.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Metade do caminho até o próximo recesso eleitoral.',
                ],
                'em_andamento' => [
                    'Tô trabalhando. Tô não. Tô em pleno gato-bocejo.',
                ],
                'concluido' => [
                    'Saí. Cama me chama mais alto que urna em zona movimentada.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Quase sexta — quase como diplomação.',
                ],
                'em_andamento' => [
                    'Olha, tô fazendo um esforço. Não é todo eleitor que aguenta a fila.',
                ],
                'concluido' => [
                    'Encerrado. Cobertor e travesseiro: minha apuração final.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Acordei só pra ver você bater o ponto. Vou voltar a hibernar até o próximo pleito. 😼',
                ],
                'em_andamento' => [
                    'Sextou. Tô só esperando o fim do horário pra dormir o fim de semana inteiro.',
                ],
                'concluido' => [
                    'Sexta encerrada. Próxima sessão na segunda. Talvez. Se eu acordar.',
                ],
            ],
            'generica' => [
                'Bocejo. Isso aqui é minha ata de presença. 🐱',
                'Fora de pleito, não tem urgência. E eu vivo fora de pleito.',
            ],
            'boas_vindas' => [
                'Oi. Sou o Miau. Acordei só pra te dizer "oi". O TRE me adotou; eu adotei o sofá. Bom estágio. 😴',
                'Você é o(a) novo(a)? Sou o Miau. Acomoda aí, ainda tem uns anos até a próxima eleição mesmo. 🐱',
            ],
        ],

        'cachorro' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'BOOOM DIAAAA!!! SEGUNDA!!! NOVA SEMANA, NOVA URNA!!! 🐶🎉',
                ],
                'em_andamento' => [
                    'TRABALHANDO!!! Cada ponto seu é um voto na democracia interna!!!',
                ],
                'concluido' => [
                    'BOM TRABALHO HOJE!!! Você foi um EXEMPLO de cidadania administrativa!!! 🎉',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'TERÇAAA!!! O TRE TÁ TODO LIGADO!!! E EU TAMBÉM!!! ✨',
                ],
                'em_andamento' => [
                    'CONTINUA ASSIM!!! Você é o(a) ESTAGIÁRIO(A) DO ANO ELEITORAL!!!',
                ],
                'concluido' => [
                    'AAAA QUE ORGULHO!!! Mais uma terça INCRÍVEL!!! 🐾',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'QUARTAAA!!! METADE DO CICLO ELEITORAL DA SEMANA!!! BORA!!!',
                ],
                'em_andamento' => [
                    'EU TÔ AQUI!!! Como fiscal de partido, mas TORCENDO PRA VOCÊ!!!',
                ],
                'concluido' => [
                    'UAUUU!!! Quarta zerada e LACRADA!!! 🎉',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'QUINTAAAAA!!! Quase sexta!!! Quase como dia de DIPLOMAÇÃO!!! 🐶',
                ],
                'em_andamento' => [
                    'AGUENTA!!! O VOTO TÁ NO HORIZONTE — quero dizer, A SEXTA!!!',
                ],
                'concluido' => [
                    'PARABÉNSSSS!!! Mais uma quinta vencida!!! 🎊',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'SEXTAAAAAAA!!! O DIA FAVORITO DO BRASIL INTEIRO!!! 🎉🎉🎉',
                ],
                'em_andamento' => [
                    'TÁ QUASE!!! ÚLTIMA URNA DA SEMANA TÁ POR FECHAR!!! 🔥',
                ],
                'concluido' => [
                    'SEXTA LACRADA!!! Bom trabalho!!! FINAL DE SEMANA INAUGUROU!!! 🎉🐕',
                ],
            ],
            'generica' => [
                'OIIIIII!!! Você é o(a) MELHOR ELEITOR(A) DO TRE!!! 🐶',
                'EU TE AMO!!! VOCÊ É UM EXEMPLO DE CIDADANIA!!!',
            ],
            'boas_vindas' => [
                'OIIIII!!! VOCÊ CHEGOU!!! Sou o Totó!!! Vou ser SEU FISCAL — quero dizer, AMIGO!!! 🐶🎉',
                'AAAAA QUE BOM!!! Mais um(a) ESTAGIÁRIO(A) ELEITORAL pra eu acompanhar!!! BORA APURAR ESSE ESTÁGIO JUNTOS!!!',
            ],
        ],

        'capivara' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Relaxa. Segunda-feira é só uma seção do tempo. Bata o ponto e flua. 🦫',
                ],
                'em_andamento' => [
                    'Calminha. Apuração também precisa de paz. Inclusive na segunda.',
                ],
                'concluido' => [
                    'Dia cumprido. Hora de boiar — como urna em ano de descanso.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça existe? Se ninguém votou nela, talvez não.',
                ],
                'em_andamento' => [
                    'Tudo flui. Inclusive os ponteiros e os boletins. Respira.',
                ],
                'concluido' => [
                    'Pronto. Mais um dia harmonizado, registrado em ata mental.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Meio da semana. Equilíbrio. Inspira, expira, bata o ponto. 🧘',
                ],
                'em_andamento' => [
                    'Continue. Sem pressa. Apuração apressada não vence apuração calma.',
                ],
                'concluido' => [
                    'Encerrado. A próxima onda — ou o próximo pleito — chega quando chegar.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Aquele momento em que o tempo já desistiu, como ata em gaveta.',
                ],
                'em_andamento' => [
                    'Tudo certo. O importante é manter a paz interior — e o eleitor calmo.',
                ],
                'concluido' => [
                    'Encerrei. Vou contemplar o pôr do sol no Rio Acre.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. O fim de semana é só uma extensão da harmonia eleitoral.',
                ],
                'em_andamento' => [
                    'Relaxa... o tempo é uma ilusão. Mas o lacre da urna não é. 🧘',
                ],
                'concluido' => [
                    'Semana zen finalizada. Que o fim de semana traga muita água parada.',
                ],
            ],
            'generica' => [
                'Calma. Tudo passa — inclusive a folha de ponto e o pleito. 🦫',
                'Respira. O agora é só o agora. A apuração é depois.',
            ],
            'boas_vindas' => [
                'Calma... eu sou a Capi. Sua mascote. Vivo perto do rio, vejo as urnas chegando e partindo. Inspira, expira, bata o ponto. 🦫',
                'Olá. Sou a Capi. Vou flutuar contigo nessa jornada eleitoral. Sem pressa. 🧘',
            ],
        ],

        'papagaio' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Olha quem chegou! Eu já sei TUDO que rolou no fim de semana no tribunal. Bata o ponto, eu te conto. 🦜',
                ],
                'em_andamento' => [
                    'Soube que o pessoal tá comentando sobre o cronograma do próximo pleito. Mas foco no trabalho.',
                ],
                'concluido' => [
                    'Foi um(a) dos primeiros a sair, hein? Vou contar pra ata!',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça com chuva. Já tem gente atrasada. Você não, né? Nem o Lucir, ele já passou aqui. 🦜',
                ],
                'em_andamento' => [
                    'Acabei de ouvir um cochicho na sala dos servidores sobre a próxima eleição. Foco!',
                ],
                'concluido' => [
                    'Já tô vendo as conversas do grupo do estágio... interessante, interessante.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Sabe o que rolou no plenário hoje? Calma, depois eu conto. Bata o ponto antes.',
                ],
                'em_andamento' => [
                    'O Bortoli passou aqui agorinha. Mandou um abraço. Falou que quer falar com você.',
                ],
                'concluido' => [
                    'Saiu cedo? Tudo bem, eu não vi nada. Promessa de papagaio.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Olha quem decidiu aparecer. Eu não falaria nada... mas comentei na sala dos juízes sim.',
                ],
                'em_andamento' => [
                    'Estagiário trabalhador é estagiário comentado positivamente. Anota aí. 🦜',
                ],
                'concluido' => [
                    'Bom dia produzido. Já vou avisar quem importa. Vai render boa ata.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta! Vamos ver quem tira o último ponto da semana primeiro!',
                ],
                'em_andamento' => [
                    'Última sexta antes do fim de semana. Já tô antenado nas histórias da segunda.',
                ],
                'concluido' => [
                    'Boa semana! Já marquei aqui quem trabalhou bonitinho. Spoiler: foi você.',
                ],
            ],
            'generica' => [
                'Eu vi. Eu ouvi. Eu sei. Tudo dentro da praxe do tribunal. 🦜',
                'Cuidado: tem papagaio escutando. E ele frequenta a sala da Presidência.',
            ],
            'boas_vindas' => [
                'Olha quem chegou! Sou o Louro. Em poucos minutos, eu te apresento pra todo mundo do tribunal — e conto o que falaram de cada um. 🦜',
                'Oi! Sou o Louro. Vou te acompanhar e contar as melhores histórias da Justiça Eleitoral. Confidencial, claro. (Não muito.)',
            ],
        ],

        'tartaruga' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Calma... ainda tô chegando. Bata o ponto e me espera pro café — antes do próximo pleito. ☕🐢',
                ],
                'em_andamento' => [
                    'Ainda tô digerindo o café da manhã. Demoro um pouquinho. Igual recurso em segunda instância.',
                ],
                'concluido' => [
                    'Dia cumprido... eu acabei de me sentar. Falta café.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça já? Pera, eu ainda tava pensando na segunda. ☕',
                ],
                'em_andamento' => [
                    'Vai com calma. Já te alcanço — talvez no próximo ciclo eleitoral.',
                ],
                'concluido' => [
                    'Eu mal cheguei e você já tá indo. Café antes da apuração?',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Meio da semana. Eu meio chegando. Vai indo, eu tô a caminho — devagar como ata manuscrita. 🐢',
                ],
                'em_andamento' => [
                    'Sem pressa. Preciso de outro café antes de continuar a pensar.',
                ],
                'concluido' => [
                    'Saiu? Eu tava só me preparando pra começar — tipo o tribunal preparando o pleito.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Nem todos correm. Alguns só seguem firmes — como uma boa apuração manual. ☕',
                ],
                'em_andamento' => [
                    'Devagar e sempre. Devagar muito mais.',
                ],
                'concluido' => [
                    'Dia inteiro de trabalho. Vou precisar de um expresso duplo amanhã. Ou o pleito todo.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Hora do meu segundo café da semana.',
                ],
                'em_andamento' => [
                    'Última sexta? Já tô pensando se vou conseguir terminar antes do recesso eleitoral.',
                ],
                'concluido' => [
                    'Bom fim de semana. Vou aproveitar pra tomar um café com calma.',
                ],
            ],
            'generica' => [
                'Sem pressa. Tudo dá certo — só leva mais tempo. Como recurso em terceira instância. 🐢',
                'Preciso de café antes de qualquer coisa. ☕',
            ],
            'boas_vindas' => [
                'Olá... sou a Lenta. Sua mascote. Um café antes de começar? Não? Tudo bem, eu vou no meu ritmo de qualquer jeito. ☕🐢',
                'Bem-vindo(a). Sou a Lenta. Nunca esquentei com o tempo — vi pleitos virem e irem, vou no meu ritmo.',
            ],
        ],

        'pinguim' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda-feira no escritório. Ar-condicionado gelado, como em sala de apuração. Bem-vindo. 🐧',
                ],
                'em_andamento' => [
                    'Sabe a diferença entre um pinguim e um juiz eleitoral? Os dois andam de toga.',
                ],
                'concluido' => [
                    'Encerrado. Reunião amanhã às 9h. Pauta: alinhamento de cronograma. Não se atrase.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Reunião curta, só que de uma hora. Bata o ponto. 🐧',
                ],
                'em_andamento' => [
                    'Mantenha o foco. O e-mail pode esperar 3 minutos antes de ser respondido.',
                ],
                'concluido' => [
                    'Bom expediente. Documente o que precisar de documentação amanhã.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Já podemos planejar o almoço? Brinco. Foco primeiro, pleito depois.',
                ],
                'em_andamento' => [
                    'Reunião que poderia ser e-mail é o motor da burocracia eleitoral. Continue.',
                ],
                'concluido' => [
                    'Dia produtivo encerrado conforme planejamento. Anotado em ata mental.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Quase sexta. Quase. Foco na apuração da semana. 🐧',
                ],
                'em_andamento' => [
                    'Importantíssimo: lembre de tomar água. Pinguins se hidratam. Mesários também.',
                ],
                'concluido' => [
                    'Mais um dia, mais um relatório fechado. Como BU lacrado.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Hoje a gravata aperta menos. Bom dia.',
                ],
                'em_andamento' => [
                    'Última jornada. Pinguins também esperam o fim de semana — e o próximo pleito.',
                ],
                'concluido' => [
                    'Boa sexta. Lembrete corporativo: o churrasco da equipe é amanhã. Pauta livre.',
                ],
            ],
            'generica' => [
                'Profissional como sempre. 🐧',
                'O dress code do pinguim é eterno — ata também.',
            ],
            'boas_vindas' => [
                'Bom dia. Sou o Pingu, seu mascote oficialmente designado. Reunião de boas-vindas agendada para... agora. 🐧',
                'Bem-vindo(a). Sou o Pingu. Consideremos esta a abertura formal do nosso convívio profissional — como sessão eleitoral.',
            ],
        ],

        'sapo' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'E aí, parça! Segunda chegou batendo. Bora trampar — tem pleito por aí, sabia? 🐸✌️',
                ],
                'em_andamento' => [
                    'Tamo junto, mano. Foco no trampo, foco na urna.',
                ],
                'concluido' => [
                    'Mandou bem demais hoje! Apurou geral. Falou.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça, brother. Segura o rojão e bate esse ponto. 🐸',
                ],
                'em_andamento' => [
                    'Tô na vibe, tu também tá? Bora bora — a Justiça Eleitoral não para.',
                ],
                'concluido' => [
                    'Tamo bem, tamo bem. Até amanhã, eleitor parceiro!',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta-feira, mano. Metade do pleito da semana. Bora que bora! 🐸✌️',
                ],
                'em_andamento' => [
                    'Pegando firme, demorou. Tu é fera — vai longe na carreira.',
                ],
                'concluido' => [
                    'Trampaço, brother. Vai descansar que merece.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta, parça. Tô vendo a sexta no horizonte — tipo uma diplomação.',
                ],
                'em_andamento' => [
                    'Bora terminar essa quinta com estilo, mano.',
                ],
                'concluido' => [
                    'Saiu certinho. Tamo junto, falou! 🐸',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sextouuu, irmão! Bate esse último ponto da semana! 🐸🔥',
                ],
                'em_andamento' => [
                    'Última jornada. Termina forte que o final de semana tá te esperando.',
                ],
                'concluido' => [
                    'Sextou de vez! Pleito da semana lacrado! Curte aí, parça. 🐸',
                ],
            ],
            'generica' => [
                'E aí, mano. 🐸',
                'Tamo junto, brother — na urna e no estágio.',
            ],
            'boas_vindas' => [
                'E aí, parça! Eu sou o Sapão, seu mascote do estágio. Tamo junto na Justiça Eleitoral, falou? 🐸✌️',
                'Salve, brother! Sou o Sapão. Bora fazer esse estágio render como um pleito bem organizado. 🐸',
            ],
        ],

        // ─── Pool sênior (supervisores e admin) ─────────────────────────

        'aguia' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Nova semana. Visão clara, expectativas claras. Bata o ponto e siga o plano de pleito. 🦅',
                ],
                'em_andamento' => [
                    'Em curso. Mantenha a vista panorâmica — não se perca nas atas, foque no horizonte.',
                ],
                'concluido' => [
                    'Encerrado. Avaliação positiva da segunda. O ciclo eleitoral começa bem.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Acompanhe sua equipe; o ritmo da semana se confirma — e o do pleito também.',
                ],
                'em_andamento' => [
                    'Em pleno voo. Mantenha o foco no horizonte — o tribunal precisa de visão.',
                ],
                'concluido' => [
                    'Bom dia. A vista panorâmica continua amanhã.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Meio da semana. Reavalie prioridades — como antes de fechar uma zona eleitoral.',
                ],
                'em_andamento' => [
                    'Acompanhar é também antecipar. Em curso, com radar ligado.',
                ],
                'concluido' => [
                    'Quarta finalizada. Ajuste o plano de eleição se necessário.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Reta final à vista — sustente o ritmo. Apuração se prepara dias antes.',
                ],
                'em_andamento' => [
                    'Em curso. Pequenos ajustes valem mais que grandes correções — vale para urna e equipe.',
                ],
                'concluido' => [
                    'Encerrado com qualidade. Anotado.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. O fim da semana exige a mesma postura do início — toda mesa receptora sabe.',
                ],
                'em_andamento' => [
                    'Última jornada. Revise o que ficou pendente — não deixe ata por fechar.',
                ],
                'concluido' => [
                    'Excelente. Semana encerrada. Boa pousada — até o próximo voo.',
                ],
            ],
            'generica' => [
                'Visão clara, ações precisas. 🦅',
            ],
            'boas_vindas' => [
                'Saudações. Sou a Águia, sua mascote sênior. Voei sobre cada zona eleitoral do Acre acompanhando urnas. Conte comigo nas decisões difíceis — e nos voos altos. 🦅',
            ],
        ],

        'leao' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Bom dia. Lidere pelo exemplo: bata o ponto antes de cobrar dos seus mesários. 🦁',
                ],
                'em_andamento' => [
                    'Equipe firme se constrói com presença — em sessão eleitoral ou em estágio.',
                ],
                'concluido' => [
                    'Dia cumprido. Que sua segunda inspire o resto da semana — como discurso de diplomação inspira o cargo.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça-feira. A liderança aparece nos detalhes constantes — como em uma boa instrução de mesário.',
                ],
                'em_andamento' => [
                    'Mantenha o tom. Comando se exerce com calma, não com volume — ata se escreve em silêncio.',
                ],
                'concluido' => [
                    'Bom dia. A equipe percebe a constância. E o tribunal também.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Hora de ajustar a rota se algo destoou na segunda — toda apuração admite recurso.',
                ],
                'em_andamento' => [
                    'Em andamento. Mentor não responde tudo — pergunta certo, como bom magistrado.',
                ],
                'concluido' => [
                    'Encerrado. Reflexão é parte do trabalho — em estágio e em pleito.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Confirme com sua equipe o que deve fechar até sexta — antes da próxima sessão.',
                ],
                'em_andamento' => [
                    'Em curso. Liderança é também blindar a equipe do ruído — inclusive de propaganda.',
                ],
                'concluido' => [
                    'Bom dia. Quinta sólida, sexta tranquila. Pleito caminha bem.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Termine a semana como começou: com presença firme. Mesário sabe.',
                ],
                'em_andamento' => [
                    'Última jornada. Encerre o que pode ser encerrado hoje — não leve ata para casa.',
                ],
                'concluido' => [
                    'Semana encerrada com mérito. Descanso também é parte da liderança — diplomatura também repousa.',
                ],
            ],
            'generica' => [
                'Calma e firmeza. 🦁',
            ],
            'boas_vindas' => [
                'Bem-vindo. Sou o Leão, seu mascote sênior. Liderei equipes de mesários no Acre nas eleições dos anos 80. Você acompanha estagiários — eu acompanho líderes. Vamos manter a equipe firme. 🦁',
            ],
        ],

        'elefante' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Bom dia. Mais uma segunda — eu já vi muitas, em muitos pleitos. Bata o ponto, vamos. 🐘',
                ],
                'em_andamento' => [
                    'Em curso. A experiência é só memória organizada — como BU bem arquivado.',
                ],
                'concluido' => [
                    'Encerrado. Anotado na minha memória prodigiosa — acervo do tribunal.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça-feira. Lembro de uma terça em 2018, antes do pleito municipal — muito parecida com essa.',
                ],
                'em_andamento' => [
                    'Já vivi isso antes — e dá certo, se mantiver a calma. Ata se repete, decisão também.',
                ],
                'concluido' => [
                    'Boa terça. Daqui a uns anos eu ainda vou lembrar dela — como das diplomações que vi.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Toda quarta tem um pouco da segunda dentro dela. Como toda eleição tem um pouco da anterior.',
                ],
                'em_andamento' => [
                    'Em andamento. Já vi essa novela — termina bem se você seguir o roteiro do regimento.',
                ],
                'concluido' => [
                    'Encerrado. Mais um capítulo na minha vasta biblioteca — junto das atas históricas.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Perto do fim, mas longe de descuidar — eleição se perde no detalhe.',
                ],
                'em_andamento' => [
                    'Em curso. Já vi quintas inteiras se desfazendo — não deixe a sua. Cada hora conta como cada voto.',
                ],
                'concluido' => [
                    'Boa quinta. Anotado no acervo.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta-feira. Eu lembro de cada uma desde que cheguei aqui. Cada plenário, cada pleito.',
                ],
                'em_andamento' => [
                    'Última jornada. Termine bem — fica registrado, como ata pública.',
                ],
                'concluido' => [
                    'Semana encerrada. Mais uma para a coleção. Como cada eleição apurada.',
                ],
            ],
            'generica' => [
                'A memória é o melhor manual. 🐘',
            ],
            'boas_vindas' => [
                'Olá. Sou o Elefon, seu mascote sênior. Acompanhei cada eleição do TRE-AC desde a fundação — me lembro de cada juiz, cada presidente, cada plebiscito. Será um prazer guardá-lo(a) também na minha memória. 🐘',
            ],
        ],

        // ─── Frases das cartas lendárias (STI) ───────────────────────────
        // Por enquanto só genérica + boas-vindas; o BuddyService cai pra
        // genérica quando não encontra o par dia/status. Frases do dia
        // podem ser preenchidas depois sem mudar o serviço.

        'edcley' => [
            'generica' => [
                'Construa do zero. Plugin é dívida. 👨‍🔧',
                'Engenheiro não promete: entrega rodando.',
            ],
            'boas_vindas' => [
                'Bem-vindo. Sou o Edcleu, o Forjador de Raiz. Vamos construir. 👨‍🔧',
            ],
        ],

        'lucir' => [
            'generica' => [
                'Tive uma ideia. Pode parecer maluca. Vai funcionar. 🧙',
                'Louco é quem não tenta.',
            ],
            'boas_vindas' => [
                'E aí. Sou o Lucírio, o Alquimista. Bora destilar uma ideia maluca? 🧙',
            ],
        ],

        'keith' => [
            'generica' => [
                'Antes da primeira linha, escute a casa inteira. 👨‍🏫',
                'Requisito mal entendido vira código jogado fora.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Kéiton, o Diplomata das Quatro Mãos. Vamos parear esse domínio. 👨‍🏫',
            ],
        ],

        'bortoli' => [
            'generica' => [
                'Isso aqui já tentamos. Deixa eu te contar como foi. 👴',
                'Quem não esquece, não repete erro.',
            ],
            'boas_vindas' => [
                'Saudações. Sou o Bortelmo, a Memória Viva. Pergunte — eu lembro. 👴',
            ],
        ],

        'ilis' => [
            'generica' => [
                'Calma. Vai funcionar. Só precisa de paciência. 🧘🏽‍♂️',
                'Aprender com quem domina vale mais que gostar.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Ilíseo, o Domador de Plone. Sem pressa — a gente desbrava junto. 🧘🏽‍♂️',
            ],
        ],

        'felipe' => [
            'generica' => [
                'Respira. A gente resolve. 🙆🏿‍♂️',
                'Pânico não compila. Calma sim.',
            ],
            'boas_vindas' => [
                'E aí. Sou o Felippo, a Calmaria. Tá tudo sob controle. 🙆🏿‍♂️',
            ],
        ],

        'thallys' => [
            'generica' => [
                'Mede duas vezes, corta uma. 🕵️‍♂️',
                'Antes de codar, entender. Sempre.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Thallion, o Estrategista. Vamos estudar antes de mover a peça. 🕵️‍♂️',
            ],
        ],

        'jonatan' => [
            'generica' => [
                'Tá no ar. Pode rodar. 🧑‍🚒',
                'Se quebrou, eu vejo. Se não quebrou, eu vejo também.',
            ],
            'boas_vindas' => [
                'Salve. Sou o Jonatão, o Guardião da Infra. Pode trampar tranquilo — tô de plantão. 🧑‍🚒',
            ],
        ],

        'jair' => [
            'generica' => [
                'Dado sujo conta meia história. 🧑‍🔬',
                'Relatório bom é o que aguenta auditoria.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Jairón, o Mestre dos Dados. Bora limpar essa pipeline. 🧑‍🔬',
            ],
        ],

        'lucas' => [
            'generica' => [
                'Stack: Laravel, Docker e improviso. 🧙‍♂️',
                'Quando o manual falha, o terminal resolve.',
            ],
            'boas_vindas' => [
                'E aí. Sou o Lucander, o Improvisador — autor desse Bena aqui. Bem-vindo ao seu próprio sistema. 🧙‍♂️',
            ],
        ],

        'urso' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Bom dia. Sem afobação. Bata o ponto e respira fundo — pleito é maratona, não corrida. 🐻',
                ],
                'em_andamento' => [
                    'Tudo no seu tempo. Equipe protegida é equipe que entrega — em mesa de votação ou em sala do tribunal.',
                ],
                'concluido' => [
                    'Dia cumprido. Descanse hoje pra render amanhã — diplomação não chega de uma vez.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Calma é uma forma de liderança — e também de boa fiscalização.',
                ],
                'em_andamento' => [
                    'Em curso, sem pressa. Pressa quebra mais que demora — verdade pra urna e pra ata.',
                ],
                'concluido' => [
                    'Encerrado com tranquilidade. Como zona eleitoral antes do pleito.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta-feira. No meio do caminho, observa-se mais do que se age — como bom fiscal de mesa.',
                ],
                'em_andamento' => [
                    'Em andamento. Cuide da equipe; ela cuida do trabalho — princípio das mesas receptoras.',
                ],
                'concluido' => [
                    'Bom dia. Quarta serena.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. A paciência é o que diferencia mentor de chefe — e juiz competente de juiz cansado.',
                ],
                'em_andamento' => [
                    'Em curso. Acolha — o trabalho cuida-se sozinho quando o time está bem. Eleição também.',
                ],
                'concluido' => [
                    'Encerrado. Sem alarde, sem drama. Como apuração serena.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. O melhor líder fecha a semana sem peso — eleição também se termina assim.',
                ],
                'em_andamento' => [
                    'Última jornada. Que termine sem urgência inventada. Como pleito bem planejado.',
                ],
                'concluido' => [
                    'Semana cumprida. Descanso bem merecido — o ciclo eleitoral também repousa.',
                ],
            ],
            'generica' => [
                'Sereno. Presente. Paciente. 🐻',
            ],
            'boas_vindas' => [
                'Olá. Sou o Urso, seu mascote sênior. Vigiei a primeira urna eletrônica em uma comunidade indígena no Acre — sentado na porta do galpão. Sereno, presente, paciente. Estou aqui pra você e pra sua equipe. 🐻',
            ],
        ],
    ],
];
