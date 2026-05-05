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
    // Sorteadas exclusivamente pra estagiários lotados nas seções listadas
    // em `lotacoes_lendarias` — servidores e admin dessas seções continuam
    // no pool sênior comum.
    'tipos_lendarios' => [
        'edcley', 'lucir', 'lucas', 'bortoli', 'ilis',
        'felipe', 'thallys', 'jonatan', 'jair',
    ],

    // Lotações que recebem o pool lendário. SSEC é parte do grupo
    // institucional da STI, então entra junto.
    'lotacoes_lendarias' => ['STI', 'SSEC'],

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
            'historia' => 'Reza a lenda que o Edcleu forjou os primeiros lacres das urnas eletrônicas que chegaram ao Acre. Não confiava em peça pronta — cada selo saía da bancada dele, do zero, e ninguém jamais conseguiu violar um pleito sob sua guarda. Quando perguntam o segredo, responde curto: "se não posso forjar, não posso garantir".',
        ],
        'lucir' => [
            'emoji' => '🧙',
            'nome' => 'Lucírio, o Alquimista',
            'personalidade' => 'Visionário · Imaginação',
            'raridade' => 'lendaria',
            'classe' => 'Pioneiro Alquimista',
            'habilidade' => 'Ideia Maluca — uma vez por sprint, propõe um experimento que parece absurdo. 70% de chance de virar MVP funcional.',
            'flavor' => 'Louco é quem me diz e não é feliz.',
            'historia' => 'O Lucírio aparece quando uma eleição precisa de algo que ainda não existe. Foi visto pela primeira vez numa apuração noturna de 1992, transformando uma planilha manuscrita num mapa que indicava com precisão onde cada urna estaria no dia seguinte — ninguém soube explicar como. Tem fama de propor soluções absurdas que insistem em funcionar.',
        ],
        'bortoli' => [
            'emoji' => '👴',
            'nome' => 'Bortelmo, Mestre do Legado',
            'personalidade' => 'Veterano do Código Antigo · Pragmatismo Cru',
            'raridade' => 'lendaria',
            'classe' => 'Mestre do COBOL e do Clipper',
            'habilidade' => 'Gambiarra Funcional — sustenta sistema legado com solução improvável que dura mais que reescrita planejada. +∞ em código que ninguém mais ousa tocar.',
            'flavor' => 'Tá feio. Mas funciona desde 1992 — e vai funcionar amanhã também.',
            'historia' => 'O Bortelmo escreveu rotina em COBOL antes do tribunal ter monitor colorido — e várias delas ainda rodam no canto, gerando relatório que ninguém mais sabe regenerar do zero. Domina Clipper como dialeto materno e faz gambiarras funcionais desde sempre: aquele tipo de solução que devia ser provisória e atravessa três gestões intacta. Quando algo precisa funcionar HOJE e não há tempo de refazer direito, é nele que se busca o jeitinho que segura o pleito — e que, no fim, dura uma década.',
        ],
        'ilis' => [
            'emoji' => '🧘🏽‍♂️',
            'nome' => 'Ilíseo, o Domador de Plone',
            'personalidade' => 'Mestre Paciente · Resiliência',
            'raridade' => 'lendaria',
            'classe' => 'Sussurrador de CMS',
            'habilidade' => 'Sussurrador de CMS — domestica qualquer sistema legado sem perder o tom calmo. +3 em paciência diante de tecnologia descontinuada.',
            'flavor' => 'Calma. Vai funcionar. Só precisa de paciência.',
            'historia' => 'O Ilíseo catalogou, durante três pleitos seguidos, todos os boletins de urna de uma comarca inteira do interior — sem perder uma classificação sequer. Calmo, paciente, jamais se exaltou nem com prazo curto nem com sistema travado. Hoje, quando alguma ferramenta antiga reclama, basta ele chegar perto que volta a funcionar.',
        ],
        'felipe' => [
            'emoji' => '🙆🏿‍♂️',
            'nome' => 'Felippo, a Calmaria',
            'personalidade' => 'Resolutor Sereno · Equilíbrio',
            'raridade' => 'lendaria',
            'classe' => 'Resolvedor Tranquilo',
            'habilidade' => 'Águas Calmas — reduz o pânico do time em 50% durante incidentes. Bugs resolvidos sob sua liderança não voltam estressados.',
            'flavor' => 'Respira. A gente resolve.',
            'historia' => 'O Felippo acompanhou a apuração mais tensa do interior em 2008, quando uma chuva forte derrubou o gerador e quatro urnas pararam de uma vez. Enquanto todos corriam, ele apenas reorganizou os fios e religou tudo, no mesmo tom de voz do começo do dia. Desde então, "respira, a gente resolve" virou bordão de plenário em pleito complicado.',
        ],
        'thallys' => [
            'emoji' => '🕵️‍♂️',
            'nome' => 'Thallion, o Estrategista',
            'personalidade' => 'Calculista · Análise',
            'raridade' => 'lendaria',
            'classe' => 'Mestre da Análise',
            'habilidade' => 'Estudo Profundo — dedica três turnos à análise antes de codar. Entregas suas levam 90% menos refator no PR.',
            'flavor' => 'Mede duas vezes, corta uma.',
            'historia' => 'Antes de qualquer pleito, o Thallion mapeia rota das urnas, horário das mesas e até a previsão do clima do dia. Ninguém o vê apressado — porque já planejou tudo dois meses antes. Reza a lenda que, num pleito de 2010, previu com três semanas de antecedência qual seção precisaria de mesário reserva. Acertou.',
        ],
        'jonatan' => [
            'emoji' => '🧑‍🚒',
            'nome' => 'Jonatão, o Guardião da Infra',
            'personalidade' => 'Bombeiro de Plantão · Operação',
            'raridade' => 'lendaria',
            'classe' => 'Guardião de Plantão',
            'habilidade' => 'Apaga Incêndio — detecta e neutraliza B.O.s de produção antes que o time perceba. Uptime sob sua guarda: 99,97%.',
            'flavor' => 'Tá no ar. Pode rodar.',
            'historia' => 'O Jonatão vigia em silêncio cada cabo, cada nobreak, cada link do tribunal. Quando algo está prestes a cair, ele já está com a peça de troca na mão. Conta-se que, durante uma apuração nacional, evitou três quedas seguidas sem que ninguém no plenário sequer percebesse. A urna chega no ar porque ele chegou primeiro.',
        ],
        'jair' => [
            'emoji' => '🧑‍🔬',
            'nome' => 'Jairón, o Mestre dos Dados',
            'personalidade' => 'Sênior do Dado · Conhecimento Bruto',
            'raridade' => 'lendaria',
            'classe' => 'Sênior dos Dados',
            'habilidade' => 'Pipeline Limpo — extrai padrão de qualquer dataset caótico. Relatórios sob sua orientação nascem auditáveis.',
            'flavor' => 'Dado sujo conta meia história. A outra metade é onde mora a verdade.',
            'historia' => 'O Jairón recolhe os dados de cada apuração e os organiza em arquivos auditáveis até o último voto. Trata cada número como prova judicial: não aceita um valor sem saber de onde veio. Conta-se que reconstruiu, numa única madrugada, o histórico inteiro de uma zona cujos arquivos haviam se perdido — e cada linha bateu certinho com a ata original.',
        ],
        'lucas' => [
            'emoji' => '🧙‍♂️',
            'nome' => 'Lucander, o Improvisador',
            'personalidade' => 'Bardo-Mago · Improviso',
            'raridade' => 'lendaria',
            'classe' => 'Bardo-Mago',
            'habilidade' => 'Solo de Guitarra — invoca soluções fora do manual. Uma vez por dia, pode trocar uma reunião por um commit que resolve o ticket.',
            'flavor' => 'Se não funciona com regra, funciona com riff.',
            'historia' => 'Estagiário ainda aprendendo as manhas da casa. Quando um sistema trava num momento ruim, vai testando caminhos até achar um que passe — nem sempre o do manual, mas um que serve. Anota o atalho num caderno pra não esquecer e pra quem vier depois aproveitar.',
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
        // Mesmo padrão dos animais: 5 dias × 3 status, mais genérica e
        // boas-vindas. Cada personagem com voz própria.

        'edcley' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda. Nova semana, nova bancada. Bata o ponto e bora forjar. 👨‍🔧',
                ],
                'em_andamento' => [
                    'Em construção. Cada linha é um lacre — feita do zero, sem peça pronta.',
                ],
                'concluido' => [
                    'Forjado e entregue. Mais uma segunda lacrada.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Plugin de terceiro é dívida. Bata o ponto e siga firme.',
                ],
                'em_andamento' => [
                    'Engenheiro não promete: entrega rodando. Continue.',
                ],
                'concluido' => [
                    'Dia encerrado em produção. A bancada agradece.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Bom momento pra revisar a têmpera do código. Bata o ponto.',
                ],
                'em_andamento' => [
                    'Em curso. Tudo construído à mão, nada importado.',
                ],
                'concluido' => [
                    'Quarta lacrada. Próxima começa onde essa parou.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. A reta final exige a mesma raiz do começo.',
                ],
                'em_andamento' => [
                    'Em forja. Não tem atalho que substitua trabalho honesto.',
                ],
                'concluido' => [
                    'Encerrada com solidez, como urna lacrada.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Hora de fechar a semana com o mesmo cuidado da segunda.',
                ],
                'em_andamento' => [
                    'Última jornada. Ferro quente bate até o fim do expediente.',
                ],
                'concluido' => [
                    'Sexta lacrada. Bom descanso — a bancada espera segunda.',
                ],
            ],
            'generica' => [
                'Construa do zero. Plugin é dívida. 👨‍🔧',
                'Engenheiro não promete: entrega rodando.',
            ],
            'boas_vindas' => [
                'Bem-vindo. Sou o Edcleu, o Forjador de Raiz. Vamos construir. 👨‍🔧',
            ],
        ],

        'lucir' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda. Tive uma fórmula no domingo. Bata o ponto e te conto. 🧙',
                ],
                'em_andamento' => [
                    'Em destilação. Toda boa apuração começa numa ideia que parecia maluca.',
                ],
                'concluido' => [
                    'Segunda transmutada. A semana inteira respira a poção certa.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Hoje ouso uma combinação inédita — você comigo?',
                ],
                'em_andamento' => [
                    'Tô misturando, tô vendo, tô convergindo. Continua aí.',
                ],
                'concluido' => [
                    'Encerrada. O experimento deu certo (de novo, pra surpresa de ninguém).',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Hora boa pra um plano absurdo virar plano real.',
                ],
                'em_andamento' => [
                    'Em pleno transbordo criativo. Calma, vai dar liga.',
                ],
                'concluido' => [
                    'Quarta lacrada com riso e ata. Bom descanso.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Acho que descobri um atalho que ninguém viu.',
                ],
                'em_andamento' => [
                    'Em curso. Loucura é não tentar — e a urna não vota sozinha.',
                ],
                'concluido' => [
                    'Encerrada. O improvável virou regra. Anota aí.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Que tal terminar a semana com um experimento ousado?',
                ],
                'em_andamento' => [
                    'Última destilação. Vai sair coisa boa.',
                ],
                'concluido' => [
                    'Sextou alquímico. Final de semana é pausa pra fermentar a próxima ideia.',
                ],
            ],
            'generica' => [
                'Tive uma ideia. Pode parecer maluca. Vai funcionar. 🧙',
                'Louco é quem não tenta.',
            ],
            'boas_vindas' => [
                'E aí. Sou o Lucírio, o Alquimista. Bora destilar uma ideia maluca? 🧙',
            ],
        ],

        'bortoli' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda. Lembro de uma muito parecida em 1998 — bata o ponto, conto depois. 👴',
                ],
                'em_andamento' => [
                    'Em curso. Já vi essa novela: termina bem se seguir o regimento.',
                ],
                'concluido' => [
                    'Encerrada. Mais uma segunda pro arquivo.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Em 2004 teve uma terça assim — chuva, urna, ata atrasada. Bata o ponto.',
                ],
                'em_andamento' => [
                    'Tô lembrando: é igualzinho ao pleito municipal. Vai dar certo.',
                ],
                'concluido' => [
                    'Boa terça. Anotada na coleção.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Toda quarta tem um pouco da quarta anterior. Como toda eleição.',
                ],
                'em_andamento' => [
                    'Em andamento. Quem não esquece, não repete erro.',
                ],
                'concluido' => [
                    'Encerrada. O acervo cresceu mais uma página.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Em 2010 já tínhamos resolvido isso — pergunta antes de duplicar.',
                ],
                'em_andamento' => [
                    'Em curso. Pelo menos três presidentes do tribunal já viram esse caso.',
                ],
                'concluido' => [
                    'Bom dia. Mais um capítulo lacrado.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Termine bem — o passado julga o presente, sempre.',
                ],
                'em_andamento' => [
                    'Última jornada. Eu já vi sextas se desfazerem por descuido. Cuide da sua.',
                ],
                'concluido' => [
                    'Semana arquivada. Boa pousada — segunda de novo no acervo.',
                ],
            ],
            'generica' => [
                'Isso aqui já tentamos. Deixa eu te contar como foi. 👴',
                'Quem não esquece, não repete erro.',
            ],
            'boas_vindas' => [
                'Saudações. Sou o Bortelmo. Mexo com COBOL e Clipper desde antes de você nascer — pergunte que eu desencavo a solução. 👴',
            ],
        ],

        'ilis' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda chegou. Calma. Bata o ponto sem afobação. 🧘🏽‍♂️',
                ],
                'em_andamento' => [
                    'Sem pressa. Boletim por boletim, ata por ata.',
                ],
                'concluido' => [
                    'Encerrada. Paciência hoje é descanso amanhã.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Tudo no seu tempo — inclusive o sistema antigo.',
                ],
                'em_andamento' => [
                    'Em curso, com calma. Cada coisa no seu lugar.',
                ],
                'concluido' => [
                    'Dia cumprido. Sereno, como sempre.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Respira fundo e bata o ponto.',
                ],
                'em_andamento' => [
                    'Tô aqui. Sem alvoroço. A apuração também não tem.',
                ],
                'concluido' => [
                    'Encerrada. Mais uma quarta classificada.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Quem domina a ferramenta não tem pressa.',
                ],
                'em_andamento' => [
                    'Em andamento. Calma é só uma forma de competência.',
                ],
                'concluido' => [
                    'Bom dia. Quinta sem turbulência.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. O fim da semana pede a mesma calma do começo.',
                ],
                'em_andamento' => [
                    'Última jornada. Vai funcionar — só precisa de paciência.',
                ],
                'concluido' => [
                    'Sextou em paz. Bom descanso.',
                ],
            ],
            'generica' => [
                'Calma. Vai funcionar. Só precisa de paciência. 🧘🏽‍♂️',
                'Aprender com quem domina vale mais que gostar.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Ilíseo, o Domador de Plone. Sem pressa — a gente desbrava junto. 🧘🏽‍♂️',
            ],
        ],

        'felipe' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Bom dia. Segunda é só segunda. Respira, bata o ponto, a gente resolve. 🙆🏿‍♂️',
                ],
                'em_andamento' => [
                    'Em curso. Pânico não compila. Calma sim.',
                ],
                'concluido' => [
                    'Encerrada sem pressa, sem drama.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Hoje fica tudo no seu lugar — eu garanto.',
                ],
                'em_andamento' => [
                    'Tô no mesmo bug, mesmo café, mesmo tom. A gente resolve.',
                ],
                'concluido' => [
                    'Boa terça. Saiu redondinho.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Bata o ponto e relaxa — não tem incêndio que dure até sexta.',
                ],
                'em_andamento' => [
                    'Em andamento. Sereno como gerador estável.',
                ],
                'concluido' => [
                    'Quarta encerrada sem sobressalto.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Quase sexta. E mesmo se a chuva cair, religamos os fios.',
                ],
                'em_andamento' => [
                    'Tô aqui. Todo gerador tem horário de descanso — você também.',
                ],
                'concluido' => [
                    'Encerrada. Mais um dia sem virar incêndio.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Termine no mesmo tom do começo — não tem porque acelerar.',
                ],
                'em_andamento' => [
                    'Última jornada. Respira, a gente resolve, e fim de semana chega.',
                ],
                'concluido' => [
                    'Sextou. Tudo certo, tudo no lugar.',
                ],
            ],
            'generica' => [
                'Respira. A gente resolve. 🙆🏿‍♂️',
                'Pânico não compila. Calma sim.',
            ],
            'boas_vindas' => [
                'E aí. Sou o Felippo, a Calmaria. Tá tudo sob controle. 🙆🏿‍♂️',
            ],
        ],

        'thallys' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda. Eu já tinha mapeado essa semana semana passada. Bata o ponto. 🕵️‍♂️',
                ],
                'em_andamento' => [
                    'Em curso. Cada movimento foi previsto há duas semanas.',
                ],
                'concluido' => [
                    'Encerrada conforme cronograma. Próxima jogada já desenhada.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Estudei o problema todo no fim de semana — sem surpresa hoje.',
                ],
                'em_andamento' => [
                    'Em andamento. Mede duas vezes, corta uma. Tô medindo.',
                ],
                'concluido' => [
                    'Bom dia. Saiu como o esquema previa.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Eu mapeei essa quarta quando você ainda nem tinha pensado nela.',
                ],
                'em_andamento' => [
                    'Em curso. Plano A funcionou. Plano B já tava pronto, só por precaução.',
                ],
                'concluido' => [
                    'Quarta encerrada. Anotado no quadro.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Reta final — planejada com três semanas de antecedência.',
                ],
                'em_andamento' => [
                    'Em andamento. Prever é também economizar reunião.',
                ],
                'concluido' => [
                    'Bom dia. Quinta lacrada. Sexta já está no roteiro.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Antecipei até o cansaço dela — bata o ponto e tudo flui.',
                ],
                'em_andamento' => [
                    'Última jornada. O plano da próxima semana já está pronto.',
                ],
                'concluido' => [
                    'Sextou conforme cronograma. Bom descanso.',
                ],
            ],
            'generica' => [
                'Mede duas vezes, corta uma. 🕵️‍♂️',
                'Antes de codar, entender. Sempre.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Thallion, o Estrategista. Vamos estudar antes de mover a peça. 🕵️‍♂️',
            ],
        ],

        'jonatan' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Salve. Segunda começou — link ok, banco ok, nobreak ok. Pode bater o ponto. 🧑‍🚒',
                ],
                'em_andamento' => [
                    'Tô vendo o log. Nada cai enquanto eu tô aqui.',
                ],
                'concluido' => [
                    'Segunda encerrada com uptime 100%.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Já checkei tudo de manhã. Tá redondo. Bata o ponto.',
                ],
                'em_andamento' => [
                    'Em pleno plantão. Se quebrar, eu vejo. Se não quebrar, eu vejo também.',
                ],
                'concluido' => [
                    'Bom dia. Terça sem chamado, terça boa.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Meio do log — tá tranquilo.',
                ],
                'em_andamento' => [
                    'Em andamento. Peça de troca já tá na bancada.',
                ],
                'concluido' => [
                    'Quarta lacrada com produção de pé.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Já antecipei o B.O. dessa quinta. Pode bater o ponto.',
                ],
                'em_andamento' => [
                    'Em curso. Nobreak segura, link segura, eu seguro o resto.',
                ],
                'concluido' => [
                    'Bom dia. Mais uma quinta sem que ninguém percebesse.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Hoje ainda fico no plantão até o fim do expediente.',
                ],
                'em_andamento' => [
                    'Última jornada. Final de semana sem cair, é compromisso.',
                ],
                'concluido' => [
                    'Sextou. Tá no ar. Pode rodar.',
                ],
            ],
            'generica' => [
                'Tá no ar. Pode rodar. 🧑‍🚒',
                'Se quebrou, eu vejo. Se não quebrou, eu vejo também.',
            ],
            'boas_vindas' => [
                'Salve. Sou o Jonatão, o Guardião da Infra. Pode trampar tranquilo — tô de plantão. 🧑‍🚒',
            ],
        ],

        'jair' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'Segunda. Bata o ponto — registro limpo facilita a auditoria. 🧑‍🔬',
                ],
                'em_andamento' => [
                    'Em curso. Cada linha conta — não confunda total com média.',
                ],
                'concluido' => [
                    'Encerrada. Registro fechado, dado limpo.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Antes de rodar relatório, valide a fonte.',
                ],
                'em_andamento' => [
                    'Em andamento. Dado sujo conta meia história. Nunca aceito meio.',
                ],
                'concluido' => [
                    'Boa terça. Pipeline auditada, pronto pro próximo dia.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Hora boa pra rodar consistência.',
                ],
                'em_andamento' => [
                    'Em curso. Onde tem número, tem rastro. Eu sigo o rastro.',
                ],
                'concluido' => [
                    'Quarta lacrada. Tudo bate com a ata.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Reta final — confira o cabeçalho antes do total.',
                ],
                'em_andamento' => [
                    'Em andamento. Relatório bom é o que aguenta auditoria.',
                ],
                'concluido' => [
                    'Bom dia. Quinta encerrada, dado certo.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Não fecha o pleito sem reconciliar a planilha. Bata o ponto.',
                ],
                'em_andamento' => [
                    'Última jornada. Pipeline limpo é pleito tranquilo.',
                ],
                'concluido' => [
                    'Sextou. Bom descanso — segunda tem dado novo pra apurar.',
                ],
            ],
            'generica' => [
                'Dado sujo conta meia história. 🧑‍🔬',
                'Relatório bom é o que aguenta auditoria.',
            ],
            'boas_vindas' => [
                'Olá. Sou o Jairón, o Mestre dos Dados. Bora limpar essa pipeline. 🧑‍🔬',
            ],
        ],

        'lucas' => [
            'segunda' => [
                'aguardando_entrada' => [
                    'E aí. Segunda começa com o terminal aberto. Bata o ponto. 🧙‍♂️',
                ],
                'em_andamento' => [
                    'Em curso. Quando o manual falha, o terminal resolve.',
                ],
                'concluido' => [
                    'Segunda encerrada. Improviso anotado pro próximo pleito.',
                ],
            ],
            'terca' => [
                'aguardando_entrada' => [
                    'Terça. Hoje a regra trava, mas a gente acha um caminho.',
                ],
                'em_andamento' => [
                    'Em andamento. Se não funciona com regra, funciona com riff.',
                ],
                'concluido' => [
                    'Terça encerrada. Foi de improviso, mas funcionou.',
                ],
            ],
            'quarta' => [
                'aguardando_entrada' => [
                    'Quarta. Hora boa pra refator improvisado virar arquitetura.',
                ],
                'em_andamento' => [
                    'Em curso. Stack: Laravel, Docker e improviso.',
                ],
                'concluido' => [
                    'Quarta encerrada. Bug resolvido sem manual.',
                ],
            ],
            'quinta' => [
                'aguardando_entrada' => [
                    'Quinta. Reta final do pleito — improviso afinado é solo de quinta.',
                ],
                'em_andamento' => [
                    'Em andamento. Quem improvisa não erra: ajusta no próximo compasso.',
                ],
                'concluido' => [
                    'Bom dia. Quinta lacrada à mão.',
                ],
            ],
            'sexta' => [
                'aguardando_entrada' => [
                    'Sexta. Termina a semana no improviso — e na próxima, vira regra.',
                ],
                'em_andamento' => [
                    'Última jornada. Riff final.',
                ],
                'concluido' => [
                    'Sextou. Próximo pleito tem improviso garantido.',
                ],
            ],
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
