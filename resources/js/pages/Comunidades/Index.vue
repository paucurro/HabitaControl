<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, Plus } from '@lucide/vue';
import { create, show } from '@/routes/comunidades';
defineProps<{
    comunidades: {
        data: Array<{
            id: number;
            nombre: string;
            poblacion?: string;
            partes_count: number;
        }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});
</script>
<template>
    <Head title="Comunidades" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Comunidades</h1>
                <p class="text-sm text-muted-foreground">
                    Gestión de comunidades, partes y propietarios.
                </p>
            </div>
            <Link
                :href="create()"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm text-primary-foreground"
                ><Plus class="size-4" /> Nueva</Link
            >
        </div>
        <div class="overflow-hidden rounded-xl border bg-card">
            <Link
                v-for="comunidad in comunidades.data"
                :key="comunidad.id"
                :href="show(comunidad.id)"
                class="flex items-center gap-4 border-b p-4 last:border-0 hover:bg-muted/50"
                ><div class="rounded-lg bg-primary/10 p-2 text-primary">
                    <Building2 class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ comunidad.nombre }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ comunidad.poblacion || 'Sin población' }}
                    </p>
                </div>
                <span class="rounded-full bg-muted px-3 py-1 text-xs"
                    >{{ comunidad.partes_count }} partes</span
                ></Link
            >
            <p
                v-if="!comunidades.data.length"
                class="p-10 text-center text-muted-foreground"
            >
                Todavía no hay comunidades.
            </p>
        </div>
    </main>
</template>
