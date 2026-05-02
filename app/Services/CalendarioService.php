<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Feriado;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class CalendarioService
{
    public function ehDiaUtil(CarbonInterface $data): bool
    {
        if ($data->isWeekend()) {
            return false;
        }

        return ! $this->ehFeriado($data);
    }

    public function ehFeriado(CarbonInterface $data): bool
    {
        return Feriado::query()
            ->where(function ($q) use ($data) {
                $q->whereDate('data', $data->format('Y-m-d'))
                    ->orWhere(function ($q2) use ($data) {
                        $q2->where('recorrente', true)
                            ->whereMonth('data', $data->month)
                            ->whereDay('data', $data->day);
                    });
            })
            ->exists();
    }

    public function feriadosDoAno(int $ano, ?string $tipo = null): Collection
    {
        $query = Feriado::query()
            ->where(function ($q) use ($ano) {
                $q->whereYear('data', $ano)
                    ->orWhere('recorrente', true);
            });

        if ($tipo !== null) {
            $query->where('tipo', $tipo);
        }

        return $query->get()
            ->map(function (Feriado $feriado) use ($ano) {
                if ($feriado->recorrente) {
                    $feriado->data = $feriado->data->setYear($ano);
                }

                return $feriado;
            })
            ->sortBy(fn (Feriado $feriado) => $feriado->data->format('m-d'))
            ->values();
    }
}
