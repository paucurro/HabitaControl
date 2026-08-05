<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    BookOpen,
    ClipboardPaste,
    MoveRight,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    index as diarioIndex,
    show as showDiario,
    store,
    transfer,
} from '@/routes/diario';

type DiarioTipo = 'apuntes' | 'especiales' | 'obras';
type Option = { id: number; label: string; aliases: string[] };
type EditableRow = {
    fecha: string;
    numero_documento: string;
    parte_id: string;
    tipo_gasto_id: string;
    banco_id: string;
    proveedor_id: string;
    tipo_obra_id: string;
    tipo: string;
    descripcion: string;
    debe: string;
    haber: string;
    importe: string;
    base_imponible: string;
    porcentaje_iva: string;
};
type FieldKey = keyof EditableRow;
type EditorField = {
    key: FieldKey;
    label: string;
    type: 'date' | 'number' | 'select' | 'text';
    options?: Option[];
    width: string;
};
type Apunte = {
    id: number;
    fecha: string;
    numero_documento?: string | null;
    descripcion: string;
    debe?: string;
    haber?: string;
    importe?: string;
    saldo: string;
    tipo?: string;
    liquidacion_id?: number | null;
    parte?: { id: number; codigo: string } | null;
    tipo_gasto?: { id: number; codigo: string; descripcion: string } | null;
    tipo_obra?: { id: number; codigo: string; descripcion: string } | null;
    banco?: { id: number; codigo_interno?: string; nombre: string } | null;
    proveedor?: { id: number; nombre: string } | null;
};

const props = defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    partes: Array<{ id: number; codigo: string; descripcion?: string }>;
    catalogos: {
        tiposGasto: Array<{ id: number; codigo: string; descripcion: string }>;
        bancos: Array<{
            id: number;
            codigo_interno?: string;
            nombre: string;
        }>;
        tiposObra: Array<{
            id: number;
            codigo: string;
            descripcion: string;
        }>;
        proveedores: Array<{ id: number; nombre: string }>;
    };
    filtros: {
        tipo: DiarioTipo;
        parte: number | null;
        apunte: number | null;
        orden: 'asc' | 'desc';
        desde: string | null;
        hasta: string | null;
    };
    saldoComunidad: string;
    puedeGestionar: boolean;
    apuntes: {
        data: Apunte[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Diario', href: '/diario' }] },
});

const editorOpen = ref(false);
const excelMode = ref(false);
const transferOpen = ref(false);
const selectedEntry = ref<Apunte | null>(null);

const diaryLabels: Record<DiarioTipo, string> = {
    apuntes: 'Diario apuntes',
    especiales: 'Diario apuntes especiales',
    obras: 'Diario obras',
};

function today(): string {
    const date = new Date();
    const offset = date.getTimezoneOffset() * 60_000;

    return new Date(date.getTime() - offset).toISOString().slice(0, 10);
}

function emptyRow(): EditableRow {
    return {
        fecha: today(),
        numero_documento: '',
        parte_id: props.filtros.parte?.toString() ?? '',
        tipo_gasto_id: '',
        banco_id: props.catalogos.bancos[0]?.id.toString() ?? '',
        proveedor_id: '',
        tipo_obra_id: props.catalogos.tiposObra[0]?.id.toString() ?? '',
        tipo: 'extraordinario',
        descripcion: '',
        debe: '',
        haber: '',
        importe: '',
        base_imponible: '',
        porcentaje_iva: '',
    };
}

const form = useForm<{ tipo: DiarioTipo; apuntes: EditableRow[] }>({
    tipo: props.filtros.tipo,
    apuntes: [emptyRow()],
});
const transferForm = useForm<{ destino: DiarioTipo; tipo_obra_id: string }>({
    destino: props.filtros.tipo === 'apuntes' ? 'especiales' : 'apuntes',
    tipo_obra_id: props.catalogos.tiposObra[0]?.id.toString() ?? '',
});

