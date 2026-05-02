<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EstagiarioController;
use App\Http\Controllers\Admin\FeriadoController;
use App\Http\Controllers\AssinaturaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DevSessionController;
use App\Http\Controllers\FolhaMensalController;
use App\Http\Controllers\ObservacaoController;
use App\Http\Controllers\PontoController;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Middleware\EnsureNotProduction;
use Illuminate\Support\Facades\Route;

Route::middleware('configure.session')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/ponto/entrada', [PontoController::class, 'entrada'])->name('ponto.entrada');
    Route::post('/ponto/saida', [PontoController::class, 'saida'])->name('ponto.saida');
    Route::get('/ponto/sucesso', [PontoController::class, 'sucesso'])->name('ponto.sucesso');

    Route::get('/frequencia', [FolhaMensalController::class, 'redirectMesCorrente'])
        ->name('frequencia.atual');
    Route::get('/frequencia/{ano}/{mes}', [FolhaMensalController::class, 'show'])
        ->whereNumber(['ano', 'mes'])
        ->name('frequencia.show');
    Route::get('/frequencia/{ano}/{mes}/pdf', [FolhaMensalController::class, 'pdf'])
        ->whereNumber(['ano', 'mes'])
        ->name('frequencia.pdf');
    Route::post('/frequencia/{ano}/{mes}/assinar', [AssinaturaController::class, 'assinarComoEstagiario'])
        ->whereNumber(['ano', 'mes'])
        ->name('frequencia.assinar');
    Route::post('/frequencia/{ano}/{mes}/contra-assinar', [AssinaturaController::class, 'contraAssinarComoSupervisor'])
        ->whereNumber(['ano', 'mes'])
        ->name('frequencia.contra-assinar');
    Route::post('/frequencia/{ano}/{mes}/{dia}/observacao', [ObservacaoController::class, 'salvar'])
        ->whereNumber(['ano', 'mes', 'dia'])
        ->name('frequencia.observacao');

    Route::get('/supervisor', [SupervisorDashboardController::class, 'index'])->name('supervisor.dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/feriados', [FeriadoController::class, 'index'])->name('feriados.index');
        Route::get('/feriados/criar', [FeriadoController::class, 'create'])->name('feriados.create');
        Route::post('/feriados', [FeriadoController::class, 'store'])->name('feriados.store');
        Route::get('/feriados/{feriado}/editar', [FeriadoController::class, 'edit'])->name('feriados.edit');
        Route::put('/feriados/{feriado}', [FeriadoController::class, 'update'])->name('feriados.update');
        Route::get('/feriados/{feriado}/remover', [FeriadoController::class, 'confirmDestroy'])->name('feriados.confirmDestroy');
        Route::delete('/feriados/{feriado}', [FeriadoController::class, 'destroy'])->name('feriados.destroy');

        Route::get('/estagiarios', [EstagiarioController::class, 'index'])->name('estagiarios.index');
        Route::get('/estagiarios/{estagiario}/editar', [EstagiarioController::class, 'edit'])->name('estagiarios.edit');
        Route::put('/estagiarios/{estagiario}', [EstagiarioController::class, 'update'])->name('estagiarios.update');
    });

    // Download de contrato — autorização inline no controller (admin OR self
    // OR supervisor responsável). Fica fora do prefix admin/group para não
    // ser barrado pelo $adminOnlyRouteNames do ConfigureUserSession.
    Route::get('/admin/estagiarios/{estagiario}/contrato', [EstagiarioController::class, 'contrato'])
        ->name('admin.estagiarios.contrato');
});

/*
|--------------------------------------------------------------------------
| Rotas de desenvolvimento — bloqueadas em produção
|--------------------------------------------------------------------------
| /_dev/sessao permite trocar o usuário/grupos simulado pelo
| AUTHELIA_DEV_BYPASS sem reiniciar o container. Substitui o trabalho que
| o Authelia faria em prod (escolher o usuário no momento do login).
*/
Route::middleware(EnsureNotProduction::class)->prefix('_dev')->group(function () {
    Route::get('/sessao', [DevSessionController::class, 'form'])->name('dev.sessao.form');
    Route::post('/sessao', [DevSessionController::class, 'set'])->name('dev.sessao.set');
    Route::post('/sessao/reset', [DevSessionController::class, 'reset'])->name('dev.sessao.reset');
});
