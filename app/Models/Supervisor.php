<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupervisorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supervisor extends Model
{
    /** @use HasFactory<SupervisorFactory> */
    use HasFactory;

    protected $table = 'supervisores';

    protected $fillable = [
        'nome',
        'username',
        'email',
        'lotacao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    /** @return HasMany<Estagiario, $this> */
    public function estagiarios(): HasMany
    {
        return $this->hasMany(Estagiario::class);
    }
}
