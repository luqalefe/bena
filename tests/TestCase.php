<?php

namespace Tests;

use App\Models\Setor;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setorId(string $sigla): int
    {
        return Setor::firstOrCreate(['sigla' => $sigla], ['ativo' => true])->id;
    }
}
