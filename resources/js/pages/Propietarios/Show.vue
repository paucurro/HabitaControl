<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { show as showComunidad } from '@/routes/comunidades';
import {
    index as propietariosIndex,
    invitacion,
    update,
} from '@/routes/propietarios';
const props = defineProps<{
    canInvite: boolean;
    canManage: boolean;
    propietario: {
        id: number;
        nombre: string;
        nif?: string;
        emails?: string;
        movil?: string;
        telefono?: string;
        direccion?: string;
        codigo_postal?: string;
        poblacion?: string;
        provincia?: string;
        observaciones?: string;
        enviar_email: boolean;
        domiciliar_recibos: boolean;
        acceso_web: boolean;
        partes: Array<{
            id: number;
            codigo: string;
            descripcion?: string;
            comunidad: { id: number; nombre: string; codigo: string };
        }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Propietarios', href: '/propietarios' }] },
});
const fields = [
    ['nombre', 'Nombre'],
    ['nif', 'NIF'],
    ['emails', 'Emails'],
    ['movil', 'Móvil'],
    ['telefono', 'Teléfono'],
    ['direccion', 'Dirección'],
    ['codigo_postal', 'Código postal'],
    ['poblacion', 'Población'],
    ['provincia', 'Provincia'],
];

function volver(): void {
    if (window.history.length > 1) {
        window.history.back();

        return;
    }

    router.visit(propietariosIndex.url());
}

function invitar(): void {
    router.post(
        invitacion.url(props.propietario.id),
        {},
        { preserveScroll: true },
    );
}
</script>
<template>
    <Head :title="propietario.nombre" />
    <main class="grid flex-1 gap-6 p-4 md:p-8 xl:grid-cols-[1fr_24rem]">
        <section>
            <div class="flex items-center gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="cursor-pointer"
                    aria-label="Volver a la pantalla anterior"
                    title="Volver"
                    @click="volver"
                >
                    <ArrowLeft class="size-5" />
                </Button>
                <h1 class="text-2xl font-semibold">
                    {{ propietario.nombre }}
                </h1>
            </div>
            <p class="mb-5 text-sm text-muted-foreground">
                Ficha global en todas las comunidades
            </p>
            <div class="overflow-hidden rounded-xl border bg-card">
                <div class="border-b p-4 font-semibold">
                    Comunidades y partes
                </div>
                <Link
                    v-for="parte in propietario.partes"
                    :key="parte.id"
                    :href="canManage ? showComunidad(parte.comunidad.id) : '#'"
                    :class="[
                        'flex items-center border-b p-4 last:border-0',
                        canManage ? 'hover:bg-muted/50' : 'pointer-events-none',
                    ]"
                    ><div class="flex-1">
                        <p class="font-medium">{{ parte.comunidad.nombre }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ parte.comunidad.codigo }}
                        </p>
                    </div>
                    <span class="rounded-full bg-muted px-3 py-1 text-xs"
                        >{{ parte.codigo }} · {{ parte.descripcion }}</span
                    ></Link
                >
                <p
                    v-if="!propietario.partes.length"
                    class="p-8 text-center text-muted-foreground"
                >
                    No tiene partes relacionadas.
                </p>
            </div>
        </section>
        <Form
            v-if="canManage"
            v-bind="update.form(propietario.id)"
            class="grid content-start gap-3 rounded-xl border bg-card p-4"
            #default="{ errors, processing }"
            ><h2 class="font-semibold">Datos de contacto</h2>
            <label
                v-for="field in fields"
                :key="field[0]"
                class="grid gap-1 text-xs"
                ><span>{{ field[1] }}</span
                ><input
                    :name="field[0]"
                    :value="(props.propietario as any)[field[0]] ?? ''"
                    class="h-9 rounded-md border bg-background px-3 text-sm"
                /><span class="text-destructive">{{
                    errors[field[0]]
                }}</span></label
            ><label class="flex items-center gap-2 text-sm"
                ><input type="hidden" name="enviar_email" value="0" /><input
                    type="checkbox"
                    name="enviar_email"
                    value="1"
                    :checked="propietario.enviar_email"
                />
                Enviar email</label
            ><label class="flex items-center gap-2 text-sm"
                ><input
                    type="hidden"
                    name="domiciliar_recibos"
                    value="0"
                /><input
                    type="checkbox"
                    name="domiciliar_recibos"
                    value="1"
                    :checked="propietario.domiciliar_recibos"
                />
                Domiciliar recibos</label
            ><button
                :disabled="processing"
                class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground"
            >
                Guardar cambios
            </button>
            <button
                v-if="canInvite && propietario.emails"
                type="button"
                class="rounded-md border px-4 py-2 text-sm hover:bg-muted"
                @click="invitar"
            >
                {{
                    propietario.acceso_web
                        ? 'Reenviar invitación de acceso'
                        : 'Invitar al acceso web'
                }}
            </button></Form
        >
        <aside
            v-else
            class="grid content-start gap-3 rounded-xl border bg-card p-4"
        >
            <h2 class="font-semibold">Datos de contacto</h2>
            <div v-for="field in fields" :key="field[0]" class="grid gap-1">
                <span class="text-xs text-muted-foreground">{{
                    field[1]
                }}</span>
                <span class="text-sm">{{
                    (props.propietario as any)[field[0]] || '—'
                }}</span>
            </div>
        </aside>
    </main>
</template>
