@extends('layouts.app')

@section('title', 'Mascotes — Bena')

@push('styles')
<style>
    .bena-mascotes {
        max-width: 980px;
        margin: 0 auto;
    }
    .bena-mascotes__header {
        text-align: center;
        margin-bottom: 2.5rem;
    }
    .bena-mascotes__header h1 {
        color: #003366;
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 0.5rem;
        letter-spacing: -0.01em;
    }
    .bena-mascotes__header p {
        color: #475569;
        margin: 0;
        font-size: 1rem;
        line-height: 1.5;
    }

    .bena-mascotes__secao {
        margin-bottom: 2.5rem;
    }
    .bena-mascotes__secao-titulo {
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin: 0 0 0.4rem;
    }
    .bena-mascotes__secao-sub {
        color: #64748b;
        font-size: 0.92rem;
        margin: 0 0 1.25rem;
        line-height: 1.5;
    }

    .bena-mascotes__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
    }

    .bena-mascote {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 12px;
        padding: 1.25rem 1.4rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04),
                    0 4px 14px rgba(0, 51, 102, 0.04);
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .bena-mascote:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(15, 23, 42, 0.06),
                    0 12px 28px rgba(0, 51, 102, 0.08);
    }
    .bena-mascote--senior {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: rgba(217, 119, 6, 0.18);
    }
    .bena-mascote--lendaria {
        position: relative;
        background:
            linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);
        border: 1px solid rgba(250, 204, 21, 0.35);
        color: #f5f3ff;
        box-shadow: 0 4px 14px rgba(76, 29, 149, 0.18),
                    inset 0 0 0 1px rgba(250, 204, 21, 0.08);
    }
    .bena-mascote--lendaria::before {
        content: 'LENDÁRIA';
        position: absolute;
        top: 0.6rem;
        right: 0.85rem;
        font-size: 0.6rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        color: #fde68a;
        background: rgba(0, 0, 0, 0.35);
        padding: 0.18rem 0.55rem;
        border-radius: 999px;
        border: 1px solid rgba(250, 204, 21, 0.4);
    }
    .bena-mascote--lendaria:hover {
        box-shadow: 0 6px 12px rgba(76, 29, 149, 0.25),
                    0 16px 32px rgba(76, 29, 149, 0.18),
                    inset 0 0 0 1px rgba(250, 204, 21, 0.18);
    }
    /* Reserva espaço para o selo "LENDÁRIA" no topo direito —
       sem isso, nomes longos como "Bortelmo, Mestre do Legado"
       passam por baixo do selo. */
    .bena-mascote--lendaria .bena-mascote__topo {
        padding-right: 5.5rem;
    }

    .bena-mascote__topo {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .bena-mascote__avatar {
        font-size: 2.4rem;
        line-height: 1;
        flex-shrink: 0;
    }
    .bena-mascote__identidade {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .bena-mascote__nome {
        color: #003366;
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.01em;
    }
    .bena-mascote__personalidade {
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-top: 0.15rem;
    }
    .bena-mascote--senior .bena-mascote__nome {
        color: #78350f;
    }
    .bena-mascote--senior .bena-mascote__personalidade {
        color: #92400e;
    }

    .bena-mascote__historia {
        color: #334155;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0;
    }
    .bena-mascote--senior .bena-mascote__historia {
        color: #422006;
    }
    .bena-mascote--lendaria .bena-mascote__nome {
        color: #fde68a;
    }
    .bena-mascote--lendaria .bena-mascote__personalidade {
        color: #c4b5fd;
    }
    .bena-mascote--lendaria .bena-mascote__historia {
        color: #ede9fe;
    }
    .bena-mascote__habilidade {
        font-size: 0.85rem;
        line-height: 1.55;
        margin: 0;
        padding: 0.55rem 0.7rem;
        background: rgba(0, 0, 0, 0.22);
        border-left: 2px solid #fde68a;
        border-radius: 4px;
        color: #fef3c7;
    }
    .bena-mascote__habilidade strong {
        color: #fde68a;
        font-weight: 700;
    }
    .bena-mascote__flavor {
        margin: 0;
        padding-top: 0.55rem;
        border-top: 1px dashed rgba(253, 230, 138, 0.25);
        color: #fde68a;
        font-style: italic;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .bena-mascotes__rodape {
        text-align: center;
        margin-top: 2rem;
    }
    .bena-mascotes__rodape a {
        color: #64748b;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .bena-mascotes__rodape a:hover {
        background: rgba(0, 51, 102, 0.06);
        color: #003366;
    }
</style>
@endpush

@section('content')
    <div class="bena-mascotes">
        <header class="bena-mascotes__header">
            <h1>Os mascotes do Bena</h1>
            <p>
                Cada usuário do sistema recebe um mascote sorteado no primeiro acesso.
                As histórias são pequenas ficções inspiradas na vida cotidiana
                da Justiça Eleitoral do Acre.
            </p>
        </header>

        <section class="bena-mascotes__secao" aria-labelledby="secao-padrao">
            <h2 id="secao-padrao" class="bena-mascotes__secao-titulo">
                Pool padrão · estagiários
            </h2>
            <p class="bena-mascotes__secao-sub">
                Oito mascotes com personalidades distintas. O sorteio acontece no
                primeiro acesso e é permanente — você adota o seu.
            </p>

            <div class="bena-mascotes__grid">
                @foreach ($tiposPadrao as $tipo)
                    @php($p = $perfis[$tipo] ?? null)
                    @if ($p)
                        <article class="bena-mascote">
                            <div class="bena-mascote__topo">
                                <span class="bena-mascote__avatar" aria-hidden="true">
                                    @if (! empty($p['sprite']))
                                        <img src="{{ $p['sprite'] }}" alt="" class="bena-mascote__sprite" style="image-rendering: pixelated; width: 64px; height: 64px;">
                                    @else
                                        {{ $p['emoji'] }}
                                    @endif
                                </span>
                                <div class="bena-mascote__identidade">
                                    <span class="bena-mascote__nome">{{ $p['nome'] }}</span>
                                    <span class="bena-mascote__personalidade">{{ $p['personalidade'] }}</span>
                                </div>
                            </div>
                            <p class="bena-mascote__historia">{{ $p['historia'] }}</p>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="bena-mascotes__secao" aria-labelledby="secao-senior">
            <h2 id="secao-senior" class="bena-mascotes__secao-titulo">
                Pool sênior · supervisores e admin
            </h2>
            <p class="bena-mascotes__secao-sub">
                Quatro mascotes em tom de mentoria, atribuídos a quem já passou da
                fase de estágio. Mais experiência, mais perspectiva.
            </p>

            <div class="bena-mascotes__grid">
                @foreach ($tiposSenior as $tipo)
                    @php($p = $perfis[$tipo] ?? null)
                    @if ($p)
                        <article class="bena-mascote bena-mascote--senior">
                            <div class="bena-mascote__topo">
                                <span class="bena-mascote__avatar" aria-hidden="true">
                                    @if (! empty($p['sprite']))
                                        <img src="{{ $p['sprite'] }}" alt="" class="bena-mascote__sprite" style="image-rendering: pixelated; width: 64px; height: 64px;">
                                    @else
                                        {{ $p['emoji'] }}
                                    @endif
                                </span>
                                <div class="bena-mascote__identidade">
                                    <span class="bena-mascote__nome">{{ $p['nome'] }}</span>
                                    <span class="bena-mascote__personalidade">{{ $p['personalidade'] }}</span>
                                </div>
                            </div>
                            <p class="bena-mascote__historia">{{ $p['historia'] }}</p>
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="bena-mascotes__secao" aria-labelledby="secao-lendaria">
            <h2 id="secao-lendaria" class="bena-mascotes__secao-titulo">
                Cartas Lendárias · STI
            </h2>
            <p class="bena-mascotes__secao-sub">
                Dez cartas únicas inspiradas em personagens da Secretaria de
                Tecnologia da Informação. Sorteadas exclusivamente no pool
                dos estagiários da STI.
            </p>

            <div class="bena-mascotes__grid">
                @foreach ($tiposLendarios as $tipo)
                    @php($p = $perfis[$tipo] ?? null)
                    @if ($p)
                        <article class="bena-mascote bena-mascote--lendaria">
                            <div class="bena-mascote__topo">
                                <span class="bena-mascote__avatar" aria-hidden="true">
                                    @if (! empty($p['sprite']))
                                        <img src="{{ $p['sprite'] }}" alt="" class="bena-mascote__sprite" style="image-rendering: pixelated; width: 64px; height: 64px;">
                                    @else
                                        {{ $p['emoji'] }}
                                    @endif
                                </span>
                                <div class="bena-mascote__identidade">
                                    <span class="bena-mascote__nome">{{ $p['nome'] }}</span>
                                    <span class="bena-mascote__personalidade">{{ $p['personalidade'] }}</span>
                                </div>
                            </div>
                            <p class="bena-mascote__historia">{{ $p['historia'] }}</p>
                            @if (! empty($p['habilidade']))
                                <p class="bena-mascote__habilidade">
                                    <strong>Habilidade.</strong> {{ $p['habilidade'] }}
                                </p>
                            @endif
                            @if (! empty($p['flavor']))
                                <p class="bena-mascote__flavor">"{{ $p['flavor'] }}"</p>
                            @endif
                        </article>
                    @endif
                @endforeach
            </div>
        </section>

        <p class="bena-mascotes__rodape">
            <a href="{{ route('onboarding.show') }}">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
                Voltar para a tela "Sobre"
            </a>
        </p>
    </div>
@endsection