function options<T extends { id: number }>(
    values: T[],
    label: (value: T) => string,
    aliases: (value: T) => string[],
): Option[] {
    return values.map((value) => ({
        id: value.id,
        label: label(value),
        aliases: [value.id.toString(), ...aliases(value)],
    }));
}

const parteOptions = computed(() =>
    options(
        props.partes,
        (parte) => `${parte.codigo} · ${parte.descripcion ?? ''}`,
        (parte) => [parte.codigo, parte.descripcion ?? ''],
    ),
);
const gastoOptions = computed(() =>
    options(
        props.catalogos.tiposGasto,
        (tipo) => `[${tipo.codigo}] ${tipo.descripcion}`,
        (tipo) => [tipo.codigo, tipo.descripcion],
    ),
);
const bancoOptions = computed(() =>
    options(
        props.catalogos.bancos,
        (banco) =>
            `${banco.codigo_interno ? `[${banco.codigo_interno}] ` : ''}${banco.nombre}`,
        (banco) => [banco.codigo_interno ?? '', banco.nombre],
    ),
);
const proveedorOptions = computed(() =>
    options(
        props.catalogos.proveedores,
        (proveedor) => proveedor.nombre,
        (proveedor) => [proveedor.nombre],
    ),
);
const obraOptions = computed(() =>
    options(
        props.catalogos.tiposObra,
        (obra) => `[${obra.codigo}] ${obra.descripcion}`,
        (obra) => [obra.codigo, obra.descripcion],
    ),
);

const editorFields = computed<EditorField[]>(() => {
    const common: EditorField[] = [
        { key: 'fecha', label: 'Fecha', type: 'date', width: 'min-w-36' },
        {
            key: 'parte_id',
            label: 'Parte',
            type: 'select',
            options: parteOptions.value,
            width: 'min-w-48',
        },
        {
            key: 'tipo_gasto_id',
            label: 'Tipo de gasto',
            type: 'select',
            options: gastoOptions.value,
            width: 'min-w-56',
        },
    ];
    const provider: EditorField = {
        key: 'proveedor_id',
        label: 'Proveedor',
        type: 'select',
        options: proveedorOptions.value,
        width: 'min-w-52',
    };
    const description: EditorField = {
        key: 'descripcion',
        label: 'Descripción',
        type: 'text',
        width: 'min-w-72',
    };
    const tax: EditorField[] = [
        {
            key: 'base_imponible',
            label: 'Base imponible',
            type: 'number',
            width: 'min-w-36',
        },
        {
            key: 'porcentaje_iva',
            label: 'IVA %',
            type: 'number',
            width: 'min-w-28',
        },
    ];

    if (props.filtros.tipo === 'especiales') {
        return [
            ...common,
            provider,
            { key: 'tipo', label: 'Tipo', type: 'text', width: 'min-w-40' },
            description,
            {
                key: 'importe',
                label: 'Importe',
                type: 'number',
                width: 'min-w-32',
            },
            ...tax,
        ];
    }

    const accounting: EditorField[] = [
        {
            key: 'numero_documento',
            label: 'Documento',
            type: 'text',
            width: 'min-w-36',
        },
        {
            key: 'banco_id',
            label: 'Contrapartida',
            type: 'select',
            options: bancoOptions.value,
            width: 'min-w-52',
        },
        provider,
        description,
        { key: 'debe', label: 'Ingreso', type: 'number', width: 'min-w-32' },
        { key: 'haber', label: 'Pago', type: 'number', width: 'min-w-32' },
        ...tax,
    ];

    if (props.filtros.tipo === 'obras') {
        return [
            common[0],
            {
                key: 'tipo_obra_id',
                label: 'Cuenta de obra',
                type: 'select',
                options: obraOptions.value,
                width: 'min-w-56',
            },
            ...common.slice(1),
            ...accounting,
        ];
    }

    return [...common, ...accounting];
});

const firstError = computed(() => Object.values(form.errors)[0]);

