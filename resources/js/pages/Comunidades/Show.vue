<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    Download,
    Landmark,
    ListTree,
    Mail,
    MapPin,
    Pencil,
    Percent,
    PiggyBank,
    Printer,
    Receipt,
    ShieldCheck,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { create as createComunicado } from '@/routes/comunicados';
import { coeficientes, edit, etiquetas, exportar } from '@/routes/comunidades';
import { index as partesIndex } from '@/routes/comunidades/partes';
import { index as tiposDepositoIndex } from '@/routes/comunidades/tipos-deposito';
import { index as tiposGastoIndex } from '@/routes/comunidades/tipos-gasto';
type Banco = {
    id: number;
    nombre?: string | null;
    codigo_interno?: string | null;
    iban?: string | null;
    bic?: string | null;
    direccion?: string | null;
    codigo_postal?: string | null;
    poblacion?: string | null;
    provincia?: string | null;
    telefonos?: string | null;
    es_principal: boolean;
};
type Comunidad = {
    id: number;
    codigo: string;
    nombre: string;
    nif?: string | null;
    direccion?: string | null;
    codigo_postal?: string | null;
    poblacion?: string | null;
    provincia?: string | null;
    pais?: string | null;
    presidente_nombre?: string | null;
    presidente_telefono?: string | null;
    vicepresidente_nombre?: string | null;
    vicepresidente_telefono?: string | null;
    aseguradora?: string | null;
    poliza_seguro?: string | null;
    contacto_seguro?: string | null;
    telefono_seguro?: string | null;
    fondo_reserva?: string | number | null;
    copias_informe?: number | null;
    modelo_impresion?: string | null;
    texto_liquidacion?: string | null;
    plazo_maximo_pago_dias?: number | null;
    penalizacion?: string | number | null;
    ano_construccion?: number | null;
    iee?: string | null;
    imprimir_estado: boolean;
    imprimir_nombres_resumen: boolean;
    observaciones?: string | null;
    bancos: Banco[];
};
type DetailItem = {
    label: string;
    value: string | number | null | undefined;
    wide?: boolean;
};

const props = defineProps<{ comunidad: Comunidad }>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const numberFormatter = new Intl.NumberFormat('es-ES', {
    maximumFractionDigits: 4,
});

function displayValue(value: string | number | null | undefined): string {
    return value === null || value === undefined || value === ''
        ? '—'
        : String(value);
}

function formatNumber(
    value: string | number | null | undefined,
    suffix = '',
): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    return `${numberFormatter.format(Number(value))}${suffix}`;
}

