<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    Building2,
    ClipboardList,
    FileText,
    Landmark,
    Plus,
    Printer,
    ShieldCheck,
    Trash2,
    Upload,
    Users,
} from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { index, store, update } from '@/routes/comunidades';

type BancoForm = {
    nombre: string;
    codigo_interno: string;
    iban: string;
    bic: string;
    direccion: string;
    codigo_postal: string;
    poblacion: string;
    provincia: string;
    telefonos: string;
    es_principal?: boolean;
};

type ComunidadForm = Record<
    string,
    string | number | boolean | BancoForm[] | null
> & {
    id: number;
    bancos?: BancoForm[];
};

const props = defineProps<{
    comunidad?: ComunidadForm;
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const generalFields = [
    ['codigo', 'Código', true],
    ['nombre', 'Nombre de la comunidad', true],
    ['nif', 'NIF'],
    ['direccion', 'Dirección'],
    ['codigo_postal', 'Código postal'],
    ['poblacion', 'Población'],
    ['provincia', 'Provincia'],
    ['pais', 'País'],
] as const;

const juntaFields = [
    ['presidente_nombre', 'Presidente'],
    ['presidente_telefono', 'Teléfono del presidente'],
    ['vicepresidente_nombre', 'Vicepresidente'],
    ['vicepresidente_telefono', 'Teléfono del vicepresidente'],
] as const;

const seguroFields = [
    ['aseguradora', 'Compañía de seguros'],
    ['poliza_seguro', 'Número de póliza'],
    ['contacto_seguro', 'Persona de contacto'],
    ['telefono_seguro', 'Teléfono de contacto'],
] as const;

const emptyBanco = (): BancoForm => ({
    nombre: '',
    codigo_interno: '',
    iban: '',
    bic: '',
    direccion: '',
    codigo_postal: '',
    poblacion: '',
    provincia: '',
    telefonos: '',
});

const bancos = ref<BancoForm[]>(
    (props.comunidad?.bancos ?? []).map((banco) => ({
        ...emptyBanco(),
        ...banco,
    })),
);
const initialPrincipalIndex = bancos.value.findIndex(
    (banco) => banco.es_principal,
);
const principalIndex = ref<number | null>(
    initialPrincipalIndex >= 0 ? initialPrincipalIndex : null,
);

function addBanco(): void {
    bancos.value.push(emptyBanco());

    if (principalIndex.value === null) {
        principalIndex.value = bancos.value.length - 1;
    }
}

function removeBanco(index: number): void {
    bancos.value.splice(index, 1);

    if (principalIndex.value === index) {
        principalIndex.value = bancos.value.length ? 0 : null;
    } else if (principalIndex.value !== null && principalIndex.value > index) {
        principalIndex.value--;
    }
}
</script>

<template>
    <Head :title="props.comunidad ? 'Editar comunidad' : 'Nueva comunidad'" />

    <main
        class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 md:p-8"
    >
        <div class="flex flex-col gap-1">
            <p class="text-sm font-medium text-primary">Ficha de comunidad</p>
            <h1 class="text-2xl font-semibold tracking-tight md:text-3xl">
                {{
                    props.comunidad
                        ? `Editar ${props.comunidad.nombre}`
                        : 'Nueva comunidad'
                }}
            </h1>
            <p class="text-sm text-muted-foreground">
                Completa los datos administrativos, de liquidación, del edificio
                y sus cuentas bancarias.
            </p>
        </div>

        <Form
            v-bind="
                props.comunidad ? update.form(props.comunidad.id) : store.form()
            "
            enctype="multipart/form-data"
            #default="{ errors, processing }"
        >
            <Card class="overflow-hidden border-border/70 shadow-sm">
                <CardContent class="p-0">
                    <Tabs default-value="general" class="gap-0">
                        <div class="border-b bg-muted/20 p-3 md:p-4">
                            <TabsList
                                class="grid h-auto w-full grid-cols-2 gap-1 rounded-xl bg-muted/70 p-1 sm:grid-cols-4"
                                :class="
                                    props.comunidad
                                        ? 'xl:grid-cols-7'
                                        : 'xl:grid-cols-8'
                                "
                            >
                                <TabsTrigger
                                    value="general"
                                    class="h-10 gap-2 px-3"
                                >
                                    <ClipboardList class="size-4" /> General
                                </TabsTrigger>
                                <TabsTrigger
                                    value="junta"
                                    class="h-10 gap-2 px-3"
                                >
                                    <Users class="size-4" /> Junta
                                </TabsTrigger>
                                <TabsTrigger
                                    value="seguro"
                                    class="h-10 gap-2 px-3"
                                >
                                    <ShieldCheck class="size-4" /> Seguro
                                </TabsTrigger>
                                <TabsTrigger
                                    value="liquidacion"
                                    class="h-10 gap-2 px-3"
                                >
                                    <FileText class="size-4" /> Liquidación
                                </TabsTrigger>
                                <TabsTrigger
                                    value="impresion"
                                    class="h-10 gap-2 px-3"
                                >
                                    <Printer class="size-4" /> Impresión
                                </TabsTrigger>
                                <TabsTrigger
                                    value="edificio"
                                    class="h-10 gap-2 px-3"
                                >
                                    <Building2 class="size-4" /> Edificio
                                </TabsTrigger>
                                <TabsTrigger
                                    value="bancos"
                                    class="h-10 gap-2 px-3"
                                >
                                    <Landmark class="size-4" /> Bancos
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="!props.comunidad"
                                    value="importar"
                                    class="h-10 gap-2 px-3"
                                >
                                    <Upload class="size-4" /> Importar
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        <TabsContent value="general" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle>Datos generales</CardTitle>
                                <CardDescription
                                    >Identificación y domicilio principal de la
                                    comunidad.</CardDescription
                                >
                            </CardHeader>
                            <div
                                class="grid gap-x-8 gap-y-6 p-5 md:grid-cols-2 md:p-8"
                            >
                                <div
                                    v-for="field in generalFields"
                                    :key="field[0]"
                                    class="grid content-start gap-2"
                                >
                                    <Label :for="field[0]">
                                        {{ field[1]
                                        }}<span
                                            v-if="field[2]"
                                            class="text-destructive"
                                        >
                                            *</span
                                        >
                                    </Label>
                                    <Input
                                        :id="field[0]"
                                        :name="field[0]"
                                        :default-value="
                                            (props.comunidad?.[field[0]] as
                                                string | number) ?? ''
                                        "
                                        :aria-invalid="!!errors[field[0]]"
                                    />
                                    <InputError :message="errors[field[0]]" />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="junta" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle>Junta directiva</CardTitle>
                                <CardDescription
                                    >Personas responsables y teléfonos de
                                    contacto.</CardDescription
                                >
                            </CardHeader>
                            <div
                                class="grid gap-x-8 gap-y-6 p-5 md:grid-cols-2 md:p-8"
                            >
                                <div
                                    v-for="field in juntaFields"
                                    :key="field[0]"
                                    class="grid content-start gap-2"
                                >
                                    <Label :for="field[0]">{{
                                        field[1]
                                    }}</Label>
                                    <Input
                                        :id="field[0]"
                                        :name="field[0]"
                                        :default-value="
                                            (props.comunidad?.[field[0]] as
                                                string | number) ?? ''
                                        "
                                        :aria-invalid="!!errors[field[0]]"
                                    />
                                    <InputError :message="errors[field[0]]" />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="seguro" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle>Seguro de la comunidad</CardTitle>
                                <CardDescription
                                    >Compañía, póliza y contacto para
                                    incidencias.</CardDescription
                                >
                            </CardHeader>
                            <div
                                class="grid gap-x-8 gap-y-6 p-5 md:grid-cols-2 md:p-8"
                            >
                                <div
                                    v-for="field in seguroFields"
                                    :key="field[0]"
                                    class="grid content-start gap-2"
                                >
                                    <Label :for="field[0]">{{
                                        field[1]
                                    }}</Label>
                                    <Input
                                        :id="field[0]"
                                        :name="field[0]"
                                        :default-value="
                                            (props.comunidad?.[field[0]] as
                                                string | number) ?? ''
                                        "
                                        :aria-invalid="!!errors[field[0]]"
                                    />
                                    <InputError :message="errors[field[0]]" />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="liquidacion" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle
                                    >Condiciones de liquidación</CardTitle
                                >
                                <CardDescription
                                    >Plazos, penalizaciones y texto que
                                    aparecerá en las
                                    liquidaciones.</CardDescription
                                >
                            </CardHeader>
                            <div class="grid gap-6 p-5 md:p-8">
                                <div class="grid gap-5 md:grid-cols-3">
                                    <div class="grid content-start gap-2">
                                        <Label for="fondo_reserva"
                                            >Fondo de reserva</Label
                                        >
                                        <Input
                                            id="fondo_reserva"
                                            name="fondo_reserva"
                                            type="number"
                                            min="0"
                                            step="0.0001"
                                            :default-value="
                                                (props.comunidad
                                                    ?.fondo_reserva as number) ??
                                                0
                                            "
                                            :aria-invalid="
                                                !!errors.fondo_reserva
                                            "
                                        />
                                        <InputError
                                            :message="errors.fondo_reserva"
                                        />
                                    </div>
                                    <div class="grid content-start gap-2">
                                        <Label for="plazo_maximo_pago_dias"
                                            >Plazo máximo de pago</Label
                                        >
                                        <Input
                                            id="plazo_maximo_pago_dias"
                                            name="plazo_maximo_pago_dias"
                                            type="number"
                                            min="0"
                                            placeholder="Sin límite"
                                            :default-value="
                                                (props.comunidad
                                                    ?.plazo_maximo_pago_dias as number) ??
                                                ''
                                            "
                                            :aria-invalid="
                                                !!errors.plazo_maximo_pago_dias
                                            "
                                        />
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Déjalo vacío para no aplicar límite.
                                        </p>
                                        <InputError
                                            :message="
                                                errors.plazo_maximo_pago_dias
                                            "
                                        />
                                    </div>
                                    <div class="grid content-start gap-2">
                                        <Label for="penalizacion"
                                            >Penalización (%)</Label
                                        >
                                        <Input
                                            id="penalizacion"
                                            name="penalizacion"
                                            type="number"
                                            min="0"
                                            step="0.0001"
                                            :default-value="
                                                (props.comunidad
                                                    ?.penalizacion as number) ??
                                                0
                                            "
                                            :aria-invalid="
                                                !!errors.penalizacion
                                            "
                                        />
                                        <InputError
                                            :message="errors.penalizacion"
                                        />
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="texto_liquidacion"
                                        >Texto de liquidación</Label
                                    >
                                    <Textarea
                                        id="texto_liquidacion"
                                        name="texto_liquidacion"
                                        class="min-h-36 resize-y"
                                        :default-value="
                                            (props.comunidad
                                                ?.texto_liquidacion as string) ??
                                            ''
                                        "
                                        :aria-invalid="
                                            !!errors.texto_liquidacion
                                        "
                                    />
                                    <InputError
                                        :message="errors.texto_liquidacion"
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="impresion" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle
                                    >Configuración de impresión</CardTitle
                                >
                                <CardDescription
                                    >Aspecto y contenido de los informes y
                                    resúmenes.</CardDescription
                                >
                            </CardHeader>
                            <div class="grid gap-6 p-5 md:p-8">
                                <div class="grid gap-5 md:grid-cols-2">
                                    <div class="grid content-start gap-2">
                                        <Label for="copias_informe"
                                            >Copias de la página
                                            principal</Label
                                        >
                                        <Input
                                            id="copias_informe"
                                            name="copias_informe"
                                            type="number"
                                            min="0"
                                            :default-value="
                                                (props.comunidad
                                                    ?.copias_informe as number) ??
                                                1
                                            "
                                            :aria-invalid="
                                                !!errors.copias_informe
                                            "
                                        />
                                        <InputError
                                            :message="errors.copias_informe"
                                        />
                                    </div>
                                    <div class="grid content-start gap-2">
                                        <Label for="modelo_impresion"
                                            >Modelo de impresión</Label
                                        >
                                        <Input
                                            id="modelo_impresion"
                                            name="modelo_impresion"
                                            placeholder="Configuración informe de liquidación"
                                            :default-value="
                                                (props.comunidad
                                                    ?.modelo_impresion as string) ??
                                                ''
                                            "
                                            :aria-invalid="
                                                !!errors.modelo_impresion
                                            "
                                        />
                                        <InputError
                                            :message="errors.modelo_impresion"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <label
                                        for="imprimir_estado"
                                        class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border bg-muted/20 p-4 transition-colors hover:bg-muted/40"
                                    >
                                        <span class="grid gap-1">
                                            <span class="text-sm font-medium"
                                                >Estado de la comunidad</span
                                            >
                                            <span
                                                class="text-xs leading-relaxed text-muted-foreground"
                                            >
                                                Incluir el estado de la
                                                comunidad en las liquidaciones.
                                            </span>
                                        </span>
                                        <input
                                            type="hidden"
                                            name="imprimir_estado"
                                            value="0"
                                        />
                                        <Switch
                                            id="imprimir_estado"
                                            name="imprimir_estado"
                                            value="1"
                                            :default-value="
                                                !!props.comunidad
                                                    ?.imprimir_estado
                                            "
                                        />
                                    </label>
                                    <label
                                        for="imprimir_nombres_resumen"
                                        class="flex cursor-pointer items-start justify-between gap-4 rounded-xl border bg-muted/20 p-4 transition-colors hover:bg-muted/40"
                                    >
                                        <span class="grid gap-1">
                                            <span class="text-sm font-medium"
                                                >Nombres en el resumen</span
                                            >
                                            <span
                                                class="text-xs leading-relaxed text-muted-foreground"
                                            >
                                                Mostrar propietarios e
                                                inquilinos en el resumen de la
                                                comunidad.
                                            </span>
                                        </span>
                                        <input
                                            type="hidden"
                                            name="imprimir_nombres_resumen"
                                            value="0"
                                        />
                                        <Switch
                                            id="imprimir_nombres_resumen"
                                            name="imprimir_nombres_resumen"
                                            value="1"
                                            :default-value="
                                                !!props.comunidad
                                                    ?.imprimir_nombres_resumen
                                            "
                                        />
                                    </label>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="edificio" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle>Edificio y observaciones</CardTitle>
                                <CardDescription
                                    >Información técnica, IEE y notas operativas
                                    de la comunidad.</CardDescription
                                >
                            </CardHeader>
                            <div class="grid gap-6 p-5 md:p-8">
                                <div
                                    class="grid gap-5 md:grid-cols-[14rem_1fr]"
                                >
                                    <div class="grid content-start gap-2">
                                        <Label for="ano_construccion"
                                            >Año de construcción</Label
                                        >
                                        <Input
                                            id="ano_construccion"
                                            name="ano_construccion"
                                            type="number"
                                            min="0"
                                            :max="new Date().getFullYear()"
                                            placeholder="1973"
                                            :default-value="
                                                (props.comunidad
                                                    ?.ano_construccion as number) ??
                                                ''
                                            "
                                            :aria-invalid="
                                                !!errors.ano_construccion
                                            "
                                        />
                                        <InputError
                                            :message="errors.ano_construccion"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="iee">IEE</Label>
                                        <Textarea
                                            id="iee"
                                            name="iee"
                                            class="min-h-24 resize-y"
                                            placeholder="Datos, inspecciones y próximas revisiones del edificio"
                                            :default-value="
                                                (props.comunidad
                                                    ?.iee as string) ?? ''
                                            "
                                            :aria-invalid="!!errors.iee"
                                        />
                                        <InputError :message="errors.iee" />
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="observaciones"
                                        >Observaciones</Label
                                    >
                                    <Textarea
                                        id="observaciones"
                                        name="observaciones"
                                        class="min-h-52 resize-y"
                                        placeholder="Contactos, incidencias, acuerdos y cualquier otra información útil"
                                        :default-value="
                                            (props.comunidad
                                                ?.observaciones as string) ?? ''
                                        "
                                        :aria-invalid="!!errors.observaciones"
                                    />
                                    <InputError
                                        :message="errors.observaciones"
                                    />
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="bancos" class="m-0">
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <div
                                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
                                >
                                    <div class="grid gap-1.5">
                                        <CardTitle>Cuentas bancarias</CardTitle>
                                        <CardDescription>
                                            Añade las cuentas de la comunidad y
                                            marca la que se usará por defecto.
                                        </CardDescription>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="addBanco"
                                    >
                                        <Plus class="size-4" /> Añadir cuenta
                                    </Button>
                                </div>
                            </CardHeader>
                            <div class="grid gap-4 p-5 md:p-8">
                                <div
                                    v-if="!bancos.length"
                                    class="grid min-h-48 place-items-center rounded-xl border border-dashed bg-muted/15 p-8 text-center"
                                >
                                    <div
                                        class="grid max-w-sm justify-items-center gap-3"
                                    >
                                        <span
                                            class="grid size-11 place-items-center rounded-full bg-primary/10 text-primary"
                                        >
                                            <Landmark class="size-5" />
                                        </span>
                                        <div>
                                            <p class="font-medium">
                                                Todavía no hay cuentas bancarias
                                            </p>
                                            <p
                                                class="mt-1 text-sm text-muted-foreground"
                                            >
                                                Añade la primera para completar
                                                la ficha de la comunidad.
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            size="sm"
                                            @click="addBanco"
                                        >
                                            <Plus class="size-4" /> Añadir
                                            cuenta
                                        </Button>
                                    </div>
                                </div>

                                <div
                                    v-for="(banco, bancoIndex) in bancos"
                                    :key="bancoIndex"
                                    class="overflow-hidden rounded-xl border bg-background shadow-xs"
                                >
                                    <div
                                        class="flex items-center justify-between gap-3 border-b bg-muted/25 px-4 py-3"
                                    >
                                        <label
                                            class="flex cursor-pointer items-center gap-3 text-sm font-medium"
                                        >
                                            <input
                                                type="radio"
                                                name="banco_principal"
                                                :value="bancoIndex"
                                                :checked="
                                                    principalIndex ===
                                                    bancoIndex
                                                "
                                                class="size-4 accent-primary"
                                                @change="
                                                    principalIndex = bancoIndex
                                                "
                                            />
                                            Cuenta {{ bancoIndex + 1 }}
                                            <span
                                                v-if="
                                                    principalIndex ===
                                                    bancoIndex
                                                "
                                                class="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                                            >
                                                Principal
                                            </span>
                                        </label>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            class="text-muted-foreground hover:text-destructive"
                                            :aria-label="`Eliminar cuenta ${bancoIndex + 1}`"
                                            @click="removeBanco(bancoIndex)"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>

                                    <div
                                        class="grid gap-5 p-4 md:grid-cols-2 md:p-5 lg:grid-cols-3"
                                    >
                                        <div
                                            class="grid content-start gap-2 lg:col-span-2"
                                        >
                                            <Label
                                                :for="`banco-${bancoIndex}-nombre`"
                                                >Banco / descripción</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-nombre`"
                                                v-model="banco.nombre"
                                                :name="`bancos.${bancoIndex}.nombre`"
                                                placeholder="CAIXABANK"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.nombre`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.nombre`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-codigo`"
                                                >Código interno</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-codigo`"
                                                v-model="banco.codigo_interno"
                                                :name="`bancos.${bancoIndex}.codigo_interno`"
                                                placeholder="541003"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.codigo_interno`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.codigo_interno`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div
                                            class="grid content-start gap-2 lg:col-span-2"
                                        >
                                            <Label
                                                :for="`banco-${bancoIndex}-iban`"
                                                >IBAN / cuenta</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-iban`"
                                                v-model="banco.iban"
                                                :name="`bancos.${bancoIndex}.iban`"
                                                autocomplete="off"
                                                placeholder="ES00 0000 0000 0000 0000 0000"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.iban`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.iban`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-bic`"
                                                >BIC / SWIFT</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-bic`"
                                                v-model="banco.bic"
                                                :name="`bancos.${bancoIndex}.bic`"
                                                autocomplete="off"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.bic`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.bic`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div
                                            class="grid content-start gap-2 lg:col-span-2"
                                        >
                                            <Label
                                                :for="`banco-${bancoIndex}-direccion`"
                                                >Dirección</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-direccion`"
                                                v-model="banco.direccion"
                                                :name="`bancos.${bancoIndex}.direccion`"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.direccion`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.direccion`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-cp`"
                                                >Código postal</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-cp`"
                                                v-model="banco.codigo_postal"
                                                :name="`bancos.${bancoIndex}.codigo_postal`"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.codigo_postal`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.codigo_postal`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-poblacion`"
                                                >Población</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-poblacion`"
                                                v-model="banco.poblacion"
                                                :name="`bancos.${bancoIndex}.poblacion`"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.poblacion`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.poblacion`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-provincia`"
                                                >Provincia</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-provincia`"
                                                v-model="banco.provincia"
                                                :name="`bancos.${bancoIndex}.provincia`"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.provincia`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.provincia`
                                                    ]
                                                "
                                            />
                                        </div>
                                        <div class="grid content-start gap-2">
                                            <Label
                                                :for="`banco-${bancoIndex}-telefonos`"
                                                >Teléfonos</Label
                                            >
                                            <Input
                                                :id="`banco-${bancoIndex}-telefonos`"
                                                v-model="banco.telefonos"
                                                :name="`bancos.${bancoIndex}.telefonos`"
                                                :aria-invalid="
                                                    !!errors[
                                                        `bancos.${bancoIndex}.telefonos`
                                                    ]
                                                "
                                            />
                                            <InputError
                                                :message="
                                                    errors[
                                                        `bancos.${bancoIndex}.telefonos`
                                                    ]
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent
                            v-if="!props.comunidad"
                            value="importar"
                            class="m-0"
                        >
                            <CardHeader
                                class="border-b bg-background px-5 py-5 md:px-8"
                            >
                                <CardTitle
                                    >Importar partes y propietarios</CardTitle
                                >
                                <CardDescription>
                                    Opcionalmente, adjunta el CSV al crear la
                                    comunidad para cargar sus partes y
                                    propietarios de una sola vez.
                                </CardDescription>
                            </CardHeader>
                            <div class="grid gap-5 p-5 md:p-8">
                                <div
                                    class="grid gap-5 rounded-xl border border-dashed bg-muted/15 p-5 md:grid-cols-[auto_1fr] md:items-start md:p-6"
                                >
                                    <span
                                        class="grid size-11 place-items-center rounded-full bg-primary/10 text-primary"
                                    >
                                        <Upload class="size-5" />
                                    </span>
                                    <div class="grid gap-4">
                                        <div class="grid gap-1">
                                            <p class="font-medium">
                                                CSV compatible con Excel
                                            </p>
                                            <p
                                                class="text-sm leading-relaxed text-muted-foreground"
                                            >
                                                Debe estar codificado en UTF-8,
                                                separado por punto y coma y
                                                pesar como máximo 10 MB.
                                            </p>
                                        </div>

                                        <div
                                            class="flex flex-wrap gap-2 text-xs"
                                        >
                                            <span
                                                class="rounded-md border bg-background px-2 py-1 font-mono"
                                                >parte_codigo</span
                                            >
                                            <span
                                                class="rounded-md border bg-background px-2 py-1 font-mono"
                                                >propietario_nombre</span
                                            >
                                            <span
                                                class="self-center text-muted-foreground"
                                                >son las columnas
                                                obligatorias</span
                                            >
                                        </div>

                                        <input
                                            id="archivo"
                                            name="archivo"
                                            type="file"
                                            accept=".csv,.txt,text/csv,text/plain"
                                            class="block w-full rounded-xl border bg-background p-2 text-sm shadow-xs file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90"
                                            :aria-invalid="!!errors.archivo"
                                        />
                                        <InputError :message="errors.archivo" />

                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Si no necesitas importar datos,
                                            puedes dejar este campo vacío y
                                            crear la comunidad normalmente.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>
                    </Tabs>

                    <div
                        class="flex flex-col-reverse gap-3 border-t bg-muted/15 px-5 py-4 sm:flex-row sm:justify-end md:px-8"
                    >
                        <Button variant="outline" as-child>
                            <Link :href="index()">Cancelar</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{
                                processing ? 'Guardando…' : 'Guardar comunidad'
                            }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </Form>
    </main>
</template>
