<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { store, show } from '@/routes/propietarios';

type Propietario = {
    id: number;
    nombre: string;
    conyuge?: string | null;
    telefono?: string | null;
    telefono_trabajo?: string | null;
    movil?: string | null;
    emails?: string | null;
    partes_count: number;
};

defineProps<{
    propietarios: {
        data: Propietario[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Propietarios', href: '/propietarios' }],
        title: 'Propietarios',
    },
});

function telefonos(propietario: Propietario): string {
    return (
        [propietario.telefono, propietario.telefono_trabajo, propietario.movil]
            .map((telefono) => telefono?.trim())
            .filter(Boolean)
            .join(' · ') || '—'
    );
}

function abrirPropietario(propietarioId: number): void {
    router.visit(show(propietarioId));
}

function etiquetaPaginacion(etiqueta: string): string {
    return etiqueta.replace('&laquo;', '‹').replace('&raquo;', '›');
}
</script>
<template>
    <Head title="Propietarios" />
    <main
        class="grid flex-1 gap-6 p-4 md:p-8 xl:h-[calc(100svh-5rem)] xl:min-h-0 xl:flex-none xl:grid-cols-[minmax(0,1fr)_30rem] xl:overflow-hidden"
    >
        <section class="flex min-h-0 flex-col">
            <div
                class="min-h-0 flex-1 overflow-hidden rounded-xl border bg-card xl:[&>[data-slot=table-container]]:h-full xl:[&>[data-slot=table-container]]:overflow-auto"
            >
                <Table class="min-w-4xl">
                    <TableHeader class="sticky top-0 z-10 bg-card">
                        <TableRow>
                            <TableHead class="min-w-56">Nombre</TableHead>
                            <TableHead class="min-w-48">Cónyuge</TableHead>
                            <TableHead class="min-w-56">Teléfonos</TableHead>
                            <TableHead class="min-w-56">Email</TableHead>
                            <TableHead class="w-28 text-right"
                                >Partes</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="propietario in propietarios.data"
                            :key="propietario.id"
                            role="link"
                            tabindex="0"
                            :aria-label="`Abrir propietario ${propietario.nombre}`"
                            class="cursor-pointer outline-none hover:bg-muted/50 focus-visible:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-inset"
                            @click="abrirPropietario(propietario.id)"
                            @keydown.enter="abrirPropietario(propietario.id)"
                            @keydown.space.prevent="
                                abrirPropietario(propietario.id)
                            "
                        >
                            <TableCell class="font-semibold text-brand-value">
                                {{ propietario.nombre }}
                            </TableCell>
                            <TableCell>{{
                                propietario.conyuge || '—'
                            }}</TableCell>
                            <TableCell>{{ telefonos(propietario) }}</TableCell>
                            <TableCell>{{
                                propietario.emails || '—'
                            }}</TableCell>
                            <TableCell class="text-right">
                                <span
                                    class="inline-flex rounded-full bg-muted px-3 py-1 text-xs whitespace-nowrap"
                                >
                                    {{ propietario.partes_count }}
                                    {{
                                        propietario.partes_count === 1
                                            ? 'parte'
                                            : 'partes'
                                    }}
                                </span>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="!propietarios.data.length">
                            <TableCell
                                :colspan="5"
                                class="h-28 text-center text-muted-foreground"
                            >
                                No hay propietarios.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <nav
                v-if="propietarios.links.length > 3"
                aria-label="Paginación de propietarios"
                class="mt-6 flex flex-wrap justify-center gap-1"
            >
                <Link
                    v-for="(pagina, paginaIndex) in propietarios.links"
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
        </section>
        <Form
            v-bind="store.form()"
            class="grid content-start gap-4 rounded-xl border bg-card p-4 xl:min-h-0 xl:overflow-y-auto"
            #default="{ errors, processing }"
        >
            <div>
                <h2 class="font-semibold">Nuevo propietario</h2>
                <p class="text-sm text-muted-foreground">
                    Completa su ficha de contacto y cobro.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm font-medium">Nombre</span>
                    <Input id="nuevo-nombre" name="nombre" required />
                    <InputError :message="errors.nombre" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">DNI / NIF</span>
                    <Input id="nuevo-nif" name="nif" />
                    <InputError :message="errors.nif" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Cónyuge</span>
                    <Input id="nuevo-conyuge" name="conyuge" />
                    <InputError :message="errors.conyuge" />
                </label>
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm font-medium">Dirección</span>
                    <Input id="nuevo-direccion" name="direccion" />
                    <InputError :message="errors.direccion" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Código postal</span>
                    <Input id="nuevo-codigo-postal" name="codigo_postal" />
                    <InputError :message="errors.codigo_postal" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Población</span>
                    <Input id="nuevo-poblacion" name="poblacion" />
                    <InputError :message="errors.poblacion" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Provincia</span>
                    <Input id="nuevo-provincia" name="provincia" />
                    <InputError :message="errors.provincia" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">País</span>
                    <Input id="nuevo-pais" name="pais" />
                    <InputError :message="errors.pais" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Teléfono</span>
                    <Input id="nuevo-telefono" name="telefono" type="tel" />
                    <InputError :message="errors.telefono" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Teléfono de trabajo</span>
                    <Input
                        id="nuevo-telefono-trabajo"
                        name="telefono_trabajo"
                        type="tel"
                    />
                    <InputError :message="errors.telefono_trabajo" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Móvil</span>
                    <Input id="nuevo-movil" name="movil" type="tel" />
                    <InputError :message="errors.movil" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">Email</span>
                    <Input id="nuevo-emails" name="emails" />
                    <InputError :message="errors.emails" />
                </label>
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm font-medium">IBAN</span>
                    <Input
                        id="nuevo-iban"
                        name="iban"
                        class="font-mono uppercase"
                    />
                    <InputError :message="errors.iban" />
                </label>
                <label class="grid gap-1">
                    <span class="text-sm font-medium">BIC</span>
                    <Input
                        id="nuevo-bic"
                        name="bic"
                        class="font-mono uppercase"
                    />
                    <InputError :message="errors.bic" />
                </label>
                <fieldset class="grid gap-1 sm:col-span-2">
                    <legend class="text-sm font-medium">Tipo de ficha</legend>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="tipo" value="1" />
                            Propietario
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="tipo" value="2" />
                            Inquilino
                        </label>
                    </div>
                    <InputError :message="errors.tipo" />
                </fieldset>
                <div class="grid gap-2 sm:col-span-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input
                            type="hidden"
                            name="domiciliar_recibos"
                            value="0"
                        />
                        <input
                            type="checkbox"
                            name="domiciliar_recibos"
                            value="1"
                        />
                        Domiciliar recibos
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="enviar_email" value="0" />
                        <input type="checkbox" name="enviar_email" value="1" />
                        Recibo electrónico
                    </label>
                </div>
                <label class="grid gap-1 sm:col-span-2">
                    <span class="text-sm font-medium">Observaciones</span>
                    <Textarea
                        id="nuevo-observaciones"
                        name="observaciones"
                        class="min-h-28"
                    />
                    <InputError :message="errors.observaciones" />
                </label>
            </div>

            <Button type="submit" :disabled="processing">
                Crear propietario
            </Button>
        </Form>
    </main>
</template>