const detailGroups = computed(
    (): Array<{ title: string; icon: typeof Users; items: DetailItem[] }> => [
        {
            title: 'Identificación y domicilio',
            icon: MapPin,
            items: [
                { label: 'NIF', value: props.comunidad.nif },
                { label: 'Dirección', value: props.comunidad.direccion },
                {
                    label: 'Código postal',
                    value: props.comunidad.codigo_postal,
                },
                { label: 'Población', value: props.comunidad.poblacion },
                { label: 'Provincia', value: props.comunidad.provincia },
                { label: 'País', value: props.comunidad.pais },
            ],
        },
        {
            title: 'Junta directiva',
            icon: Users,
            items: [
                {
                    label: 'Presidente',
                    value: props.comunidad.presidente_nombre,
                },
                {
                    label: 'Teléfono',
                    value: props.comunidad.presidente_telefono,
                },
                {
                    label: 'Vicepresidente',
                    value: props.comunidad.vicepresidente_nombre,
                },
                {
                    label: 'Teléfono',
                    value: props.comunidad.vicepresidente_telefono,
                },
            ],
        },
        {
            title: 'Seguro',
            icon: ShieldCheck,
            items: [
                { label: 'Compañía', value: props.comunidad.aseguradora },
                { label: 'Póliza', value: props.comunidad.poliza_seguro },
                { label: 'Contacto', value: props.comunidad.contacto_seguro },
                { label: 'Teléfono', value: props.comunidad.telefono_seguro },
            ],
        },
        {
            title: 'Liquidación e impresión',
            icon: Receipt,
            items: [
                {
                    label: 'Fondo de reserva',
                    value: formatNumber(props.comunidad.fondo_reserva, ' €'),
                },
                {
                    label: 'Plazo de pago',
                    value:
                        props.comunidad.plazo_maximo_pago_dias === null ||
                        props.comunidad.plazo_maximo_pago_dias === undefined
                            ? 'Sin límite'
                            : `${props.comunidad.plazo_maximo_pago_dias} días`,
                },
                {
                    label: 'Penalización',
                    value: formatNumber(props.comunidad.penalizacion, ' %'),
                },
                {
                    label: 'Copias página principal',
                    value: props.comunidad.copias_informe ?? 1,
                },
                {
                    label: 'Modelo de impresión',
                    value: props.comunidad.modelo_impresion,
                    wide: true,
                },
                {
                    label: 'Estado de la comunidad',
                    value: props.comunidad.imprimir_estado
                        ? 'Incluir en las liquidaciones'
                        : 'No incluir en las liquidaciones',
                    wide: true,
                },
                {
                    label: 'Nombres en el resumen',
                    value: props.comunidad.imprimir_nombres_resumen
                        ? 'Mostrar propietarios e inquilinos'
                        : 'No mostrar nombres',
                    wide: true,
                },
                {
                    label: 'Texto de liquidación',
                    value: props.comunidad.texto_liquidacion,
                    wide: true,
                },
            ],
        },
    ],
);
</script>

