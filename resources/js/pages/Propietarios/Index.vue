<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { store, show } from '@/routes/propietarios';
defineProps<{
    propietarios: {
        data: Array<{
            id: number;
            nombre: string;
            nif?: string;
            emails?: string;
            movil?: string;
            partes_count: number;
        }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Propietarios', href: '/propietarios' }] },
});
</script>
<template>
    <Head title="Propietarios" />
    <main class="grid flex-1 gap-6 p-4 md:p-8 xl:grid-cols-[1fr_22rem]">
        <section>
            <h1 class="mb-5 text-2xl font-semibold">Propietarios</h1>
            <div class="overflow-hidden rounded-xl border bg-card">
                <Link
                    v-for="owner in propietarios.data"
                    :key="owner.id"
                    :href="show(owner.id)"
                    class="flex flex-col gap-1 border-b p-4 last:border-0 hover:bg-muted/50 md:flex-row md:items-center"
                    ><div class="flex-1">
                        <p class="font-medium">{{ owner.nombre }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ owner.nif || 'Sin NIF' }} ·
                            {{ owner.emails || owner.movil || 'Sin contacto' }}
                        </p>
                    </div>
                    <span class="text-xs text-muted-foreground"
                        >{{ owner.partes_count }} partes</span
                    ></Link
                >
                <p
                    v-if="!propietarios.data.length"
                    class="p-8 text-center text-muted-foreground"
                >
                    No hay propietarios.
                </p>
            </div>
        </section>
        <Form
            v-bind="store.form()"
            class="grid content-start gap-3 rounded-xl border bg-card p-4"
            #default="{ errors, processing }"
            ><h2 class="font-semibold">Nuevo propietario</h2>
            <input
                name="nombre"
                placeholder="Nombre *"
                class="h-10 rounded-md border bg-background px-3"
            /><input
                name="nif"
                placeholder="NIF"
                class="h-10 rounded-md border bg-background px-3"
            /><input
                name="emails"
                type="email"
                placeholder="Email"
                class="h-10 rounded-md border bg-background px-3"
            /><input
                name="movil"
                placeholder="Móvil"
                class="h-10 rounded-md border bg-background px-3"
            /><input
                name="direccion"
                placeholder="Dirección"
                class="h-10 rounded-md border bg-background px-3"
            /><span class="text-xs text-destructive">{{ errors.nombre }}</span
            ><button
                :disabled="processing"
                class="rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground"
            >
                Crear propietario
            </button></Form
        >
    </main>
</template>
