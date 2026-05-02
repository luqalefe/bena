<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assinatura extends Model
{
    public const PAPEL_ESTAGIARIO = 'estagiario';

    public const PAPEL_SUPERVISOR = 'supervisor';

    protected $table = 'assinaturas';

    protected $fillable = [
        'estagiario_id',
        'ano',
        'mes',
        'papel',
        'assinante_username',
        'snapshot',
        'hash',
        'assinado_em',
        'ip',
        'substituida_em',
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'mes' => 'integer',
            'assinado_em' => 'datetime',
            'substituida_em' => 'datetime',
        ];
    }

    public function estagiario(): BelongsTo
    {
        return $this->belongsTo(Estagiario::class);
    }

    protected function hashTruncado(): Attribute
    {
        return Attribute::get(fn () => substr((string) $this->hash, 0, 12));
    }
}
