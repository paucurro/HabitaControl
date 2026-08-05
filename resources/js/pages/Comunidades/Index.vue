<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Plus } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, index, show } from '@/routes/comunidades';

type ColumnaOrden =
    'codigo' | 'nombre' | 'nif' | 'direccion' | 'poblacion' | 'partes_count';

type DireccionOrden = 'asc' | 'desc';

type Comunidad = {
    id: number;
    codigo: string;
    nombre: string;
    nif?: string | null;
    direccion?: string | null;
    poblacion?: string | null;
    partes_count: number;
};

const props = defineProps<{
    comunidades: {
        data: Comunidad[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    orden: { columna: ColumnaOrden; direccion: DireccionOrden };
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const columnas: Array<{
    clave: ColumnaOrden;
    etiqueta: string;
    class?: string;
}> = [
    { clave: 'codigo', etiqueta: 'Código', class: 'w-32' },
    { clave: 'nombre', etiqueta: 'Nombre', class: 'min-w-56' },
    { clave: 'nif', etiqueta: 'NIF', class: 'w-36' },
    { clave: 'direccion', etiqueta: 'Dirección', class: 'min-w-64' },
    { clave: 'poblacion', etiqueta: 'Población', class: 'min-w-40' },
    { clave: 'partes_count', etiqueta: 'Partes', class: 'w-28 text-right' },
];

function direccionSiguiente(columna: ColumnaOrden): DireccionOrden {
    return props.orden.columna === columna && props.orden.direccion === 'asc'
        ? 'desc'
        : 'asc';
}

function enlaceOrden(columna: ColumnaOrden): ReturnType<typeof index> {
    return index({
        query: {
            sort: columna,
            direction: direccionSiguiente(columna),
        },
    });
}

function ariaOrden(columna: ColumnaOrden): 'ascending' | 'descending' | 'none' {
    if (props.orden.columna !== columna) {
        return 'none';
    }

    return props.orden.direccion === 'asc' ? 'ascending' : 'descending';
}

function abrirComunidad(comunidadId: number): void {
    router.visit(show(comunidadId));
}

function etiquetaPaginacion(etiqueta: string): string {
    return etiqueta.replace('&laquo;', '‹').replace('&raquo;', '›');
}
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
            <Button as-child>
                <Link :href="create()"> <Plus class="size-4" /> Nueva </Link>
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border bg-card">
            <Table class="min-w-6xl">
                <TableHeader>
                    <TableRow>
                        <TableHead
                            v-for="columna in columnas"
                            :key="columna.clave"
                            :class="columna.class"
                            :aria-sort="ariaOrden(columna.clave)"
                        >
                            <Link
                                :href="enlaceOrden(columna.clave)"
                                class="flex items-center gap-1.5 py-1 hover:text-primary"
                                :class="
                                    columna.clave === 'partes_count' &&
                                    'justify-end'
                                "
                                :aria-label="`Ordenar por ${columna.etiqueta}`"
                            >
                                <span>{{ columna.etiqueta }}</span>
                                <ArrowUp
                                    v-if="
                                        orden.columna === columna.clave &&
                                        orden.direccion === 'asc'
                                    "
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                                <ArrowDown
                                    v-else-if="
                                        orden.columna === columna.clave &&
                                        orden.direccion === 'desc'
                                    "
                                    class="size-3.5"
                                    aria-hidden="true"
                                />
                                <ArrowUpDown
                                    v-else
                                    class="size-3.5 opacity-40"
                                    aria-hidden="true"
                                />
                            </Link>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="comunidad in comunidades.data"
                        :key="comunidad.id"
                        role="link"
                        tabindex="0"
                        :aria-label="`Abrir comunidad ${comunidad.nombre}`"
                        class="cursor-pointer outline-none hover:bg-muted/50 focus-visible:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset"
                        @click="abrirComunidad(comunidad.id)"
                        @keydown.enter="abrirComunidad(comunidad.id)"
                        @keydown.space.prevent="abrirComunidad(comunidad.id)"
                    >
                        <TableCell class="font-medium whitespace-nowrap">
                            {{ comunidad.codigo }}
                        </TableCell>
                        <TableCell class="font-semibold text-brand-value">
                            {{ comunidad.nombre }}
                        </TableCell>
                        <TableCell class="whitespace-nowrap">
                            {{ comunidad.nif || '—' }}
                        </TableCell>
                        <TableCell>{{ comunidad.direccion || '—' }}</TableCell>
                        <TableCell>{{ comunidad.poblacion || '—' }}</TableCell>
                        <TableCell class="text-right">
                            <span
                                class="inline-flex rounded-full bg-muted px-3 py-1 text-xs whitespace-nowrap"
                            >
                                {{ comunidad.partes_count }}
                                {{
                                    comunidad.partes_count === 1
                                        ? 'parte'
                                        : 'partes'
                                }}
                            </span>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="!comunidades.data.length">
                        <TableCell
                            :colspan="columnas.length"
                            class="h-28 text-center text-muted-foreground"
                        >
                            Todavía no hay comunidades.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <nav
            v-if="comunidades.links.length > 3"
            aria-label="Paginación de comunidades"
            class="flex flex-wrap justify-center gap-1"
        >
            <Link
                v-for="(pagina, paginaIndex) in comunidades.links"
                :key="`${pagina.label}-${paginaIndex}`"
                :href="pagina.url || '#'"
                :class="[
                    'rounded-md border px-3 py-1.5 text-sm',
                    pagina.active && 'bg-primary text-primary-foreground',
                    !pagina.url && 'pointer-events-none opacity-50',
                ]"
            >
                {{ etiquetaPaginacion(pagina.label) }}
            </Link>
        </nav>
    </main>
</template>
