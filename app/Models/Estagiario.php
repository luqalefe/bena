<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EstagiarioFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estagiario extends Model implements Authenticatable
{
    /** @use HasFactory<EstagiarioFactory> */
    use HasFactory;

    protected $table = 'estagiarios';

    protected $fillable = [
        'username',
        'nome',
        'email',
        'matricula',
        'lotacao',
        'supervisor_nome',
        'supervisor_username',
        'supervisor_id',
        'sei',
        'instituicao_ensino',
        'inicio_estagio',
        'fim_estagio',
        'prorrogacao_inicio',
        'prorrogacao_fim',
        'horas_diarias',
        'contrato_path',
        'ativo',
        'tutorial_visto_em',
        'buddy_tipo',
    ];

    protected function casts(): array
    {
        return [
            'inicio_estagio' => 'date',
            'fim_estagio' => 'date',
            'prorrogacao_inicio' => 'date',
            'prorrogacao_fim' => 'date',
            'horas_diarias' => 'decimal:2',
            'ativo' => 'boolean',
            'tutorial_visto_em' => 'datetime',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->username;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }

    /** @return HasMany<RecessoEstagiario, $this> */
    public function recessos(): HasMany
    {
        return $this->hasMany(RecessoEstagiario::class);
    }

    /** @return BelongsTo<Supervisor, $this> */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }
}
