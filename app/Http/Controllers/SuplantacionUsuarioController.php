<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImpersonateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuplantacionUsuarioController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function store(ImpersonateUserRequest $request): RedirectResponse
    {
        $request->session()->put('impersonator_user_id', $request->user()->id);
        Auth::login(User::query()->findOrFail($request->integer('user_id')));
        $request->session()->regenerate();

        return to_route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get('impersonator_user_id') === 1, 403);

        $request->session()->forget(['impersonator_user_id', 'selected_administracion_id']);
        Auth::login(User::query()->findOrFail(1));
        $request->session()->regenerate();

        return to_route('sistema.usuarios.index');
    }
}
