<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpen } from '@lucide/vue';
import { diario } from '@/routes/comunidades';

type Apunte = {
    id: number;
    fecha: string;
    numero_documento?: string;
    descripcion: string;
    debe: string;
    haber: string;
    parte?: { id: number; codigo: string };
};

const props = defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    partes: Array<{ id: number; codigo: string; descripcion?: string }>;
    filtros: { parte: number | null; apunte: number | null };
    apuntes: {
        data: Apunte[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

defineOptions({ layout: { breadcrumbs: [{ title: 'Diario' }] } });

function filtrar(event: Event): void {
    const value = (event.target as HTMLSelectElement).value;
    router.get(
        diario.url(props.comunidad.id),
        { parte: value || undefined },
        { preserveState: true },
    );
}

function importe(valor: string): string {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(valor));
}

function paginationLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>

<template>
    <Head :title="`Diario · ${comunidad.nombre}`" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-semibold">
                    <BookOpen class="size-6" />Diario
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ comunidad.codigo }} · {{ comunidad.nombre }}
                </p>
            </div>
            <label class="grid gap-1 text-xs"
                ><span>Parte</span
                ><select
                    :value="filtros.parte ?? ''"
                    class="h-9 min-w-56 rounded-md border bg-background px-2 text-sm"
                    @change="filtrar"
                >
                    <option value="">Todas las partes</option>
                    <option
                        v-for="parte in partes"
                        :key="parte.id"
                        :value="parte.id"
                    >
                        {{ parte.codigo }} · {{ parte.descripcion }}
                    </option>
                </select></label
            >
        </div>

        <div class="overflow-x-auto rounded-xl border bg-card">
            <table class="w-full min-w-4xl text-sm">
                <thead class="border-b bg-muted/40 text-left">
                    <tr>
                        <th class="p-3">Fecha</th>
                        <th class="p-3">Documento</th>
                        <th class="p-3">Parte</th>
                        <th class="p-3">Descripción</th>
                        <th class="p-3 text-right">Debe</th>
                        <th class="p-3 text-right">Haber</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="apunte in apuntes.data"
                        :key="apunte.id"
                        :class="[
                            'border-b last:border-0',
                            filtros.apunte === apunte.id &&
                                'bg-primary/10 ring-1 ring-primary/30 ring-inset',
                        ]"
                    >
                        <td class="p-3 whitespace-nowrap">
                            {{
                                new Date(apunte.fecha).toLocaleDateString(
                                    'es-ES',
                                )
                            }}
                        </td>
                        <td class="p-3">
                            {{ apunte.numero_documento || '—' }}
                        </td>
                        <td class="p-3">{{ apunte.parte?.codigo || '—' }}</td>
                        <td class="p-3">{{ apunte.descripcion }}</td>
                        <td class="p-3 text-right tabular-nums">
                            {{ importe(apunte.debe) }}
                        </td>
                        <td class="p-3 text-right tabular-nums">
                            {{ importe(apunte.haber) }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p
                v-if="!apuntes.data.length"
                class="p-10 text-center text-muted-foreground"
            >
                No hay apuntes para este filtro.
            </p>
        </div>
        <nav class="flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in apuntes.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                    'rounded-md border px-3 py-1.5 text-sm',
                    link.active && 'bg-primary text-primary-foreground',
                    !link.url && 'pointer-events-none opacity-50',
                ]"
                >{{ paginationLabel(link.label) }}</Link
            >
        </nav>
    </main>
</template>
