<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\BuddySprite;
use Illuminate\View\View;

class MascotesController extends Controller
{
    public function __construct(private readonly BuddySprite $sprites) {}

    public function index(): View
    {
        $perfis = (array) config('buddies.perfis', []);

        foreach ($perfis as $slug => $perfil) {
            $perfis[$slug]['sprite'] = $this->sprites->caminho((string) $slug);
        }

        return view('mascotes.index', [
            'tiposPadrao' => (array) config('buddies.tipos', []),
            'tiposSenior' => (array) config('buddies.tipos_supervisores', []),
            'tiposLendarios' => (array) config('buddies.tipos_lendarios', []),
            'perfis' => $perfis,
        ]);
    }
}
