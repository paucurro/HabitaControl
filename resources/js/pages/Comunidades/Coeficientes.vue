<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { show as showComunidad } from '@/routes/comunidades';
import { update } from '@/routes/comunidades/coeficientes';

const props = defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    partes: Array<{ id: number; codigo: string; descripcion?: string }>;
    tiposGasto: Array<{ id: number; codigo: string; descripcion: string }>;
    coeficientes: Array<{
        parte_id: number;
        tipo_gasto_id: number;
        porcentaje: string;
    }>;
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const grid = reactive<Record<number, Record<number, string>>>({});

function formatPorcentajeInput(value: string): string {
    const porcentaje = Number(value) * 100;

    if (!Number.isFinite(porcentaje)) {
        return '0.00';
    }

    const [entero, decimales = ''] = porcentaje.toFixed(6).split('.');

    return `${entero}.${decimales.replace(/0+$/, '').padEnd(2, '0')}`;
}

function navegarConFlechas(
    event: KeyboardEvent,
    fila: number,
    columna: number,
): void {
    let filaDestino = fila;
    let columnaDestino = columna;

    if (event.key === 'ArrowUp') {
        filaDestino--;
    } else if (event.key === 'ArrowDown') {
        filaDestino++;
    } else if (event.key === 'ArrowRight') {
        columnaDestino++;
    } else {
        return;
    }

    event.preventDefault();

    const inputDestino = document.querySelector<HTMLInputElement>(
        `[data-coeficiente-fila="${filaDestino}"][data-coeficiente-columna="${columnaDestino}"]`,
    );

    inputDestino?.focus();
    inputDestino?.select();
}

for (const parte of props.partes) {
    grid[parte.id] = {};

    for (const tipo of props.tiposGasto) {
        grid[parte.id][tipo.id] = '0.00';
    }
}

for (const fila of props.coeficientes) {
    if (grid[fila.parte_id] && fila.tipo_gasto_id in grid[fila.parte_id]) {
        grid[fila.parte_id][fila.tipo_gasto_id] = formatPorcentajeInput(
            fila.porcentaje,
        );
    }
}

const totales = computed(() =>
    props.tiposGasto.map((tipo) =>
        props.partes.reduce(
            (suma, parte) => suma + (Number(grid[parte.id]?.[tipo.id]) || 0),
            0,
        ),
    ),
);

const form = useForm<{
    coeficientes: Array<{
        parte_id: number;
        tipo_gasto_id: number;
        porcentaje: number;
    }>;
}>({
    coeficientes: [],
});

function guardar() {
    form.transform(() => ({
        coeficientes: props.partes.flatMap((parte) =>
            props.tiposGasto.map((tipo) => ({
                parte_id: parte.id,
                tipo_gasto_id: tipo.id,
                porcentaje: (Number(grid[parte.id]?.[tipo.id]) || 0) / 100,
            })),
        ),
    })).put(update(props.comunidad.id).url, { preserveScroll: true });
}
</script>
<template>
    <Head title="Coeficientes" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
        >
            <div>
                <CommunityBackLink :href="showComunidad.url(comunidad.id)">
                    {{ comunidad.nombre }}
                </CommunityBackLink>
                <h1 class="mt-3 text-2xl font-semibold">
                    Coeficientes por tipo de gasto
                </h1>
                <p class="text-sm text-muted-foreground">
                    Introduce los coeficientes como porcentajes (por ejemplo,
                    13,80), sin escribir el símbolo %. Usa ↑ y ↓ para cambiar de
                    fila y → para avanzar a la siguiente columna.
                </p>
            </div>
            <Button :disabled="form.processing" @click="guardar"
                >Guardar cambios</Button
            >
        </div>

        <Card
            class="overflow-hidden py-0"
            v-if="partes.length && tiposGasto.length"
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="sticky left-0 bg-background"
                            >Parte</TableHead
                        >
                        <TableHead
                            v-for="tipo in tiposGasto"
                            :key="tipo.id"
                            class="min-w-44 py-2 text-center whitespace-normal"
                        >
                            <span class="block font-semibold"
                                >[{{ tipo.codigo }}]</span
                            >
                            <span
                                class="block text-xs font-normal text-muted-foreground"
                            >
                                {{ tipo.descripcion }}
                            </span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(parte, parteIndex) in partes"
                        :key="parte.id"
                    >
                        <TableCell
                            class="sticky left-0 bg-background font-medium"
                        >
                            {{ parte.codigo }}
                            <span
                                v-if="parte.descripcion"
                                class="text-muted-foreground"
                                >· {{ parte.descripcion }}</span
                            >
                        </TableCell>
                        <TableCell
                            v-for="(tipo, tipoIndex) in tiposGasto"
                            :key="tipo.id"
                            class="text-center"
                        >
                            <Input
                                v-model="grid[parte.id][tipo.id]"
                                :data-coeficiente-fila="parteIndex"
                                :data-coeficiente-columna="tipoIndex"
                                type="number"
                                step="0.000001"
                                min="0"
                                max="100"
                                class="mx-auto h-8 w-28 text-center"
                                @keydown="
                                    navegarConFlechas(
                                        $event,
                                        parteIndex,
                                        tipoIndex,
                                    )
                                "
                            />
                        </TableCell>
                    </TableRow>
                </TableBody>
                <TableFooter>
                    <TableRow>
                        <TableCell class="sticky left-0 bg-muted/50"
                            >Total</TableCell
                        >
                        <TableCell
                            v-for="(total, index) in totales"
                            :key="tiposGasto[index].id"
                            class="text-center tabular-nums"
                            :class="
                                Math.abs(total - 100) > 0.01
                                    ? 'font-semibold text-destructive'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ total.toFixed(2) }}%
                        </TableCell>
                    </TableRow>
                </TableFooter>
            </Table>
        </Card>
        <p
            v-else
            class="rounded-xl border bg-card p-8 text-center text-muted-foreground"
        >
            Necesitas al menos una parte y un tipo de gasto para editar
            coeficientes.
        </p>
    </main>
</template>
