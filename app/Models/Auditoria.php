<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro append-only de auções sensíveis. Por convenção, a app
 * **nunca** chama update() ou delete() neste modelo — só insert via
 * AuditoriaService::registrar(). Logs antigos podem ser arquivados
 * fora da app por DBA.
 */
class Auditoria extends Model
{
    protected $table = 'auditoria';

    public $timestamps = false;

    protected $fillable = [
        'usuario_username',
        'acao',
        'entidade',
        'entidade_id',
        'payload',
        'ip',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
