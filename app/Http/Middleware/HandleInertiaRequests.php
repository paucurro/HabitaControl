<?php

namespace App\Http\Middleware;

use App\Models\Comunidad;
use App\Models\Administracion;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $selectedAdministracionId = $user?->selectedAdministracionId();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'canManageAdministration' => $user?->managedAdministracionId() !== null,
                'canViewCommunities' => $user?->can('viewAny', Comunidad::class) ?? false,
            ],
            'administrationContext' => [
                'isSuperuser' => $user?->role === 'superusuario',
                'selectedId' => $selectedAdministracionId,
                'selectedName' => $selectedAdministracionId === null
                    ? null
                    : Administracion::query()->whereKey($selectedAdministracionId)->value('nombre'),
                'options' => $user?->role === 'superusuario'
                    ? Administracion::query()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre'])
                    : [],
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
