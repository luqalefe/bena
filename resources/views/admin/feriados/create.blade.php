@extends('layouts.app')

@section('title', 'Novo feriado — Bena')

@push('styles')
<style>
    .bena-picker {
        margin-top: 0.85rem;
        background: #fafafa;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 10px;
        padding: 0.85rem 1rem 1rem;
        max-width: 340px;
    }
    .bena-picker__nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.65rem;
    }
    .bena-picker__nav button {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 6px;
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #64748b;
        font-size: 1rem;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .bena-picker__nav button:hover {
        background: rgba(0, 51, 102, 0.06);
        color: #003366;
    }
    .bena-picker__title {
        color: rgb(var(--cal-theme));
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        filter: brightness(0.7) saturate(1.2);
    }
    .bena-picker__week {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
        margin-bottom: 0.3rem;
    }
    .bena-picker__week span {
        text-align: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: #94a3b8;
        letter-spacing: 0.05em;
        padding: 0.15rem 0;
    }
    .bena-picker__week span.fim {
        color: #60a5fa;
    }
    .bena-picker__days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }
    .bena-picker__day {
        aspect-ratio: 1;
        background: rgba(var(--cal-theme), 0.15);
        border: none;
        border-radius: 6px;
        font-family: inherit;
        font-size: 0.85rem;
        color: #475569;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease, color 0.15s ease;
    }
    .bena-picker__day:hover {
        background: rgba(var(--cal-theme), 0.4);
        transform: scale(1.06);
    }
    .bena-picker__day:focus-visible {
        outline: 2px solid #003366;
        outline-offset: 1px;
    }
    .bena-picker__day--vazio {
        background: transparent;
        cursor: default;
    }
    .bena-picker__day--fds {
        background: #dbeafe;
        color: #1e3a8a;
    }
    .bena-picker__day--hoje {
        box-shadow: inset 0 0 0 1.5px #003366;
        font-weight: 700;
    }
    .bena-picker__day--selecionado,
    .bena-picker__day--selecionado.bena-picker__day--fds {
        background: #003366;
        color: #ffffff;
        font-weight: 700;
    }
    .bena-picker__day--selecionado:hover {
        background: #001f3f;
        transform: scale(1.06);
    }
</style>
@endpush

