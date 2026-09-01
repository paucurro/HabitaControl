<script setup lang="ts">
import { Head, Link, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    Building2,
    MoveRight,
    Pencil,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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
    destroy,
    index as diaryIndex,
    show as showDiario,
    store,
    transfer,
    update,
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
const communityOnlyFieldKeys = new Set<FieldKey>([
    'numero_documento',
    'banco_id',
    'proveedor_id',
    'base_imponible',
    'porcentaje_iva',
]);
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
    fecha_calculada: boolean;
    numero_documento?: string | null;
    descripcion: string;
    debe?: string;
    haber?: string;
    importe?: string;
    base_imponible?: string | null;
    porcentaje_iva?: string | null;
    saldo: string;
    tipo?: string;
    liquidacion_id?: number | null;
    parte_id?: number | null;
    tipo_gasto_id?: number | null;
    banco_id?: number | null;
    proveedor_id?: number | null;
    tipo_obra_id?: number | null;
    parte?: {
        id: number;
        codigo: string;
        descripcion?: string | null;
    } | null;
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
    saldoSeleccionado: string;
    puedeGestionar: boolean;
    apuntes: {
        data: Apunte[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Diario', href: '/diario' }] },
});

setLayoutProps({
    title: props.comunidad.nombre,
    description: undefined,
});

const editorOpen = ref(false);
const copyDescriptionAndAmount = ref(false);
const transferOpen = ref(false);
const selectedEntry = ref<Apunte | null>(null);
const editingEntry = ref<Apunte | null>(null);

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

function editableRow(entry: Apunte): EditableRow {
    return {
        fecha: entry.fecha.slice(0, 10),
        numero_documento: entry.numero_documento ?? '',
        parte_id: (entry.parte_id ?? entry.parte?.id)?.toString() ?? '',
        tipo_gasto_id:
            (entry.tipo_gasto_id ?? entry.tipo_gasto?.id)?.toString() ?? '',
        banco_id: (entry.banco_id ?? entry.banco?.id)?.toString() ?? '',
        proveedor_id:
            (entry.proveedor_id ?? entry.proveedor?.id)?.toString() ?? '',
        tipo_obra_id:
            (entry.tipo_obra_id ?? entry.tipo_obra?.id)?.toString() ?? '',
        tipo: entry.tipo ?? 'extraordinario',
        descripcion: entry.descripcion,
        debe: entry.debe ?? '',
        haber: entry.haber ?? '',
        importe: entry.importe ?? '',
        base_imponible: entry.base_imponible ?? '',
        porcentaje_iva: entry.porcentaje_iva ?? '',
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

const visibleEditorFields = computed(() => {
    const hasCommunityEntry = form.apuntes.some((row) => !row.parte_id);

    return hasCommunityEntry
        ? editorFields.value
        : editorFields.value.filter(
              (field) => !communityOnlyFieldKeys.has(field.key),
          );
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

function openEditor(): void {
    editingEntry.value = null;
    copyDescriptionAndAmount.value = false;
    form.tipo = props.filtros.tipo;
    form.apuntes = [emptyRow()];
    form.clearErrors();
    editorOpen.value = true;
}

function openEdit(entry: Apunte): void {
    editingEntry.value = entry;
    copyDescriptionAndAmount.value = false;
    form.tipo = props.filtros.tipo;
    form.apuntes = [editableRow(entry)];
    form.clearErrors();
    editorOpen.value = true;
}

function addRow(): void {
    form.apuntes.push(emptyRow());
}

function clearCommunityOnlyFields(row: EditableRow): void {
    if (!row.parte_id) {
        return;
    }

    for (const field of communityOnlyFieldKeys) {
        row[field] = '';
    }
}

function fieldAppliesToRow(row: EditableRow, field: EditorField): boolean {
    return !row.parte_id || !communityOnlyFieldKeys.has(field.key);
}

function handleEditorFieldChange(row: EditableRow, field: EditorField): void {
    if (field.key === 'parte_id') {
        clearCommunityOnlyFields(row);
    }
}

function createRowsForAllParts(): void {
    const source = form.apuntes[0] ?? emptyRow();

    form.apuntes = props.partes.map((parte) => {
        const row = emptyRow();
        row.fecha = source.fecha || row.fecha;
        row.parte_id = parte.id.toString();
        row.tipo_gasto_id = source.tipo_gasto_id;
        row.tipo_obra_id = source.tipo_obra_id;
        row.tipo = source.tipo;

        if (copyDescriptionAndAmount.value) {
            row.descripcion = source.descripcion;
            row.debe = source.debe;
            row.haber = source.haber;
            row.importe = source.importe;
        }

        clearCommunityOnlyFields(row);

        return row;
    });
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
    const fields = visibleEditorFields.value;
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
            const field = fields[startColumn + columnOffset];

            if (field) {
                form.apuntes[startRow + rowOffset][field.key] = pastedValue(
                    field,
                    value,
                );
            }
        });

        clearCommunityOnlyFields(form.apuntes[startRow + rowOffset]);
    });
}

function saveEntries(): void {
    form.apuntes.forEach(clearCommunityOnlyFields);

    if (editingEntry.value) {
        form.put(
            update({
                comunidad: props.comunidad.id,
                tipo: props.filtros.tipo,
                apunte: editingEntry.value.id,
            }).url,
            {
                preserveScroll: true,
                onSuccess: () => {
                    editorOpen.value = false;
                    editingEntry.value = null;
                    form.apuntes = [emptyRow()];
                },
            },
        );

        return;
    }

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

function deleteEntry(entry: Apunte): void {
    if (!window.confirm('¿Eliminar este apunte?')) {
        return;
    }

    router.delete(
        destroy({
            comunidad: props.comunidad.id,
            apunte: entry.id,
        }).url,
        { preserveScroll: true },
    );
}

const currencyFormatter = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    useGrouping: true,
});
const signedCurrencyFormatter = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    useGrouping: true,
    signDisplay: 'exceptZero',
});
const dateFormatter = new Intl.DateTimeFormat('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

function currency(value?: string | number | null): string {
    return currencyFormatter.format(Number(value ?? 0));
}

function signedCurrency(value?: string | number | null): string {
    return signedCurrencyFormatter.format(Number(value ?? 0));
}

function entryMovement(entry: Apunte): number {
    return Number(entry.debe ?? 0) - Number(entry.haber ?? 0);
}

function movementClass(value: number): string {
    if (value > 0) {
        return 'text-emerald-600 dark:text-emerald-400';
    }

    if (value < 0) {
        return 'text-destructive';
    }

    return '';
}

function isNegative(value?: string | null): boolean {
    return Number(value ?? 0) < 0;
}

function formatDate(value: string): string {
    return dateFormatter.format(new Date(`${value.slice(0, 10)}T00:00:00`));
}

function parteLabel(parte: NonNullable<Apunte['parte']>): string {
    return [parte.codigo, parte.descripcion].filter(Boolean).join(' · ');
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
    <main
        class="flex flex-1 flex-col gap-6 p-4 md:p-8 xl:h-[calc(100svh-5rem)] xl:min-h-0 xl:flex-none xl:overflow-hidden"
    >
        <div class="flex shrink-0 flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <Button variant="outline" as-child>
                    <Link :href="diaryIndex()">
                        <Building2 class="size-4" />
                        Cambiar de comunidad
                    </Link>
                </Button>
                <div
                    class="hidden h-7 w-px bg-border sm:block"
                    aria-hidden="true"
                />
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
            </div>
            <div v-if="puedeGestionar" class="flex flex-wrap gap-2">
                <Button @click="openEditor">
                    <Plus class="size-4" /> Insertar apuntes
                </Button>
            </div>
        </div>

        <Card
            class="grid shrink-0 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto_minmax(11rem,auto)] xl:items-end"
        >
            <label
                class="grid min-w-0 gap-1 text-xs sm:col-span-2 xl:col-span-1"
            >
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
                    <option value="">Diario de la comunidad</option>
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
            <div class="flex items-end xl:justify-center">
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
            <div
                class="flex min-h-14 flex-col justify-center border-t pt-4 text-right sm:col-span-2 xl:col-span-1 xl:border-t-0 xl:border-l xl:pt-0 xl:pl-4"
            >
                <span class="text-xs text-muted-foreground">
                    {{
                        filtros.parte
                            ? 'Saldo de la parte'
                            : 'Saldo de la comunidad'
                    }}
                </span>
                <strong
                    class="text-xl tabular-nums"
                    :class="isNegative(saldoSeleccionado) && 'text-destructive'"
                >
                    {{ currency(saldoSeleccionado) }}
                </strong>
            </div>
        </Card>

        <Card class="flex min-h-0 flex-1 flex-col overflow-hidden py-0">
            <div class="min-h-0 flex-1 overflow-auto">
                <table class="w-full min-w-6xl text-sm">
                    <thead
                        class="sticky top-0 z-10 border-b bg-muted text-left"
                    >
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
                            <th class="p-3">Tipo gasto</th>
                            <th
                                v-if="filtros.tipo !== 'especiales'"
                                class="p-3"
                            >
                                Contrapartida
                            </th>
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
                            <th v-else class="p-3 text-right">Ingreso/Pago</th>
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
                                <Badge
                                    v-if="entry.fecha_calculada"
                                    variant="outline"
                                    class="ml-1 border-amber-500/60 bg-amber-500/10 text-amber-700 dark:text-amber-300"
                                >
                                    (Aprox)
                                </Badge>
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
                                <span
                                    v-if="entry.tipo_gasto"
                                    :title="entry.tipo_gasto.descripcion"
                                    class="cursor-help underline decoration-dotted underline-offset-4"
                                >
                                    {{ entry.tipo_gasto.codigo }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td
                                v-if="filtros.tipo !== 'especiales'"
                                class="p-3 whitespace-nowrap"
                            >
                                <span
                                    v-if="entry.banco"
                                    :title="entry.banco.nombre"
                                    class="cursor-help underline decoration-dotted underline-offset-4"
                                >
                                    {{
                                        entry.banco.codigo_interno ||
                                        entry.banco.id
                                    }}
                                </span>
                                <span v-else>—</span>
                            </td>
                            <td class="max-w-md p-3">
                                <div>{{ entry.descripcion }}</div>
                                <div
                                    v-if="entry.parte && !filtros.parte"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    {{ parteLabel(entry.parte) }}
                                </div>
                                <div
                                    v-if="entry.proveedor"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    Prov: {{ entry.proveedor.nombre }}
                                </div>
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
                                :class="
                                    isNegative(entry.importe) &&
                                    'text-destructive'
                                "
                            >
                                {{ currency(entry.importe) }}
                            </td>
                            <td
                                v-else
                                class="p-3 text-right font-medium tabular-nums"
                                :class="movementClass(entryMovement(entry))"
                            >
                                {{ signedCurrency(entryMovement(entry)) }}
                            </td>
                            <td
                                class="p-3 text-right font-medium tabular-nums"
                                :class="
                                    isNegative(entry.saldo) &&
                                    'text-destructive'
                                "
                            >
                                {{ currency(entry.saldo) }}
                            </td>
                            <td
                                v-if="puedeGestionar"
                                class="p-3 text-right whitespace-nowrap"
                            >
                                <div class="flex justify-end gap-1">
                                    <Button
                                        v-if="entry.liquidacion_id === null"
                                        size="icon-sm"
                                        variant="ghost"
                                        title="Traspasar a otro diario"
                                        aria-label="Traspasar a otro diario"
                                        @click="openTransfer(entry)"
                                    >
                                        <MoveRight class="size-4" />
                                    </Button>
                                    <Button
                                        v-if="entry.liquidacion_id === null"
                                        size="icon-sm"
                                        variant="ghost"
                                        title="Editar apunte"
                                        aria-label="Editar apunte"
                                        @click="openEdit(entry)"
                                    >
                                        <Pencil class="size-4" />
                                    </Button>
                                    <Button
                                        v-if="
                                            filtros.tipo === 'apuntes' &&
                                            entry.liquidacion_id === null
                                        "
                                        size="icon-sm"
                                        variant="ghost"
                                        class="text-destructive"
                                        title="Eliminar apunte"
                                        aria-label="Eliminar apunte"
                                        @click="deleteEntry(entry)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
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

        <nav class="flex shrink-0 flex-wrap justify-center gap-1">
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
            <DialogContent
                class="flex h-[90vh] w-[90vw] max-w-[90vw] flex-col overflow-hidden sm:max-w-[90vw]"
            >
                <DialogHeader>
                    <DialogTitle>
                        {{
                            editingEntry ? 'Editar apunte' : 'Insertar apuntes'
                        }}
                        ·
                        {{ diaryLabels[filtros.tipo] }}
                    </DialogTitle>
                    <DialogDescription>
                        <template v-if="editingEntry">
                            Modifica los datos del apunte y guarda los cambios.
                        </template>
                        <template v-else>
                            Añade filas o pega directamente un rango de Excel en
                            cualquier celda. Los valores separados por columnas
                            y filas se rellenarán automáticamente.
                        </template>
                    </DialogDescription>
                </DialogHeader>

                <p
                    v-if="firstError"
                    class="rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive"
                >
                    {{ firstError }}
                </p>

                <div class="min-h-0 flex-1 overflow-auto rounded-lg border">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50 text-left">
                            <tr>
                                <th
                                    v-for="field in visibleEditorFields"
                                    :key="field.key"
                                    :class="['p-2', field.width]"
                                >
                                    {{ field.label }}
                                </th>
                                <th v-if="!editingEntry" class="w-12 p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, rowIndex) in form.apuntes"
                                :key="rowIndex"
                                class="border-b last:border-0"
                            >
                                <td
                                    v-for="(
                                        field, columnIndex
                                    ) in visibleEditorFields"
                                    :key="field.key"
                                    class="p-1.5"
                                >
                                    <template
                                        v-if="fieldAppliesToRow(row, field)"
                                    >
                                        <select
                                            v-if="field.type === 'select'"
                                            v-model="row[field.key]"
                                            :data-editor-cell="`${rowIndex}-${columnIndex}`"
                                            class="h-9 w-full rounded-md border bg-background px-2"
                                            @change="
                                                handleEditorFieldChange(
                                                    row,
                                                    field,
                                                )
                                            "
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
                                    </template>
                                    <span
                                        v-else
                                        class="flex h-9 items-center justify-center text-muted-foreground"
                                        aria-label="No se aplica a apuntes de partes"
                                    >
                                        —
                                    </span>
                                </td>
                                <td
                                    v-if="!editingEntry"
                                    class="p-1.5 text-center"
                                >
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
                    :class="[
                        'flex-wrap',
                        editingEntry
                            ? 'justify-end sm:justify-end'
                            : 'justify-between sm:justify-between',
                    ]"
                >
                    <div
                        v-if="!editingEntry"
                        class="flex flex-wrap items-center gap-2"
                    >
                        <Button variant="outline" @click="addRow">
                            <Plus class="size-4" /> Añadir línea
                        </Button>
                        <Button
                            variant="outline"
                            :disabled="!partes.length"
                            @click="createRowsForAllParts"
                        >
                            Crear líneas de todas las partes
                        </Button>
                        <label
                            class="flex cursor-pointer items-center gap-2 px-1 text-sm"
                        >
                            <Checkbox
                                :model-value="copyDescriptionAndAmount"
                                @update:model-value="
                                    copyDescriptionAndAmount = $event === true
                                "
                            />
                            Copiar descripción e importe
                        </label>
                    </div>
                    <Button :disabled="form.processing" @click="saveEntries">
                        <template v-if="editingEntry">Guardar cambios</template>
                        <template v-else>
                            Guardar {{ form.apuntes.length }}
                            {{
                                form.apuntes.length === 1 ? 'apunte' : 'apuntes'
                            }}
                        </template>
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
