@extends('layouts.app')

@php
    use Carbon\CarbonImmutable;

    $primeiro = CarbonImmutable::create($ano, $mes, 1);
    $hoje = CarbonImmutable::today();
    $diasNoMes = $primeiro->daysInMonth;
    $offsetInicial = $primeiro->dayOfWeek;
    $nomeMes = $primeiro->translatedFormat('F');

    $tema = [
        1  => '226, 232, 240',
        2  => '192, 132, 252',
        3  => '96, 165, 250',
        4  => '74, 222, 128',
        5  => '250, 204, 21',
        6  => '248, 113, 113',
        7  => '251, 191, 36',
        8  => '251, 146, 60',
        9  => '253, 224, 71',
        10 => '244, 114, 182',
        11 => '56, 189, 248',
        12 => '239, 68, 68',
    ];

    $isAdmin = session('grupodeacesso') === '0';

    $anterior = $primeiro->subMonth();
    $proximo = $primeiro->addMonth();

    $redirectTo = '/calendario/'.$ano.'/'.$mes;
@endphp

@section('title', "{$nomeMes} de {$ano} — Calendário")

@push('styles')
<style>
    .bena-mes {
        max-width: 720px;
        --cal-theme: {{ $tema[$mes] }};
    }
    .bena-mes__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .bena-mes__title-wrap {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .bena-mes__title {
        color: rgb(var(--cal-theme));
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.1;
        letter-spacing: -0.01em;
        text-transform: capitalize;
        filter: brightness(0.6) saturate(1.2);
    }
    .bena-mes__subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin: 0;
    }
    .bena-mes__nav {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .bena-mes__nav a {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.85rem;
        border-radius: 8px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: #475569;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    .bena-mes__nav a:hover {
        background: rgba(0, 51, 102, 0.04);
        color: #003366;
        border-color: rgba(0, 51, 102, 0.2);
    }

    .bena-mes__card {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04),
                    0 4px 14px rgba(0, 51, 102, 0.04);
    }
    .bena-mes__week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        margin-bottom: 0.6rem;
    }
    .bena-mes__week span {
        text-align: center;
        font-size: 0.78rem;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.4rem 0;
    }
    .bena-mes__week span.fim {
        color: #60a5fa;
    }

    .bena-mes__days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
    }
    .bena-mes__day {
        position: relative;
        aspect-ratio: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
        padding: 0.5rem 0.55rem;
        background: rgba(var(--cal-theme), 0.13);
        border-radius: 8px;
        font-size: 1.05rem;
        color: #475569;
        text-decoration: none;
        text-align: left;
        font-family: inherit;
        transition: background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        cursor: default;
    }
    button.bena-mes__day {
        border: none;
        cursor: pointer;
    }
    .bena-mes__day--vazio {
        background: transparent;
    }
    .bena-mes__day--fds {
        background: #dbeafe;
        color: #1e3a8a;
    }
    .bena-mes__day--feriado {
        background: #fef3c7;
        color: #78350f;
        font-weight: 600;
        cursor: help;
    }
    a.bena-mes__day--feriado,
    button.bena-mes__day {
        cursor: pointer;
    }
    .bena-mes__day--hoje {
        box-shadow: inset 0 0 0 2px #003366;
        font-weight: 700;
        color: #003366;
    }
    .bena-mes__day-num {
        font-size: 1rem;
        font-weight: 600;
    }
    .bena-mes__day--feriado .bena-mes__day-num {
        font-weight: 700;
    }
    .bena-mes__day-desc {
        font-size: 0.7rem;
        font-weight: 500;
        color: #92400e;
        line-height: 1.25;
        margin-top: 0.15rem;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        max-width: 100%;
    }
    .bena-mes__day--feriado::after {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #d97706;
    }
    button.bena-mes__day:hover {
        background: rgba(var(--cal-theme), 0.32);
        transform: translateY(-1px);
    }
    button.bena-mes__day.bena-mes__day--fds:hover {
        background: #bfdbfe;
    }
    button.bena-mes__day::before {
        content: '\f067'; /* fa-plus */
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 0.65rem;
        color: rgba(15, 23, 42, 0.25);
        opacity: 0;
        transition: opacity 0.18s ease;
    }
    button.bena-mes__day:hover::before {
        opacity: 1;
    }

    .bena-mes__day--feriado:hover,
    .bena-mes__day--feriado:focus-visible {
        background: #fde68a;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(217, 119, 6, 0.15);
    }
    .bena-mes__day-tooltip {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%) translateY(4px);
        background: #1e293b;
        color: #ffffff;
        padding: 0.45rem 0.7rem;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.18s ease, transform 0.18s ease;
        z-index: 20;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
    }
    .bena-mes__day-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1e293b;
    }
    .bena-mes__day--feriado:hover .bena-mes__day-tooltip,
    .bena-mes__day--feriado:focus-visible .bena-mes__day-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .bena-mes__hint {
        margin-top: 1rem;
        font-size: 0.85rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .bena-mes__hint i {
        color: #003366;
    }

    /* ─── Dialog de adicionar feriado (admin) ──────────────────── */
    .bena-dialog {
        border: none;
        border-radius: 14px;
        padding: 0;
        max-width: 480px;
        width: calc(100% - 2rem);
        background: #ffffff;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25),
                    0 0 0 1px rgba(15, 23, 42, 0.05);
    }
    .bena-dialog::backdrop {
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(2px);
    }
    .bena-dialog__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem 0.75rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }
    .bena-dialog__title {
        color: #003366;
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .bena-dialog__close {
        background: transparent;
        border: none;
        font-size: 1.4rem;
        line-height: 1;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .bena-dialog__close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: #0f172a;
    }
    .bena-dialog__body {
        padding: 1.25rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .bena-dialog__date {
        background: #f0f9ff;
        border-left: 3px solid #003366;
        padding: 0.65rem 0.85rem;
        border-radius: 6px;
        margin: 0;
        font-size: 0.95rem;
        color: #003366;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .bena-dialog__actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem 1.25rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
    }

    @media (max-width: 600px) {
        .bena-mes__day {
            padding: 0.35rem 0.4rem;
            font-size: 0.85rem;
        }
        .bena-mes__day-desc {
            display: none;
        }
        .bena-mes__day-num {
            font-size: 0.9rem;
        }
        .bena-dialog__body {
            padding: 1rem 1.1rem;
        }
        .bena-dialog__header,
        .bena-dialog__actions {
            padding-left: 1.1rem;
            padding-right: 1.1rem;
        }
        .bena-dialog__actions {
            flex-direction: column-reverse;
        }
        .bena-dialog__actions .br-button {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
    <div class="bena-mes">
        <div class="bena-mes__header">
            <div class="bena-mes__title-wrap">
                <h1 class="bena-mes__title">{{ $nomeMes }} {{ $ano }}</h1>
                <p class="bena-mes__subtitle">
                    {{ count($feriadosPorData) }} {{ count($feriadosPorData) === 1 ? 'feriado' : 'feriados' }} neste mês.
                    @if ($isAdmin)
                        Clique em um dia em branco para adicionar.
                    @else
                        Passe o mouse sobre um dia em destaque para ver a descrição.
                    @endif
                </p>
            </div>
            <div class="bena-mes__nav">
                <a href="{{ route('calendario.mes', ['ano' => $anterior->year, 'mes' => $anterior->month]) }}"
                   title="{{ $anterior->translatedFormat('F') }} de {{ $anterior->year }}">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                    {{ $anterior->translatedFormat('M') }}
                </a>
                <a href="{{ route('calendario.mes', ['ano' => $proximo->year, 'mes' => $proximo->month]) }}"
                   title="{{ $proximo->translatedFormat('F') }} de {{ $proximo->year }}">
                    {{ $proximo->translatedFormat('M') }}
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <div class="bena-mes__card">
            <div class="bena-mes__week" aria-hidden="true">
                <span class="fim">Dom</span>
                <span>Seg</span>
                <span>Ter</span>
                <span>Qua</span>
                <span>Qui</span>
                <span>Sex</span>
                <span class="fim">Sáb</span>
            </div>

            <div class="bena-mes__days">
                @for ($i = 0; $i < $offsetInicial; $i++)
                    <span class="bena-mes__day bena-mes__day--vazio" aria-hidden="true"></span>
                @endfor

                @for ($d = 1; $d <= $diasNoMes; $d++)
                    @php
                        $data = $primeiro->setDay($d);
                        $chave = $data->format('Y-m-d');
                        $feriado = $feriadosPorData[$chave] ?? null;
                        $isFds = $data->isWeekend();
                        $isHoje = $data->equalTo($hoje);

                        $classes = ['bena-mes__day'];
                        if ($feriado !== null) {
                            $classes[] = 'bena-mes__day--feriado';
                        } elseif ($isFds) {
                            $classes[] = 'bena-mes__day--fds';
                        }
                        if ($isHoje) {
                            $classes[] = 'bena-mes__day--hoje';
                        }
                        $classe = implode(' ', $classes);
                        $titulo = $feriado !== null
                            ? $feriado['descricao'].' · '.$data->format('d/m/Y')
                            : $data->format('d/m/Y');
                    @endphp

                    @if ($feriado !== null && $isAdmin)
                        <a class="{{ $classe }}"
                           href="{{ route('admin.feriados.edit', $feriado['id']) }}"
                           title="{{ $titulo }} (clique para editar)">
                            <span class="bena-mes__day-num">{{ $d }}</span>
                            <span class="bena-mes__day-desc">{{ $feriado['descricao'] }}</span>
                            <span class="bena-mes__day-tooltip" aria-hidden="true">{{ $feriado['descricao'] }}</span>
                        </a>
                    @elseif ($feriado !== null)
                        <span class="{{ $classe }}" title="{{ $titulo }}" tabindex="0">
                            <span class="bena-mes__day-num">{{ $d }}</span>
                            <span class="bena-mes__day-desc">{{ $feriado['descricao'] }}</span>
                            <span class="bena-mes__day-tooltip" aria-hidden="true">{{ $feriado['descricao'] }}</span>
                        </span>
                    @elseif ($isAdmin)
                        <button type="button" class="{{ $classe }}"
                                data-add-feriado="{{ $chave }}"
                                title="{{ $titulo }} · adicionar feriado">
                            <span class="bena-mes__day-num">{{ $d }}</span>
                        </button>
                    @else
                        <span class="{{ $classe }}" title="{{ $titulo }}">
                            <span class="bena-mes__day-num">{{ $d }}</span>
                        </span>
                    @endif
                @endfor
            </div>

            @if ($isAdmin)
                <p class="bena-mes__hint">
                    <i class="fas fa-lightbulb" aria-hidden="true"></i>
                    Clique em um dia para adicionar um feriado · clique num dia âmbar para editar.
                </p>
            @endif
        </div>
    </div>

    @if ($isAdmin)
        <dialog id="bena-dialog" class="bena-dialog" aria-labelledby="bena-dialog-title">
            <form method="POST" action="{{ route('admin.feriados.store') }}" class="bena-form">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <input type="hidden" id="bena-dialog-data" name="data" value="">

                <header class="bena-dialog__header">
                    <h2 id="bena-dialog-title" class="bena-dialog__title">
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                        Adicionar feriado
                    </h2>
                    <button type="button" class="bena-dialog__close" data-bena-close aria-label="Fechar">
                        &times;
                    </button>
                </header>

                <div class="bena-dialog__body">
                    <p class="bena-dialog__date">
                        <i class="fas fa-calendar-day" aria-hidden="true"></i>
                        <span id="bena-dialog-data-display"></span>
                    </p>

                    <div class="bena-form__field">
                        <label for="bena-dialog-descricao" class="bena-form__label">
                            Descrição <span class="required" aria-hidden="true">*</span>
                        </label>
                        <input type="text" id="bena-dialog-descricao" name="descricao"
                               required maxlength="200" class="bena-form__input"
                               placeholder="Ex: Aniversário de Rio Branco">
                    </div>

                    <div class="bena-form__row">
                        <div class="bena-form__field">
                            <label for="bena-dialog-tipo" class="bena-form__label">
                                Tipo <span class="required" aria-hidden="true">*</span>
                            </label>
                            <select id="bena-dialog-tipo" name="tipo" required class="bena-form__select">
                                <option value="">Selecione…</option>
                                <option value="nacional">Nacional</option>
                                <option value="estadual">Estadual</option>
                                <option value="municipal">Municipal</option>
                                <option value="recesso">Recesso</option>
                            </select>
                        </div>

                        <div class="bena-form__field">
                            <label for="bena-dialog-uf" class="bena-form__label">UF</label>
                            <input type="text" id="bena-dialog-uf" name="uf" maxlength="2"
                                   class="bena-form__input" style="text-transform: uppercase;"
                                   placeholder="AC">
                        </div>
                    </div>

                    <label class="bena-form__checkbox">
                        <input type="checkbox" name="recorrente" value="1">
                        <span>Recorrente — repete todo ano nesta data</span>
                    </label>
                </div>

                <div class="bena-dialog__actions">
                    <button type="button" class="br-button secondary" data-bena-close>Cancelar</button>
                    <button type="submit" class="br-button primary">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        Cadastrar
                    </button>
                </div>
            </form>
        </dialog>
    @endif
@endsection

@if ($isAdmin)
@push('scripts')
<script>
    (function () {
        const dialog = document.getElementById('bena-dialog');
        if (!dialog) return;

        const dataInput = document.getElementById('bena-dialog-data');
        const dataDisplay = document.getElementById('bena-dialog-data-display');
        const descricao = document.getElementById('bena-dialog-descricao');

        const meses = [
            'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
            'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro',
        ];

        function formatPtBr(iso) {
            const [a, m, d] = iso.split('-');
            return parseInt(d, 10) + ' de ' + meses[parseInt(m, 10) - 1] + ' de ' + a;
        }

        document.querySelectorAll('[data-add-feriado]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const iso = btn.dataset.addFeriado;
                dataInput.value = iso;
                dataDisplay.textContent = formatPtBr(iso);
                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                    setTimeout(function () { descricao.focus(); }, 50);
                } else {
                    // fallback: navega para a página de criação com data pré-preenchida
                    window.location.href = '{{ route('admin.feriados.create') }}';
                }
            });
        });

        dialog.querySelectorAll('[data-bena-close]').forEach(function (btn) {
            btn.addEventListener('click', function () { dialog.close(); });
        });

        // Fechar ao clicar no backdrop
        dialog.addEventListener('click', function (e) {
            const rect = dialog.getBoundingClientRect();
            const dentro = e.clientX >= rect.left && e.clientX <= rect.right
                && e.clientY >= rect.top && e.clientY <= rect.bottom;
            if (!dentro) dialog.close();
        });
    })();
</script>
@endpush
@endif