@section('content')
    <div class="bena-page-header">
        <a href="{{ route('calendario.index') }}" class="bena-page-header__back">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
            Voltar para o calendário
        </a>
        <h1 class="bena-page-header__title">Novo feriado</h1>
        <p class="bena-page-header__subtitle">
            Cadastre uma data que deve ser tratada como não útil na folha de ponto.
        </p>
    </div>

    @if ($errors->any())
        <div class="bena-error-summary" role="alert">
            <p class="bena-error-summary__title">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                Corrija os erros abaixo:
            </p>
            <ul class="bena-error-summary__list">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bena-card">
        <form method="POST" action="{{ route('admin.feriados.store') }}" class="bena-form">
            @csrf

            <div class="bena-form__field">
                <label for="data" class="bena-form__label">
                    Data <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="date" id="data" name="data" value="{{ old('data') }}" required class="bena-form__input">
                <p class="bena-form__help">Clique em um dia no calendário abaixo ou digite a data.</p>
                <div id="picker" class="bena-picker" aria-label="Selecionar dia"></div>
            </div>

            <div class="bena-form__field">
                <label for="descricao" class="bena-form__label">
                    Descrição <span class="required" aria-hidden="true">*</span>
                </label>
                <input type="text" id="descricao" name="descricao" value="{{ old('descricao') }}" maxlength="200" required class="bena-form__input" placeholder="Ex: Aniversário de Rio Branco">
            </div>

            <div class="bena-form__row">
                <div class="bena-form__field">
                    <label for="tipo" class="bena-form__label">
                        Tipo <span class="required" aria-hidden="true">*</span>
                    </label>
                    <select id="tipo" name="tipo" required class="bena-form__select">
                        <option value="">Selecione…</option>
                        <option value="nacional"  @selected(old('tipo') === 'nacional')>Nacional</option>
                        <option value="estadual"  @selected(old('tipo') === 'estadual')>Estadual</option>
                        <option value="municipal" @selected(old('tipo') === 'municipal')>Municipal</option>
                        <option value="recesso"   @selected(old('tipo') === 'recesso')>Recesso</option>
                    </select>
                </div>

                <div class="bena-form__field">
                    <label for="uf" class="bena-form__label">UF</label>
                    <input type="text" id="uf" name="uf" value="{{ old('uf') }}" maxlength="2" minlength="2" pattern="[A-Za-z]{2}" placeholder="AC" class="bena-form__input" style="text-transform: uppercase;">
                    <p class="bena-form__help">Apenas para feriados estaduais.</p>
                </div>
            </div>

            <label class="bena-form__checkbox">
                <input type="checkbox" name="recorrente" value="1" @checked(old('recorrente'))>
                <span>Recorrente — repete todo ano nesta data</span>
            </label>

            <div class="bena-form__actions">
                <a href="{{ route('calendario.index') }}" class="br-button secondary">Cancelar</a>
                <button type="submit" class="br-button primary">
                    <i class="fas fa-check" aria-hidden="true"></i>
                    Cadastrar feriado
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('data');
        const picker = document.getElementById('picker');
        if (!input || !picker) return;

        const meses = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
        ];

        const tema = {
            1: '226, 232, 240', 2: '192, 132, 252', 3: '96, 165, 250',
            4: '74, 222, 128',  5: '250, 204, 21',  6: '248, 113, 113',
            7: '251, 191, 36',  8: '251, 146, 60',  9: '253, 224, 71',
            10: '244, 114, 182', 11: '56, 189, 248', 12: '239, 68, 68',
        };

        const hoje = new Date();
        const hojeISO = formatar(hoje.getFullYear(), hoje.getMonth() + 1, hoje.getDate());

        const cursor = (() => {
            if (input.value) {
                const [a, m] = input.value.split('-');
                return { ano: parseInt(a, 10), mes: parseInt(m, 10) };
            }
            return { ano: hoje.getFullYear(), mes: hoje.getMonth() + 1 };
        })();

        function formatar(ano, mes, dia) {
            return ano + '-' + String(mes).padStart(2, '0') + '-' + String(dia).padStart(2, '0');
        }

        function render() {
            const primeiro = new Date(cursor.ano, cursor.mes - 1, 1);
            const dias = new Date(cursor.ano, cursor.mes, 0).getDate();
            const offset = primeiro.getDay();
            const selecionado = input.value;

            picker.style.setProperty('--cal-theme', tema[cursor.mes]);

            const partes = [];
            partes.push(
                '<div class="bena-picker__nav">',
                '<button type="button" data-acao="prev" aria-label="Mês anterior">',
                '<i class="fas fa-chevron-left" aria-hidden="true"></i></button>',
                '<span class="bena-picker__title">' + meses[cursor.mes - 1] + ' ' + cursor.ano + '</span>',
                '<button type="button" data-acao="next" aria-label="Próximo mês">',
                '<i class="fas fa-chevron-right" aria-hidden="true"></i></button>',
                '</div>',
                '<div class="bena-picker__week" aria-hidden="true">',
                '<span class="fim">D</span><span>S</span><span>T</span>',
                '<span>Q</span><span>Q</span><span>S</span><span class="fim">S</span>',
                '</div>',
                '<div class="bena-picker__days">'
            );

            for (let i = 0; i < offset; i++) {
                partes.push('<span class="bena-picker__day bena-picker__day--vazio" aria-hidden="true"></span>');
            }

            for (let d = 1; d <= dias; d++) {
                const data = formatar(cursor.ano, cursor.mes, d);
                const dow = new Date(cursor.ano, cursor.mes - 1, d).getDay();
                const fds = dow === 0 || dow === 6;
                const sel = data === selecionado;
                const isHoje = data === hojeISO;

                const classes = ['bena-picker__day'];
                if (fds) classes.push('bena-picker__day--fds');
                if (isHoje) classes.push('bena-picker__day--hoje');
                if (sel) classes.push('bena-picker__day--selecionado');

                partes.push(
                    '<button type="button" class="' + classes.join(' ') + '" ',
                    'data-data="' + data + '" aria-label="' + data + '">',
                    d,
                    '</button>'
                );
            }
            partes.push('</div>');
            picker.innerHTML = partes.join('');
        }

        picker.addEventListener('click', function (e) {
            const target = e.target.closest('[data-acao], [data-data]');
            if (!target) return;

            if (target.dataset.acao === 'prev') {
                cursor.mes--;
                if (cursor.mes === 0) { cursor.mes = 12; cursor.ano--; }
                render();
                return;
            }
            if (target.dataset.acao === 'next') {
                cursor.mes++;
                if (cursor.mes === 13) { cursor.mes = 1; cursor.ano++; }
                render();
                return;
            }
            if (target.dataset.data) {
                input.value = target.dataset.data;
                input.dispatchEvent(new Event('change', { bubbles: true }));
                render();
            }
        });

        input.addEventListener('change', function () {
            if (input.value) {
                const [a, m] = input.value.split('-');
                cursor.ano = parseInt(a, 10);
                cursor.mes = parseInt(m, 10);
            }
            render();
        });

        render();
    })();
</script>
@endpush
