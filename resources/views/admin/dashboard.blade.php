@extends('layouts.app')

@section('title', 'Dashboard admin — Bena')

@section('content')
    <h1 style="color: var(--color-primary-default); margin: 0 0 0.25rem;">Dashboard administrativo</h1>
    <p style="color: var(--color-secondary-07); margin: 0 0 1.5rem; text-transform: capitalize;">{{ $mesAnoExtenso }}</p>

    <form method="GET" action="{{ url('/admin') }}" style="margin-bottom: 1.5rem; display: flex; gap: 0.75rem; align-items: end; flex-wrap: wrap;">
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Lotação</span>
            <select name="lotacao" style="padding: 0.4rem 0.6rem;">
                <option value="">Todas</option>
                @foreach ($lotacoes as $opt)
                    <option value="{{ $opt }}" @selected($lotacao === $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Ano</span>
            <input type="number" name="ano" value="{{ $ano }}" min="2000" max="2100" style="padding: 0.4rem 0.6rem; width: 100px;">
        </label>
        <label style="display: flex; flex-direction: column; gap: 0.25rem;">
            <span style="font-size: 0.875rem; color: var(--color-secondary-07);">Mês</span>
            <select name="mes" style="padding: 0.4rem 0.6rem;">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected($mes === $m)>{{ str_pad((string) $m, 2, '0', STR_PAD_LEFT) }}</option>
                @endforeach
            </select>
        </label>
        <label style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="liberadas" value="1" @checked($apenasLiberadas)>
            <span>Apenas liberadas para RH</span>
        </label>
        <button type="submit" class="br-button primary">Filtrar</button>
    </form>

    @if ($linhas->isEmpty())
        <p style="color: var(--color-secondary-07);">Nenhum estagiário ativo encontrado.</p>
    @else
        <table class="tre-ac-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 0.5rem;">Nome</th>
                    <th style="text-align: left; padding: 0.5rem;">Lotação</th>
                    <th style="text-align: right; padding: 0.5rem;">Horas no mês</th>
                    <th style="text-align: right; padding: 0.5rem;">Dias batidos</th>
                    <th style="text-align: left; padding: 0.5rem;">Assinatura</th>
                    <th style="text-align: left; padding: 0.5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($linhas as $linha)
                    <tr>
                        <td style="padding: 0.5rem;">{{ $linha->estagiario->nome }}</td>
                        <td style="padding: 0.5rem;">{{ $linha->estagiario->lotacao ?? '—' }}</td>
                        <td style="padding: 0.5rem; text-align: right;">{{ number_format($linha->horasMes, 2, ',', '.') }}</td>
                        <td style="padding: 0.5rem; text-align: right;">{{ $linha->diasBatidos }} dias</td>
                        <td style="padding: 0.5rem; font-size: 0.85rem;">
                            <div>estagiário {!! $linha->assinadoEstagiario ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                            <div>supervisor {!! $linha->assinadoSupervisor ? '<strong style="color:#166534;">✓</strong>' : '<span style="color:#991b1b;">✗</span>' !!}</div>
                        </td>
                        <td style="padding: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <a href="{{ route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button primary small">Ver folha</a>
                            @if ($linha->liberadaParaRh())
                                <a href="{{ route('frequencia.pdf', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $linha->estagiario->username]) }}" class="br-button small">Baixar PDF</a>
                            @endif
                            <a href="{{ route('admin.estagiarios.edit', $linha->estagiario) }}" class="br-button secondary small">Editar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
