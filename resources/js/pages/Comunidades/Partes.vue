<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Save, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { updateMany } from '@/actions/App/Http/Controllers/ParteController';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { show as showComunidad } from '@/routes/comunidades';
import { create as createParte } from '@/routes/comunidades/partes';
import { show as showParte } from '@/routes/partes';
import { show as showPropietario } from '@/routes/propietarios';

type Propietario = {
    id: number;
    nombre: string;
    nif?: string | null;
    movil?: string | null;
    telefono?: string | null;
};

type Parte = {
    id: number;
    codigo: string;
    descripcion?: string | null;
    coeficiente_general: string;
    propietarios: Propietario[];
};

type ParteEditable = {
    id: number;
    codigo: string;
    descripcion: string;
    coeficiente_general: string;
    propietario_ids: number[];
};

const props = defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    partes: Parte[];
    propietarios: Propietario[];
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const editandoMasivamente = ref(false);
const partesEditables = ref<ParteEditable[]>([]);
const form = useForm<{ partes: ParteEditable[] }>({ partes: [] });

const coeficientesActuales = computed(() =>
    editandoMasivamente.value
        ? partesEditables.value.map((parte) => parte.coeficiente_general)
        : props.partes.map((parte) => parte.coeficiente_general),
);

const totalCoeficiente = computed(() =>
    coeficientesActuales.value.reduce(
        (total, coeficiente) => total + Number(coeficiente || 0),
        0,
    ),
);

const totalCoeficienteEsValido = computed(
    () => Math.abs(totalCoeficiente.value - 100) < 0.0001,
);

const decimalesCoeficiente = computed(() =>
    Math.min(
        8,
        Math.max(
            2,
            ...coeficientesActuales.value.map((coeficiente) => {
                const [, decimals = ''] = String(coeficiente).split('.');

                return decimals.replace(/0+$/, '').length;
            }),
        ),
    ),
);

const formateadorCoeficiente = computed(
    () =>
        new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: decimalesCoeficiente.value,
            maximumFractionDigits: decimalesCoeficiente.value,
        }),
);

const totalCoeficienteFormateado = computed(() =>
    formateadorCoeficiente.value.format(totalCoeficiente.value),
);

function crearPartesEditables(): ParteEditable[] {
    return props.partes.map((parte) => ({
        id: parte.id,
        codigo: parte.codigo,
        descripcion: parte.descripcion ?? '',
        coeficiente_general: parte.coeficiente_general,
        propietario_ids: parte.propietarios.map(
            (propietario) => propietario.id,
        ),
    }));
}

function empezarEdicionMasiva(): void {
    form.clearErrors();
    partesEditables.value = crearPartesEditables();
    editandoMasivamente.value = true;
}

function cancelarEdicionMasiva(): void {
    form.clearErrors();
    editandoMasivamente.value = false;
}

function guardarMasivamente(): void {
    form.partes = partesEditables.value.map((parte) => ({
        ...parte,
        propietario_ids: [...parte.propietario_ids],
    }));

    form.put(updateMany(props.comunidad.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            editandoMasivamente.value = false;
            form.reset();
        },
    });
}

function errorParte(
    parteIndex: number,
    campo: keyof ParteEditable,
): string | undefined {
    return (form.errors as Record<string, string>)[
        `partes.${parteIndex}.${campo}`
    ];
}

function formatCoeficiente(value: string | number): string {
    return formateadorCoeficiente.value.format(Number(value) || 0);
}

function telefonoPropietario(propietario: Propietario): string | null {
    return propietario.movil?.trim() || propietario.telefono?.trim() || null;
}

function goToParte(parteId: number): void {
    if (editandoMasivamente.value) {
        return;
    }

    router.visit(showParte.url(parteId));
}
</script>

