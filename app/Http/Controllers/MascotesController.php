<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class MascotesController extends Controller
{
    public function index(): View
    {
        return view('mascotes.index', [
            'tiposPadrao' => (array) config('buddies.tipos', []),
            'tiposSenior' => (array) config('buddies.tipos_supervisores', []),
            'tiposLendarios' => (array) config('buddies.tipos_lendarios', []),
            'perfis' => (array) config('buddies.perfis', []),
        ]);
    }
}