function query(overrides: Record<string, string | number | null>): void {
    const values: Record<string, string | number> = {
        tipo: props.filtros.tipo,
        orden: props.filtros.orden,
    };
    const current = {
        parte: props.filtros.parte,
        desde: props.filtros.desde,
        hasta: props.filtros.hasta,
        ...overrides,
    };

    for (const [key, value] of Object.entries(current)) {
        if (value !== null && value !== '') {
            values[key] = value;
        }
    }

    router.get(showDiario.url(props.comunidad.id), values, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function changeDiary(tipo: DiarioTipo): void {
    query({ tipo, apunte: null });
}

function openEditor(asExcel: boolean): void {
    excelMode.value = asExcel;
    form.tipo = props.filtros.tipo;
    form.apuntes = [emptyRow()];
    form.clearErrors();
    editorOpen.value = true;
}

function addRow(): void {
    form.apuntes.push(emptyRow());
}

function removeRow(index: number): void {
    if (form.apuntes.length === 1) {
        form.apuntes[0] = emptyRow();

        return;
    }

    form.apuntes.splice(index, 1);
}

function normalize(value: string): string {
    return value.trim().toLocaleLowerCase('es-ES');
}

function pastedValue(field: EditorField, value: string): string {
    const clean = value.trim();

    if (field.type === 'select') {
        const wanted = normalize(clean);
        const match = field.options?.find(
            (option) =>
                normalize(option.label) === wanted ||
                option.aliases.some((alias) => normalize(alias) === wanted),
        );

        return match?.id.toString() ?? '';
    }

    if (field.type === 'number') {
        return clean.includes(',')
            ? clean.replaceAll('.', '').replace(',', '.')
            : clean;
    }

    if (field.type === 'date') {
        const parts = clean.split('/');

        if (parts.length === 3) {
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
    }

    return clean;
}

function pasteCells(
    event: ClipboardEvent,
    startRow: number,
    startColumn: number,
): void {
    const clipboard = event.clipboardData?.getData('text');

    if (!clipboard) {
        return;
    }

    event.preventDefault();
    const matrix = clipboard
        .replace(/\r/g, '')
        .replace(/\n$/, '')
        .split('\n')
        .map((line) => line.split('\t'));

    while (form.apuntes.length < startRow + matrix.length) {
        addRow();
    }

    matrix.forEach((values, rowOffset) => {
        values.forEach((value, columnOffset) => {
            const field = editorFields.value[startColumn + columnOffset];

            if (field) {
                form.apuntes[startRow + rowOffset][field.key] = pastedValue(
                    field,
                    value,
                );
            }
        });
    });
}

function saveEntries(): void {
    form.post(store(props.comunidad.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            editorOpen.value = false;
            form.apuntes = [emptyRow()];
        },
    });
}

function openTransfer(entry: Apunte): void {
    selectedEntry.value = entry;
    transferForm.destino =
        props.filtros.tipo === 'apuntes' ? 'especiales' : 'apuntes';
    transferForm.tipo_obra_id =
        props.catalogos.tiposObra[0]?.id.toString() ?? '';
    transferForm.clearErrors();
    transferOpen.value = true;
}

function saveTransfer(): void {
    if (!selectedEntry.value) {
        return;
    }

    transferForm.put(
        transfer({
            comunidad: props.comunidad.id,
            tipo: props.filtros.tipo,
            apunte: selectedEntry.value.id,
        }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                transferOpen.value = false;
                selectedEntry.value = null;
            },
        },
    );
}

function currency(value?: string | null): string {
    return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(value ?? 0));
}

function formatDate(value: string): string {
    return new Date(`${value.slice(0, 10)}T00:00:00`).toLocaleDateString(
        'es-ES',
    );
}

function paginationLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}

async function focusFirstCell(): Promise<void> {
    await nextTick();
    document.querySelector<HTMLElement>('[data-editor-cell="0-0"]')?.focus();
}
</script>

