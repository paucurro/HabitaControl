<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Trash2, UserPlus, Users } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy, store } from '@/routes/administracion/usuarios';
import { update } from '@/routes/administracion/usuarios/comunidades';

type Asignacion = {
    id: number;
    pivot: { puede_ver: boolean; puede_gestionar: boolean };
};

type Usuario = {
    id: number;
    name: string;
    email: string;
    comunidades_asignadas: Asignacion[];
};

defineProps<{
    administracion: { id: number; nombre: string };
    usuarios: Usuario[];
    comunidades: Array<{ id: number; nombre: string }>;
    invitaciones: Array<{ id: number; email: string; expires_at: string }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Usuarios', href: '/administracion/usuarios' }],
    },
});

function asignacion(
    usuario: Usuario,
    comunidadId: number,
): Asignacion | undefined {
    return usuario.comunidades_asignadas.find(
        (item) => item.id === comunidadId,
    );
}

function actualizar(
    usuario: Usuario,
    comunidadId: number,
    campo: 'puede_ver' | 'puede_gestionar',
    valor: boolean,
): void {
    const actual = asignacion(usuario, comunidadId)?.pivot;
    const datos = {
        puede_ver: actual?.puede_ver ?? false,
        puede_gestionar: actual?.puede_gestionar ?? false,
        [campo]: valor,
    };

    router.put(
        update.url({ usuario: usuario.id, comunidad: comunidadId }),
        datos,
        { preserveScroll: true },
    );
}

function eliminar(usuario: Usuario): void {
    if (window.confirm(`¿Eliminar el acceso de ${usuario.name}?`)) {
        router.delete(destroy.url(usuario.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Usuarios y permisos" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <h1 class="text-2xl font-semibold">Usuarios y permisos</h1>
            <p class="text-sm text-muted-foreground">
                {{ administracion.nombre }}
            </p>
        </div>

        <section
            class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-[1fr_auto] md:items-end"
        >
            <Form
                v-bind="store.form()"
                reset-on-success
                #default="{ errors, processing }"
                class="contents"
            >
                <div class="grid gap-2">
                    <Label for="email">Invitar subusuario</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        required
                        placeholder="persona@empresa.com"
                    />
                    <InputError :message="errors.email" />
                </div>
                <Button type="submit" :disabled="processing"
                    ><UserPlus class="size-4" /> Enviar invitación</Button
                >
            </Form>
        </section>

        <section
            v-if="invitaciones.length"
            class="rounded-xl border bg-card p-4"
        >
            <h2 class="mb-3 font-semibold">Invitaciones pendientes</h2>
            <div class="grid gap-2 text-sm">
                <div
                    v-for="invitacion in invitaciones"
                    :key="invitacion.id"
                    class="flex justify-between gap-4 rounded-lg bg-muted/50 px-3 py-2"
                >
                    <span>{{ invitacion.email }}</span
                    ><span class="text-muted-foreground"
                        >Caduca
                        {{
                            new Date(invitacion.expires_at).toLocaleDateString()
                        }}</span
                    >
                </div>
            </div>
        </section>

        <section class="overflow-x-auto rounded-xl border bg-card">
            <div
                v-if="!usuarios.length"
                class="grid place-items-center gap-2 p-10 text-center text-muted-foreground"
            >
                <Users class="size-8" />
                <p>Todavía no hay subusuarios activos.</p>
            </div>
            <table v-else class="w-full min-w-3xl text-sm">
                <thead class="border-b bg-muted/40 text-left">
                    <tr>
                        <th class="p-3">Usuario</th>
                        <th
                            v-for="comunidad in comunidades"
                            :key="comunidad.id"
                            class="p-3 text-center"
                        >
                            {{ comunidad.nombre }}
                        </th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="usuario in usuarios"
                        :key="usuario.id"
                        class="border-b last:border-0"
                    >
                        <td class="p-3">
                            <p class="font-medium">{{ usuario.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ usuario.email }}
                            </p>
                        </td>
                        <td
                            v-for="comunidad in comunidades"
                            :key="comunidad.id"
                            class="p-3"
                        >
                            <div class="flex justify-center gap-3">
                                <label class="flex items-center gap-1"
                                    ><input
                                        type="checkbox"
                                        :checked="
                                            asignacion(usuario, comunidad.id)
                                                ?.pivot.puede_ver ?? false
                                        "
                                        @change="
                                            actualizar(
                                                usuario,
                                                comunidad.id,
                                                'puede_ver',
                                                (
                                                    $event.target as HTMLInputElement
                                                ).checked,
                                            )
                                        "
                                    />
                                    Ver</label
                                ><label class="flex items-center gap-1"
                                    ><input
                                        type="checkbox"
                                        :checked="
                                            asignacion(usuario, comunidad.id)
                                                ?.pivot.puede_gestionar ?? false
                                        "
                                        @change="
                                            actualizar(
                                                usuario,
                                                comunidad.id,
                                                'puede_gestionar',
                                                (
                                                    $event.target as HTMLInputElement
                                                ).checked,
                                            )
                                        "
                                    />
                                    Gestionar</label
                                >
                            </div>
                        </td>
                        <td class="p-3 text-right">
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                title="Eliminar acceso"
                                @click="eliminar(usuario)"
                                ><Trash2 class="size-4 text-destructive"
                            /></Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</template>
