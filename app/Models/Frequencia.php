<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\FrequenciaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Frequencia extends Model
{
    /** @use HasFactory<FrequenciaFactory> */
    use HasFactory;

    protected $table = 'frequencias';

    protected $fillable = [
        'estagiario_id',
        'data',
        'entrada',
        'saida',
        'horas',
        'ip_entrada',
        'ip_saida',
        'observacao',
        'saida_automatica',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'horas' => 'decimal:2',
            'saida_automatica' => 'boolean',
        ];
    }

    /**
     * Entrada armazenada como string 'HH:MM:SS' (compat Oracle, que não
     * tem TIME). Acessor devolve CarbonImmutable; mutator aceita string,
     * Carbon ou null.
     */
    protected function entrada(): Attribute
    {
        return $this->tempoAttribute();
    }

    protected function saida(): Attribute
    {
        return $this->tempoAttribute();
    }

    public function estagiario(): BelongsTo
    {
        return $this->belongsTo(Estagiario::class);
    }

    private function tempoAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?CarbonInterface => $value === null
                ? null
                : CarbonImmutable::createFromFormat('H:i:s', $value),
            set: fn ($value): ?string => match (true) {
                $value === null => null,
                $value instanceof CarbonInterface => $value->format('H:i:s'),
                default => (string) $value,
            },
        );
    }
}