<template>
    <Head :title="`${diaryLabels[filtros.tipo]} · ${comunidad.nombre}`" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col justify-between gap-4 xl:flex-row xl:items-end"
        >
            <div>
                <CommunityBackLink :href="diarioIndex.url()">
                    Cambiar comunidad
                </CommunityBackLink>
                <h1 class="mt-3 flex items-center gap-2 text-2xl font-semibold">
                    <BookOpen class="size-6" /> Diario
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ comunidad.codigo }} · {{ comunidad.nombre }}
                </p>
            </div>
            <Card class="gap-1 px-5 py-3 text-right">
                <span class="text-xs text-muted-foreground"
                    >Saldo de la comunidad en este diario</span
                >
                <strong
                    class="text-xl tabular-nums"
                    :class="Number(saldoComunidad) < 0 && 'text-destructive'"
                    >{{ currency(saldoComunidad) }}</strong
                >
            </Card>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <Button
                    v-for="(label, tipo) in diaryLabels"
                    :key="tipo"
                    :variant="filtros.tipo === tipo ? 'default' : 'outline'"
                    @click="changeDiary(tipo)"
                >
                    {{ label }}
                </Button>
            </div>
            <div v-if="puedeGestionar" class="flex flex-wrap gap-2">
                <Button variant="outline" @click="openEditor(true)">
                    <ClipboardPaste class="size-4" /> Pegar lista de Excel
                </Button>
                <Button @click="openEditor(false)">
                    <Plus class="size-4" /> Nuevo apunte
                </Button>
            </div>
        </div>

        <Card class="grid gap-4 p-4 md:grid-cols-4">
            <label class="grid gap-1 text-xs">
                <span>Parte</span>
                <select
                    :value="filtros.parte ?? ''"
                    class="h-9 rounded-md border bg-background px-2 text-sm"
                    @change="
                        query({
                            parte:
                                ($event.target as HTMLSelectElement).value ||
                                null,
                            apunte: null,
                        })
                    "
                >
                    <option value="">Todas las partes</option>
                    <option
                        v-for="parte in partes"
                        :key="parte.id"
                        :value="parte.id"
                    >
                        {{ parte.codigo }} · {{ parte.descripcion }}
                    </option>
                </select>
            </label>
            <label class="grid gap-1 text-xs">
                <span>Desde</span>
                <Input
                    type="date"
                    :model-value="filtros.desde ?? ''"
                    @change="
                        query({
                            desde:
                                ($event.target as HTMLInputElement).value ||
                                null,
                            apunte: null,
                        })
                    "
                />
            </label>
            <label class="grid gap-1 text-xs">
                <span>Hasta</span>
                <Input
                    type="date"
                    :model-value="filtros.hasta ?? ''"
                    @change="
                        query({
                            hasta:
                                ($event.target as HTMLInputElement).value ||
                                null,
                            apunte: null,
                        })
                    "
                />
            </label>
            <div class="flex items-end">
                <Button
                    variant="ghost"
                    :disabled="
                        !filtros.parte && !filtros.desde && !filtros.hasta
                    "
                    @click="
                        query({
                            parte: null,
                            desde: null,
                            hasta: null,
                            apunte: null,
                        })
                    "
                >
                    Limpiar filtros
                </Button>
            </div>
        </Card>

        <Card class="overflow-hidden py-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-6xl text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="p-3">Doc.</th>
                            <th class="p-3">
                                <button
                                    class="flex items-center gap-1 font-medium"
                                    title="Ordenar por fecha"
                                    @click="
                                        query({
                                            orden:
                                                filtros.orden === 'asc'
                                                    ? 'desc'
                                                    : 'asc',
                                            apunte: null,
                                        })
                                    "
                                >
                                    Fecha
                                    <ArrowUp
                                        v-if="filtros.orden === 'asc'"
                                        class="size-3.5"
                                    />
                                    <ArrowDown v-else class="size-3.5" />
                                </button>
                            </th>
                            <th v-if="filtros.tipo === 'obras'" class="p-3">
                                Cuenta de obra
                            </th>
                            <th class="p-3">Parte</th>
                            <th class="p-3">Tipo gasto</th>
                            <th
                                v-if="filtros.tipo !== 'especiales'"
                                class="p-3"
                            >
                                Contrapartida
                            </th>
                            <th class="p-3">Proveedor</th>
                            <th class="p-3">Descripción</th>
                            <th
                                v-if="filtros.tipo === 'especiales'"
                                class="p-3"
                            >
                                Tipo
                            </th>
                            <th
                                v-if="filtros.tipo === 'especiales'"
                                class="p-3 text-right"
                            >
                                Importe
                            </th>
                            <template v-else>
                                <th class="p-3 text-right">Ingreso</th>
                                <th class="p-3 text-right">Pago</th>
                            </template>
                            <th class="p-3 text-right">Saldo</th>
                            <th v-if="puedeGestionar" class="p-3 text-right">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="entry in apuntes.data"
                            :key="entry.id"
                            :class="[
                                'border-b last:border-0',
                                filtros.apunte === entry.id && 'bg-primary/10',
                            ]"
                        >
                            <td class="p-3 whitespace-nowrap">
                                {{ entry.numero_documento || entry.id }}
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                {{ formatDate(entry.fecha) }}
                            </td>
                            <td
                                v-if="filtros.tipo === 'obras'"
                                class="p-3 whitespace-nowrap"
                            >
                                <span v-if="entry.tipo_obra">
                                    [{{ entry.tipo_obra.codigo }}]
                                    {{ entry.tipo_obra.descripcion }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                {{ entry.parte?.codigo || '—' }}
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                <span v-if="entry.tipo_gasto">
                                    [{{ entry.tipo_gasto.codigo }}]
                                    {{ entry.tipo_gasto.descripcion }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td
                                v-if="filtros.tipo !== 'especiales'"
                                class="p-3 whitespace-nowrap"
                            >
                                {{
                                    entry.banco?.codigo_interno ||
                                    entry.banco?.nombre ||
                                    '—'
                                }}
                            </td>
                            <td class="p-3 whitespace-nowrap">
                                {{ entry.proveedor?.nombre || '—' }}
                            </td>
                            <td class="max-w-md p-3">
                                {{ entry.descripcion }}
                            </td>
                            <td
                                v-if="filtros.tipo === 'especiales'"
                                class="p-3"
                            >
                                {{ entry.tipo }}
                            </td>
                            <td
                                v-if="filtros.tipo === 'especiales'"
                                class="p-3 text-right tabular-nums"
                            >
                                {{ currency(entry.importe) }}
                            </td>
                            <template v-else>
                                <td class="p-3 text-right tabular-nums">
                                    {{ currency(entry.debe) }}
                                </td>
                                <td class="p-3 text-right tabular-nums">
                                    {{ currency(entry.haber) }}
                                </td>
                            </template>
                            <td
                                class="p-3 text-right font-medium tabular-nums"
                                :class="
                                    Number(entry.saldo) < 0 &&
                                    'text-destructive'
                                "
                            >
                                {{ currency(entry.saldo) }}
                            </td>
                            <td v-if="puedeGestionar" class="p-3 text-right">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    :disabled="entry.liquidacion_id !== null"
                                    :title="
                                        entry.liquidacion_id !== null
                                            ? 'El apunte ya está liquidado'
                                            : 'Mover a otro diario'
                                    "
                                    @click="openTransfer(entry)"
                                >
                                    <MoveRight class="size-4" /> Traspasar
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p
                v-if="!apuntes.data.length"
                class="p-10 text-center text-muted-foreground"
            >
                No hay apuntes para este filtro.
            </p>
        </Card>

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
            >
                {{ paginationLabel(link.label) }}
            </Link>
        </nav>

        <Dialog v-model:open="editorOpen" @update:open="focusFirstCell">
            <DialogContent class="max-h-[92vh] max-w-[96vw] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {{
                            excelMode ? 'Pegar lista de Excel' : 'Nuevo apunte'
                        }}
                        ·
                        {{ diaryLabels[filtros.tipo] }}
                    </DialogTitle>
                    <DialogDescription>
                        Añade filas o pega directamente un rango de Excel en
                        cualquier celda. Los valores separados por columnas y
                        filas se rellenarán automáticamente.
                    </DialogDescription>
                </DialogHeader>

                <p
                    v-if="firstError"
                    class="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    {{ firstError }}
                </p>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50 text-left">
                            <tr>
                                <th
                                    v-for="field in editorFields"
                                    :key="field.key"
                                    :class="['p-2', field.width]"
                                >
                                    {{ field.label }}
                                </th>
                                <th class="w-12 p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rowIndex) in form.apuntes"
                                :key="rowIndex"
                                class="border-b last:border-0"
                            >
                                <td
                                    v-for="(field, columnIndex) in editorFields"
                                    :key="field.key"
                                    class="p-1.5"
                                >
                                    <select
                                        v-if="field.type === 'select'"
                                        v-model="row[field.key]"
                                        :data-editor-cell="`${rowIndex}-${columnIndex}`"
                                        class="h-9 w-full rounded-md border bg-background px-2"
                                        @paste="
                                            pasteCells(
                                                $event,
                                                rowIndex,
                                                columnIndex,
                                            )
                                        "
                                    >
                                        <option value="">—</option>
                                        <option
                                            v-for="option in field.options"
                                            :key="option.id"
                                            :value="option.id"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <Input
                                        v-else
                                        v-model="row[field.key]"
                                        :data-editor-cell="`${rowIndex}-${columnIndex}`"
                                        :type="field.type"
                                        :step="
                                            field.type === 'number'
                                                ? '0.01'
                                                : undefined
                                        "
                                        @paste="
                                            pasteCells(
                                                $event,
                                                rowIndex,
                                                columnIndex,
                                            )
                                        "
                                    />
                                </td>
                                <td class="p-1.5 text-center">
                                    <Button
                                        size="icon-sm"
                                        variant="ghost"
                                        title="Quitar fila"
                                        @click="removeRow(rowIndex)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <DialogFooter
                    class="flex-wrap justify-between sm:justify-between"
                >
                    <Button variant="outline" @click="addRow">
                        <Plus class="size-4" /> Añadir fila
                    </Button>
                    <Button :disabled="form.processing" @click="saveEntries">
                        Guardar {{ form.apuntes.length }}
                        {{ form.apuntes.length === 1 ? 'apunte' : 'apuntes' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="transferOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Traspasar apunte</DialogTitle>
                    <DialogDescription>
                        El apunte se moverá al diario elegido y desaparecerá del
                        diario actual.
                    </DialogDescription>
                </DialogHeader>
                <div class="grid gap-4 py-2">
                    <label class="grid gap-1.5 text-sm">
                        <span>Diario de destino</span>
                        <select
                            v-model="transferForm.destino"
                            class="h-9 rounded-md border bg-background px-2"
                        >
                            <option
                                v-if="filtros.tipo !== 'apuntes'"
                                value="apuntes"
                            >
                                {{ diaryLabels.apuntes }}
                            </option>
                            <option
                                v-if="filtros.tipo !== 'especiales'"
                                value="especiales"
                            >
                                {{ diaryLabels.especiales }}
                            </option>
                            <option
                                v-if="filtros.tipo !== 'obras'"
                                value="obras"
                            >
                                {{ diaryLabels.obras }}
                            </option>
                        </select>
                        <span class="text-xs text-destructive">{{
                            transferForm.errors.destino
                        }}</span>
                    </label>
                    <label
                        v-if="transferForm.destino === 'obras'"
                        class="grid gap-1.5 text-sm"
                    >
                        <span>Cuenta de obra</span>
                        <select
                            v-model="transferForm.tipo_obra_id"
                            class="h-9 rounded-md border bg-background px-2"
                        >
                            <option value="">Selecciona una cuenta</option>
                            <option
                                v-for="obra in catalogos.tiposObra"
                                :key="obra.id"
                                :value="obra.id"
                            >
                                [{{ obra.codigo }}] {{ obra.descripcion }}
                            </option>
                        </select>
                        <span class="text-xs text-destructive">{{
                            transferForm.errors.tipo_obra_id
                        }}</span>
                    </label>
                </div>
                <DialogFooter>
                    <Button
                        :disabled="transferForm.processing"
                        @click="saveTransfer"
                    >
                        Traspasar apunte
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </main>
</template>
