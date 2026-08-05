<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComunicadoRequest;
use App\Models\Comunicado;
use App\Models\Comunidad;
use App\Models\Propietario;
use App\Notifications\ComunicadoIndividualNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ComunicadoController extends Controller
{
    public function index(): Response
    {
        $comunidades = Comunidad::query()->visibleTo(request()->user());

        return Inertia::render('Comunicados/Index', [
            'comunicados' => Comunicado::query()->whereIn('comunidad_id', (clone $comunidades)->select('id'))->with('comunidad:id,nombre')->withCount('destinatarios')->latest()->paginate(20),
            'comunidades' => $comunidades->select('id', 'nombre')->orderBy('nombre')->get(),
        ]);
    }

    public function create(): Response
    {
        $comunidades = Comunidad::query()->visibleTo(request()->user());

        return Inertia::render('Comunicados/Form', [
            'comunidades' => $comunidades->select('id', 'nombre')->orderBy('nombre')->get(),
            'propietarios' => Propietario::query()->visibleTo(request()->user())->select('id', 'nombre', 'emails')->where('enviar_email', true)->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreComunicadoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $comunidad = Comunidad::findOrFail($data['comunidad_id']);
        Gate::authorize('update', $comunidad);
        $propietarios = Propietario::query()
            ->visibleTo($request->user())
            ->whereHas('partes', fn ($query) => $query->where('comunidad_id', $comunidad->id))
            ->whereKey($data['propietario_ids'])
            ->get();

        $comunicado = DB::transaction(function () use ($data, $propietarios, $request): Comunicado {
            $comunicado = Comunicado::create(['comunidad_id' => $data['comunidad_id'], 'creado_por_user_id' => $request->user()->id, 'asunto' => $data['asunto'], 'contenido' => $data['contenido'], 'estado' => 'enviado', 'enviado_at' => now()]);
            foreach ($propietarios as $propietario) {
                $email = $propietario->emailPrincipal();
                if ($email === null) {
                    continue;
                }
                $comunicado->destinatarios()->create(['propietario_id' => $propietario->id, 'email' => $email, 'estado' => 'enviado', 'enviado_at' => now()]);
                Notification::route('mail', [$email => $propietario->nombre])->notify(new ComunicadoIndividualNotification($comunicado->loadMissing('comunidad'), $propietario));
            }

            return $comunicado;
        });

        $this->flashSuccess("Comunicado preparado para {$comunicado->destinatarios()->count()} destinatarios.");

        return to_route('comunicados.index');
    }
}
