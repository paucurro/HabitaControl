<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { create } from '@/routes/comunicados';
defineProps<{
    comunicados: {
        data: Array<{
            id: number;
            asunto: string;
            estado: string;
            enviado_at?: string;
            destinatarios_count: number;
            comunidad: { nombre: string };
        }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunicados', href: '/comunicados' }] },
});
</script>
<template>
    <Head title="Comunicados" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Comunicados</h1>
            <Link
                :href="create()"
                class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm text-primary-foreground"
                ><Plus class="size-4" /> Nuevo</Link
            >
        </div>
        <div class="overflow-hidden rounded-xl border bg-card">
            <div
                v-for="item in comunicados.data"
                :key="item.id"
                class="flex flex-col gap-1 border-b p-4 last:border-0 md:flex-row md:items-center"
            >
                <div class="flex-1">
                    <p class="font-medium">{{ item.asunto }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ item.comunidad.nombre }}
                    </p>
                </div>
                <span class="text-xs"
                    >{{ item.destinatarios_count }} destinatarios ·
                    {{ item.estado }}</span
                >
            </div>
            <p
                v-if="!comunicados.data.length"
                class="p-8 text-center text-muted-foreground"
            >
                No hay comunicados.
            </p>
        </div>
    </main>
</template>
