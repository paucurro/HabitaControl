<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Percent, Trash2 } from '@lucide/vue';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { coeficientes } from '@/routes/comunidades';
import { index as partesIndex } from '@/routes/comunidades/partes';
import { destroy, edit } from '@/routes/partes';
import { show as showPropietario } from '@/routes/propietarios';

const props = defineProps<{
    parte: {
        id: number;
        codigo: string;
        descripcion?: string;
        deposito?: string;
        coeficiente_general?: string;
        orden?: string;
        tomo?: string;
        libro?: string;
        folio?: string;
        finca?: string;
        observaciones?: string;
        comunidad: { id: number; codigo: string; nombre: string };
        tipo_deposito?: { id: number; nombre: string } | null;
        propietarios: Array<{ id: number; nombre: string; nif?: string }>;
        coeficientes: Array<{
            id: number;
            porcentaje: string;
            tipo_gasto: { id: number; codigo: string; descripcion: string };
        }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const detailFields = [
    ['orden', 'Orden'],
    ['tomo', 'Tomo'],
    ['libro', 'Libro'],
    ['folio', 'Folio'],
    ['finca', 'Finca'],
] as const;

function eliminar() {
    if (confirm('¿Archivar esta parte?')) {
        router.delete(destroy(props.parte.id).url);
    }
}
</script>
<template>
    <Head :title="parte.codigo" />
    <main
        class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8"
    >
        <div
            class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
        >
            <div>
                <CommunityBackLink :href="partesIndex.url(parte.comunidad.id)">
                    Partes y propietarios · {{ parte.comunidad.nombre }}
                </CommunityBackLink>
                <h1 class="mt-3 text-2xl font-semibold">
                    {{ parte.codigo }}
                    <span
                        v-if="parte.descripcion"
                        class="font-normal text-muted-foreground"
                        >· {{ parte.descripcion }}</span
                    >
                </h1>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link :href="edit(parte.id)"
                        ><Pencil class="size-4" /> Editar</Link
                    >
                </Button>
                <Button
                    variant="outline"
                    class="text-destructive"
                    @click="eliminar"
                    ><Trash2 class="size-4" /> Archivar</Button
                >
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <Card>
                <CardContent class="grid gap-3">
                    <h2 class="font-semibold">Datos de la parte</h2>
                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <dt class="text-muted-foreground">Tipo de depósito</dt>
                        <dd>{{ parte.tipo_deposito?.nombre ?? '—' }}</dd>
                        <dt class="text-muted-foreground">Depósito</dt>
                        <dd>{{ parte.deposito ?? '—' }}</dd>
                        <dt class="text-muted-foreground">
                            Coeficiente general
                        </dt>
                        <dd>{{ parte.coeficiente_general ?? '—' }}%</dd>
                        <template v-for="field in detailFields" :key="field[0]">
                            <dt class="text-muted-foreground">
                                {{ field[1] }}
                            </dt>
                            <dd>{{ parte[field[0]] || '—' }}</dd>
                        </template>
                    </dl>
                    <template v-if="parte.observaciones">
                        <h3 class="mt-2 text-sm font-medium">Observaciones</h3>
                        <p
                            class="text-sm whitespace-pre-line text-muted-foreground"
                        >
                            {{ parte.observaciones }}
                        </p>
                    </template>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="grid gap-3">
                    <h2 class="font-semibold">Propietarios</h2>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="owner in parte.propietarios"
                            :key="owner.id"
                            :href="showPropietario(owner.id)"
                            class="rounded-full bg-muted px-3 py-1 text-xs hover:bg-muted/70"
                            >{{ owner.nombre }}</Link
                        >
                        <span
                            v-if="!parte.propietarios.length"
                            class="text-xs text-amber-600"
                            >Sin propietario asignado</span
                        >
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="overflow-hidden py-0">
            <div class="flex items-center justify-between border-b p-4">
                <h2 class="font-semibold">Coeficientes por tipo de gasto</h2>
                <Button size="sm" variant="outline" as-child>
                    <Link :href="coeficientes(parte.comunidad.id)"
                        ><Percent class="size-4" /> Editar coeficientes</Link
                    >
                </Button>
            </div>
            <Table v-if="parte.coeficientes.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Tipo de gasto</TableHead>
                        <TableHead>Porcentaje</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="coeficiente in parte.coeficientes"
                        :key="coeficiente.id"
                    >
                        <TableCell
                            >{{ coeficiente.tipo_gasto.codigo }} ·
                            {{ coeficiente.tipo_gasto.descripcion }}</TableCell
                        >
                        <TableCell>{{ coeficiente.porcentaje }}%</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-else class="p-8 text-center text-muted-foreground">
                Todavía no tiene coeficientes asignados.
            </p>
        </Card>
    </main>
</template>
