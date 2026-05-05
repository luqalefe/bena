<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    protected $table = 'setores';

    protected $fillable = [
        'sigla',
        'quantidade_servidores',
        'ativo',
        'sincronizado_em',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_servidores' => 'integer',
            'ativo' => 'boolean',
            'sincronizado_em' => 'datetime',
        ];
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }
}
