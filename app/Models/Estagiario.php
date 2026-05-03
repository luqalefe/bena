<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EstagiarioFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'sei',
        'inicio_estagio',
        'fim_estagio',
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
}
