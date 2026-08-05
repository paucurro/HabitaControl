<?php

use App\Http\Controllers\AdministracionContextController;
use App\Http\Controllers\AdministracionUsuarioController;
use App\Http\Controllers\Auth\SocialAuthenticationController;
use App\Http\Controllers\BusquedaGlobalController;
use App\Http\Controllers\CoeficienteController;
use App\Http\Controllers\ComunicadoController;
use App\Http\Controllers\ComunidadController;
use App\Http\Controllers\ComunidadDataController;
use App\Http\Controllers\ComunidadUsuarioController;
use App\Http\Controllers\DiarioController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\InvitacionAccesoController;
use App\Http\Controllers\ParteController;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\PropietarioInvitacionController;
use App\Http\Controllers\TipoDepositoController;
use App\Http\Controllers\TipoGastoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('cart', [App\Http\Controllers\CartController::class, 'execute_cart'])->name('cart');

Route::middleware('guest')->group(function () {
    Route::get('invitaciones/{token}', [InvitacionAccesoController::class, 'show'])->whereAlphaNumeric('token')->name('invitaciones.show');
    Route::post('invitaciones/{token}', [InvitacionAccesoController::class, 'store'])
        ->whereAlphaNumeric('token')->middleware('throttle:10,1')->name('invitaciones.store');
    Route::get('auth/{provider}/redirect', [SocialAuthenticationController::class, 'redirect'])
        ->whereIn('provider', ['google', 'apple'])
        ->middleware('throttle:10,1')
        ->name('social.redirect');
    Route::match(['get', 'post'], 'auth/{provider}/callback', [SocialAuthenticationController::class, 'callback'])
        ->whereIn('provider', ['google', 'apple'])
        ->middleware('throttle:10,1')
        ->name('social.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::put('contexto/administracion', AdministracionContextController::class)->name('contexto.administracion.update');
    Route::get('buscar', BusquedaGlobalController::class)->middleware('throttle:60,1')->name('buscar');
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('comunidades', ComunidadController::class)->parameters(['comunidades' => 'comunidad']);
    Route::post('comunidades/{comunidad}/importar', [ComunidadDataController::class, 'import'])->name('comunidades.importar');
    Route::get('comunidades/{comunidad}/exportar', [ComunidadDataController::class, 'export'])->name('comunidades.exportar');
    Route::get('comunidades/{comunidad}/etiquetas', EtiquetaController::class)->name('comunidades.etiquetas');
    Route::get('comunidades/{comunidad}/coeficientes', [CoeficienteController::class, 'index'])->name('comunidades.coeficientes');
    Route::get('comunidades/{comunidad}/diario', [DiarioController::class, 'index'])->name('comunidades.diario');
    Route::post('comunidades/{comunidad}/diario', [DiarioController::class, 'store'])->name('comunidades.diario.store');
    Route::put('comunidades/{comunidad}/diario/{tipo}/{apunte}/traspasar', [DiarioController::class, 'transfer'])
        ->whereIn('tipo', ['apuntes', 'especiales', 'obras'])
        ->whereNumber('apunte')
        ->name('comunidades.diario.transfer');
    Route::put('comunidades/{comunidad}/coeficientes', [CoeficienteController::class, 'update'])->name('comunidades.coeficientes.update');
    Route::put('comunidades/{comunidad}/partes', [ParteController::class, 'updateMany'])->name('comunidades.partes.update_many');
    Route::resource('comunidades.partes', ParteController::class)
        ->shallow()->parameters(['comunidades' => 'comunidad', 'partes' => 'parte']);
    Route::resource('comunidades.tipos-gasto', TipoGastoController::class)
        ->shallow()->except(['show', 'create', 'edit'])->parameters(['comunidades' => 'comunidad', 'tipos-gasto' => 'tipoGasto']);
    Route::resource('comunidades.tipos-deposito', TipoDepositoController::class)
        ->shallow()->except(['show', 'create', 'edit'])->parameters(['comunidades' => 'comunidad', 'tipos-deposito' => 'tipoDeposito']);
    Route::resource('propietarios', PropietarioController::class)->except(['create', 'edit']);
    Route::post('propietarios/{propietario}/invitacion', PropietarioInvitacionController::class)
        ->middleware('throttle:10,1')->name('propietarios.invitacion');
    Route::get('administracion/usuarios', [AdministracionUsuarioController::class, 'index'])->name('administracion.usuarios.index');
    Route::post('administracion/usuarios', [AdministracionUsuarioController::class, 'store'])
        ->middleware('throttle:10,1')->name('administracion.usuarios.store');
    Route::delete('administracion/usuarios/{usuario}', [AdministracionUsuarioController::class, 'destroy'])->name('administracion.usuarios.destroy');
    Route::put('administracion/usuarios/{usuario}/comunidades/{comunidad}', [ComunidadUsuarioController::class, 'update'])
        ->name('administracion.usuarios.comunidades.update');
    Route::resource('comunicados', ComunicadoController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/settings.php';
