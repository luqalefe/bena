<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\RecessoEstagiarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecessoEstagiario extends Model
{
    /** @use HasFactory<RecessoEstagiarioFactory> */
    use HasFactory;

    protected $table = 'recessos_estagiario';

    protected $fillable = [
        'estagiario_id',
        'inicio',
        'fim',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'inicio' => 'date',
            'fim' => 'date',
        ];
    }

    /** @return BelongsTo<Estagiario, $this> */
    public function estagiario(): BelongsTo
    {
        return $this->belongsTo(Estagiario::class);
    }
}
