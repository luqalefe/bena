<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\BuddyService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(private readonly BuddyService $buddy) {}

    public function show(Request $request): View
    {
        $usuario = $request->user();
        $buddyData = null;
        $grupo = (string) session('grupodeacesso');

        // A apresentação do mascote aparece pra todos os grupos no /bem-vindo,
        // independente de admin/supervisor/estagiário. No dashboard de cada
        // grupo é que a regra muda — buddy fica visível só no dashboard do
        // estagiário (grupo 'E'). Supervisores e admin recebem buddy do pool
        // sênior (águia, leão, elefante, urso).
        if ($usuario instanceof Estagiario) {
            $this->buddy->garantirBuddy($usuario, $grupo);
            $buddyData = $this->buddy->boasVindas($usuario);
        }

        return view('onboarding.show', [
            'buddy' => $buddyData,
            'passos' => $this->passosPara($grupo),
        ]);
    }

    /**
     * @return array<int, array{icone: string, titulo: string, texto: string}>
     */
    private function passosPara(string $grupo): array
    {
        return match ($grupo) {
            '0' => [
                [
                    'icone' => 'fa-users',
                    'titulo' => 'Cadastrar estagiários e supervisores',
                    'texto' => 'Mantenha o cadastro de quem usa o sistema. Importação CSV institucional disponível pra trazer a lista do RH em massa.',
                ],
                [
                    'icone' => 'fa-calendar-day',
                    'titulo' => 'Feriados e recessos do calendário',
                    'texto' => 'Configure feriados nacionais, estaduais e recessos do tribunal. O sistema bloqueia ponto nesses dias e destaca na folha mensal.',
                ],
                [
                    'icone' => 'fa-shield-alt',
                    'titulo' => 'Auditoria de ações sensíveis',
                    'texto' => 'Log append-only de bater ponto, assinar, contra-assinar e edições administrativas. Filtros por usuário, ação e período.',
                ],
                [
                    'icone' => 'fa-chart-line',
                    'titulo' => 'Acompanhar conformidade',
                    'texto' => 'Painel com estagiários ativos, supervisores responsáveis e indicadores de conformidade com a Lei 11.788/2008.',
                ],
            ],
            'S' => [
                [
                    'icone' => 'fa-bell',
                    'titulo' => 'Receba aviso quando a folha for assinada',
                    'texto' => 'Quando o estagiário fechar e assinar a folha do mês, ela aparece no seu painel pra revisão.',
                ],
                [
                    'icone' => 'fa-search',
                    'titulo' => 'Revisar a folha mensal',
                    'texto' => 'Confira horas batidas, feriados, fins de semana e observações do estagiário antes de contra-assinar. Tudo na mesma tela, sem trocar de aba.',
                ],
                [
                    'icone' => 'fa-pen-fancy',
                    'titulo' => 'Contra-assinar pelo sistema',
                    'texto' => 'Sua contra-assinatura gera um hash SHA-256 + carimbo de tempo, no modelo do SEI. O RH baixa o PDF assinado e anexa direto no processo.',
                ],
            ],
            default => [
                [
                    'icone' => 'fa-clock',
                    'titulo' => 'Bater ponto pelo navegador',
                    'texto' => 'Entrada e saída diretamente da tela inicial. Sem planilha, sem papel. O sistema já calcula as horas trabalhadas.',
                ],
                [
                    'icone' => 'fa-calendar-alt',
                    'titulo' => 'Folha mensal automática',
                    'texto' => 'Veja o mês inteiro com horas calculadas, feriados destacados e fins de semana classificados. Adicione observações em dias específicos.',
                ],
                [
                    'icone' => 'fa-pen-fancy',
                    'titulo' => 'Assinatura digital ao final do mês',
                    'texto' => 'Quando o mês fechar, você assina sua folha eletronicamente. O sistema gera um hash SHA-256 do conteúdo + carimbo de tempo, no modelo do SEI, sem necessidade de certificado físico.',
                ],
                [
                    'icone' => 'fa-magic',
                    'titulo' => 'Esqueceu de bater saída?',
                    'texto' => 'O sistema fecha automaticamente após sua jornada (5h por padrão). Aparece marcado como "auto" para você saber que foi auto-fechamento.',
                ],
            ],
        };
    }

    public function concluir(Request $request): RedirectResponse
    {
        /** @var Estagiario $usuario */
        $usuario = $request->user();
        $usuario->tutorial_visto_em = CarbonImmutable::now();
        $usuario->save();

        return redirect()->route('dashboard');
    }
}
