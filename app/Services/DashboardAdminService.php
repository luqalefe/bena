<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use Illuminate\Support\Collection;

class DashboardAdminService
{
    public function __construct(private readonly ConformidadeService $conformidade) {}

    /**
     * @return Collection<int, DashboardAdminLinha>
     */
    public function montar(int $ano, int $mes, ?string $setorSigla = null, bool $apenasLiberadas = false, ?string $alerta = null): Collection
    {
        $estagiarios = Estagiario::query()
            ->where('ativo', true)
            ->when($setorSigla, fn ($q) => $q->whereHas('setor', fn ($s) => $s->where('sigla', $setorSigla)))
            ->with('setor')
            ->orderBy('nome')
            ->get();

        $ids = $estagiarios->pluck('id');

        $agregados = Frequencia::query()
            ->whereIn('estagiario_id', $ids)
            ->whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->selectRaw('estagiario_id, SUM(horas) as horas, COUNT(saida) as dias')
            ->groupBy('estagiario_id')
            ->get()
            ->keyBy('estagiario_id');

        $assinaturas = Assinatura::query()
            ->whereIn('estagiario_id', $ids)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->get()
            ->groupBy('estagiario_id');

        $linhas = $estagiarios->map(function (Estagiario $e) use ($agregados, $assinaturas) {
            $linha = $agregados->get($e->id);
            $sigs = $assinaturas->get($e->id, collect());

            return new DashboardAdminLinha(
                estagiario: $e,
                horasMes: round((float) ($linha->horas ?? 0), 2),
                diasBatidos: (int) ($linha->dias ?? 0),
                assinadoEstagiario: $sigs->contains('papel', Assinatura::PAPEL_ESTAGIARIO),
                assinadoSupervisor: $sigs->contains('papel', Assinatura::PAPEL_SUPERVISOR),
                alertas: $this->conformidade->alertasParaEstagiario($e),
            );
        });

        if ($apenasLiberadas) {
            $linhas = $linhas->filter(fn (DashboardAdminLinha $l) => $l->liberadaParaRh());
        }

        if ($alerta !== null && $alerta !== '') {
            $linhas = $linhas->filter(fn (DashboardAdminLinha $l) => in_array($alerta, $l->alertas, true));
        }

        return $linhas->values();
    }
}