<template>
    <Head :title="`Partes · ${comunidad.nombre}`" />

    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <CommunityBackLink :href="showComunidad.url(comunidad.id)">
                {{ comunidad.nombre }}
            </CommunityBackLink>
            <div
                class="mt-3 flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
            >
                <div>
                    <h1 class="text-2xl font-semibold">
                        Partes y propietarios
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{
                            editandoMasivamente
                                ? 'Edita los datos directamente en la tabla y guarda todos los cambios.'
                                : 'Consulta las partes de la comunidad y sus propietarios.'
                        }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        v-if="!editandoMasivamente && partes.length"
                        type="button"
                        variant="outline"
                        @click="empezarEdicionMasiva"
                    >
                        <Pencil class="size-4" /> Editar masivamente
                    </Button>
                    <Button
                        v-if="editandoMasivamente"
                        type="button"
                        variant="outline"
                        :disabled="form.processing"
                        @click="cancelarEdicionMasiva"
                    >
                        <X class="size-4" /> Cancelar
                    </Button>
                    <Button
                        v-if="editandoMasivamente"
                        type="button"
                        :disabled="form.processing"
                        @click="guardarMasivamente"
                    >
                        <Save class="size-4" />
                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Guardar masivamente'
                        }}
                    </Button>
                    <Button as-child>
                        <Link :href="createParte(comunidad.id)">
                            <Plus class="size-4" /> Nueva parte
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <Card class="overflow-hidden py-0">
            <Table v-if="partes.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Código</TableHead>
                        <TableHead>Descripción</TableHead>
                        <TableHead>Propietarios</TableHead>
                        <TableHead class="text-right">
                            <span
                                class="flex items-baseline justify-end gap-1.5"
                            >
                                <span>Coeficiente</span>
                                <span
                                    class="text-[10px] font-medium whitespace-nowrap"
                                    :class="
                                        totalCoeficienteEsValido
                                            ? 'text-emerald-600'
                                            : 'text-destructive'
                                    "
                                >
                                    Total {{ totalCoeficienteFormateado }}%
                                </span>
                            </span>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="(parte, parteIndex) in partes"
                        :key="parte.id"
                        :role="editandoMasivamente ? undefined : 'link'"
                        :tabindex="editandoMasivamente ? undefined : 0"
                        :aria-label="
                            editandoMasivamente
                                ? undefined
                                : `Abrir parte ${parte.codigo}`
                        "
                        class="outline-none"
                        :class="
                            editandoMasivamente
                                ? 'align-top'
                                : 'cursor-pointer hover:bg-muted/50 focus-visible:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset'
                        "
                        @click="goToParte(parte.id)"
                        @keydown.enter="goToParte(parte.id)"
                        @keydown.space.prevent="goToParte(parte.id)"
                    >
                        <TableCell class="font-semibold text-brand-value">
                            <div v-if="editandoMasivamente" class="grid gap-1">
                                <Input
                                    v-model="partesEditables[parteIndex].codigo"
                                    :aria-label="`Código de la parte ${parte.codigo}`"
                                    :aria-invalid="
                                        !!errorParte(parteIndex, 'codigo')
                                    "
                                    class="min-w-32"
                                    @click.stop
                                    @keydown.stop
                                />
                                <span
                                    v-if="errorParte(parteIndex, 'codigo')"
                                    class="text-xs font-normal text-destructive"
                                >
                                    {{ errorParte(parteIndex, 'codigo') }}
                                </span>
                            </div>
                            <template v-else>
                                {{ parte.codigo }} [{{ parte.id }}]
                            </template>
                        </TableCell>
                        <TableCell>
                            <div v-if="editandoMasivamente" class="grid gap-1">
                                <Input
                                    v-model="
                                        partesEditables[parteIndex].descripcion
                                    "
                                    :aria-label="`Descripción de la parte ${parte.codigo}`"
                                    :aria-invalid="
                                        !!errorParte(parteIndex, 'descripcion')
                                    "
                                    class="min-w-48"
                                    @click.stop
                                    @keydown.stop
                                />
                                <span
                                    v-if="errorParte(parteIndex, 'descripcion')"
                                    class="text-xs text-destructive"
                                >
                                    {{ errorParte(parteIndex, 'descripcion') }}
                                </span>
                            </div>
                            <template v-else>
                                {{ parte.descripcion ?? '—' }}
                            </template>
                        </TableCell>
                        <TableCell>
                            <div
                                v-if="editandoMasivamente"
                                class="grid min-w-64 gap-1"
                            >
                                <select
                                    v-model="
                                        partesEditables[parteIndex]
                                            .propietario_ids
                                    "
                                    multiple
                                    :size="
                                        Math.min(
                                            Math.max(propietarios.length, 2),
                                            4,
                                        )
                                    "
                                    :aria-label="`Propietarios de la parte ${parte.codigo}`"
                                    :aria-invalid="
                                        !!errorParte(
                                            parteIndex,
                                            'propietario_ids',
                                        )
                                    "
                                    class="w-full rounded-md border border-input bg-transparent px-2 py-1 text-sm text-brand-value shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    @click.stop
                                    @keydown.stop
                                >
                                    <option
                                        v-for="propietario in propietarios"
                                        :key="propietario.id"
                                        :value="propietario.id"
                                    >
                                        {{ propietario.nombre }}
                                        {{
                                            propietario.nif
                                                ? `· ${propietario.nif}`
                                                : ''
                                        }}
                                    </option>
                                    <option
                                        v-if="!propietarios.length"
                                        disabled
                                    >
                                        No hay propietarios disponibles
                                    </option>
                                </select>
                                <span class="text-[11px] text-muted-foreground">
                                    Ctrl/Cmd + clic para seleccionar varios.
                                </span>
                                <span
                                    v-if="
                                        errorParte(
                                            parteIndex,
                                            'propietario_ids',
                                        )
                                    "
                                    class="text-xs text-destructive"
                                >
                                    {{
                                        errorParte(
                                            parteIndex,
                                            'propietario_ids',
                                        )
                                    }}
                                </span>
                            </div>
                            <div v-else class="flex flex-wrap gap-1">
                                <Link
                                    v-for="propietario in parte.propietarios"
                                    :key="propietario.id"
                                    :href="showPropietario(propietario.id)"
                                    :aria-label="`Editar propietario ${propietario.nombre}`"
                                    class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs hover:bg-muted/70 hover:text-primary"
                                    @click.stop
                                    @keydown.stop
                                >
                                    <span class="font-medium">{{
                                        propietario.nombre
                                    }}</span>
                                    <span
                                        v-if="telefonoPropietario(propietario)"
                                        class="text-muted-foreground"
                                    >
                                        · {{ telefonoPropietario(propietario) }}
                                    </span>
                                </Link>
                                <span
                                    v-if="!parte.propietarios.length"
                                    class="text-xs text-amber-600"
                                >
                                    Sin propietario
                                </span>
                            </div>
                        </TableCell>
                        <TableCell class="text-right tabular-nums">
                            <div
                                v-if="editandoMasivamente"
                                class="ml-auto grid min-w-32 gap-1"
                            >
                                <div class="flex items-center gap-1">
                                    <Input
                                        v-model="
                                            partesEditables[parteIndex]
                                                .coeficiente_general
                                        "
                                        type="number"
                                        step="0.00000001"
                                        :aria-label="`Coeficiente de la parte ${parte.codigo}`"
                                        :aria-invalid="
                                            !!errorParte(
                                                parteIndex,
                                                'coeficiente_general',
                                            )
                                        "
                                        class="text-right"
                                        @click.stop
                                        @keydown.stop
                                    />
                                    <span>%</span>
                                </div>
                                <span
                                    v-if="
                                        errorParte(
                                            parteIndex,
                                            'coeficiente_general',
                                        )
                                    "
                                    class="text-xs text-destructive"
                                >
                                    {{
                                        errorParte(
                                            parteIndex,
                                            'coeficiente_general',
                                        )
                                    }}
                                </span>
                            </div>
                            <template v-else>
                                {{
                                    formatCoeficiente(
                                        parte.coeficiente_general,
                                    )
                                }}%
                            </template>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-else class="p-8 text-center text-muted-foreground">
                Añade la primera parte de esta comunidad.
            </p>
        </Card>
        <p v-if="form.errors.partes" class="text-sm text-destructive">
            {{ form.errors.partes }}
        </p>
    </main>
</template>
