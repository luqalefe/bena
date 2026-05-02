<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controller para trocar o usuário/grupos simulado em runtime durante o
 * desenvolvimento. Substitui o Authelia local — sem container, sem TOTP.
 *
 * Bloqueado em produção pelo middleware de rota (ver routes/web.php).
 */
class DevSessionController extends Controller
{
    public function form(Request $request): View
    {
        return view('dev.sessao', [
            'atual' => (array) $request->session()->get('dev_session', []),
            'defaults' => [
                'username' => config('authelia.dev_user'),
                'groups' => config('authelia.dev_groups'),
                'nome' => config('authelia.dev_name'),
                'email' => config('authelia.dev_email'),
            ],
        ]);
    }

    public function set(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'username' => ['required', 'string', 'max:100'],
            'groups' => ['required', 'string', 'max:200'],
            'nome' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:200'],
        ]);

        $request->session()->put('dev_session', $dados);

        return redirect('/')->with('status', "Sessão simulada: {$dados['username']} ({$dados['groups']}).");
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget('dev_session');

        return redirect('/')->with('status', 'Sessão simulada resetada para o default do .env.');
    }
}
