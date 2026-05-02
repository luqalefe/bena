<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feriado extends Model
{
    protected $table = 'feriados';

    protected $fillable = [
        'data',
        'descricao',
        'tipo',
        'uf',
        'recorrente',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'recorrente' => 'boolean',
        ];
    }
}
