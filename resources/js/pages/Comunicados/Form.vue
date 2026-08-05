<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { index, store } from '@/routes/comunicados';
defineProps<{
    comunidades: Array<{ id: number; nombre: string }>;
    propietarios: Array<{ id: number; nombre: string; emails?: string }>;
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunicados', href: '/comunicados' }] },
});
</script>
<template>
    <Head title="Nuevo comunicado" />
    <main
        class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 md:p-8"
    >
        <div>
            <h1 class="text-2xl font-semibold">Nuevo comunicado</h1>
            <p class="text-sm text-muted-foreground">
                Cada propietario recibirá un mensaje individual.
            </p>
        </div>
        <Form
            v-bind="store.form()"
            class="grid gap-x-10 gap-y-7 rounded-xl border bg-card p-5 md:p-8 lg:grid-cols-2"
            #default="{ errors, processing }"
            ><label class="grid gap-1 text-sm md:col-span-2 lg:col-span-1"
                >Comunidad<select
                    name="comunidad_id"
                    class="h-10 rounded-md border bg-background px-3"
                >
                    <option
                        v-for="item in comunidades"
                        :key="item.id"
                        :value="item.id"
                    >
                        {{ item.nombre }}
                    </option>
                </select></label
            ><label class="grid gap-1 text-sm md:col-span-2 lg:col-span-1"
                >Asunto<input
                    name="asunto"
                    class="h-10 rounded-md border bg-background px-3"
                /><span class="text-xs text-destructive">{{
                    errors.asunto
                }}</span></label
            ><label class="grid gap-1 text-sm"
                >Contenido<textarea
                    name="contenido"
                    class="min-h-40 rounded-md border bg-background p-3"
                /><span class="text-xs text-destructive">{{
                    errors.contenido
                }}</span></label
            ><label class="grid gap-1 text-sm"
                >Destinatarios<select
                    name="propietario_ids[]"
                    multiple
                    class="min-h-48 rounded-md border bg-background p-2"
                >
                    <option
                        v-for="owner in propietarios"
                        :key="owner.id"
                        :value="owner.id"
                    >
                        {{ owner.nombre }} · {{ owner.emails }}
                    </option></select
                ><span class="text-xs text-muted-foreground"
                    >Ctrl/Cmd + clic para seleccionar varios.</span
                ><span class="text-xs text-destructive">{{
                    errors.propietario_ids
                }}</span></label
            >
            <div class="flex justify-end gap-3 md:col-span-2">
                <Link
                    :href="index()"
                    class="rounded-md border px-4 py-2 text-sm"
                    >Cancelar</Link
                ><button
                    :disabled="processing"
                    class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground"
                >
                    Enviar comunicado
                </button>
            </div></Form
        >
    </main>
</template>
