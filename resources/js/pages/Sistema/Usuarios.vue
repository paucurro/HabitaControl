<script setup lang="ts">
import { Form, Head, router, setLayoutProps } from '@inertiajs/vue3';
import { LogIn, MailPlus, ShieldCheck, Users } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store as suplantar } from '@/routes/sistema/suplantacion';
import { store, update } from '@/routes/sistema/usuarios';

type Administracion = { id: number; nombre: string; pivot?: { rol: string } };
type Usuario = {
    id: number;
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    administraciones: Administracion[];
    administraciones_propias: Administracion[];
};

defineProps<{
    usuarios: Usuario[];
    administraciones: Administracion[];
    invitaciones: Array<{
        id: number;
        email: string;
        tipo: string;
        expires_at: string;
        administracion: Administracion;
    }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Usuarios del sistema', href: '/sistema/usuarios' },
        ],
    },
});

setLayoutProps({
    title: 'Usuarios del sistema',
    description: 'Administradores, colaboradores y accesos',
});

function accederComo(usuario: Usuario): void {
    if (window.confirm(`¿Acceder temporalmente como ${usuario.name}?`)) {
        router.post(suplantar.url(), { user_id: usuario.id });
    }
}

function actualizarUsuario(event: Event, usuario: Usuario): void {
    const form = new FormData(event.target as HTMLFormElement);
    router.put(
        update.url(usuario.id),
        {
            role: form.get('role'),
            tipo_acceso: form.get('tipo_acceso'),
            administracion_id: form.get('administracion_id') || null,
        },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Usuarios del sistema" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <section class="rounded-xl border bg-card p-5">
            <div class="mb-5 flex items-center gap-3">
                <MailPlus class="size-5" />
                <div>
                    <h2 class="font-semibold">
                        Crear acceso mediante invitación
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        El destinatario elegirá su contraseña desde un enlace de
                        un solo uso.
                    </p>
                </div>
            </div>
            <Form
                v-bind="store.form()"
                reset-on-success
                #default="{ errors, processing }"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-5"
            >
                <div class="grid gap-2">
                    <Label for="name">Nombre</Label>
                    <Input id="name" name="name" required />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-2 lg:col-span-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input id="email" name="email" type="email" required />
                    <InputError :message="errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="tipo">Tipo de usuario</Label>
                    <select
                        id="tipo"
                        name="tipo"
                        required
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="administrador">Administrador</option>
                        <option value="subusuario">Subusuario</option>
                        <option value="autorizado">Usuario autorizado</option>
                        <option value="superusuario">Superadministrador</option>
                    </select>
                    <InputError :message="errors.tipo" />
                </div>
                <div class="grid gap-2">
                    <Label for="administracion_id">Administradora</Label>
                    <select
                        id="administracion_id"
                        name="administracion_id"
                        class="h-9 rounded-md border border-input bg-transparent px-3 text-sm"
                    >
                        <option value="">Sin asignar</option>
                        <option
                            v-for="administracion in administraciones"
                            :key="administracion.id"
                            :value="administracion.id"
                        >
                            {{ administracion.nombre }}
                        </option>
                    </select>
                    <InputError :message="errors.administracion_id" />
                </div>
                <div class="md:col-span-2 lg:col-span-5">
                    <Button type="submit" :disabled="processing"
                        ><MailPlus class="size-4" /> Enviar invitación</Button
                    >
                </div>
            </Form>
        </section>

        <section class="overflow-hidden rounded-xl border bg-card">
            <div class="flex items-center gap-3 border-b p-5">
                <Users class="size-5" />
                <h2 class="font-semibold">Usuarios registrados</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-muted/50 text-left">
                        <tr>
                            <th class="p-3">Usuario</th>
                            <th class="p-3">Tipo</th>
                            <th class="p-3">Administradoras</th>
                            <th class="p-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="usuario in usuarios" :key="usuario.id">
                            <td class="p-3">
                                <div class="font-medium">
                                    {{ usuario.name }}
                                </div>
                                <div class="text-muted-foreground">
                                    {{ usuario.email }}
                                </div>
                            </td>
                            <td v-if="usuario.id === 1" class="p-3">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-1"
                                    ><ShieldCheck class="size-3" />
                                    {{ usuario.role }}</span
                                >
                            </td>
                            <td
                                v-if="usuario.id === 1"
                                class="p-3 text-muted-foreground"
                            >
                                —
                            </td>
                            <td
                                v-if="usuario.id === 1"
                                class="p-3 text-right"
                            ></td>
                            <td v-else colspan="3" class="p-3">
                                <form
                                    class="grid items-center gap-2 lg:grid-cols-[1fr_1fr_1.4fr_auto_auto]"
                                    @submit.prevent="
                                        actualizarUsuario($event, usuario)
                                    "
                                >
                                    <select
                                        name="role"
                                        :value="usuario.role"
                                        class="h-9 rounded-md border border-input bg-transparent px-2"
                                    >
                                        <option value="administrador">
                                            Administrador
                                        </option>
                                        <option value="autorizado">
                                            Autorizado
                                        </option>
                                        <option value="superusuario">
                                            Superadministrador
                                        </option>
                                    </select>
                                    <select
                                        name="tipo_acceso"
                                        :value="
                                            usuario.administraciones_propias
                                                .length
                                                ? 'administrador'
                                                : (usuario.administraciones[0]
                                                      ?.pivot?.rol ?? 'ninguno')
                                        "
                                        class="h-9 rounded-md border border-input bg-transparent px-2"
                                    >
                                        <option value="administrador">
                                            Administrador
                                        </option>
                                        <option value="subusuario">
                                            Subusuario
                                        </option>
                                        <option value="ninguno">
                                            Sin acceso
                                        </option>
                                    </select>
                                    <select
                                        name="administracion_id"
                                        :value="
                                            usuario.administraciones[0]?.id ??
                                            usuario.administraciones_propias[0]
                                                ?.id ??
                                            ''
                                        "
                                        class="h-9 rounded-md border border-input bg-transparent px-2"
                                    >
                                        <option value="">
                                            Sin administradora
                                        </option>
                                        <option
                                            v-for="administracion in administraciones"
                                            :key="administracion.id"
                                            :value="administracion.id"
                                        >
                                            {{ administracion.nombre }}
                                        </option>
                                    </select>
                                    <Button type="submit" size="sm"
                                        >Guardar</Button
                                    >
                                    <Button
                                        v-if="usuario.email_verified_at"
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="accederComo(usuario)"
                                        ><LogIn class="size-4" /> Acceder
                                        como</Button
                                    >
                                    <span
                                        v-else
                                        class="text-center text-xs text-muted-foreground"
                                        >Invitación pendiente</span
                                    >
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section
            v-if="invitaciones.length"
            class="rounded-xl border bg-card p-5"
        >
            <h2 class="mb-4 font-semibold">Invitaciones pendientes</h2>
            <div class="grid gap-2">
                <div
                    v-for="invitacion in invitaciones"
                    :key="invitacion.id"
                    class="flex flex-wrap justify-between gap-2 rounded-md bg-muted px-3 py-2 text-sm"
                >
                    <span
                        >{{ invitacion.email }} · {{ invitacion.tipo }} ·
                        {{ invitacion.administracion.nombre }}</span
                    >
                    <span class="text-muted-foreground"
                        >Caduca
                        {{
                            new Date(invitacion.expires_at).toLocaleDateString()
                        }}</span
                    >
                </div>
            </div>
        </section>
    </main>
</template>