<template>
    <Head :title="comunidad.nombre" />

    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div
            class="flex flex-col justify-between gap-4 lg:flex-row lg:items-center"
        >
            <div class="grid gap-1">
                <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                    {{ comunidad.nombre }}
                </h1>
                <p
                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                >
                    <MapPin class="size-4 shrink-0" />
                    {{
                        [
                            comunidad.direccion,
                            comunidad.codigo_postal,
                            comunidad.poblacion,
                        ]
                            .filter(Boolean)
                            .join(' · ') || 'Sin dirección'
                    }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" as-child>
                    <Link :href="edit(comunidad.id)"
                        ><Pencil class="size-4" /> Editar</Link
                    >
                </Button>
                <Button variant="outline" as-child>
                    <a :href="exportar(comunidad.id).url"
                        ><Download class="size-4" /> Excel/CSV</a
                    >
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="etiquetas(comunidad.id)"
                        ><Printer class="size-4" /> Etiquetas</Link
                    >
                </Button>
                <Button as-child>
                    <Link :href="createComunicado()"
                        ><Mail class="size-4" /> Comunicado</Link
                    >
                </Button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button variant="secondary" as-child>
                <Link :href="partesIndex(comunidad.id)"
                    ><ListTree class="size-4" /> Partes</Link
                >
            </Button>
            <Button variant="secondary" as-child>
                <Link :href="coeficientes(comunidad.id)"
                    ><Percent class="size-4" /> Coeficientes</Link
                >
            </Button>
            <Button variant="secondary" as-child>
                <Link :href="tiposGastoIndex(comunidad.id)"
                    ><Receipt class="size-4" /> Tipos de gasto</Link
                >
            </Button>
            <Button variant="secondary" as-child>
                <Link :href="tiposDepositoIndex(comunidad.id)"
                    ><PiggyBank class="size-4" /> Tipos de depósito</Link
                >
            </Button>
        </div>

        <section aria-labelledby="ficha-heading" class="grid gap-3">
            <div>
                <h2 id="ficha-heading" class="text-lg font-semibold">
                    Ficha de la comunidad
                </h2>
                <p class="text-sm text-muted-foreground">
                    Información administrativa y configuración principal.
                </p>
            </div>

            <div class="grid gap-5 xl:grid-cols-2">
                <Card
                    v-for="group in detailGroups"
                    :key="group.title"
                    class="gap-0 py-0 shadow-xs"
                >
                    <CardHeader class="border-b bg-muted/20 px-5 py-4">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <component
                                :is="group.icon"
                                class="size-4 text-primary"
                            />
                            {{ group.title }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent
                        class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 sm:grid-cols-2"
                    >
                        <div
                            v-for="item in group.items"
                            :key="item.label"
                            :class="item.wide && 'sm:col-span-2'"
                        >
                            <dt
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                {{ item.label }}
                            </dt>
                            <dd
                                class="mt-1 text-sm leading-relaxed whitespace-pre-line"
                            >
                                {{ displayValue(item.value) }}
                            </dd>
                        </div>
                    </CardContent>
                </Card>

                <Card class="gap-0 py-0 shadow-xs xl:col-span-2">
                    <CardHeader class="border-b bg-muted/20 px-5 py-4">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Building2 class="size-4 text-primary" /> Edificio y
                            observaciones
                        </CardTitle>
                    </CardHeader>
                    <CardContent
                        class="grid gap-5 p-5 md:grid-cols-[12rem_1fr]"
                    >
                        <div>
                            <dt
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Año de construcción
                            </dt>
                            <dd class="mt-1 text-sm">
                                {{ displayValue(comunidad.ano_construccion) }}
                            </dd>
                        </div>
                        <div>
                            <dt
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                IEE
                            </dt>
                            <dd
                                class="mt-1 text-sm leading-relaxed whitespace-pre-line"
                            >
                                {{ displayValue(comunidad.iee) }}
                            </dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt
                                class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                            >
                                Observaciones
                            </dt>
                            <dd
                                class="mt-1 text-sm leading-relaxed whitespace-pre-line"
                            >
                                {{ displayValue(comunidad.observaciones) }}
                            </dd>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <Card class="gap-0 py-0 shadow-xs">
                <CardHeader class="border-b bg-muted/20 px-5 py-4">
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Landmark class="size-4 text-primary" /> Bancos y
                        cuentas
                    </CardTitle>
                    <CardDescription
                        >La cuenta principal se utiliza por defecto en diarios y
                        apartados.</CardDescription
                    >
                </CardHeader>
                <CardContent
                    v-if="comunidad.bancos.length"
                    class="grid gap-4 p-5 lg:grid-cols-2"
                >
                    <article
                        v-for="banco in comunidad.bancos"
                        :key="banco.id"
                        class="rounded-xl border bg-muted/10 p-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-medium">
                                    {{ displayValue(banco.nombre) }}
                                </h3>
                                <p
                                    v-if="banco.codigo_interno"
                                    class="text-xs text-muted-foreground"
                                >
                                    Código {{ banco.codigo_interno }}
                                </p>
                            </div>
                            <span
                                v-if="banco.es_principal"
                                class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                            >
                                Principal
                            </span>
                        </div>
                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-muted-foreground">
                                    IBAN / cuenta
                                </dt>
                                <dd class="mt-0.5 font-mono break-all">
                                    {{ displayValue(banco.iban) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    BIC
                                </dt>
                                <dd class="mt-0.5 font-mono">
                                    {{ displayValue(banco.bic) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted-foreground">
                                    Teléfonos
                                </dt>
                                <dd class="mt-0.5">
                                    {{ displayValue(banco.telefonos) }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-muted-foreground">
                                    Dirección
                                </dt>
                                <dd class="mt-0.5">
                                    {{
                                        [
                                            banco.direccion,
                                            banco.codigo_postal,
                                            banco.poblacion,
                                            banco.provincia,
                                        ]
                                            .filter(Boolean)
                                            .join(' · ') || '—'
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </article>
                </CardContent>
                <CardContent
                    v-else
                    class="p-8 text-center text-sm text-muted-foreground"
                >
                    No hay cuentas bancarias registradas.
                </CardContent>
            </Card>
        </section>
    </main>
</template>
