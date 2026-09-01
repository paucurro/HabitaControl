<script setup lang="ts">
import { Form, Head, Link, router, setLayoutProps } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { show as showComunidad } from '@/routes/comunidades';
import {
    index as propietariosIndex,
    invitacion,
    update,
} from '@/routes/propietarios';

type Parte = {
    id: number;
    codigo: string;
    descripcion?: string | null;
    comunidad: { id: number; nombre: string; codigo: string };
};

type Propietario = {
    id: number;
    tipo?: string | null;
    nombre: string;
    conyuge?: string | null;
    nif?: string | null;
    direccion?: string | null;
    codigo_postal?: string | null;
    poblacion?: string | null;
    provincia?: string | null;
    pais?: string | null;
    telefono?: string | null;
    telefono_trabajo?: string | null;
    movil?: string | null;
    emails?: string | null;
    iban?: string | null;
    bic?: string | null;
    observaciones?: string | null;
    enviar_email: boolean;
    domiciliar_recibos: boolean;
    acceso_web: boolean;
    partes: Parte[];
};

const props = defineProps<{
    canInvite: boolean;
    canManage: boolean;
    propietario: Propietario;
}>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Propietarios', href: '/propietarios' }] },
});

setLayoutProps({
    title: props.propietario.nombre,
    description: 'Ficha global en todas las comunidades',
});

function volver(): void {
    if (window.history.length > 1) {
        window.history.back();

        return;
    }

    router.visit(propietariosIndex.url());
}

function invitar(): void {
    router.post(
        invitacion.url(props.propietario.id),
        {},
        { preserveScroll: true },
    );
}

function tipoFicha(tipo?: string | null): string {
    if (tipo === '1') {
        return 'Propietario';
    }

    if (tipo === '2') {
        return 'Inquilino';
    }

    return '—';
}
</script>

<template>
    <Head :title="propietario.nombre" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-center gap-2">
            <Button
                type="button"
                variant="ghost"
                size="icon-sm"
                class="cursor-pointer"
                aria-label="Volver a la pantalla anterior"
                title="Volver"
                @click="volver"
            >
                <ArrowLeft class="size-5" />
            </Button>
        </div>

        <Form
            v-if="canManage"
            v-bind="update.form(propietario.id)"
            class="grid gap-6"
            #default="{ errors, processing }"
        >
            <section class="overflow-hidden rounded-xl border bg-card">
                <div class="border-b p-4">
                    <h2 class="font-semibold">Datos del propietario</h2>
                    <p class="text-sm text-muted-foreground">
                        Información personal, de contacto y de cobro.
                    </p>
                </div>

                <div class="grid gap-5 p-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="nombre">Nombre</Label>
                        <Input
                            id="nombre"
                            name="nombre"
                            :default-value="propietario.nombre"
                            required
                        />
                        <InputError :message="errors.nombre" />
                    </div>

                    <div class="grid gap-2 md:col-span-2 xl:col-span-1">
                        <Label for="nif">DNI / NIF</Label>
                        <Input
                            id="nif"
                            name="nif"
                            :default-value="propietario.nif ?? ''"
                        />
                        <InputError :message="errors.nif" />
                    </div>

                    <div class="grid gap-2 md:col-span-2 xl:col-span-1">
                        <Label for="conyuge">Cónyuge</Label>
                        <Input
                            id="conyuge"
                            name="conyuge"
                            :default-value="propietario.conyuge ?? ''"
                        />
                        <InputError :message="errors.conyuge" />
                    </div>

                    <div class="grid gap-2 md:col-span-2 xl:col-span-4">
                        <Label for="direccion">Dirección</Label>
                        <Input
                            id="direccion"
                            name="direccion"
                            :default-value="propietario.direccion ?? ''"
                        />
                        <InputError :message="errors.direccion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="codigo_postal">Código postal</Label>
                        <Input
                            id="codigo_postal"
                            name="codigo_postal"
                            :default-value="propietario.codigo_postal ?? ''"
                        />
                        <InputError :message="errors.codigo_postal" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="poblacion">Población</Label>
                        <Input
                            id="poblacion"
                            name="poblacion"
                            :default-value="propietario.poblacion ?? ''"
                        />
                        <InputError :message="errors.poblacion" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="provincia">Provincia</Label>
                        <Input
                            id="provincia"
                            name="provincia"
                            :default-value="propietario.provincia ?? ''"
                        />
                        <InputError :message="errors.provincia" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pais">País</Label>
                        <Input
                            id="pais"
                            name="pais"
                            :default-value="propietario.pais ?? ''"
                        />
                        <InputError :message="errors.pais" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="telefono">Teléfono</Label>
                        <Input
                            id="telefono"
                            name="telefono"
                            type="tel"
                            :default-value="propietario.telefono ?? ''"
                        />
                        <InputError :message="errors.telefono" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="telefono_trabajo"
                            >Teléfono de trabajo</Label
                        >
                        <Input
                            id="telefono_trabajo"
                            name="telefono_trabajo"
                            type="tel"
                            :default-value="propietario.telefono_trabajo ?? ''"
                        />
                        <InputError :message="errors.telefono_trabajo" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="movil">Móvil</Label>
                        <Input
                            id="movil"
                            name="movil"
                            type="tel"
                            :default-value="propietario.movil ?? ''"
                        />
                        <InputError :message="errors.movil" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="emails">Email</Label>
                        <Input
                            id="emails"
                            name="emails"
                            :default-value="propietario.emails ?? ''"
                        />
                        <p class="text-xs text-muted-foreground">
                            Separa varias direcciones con punto y coma.
                        </p>
                        <InputError :message="errors.emails" />
                    </div>

                    <div class="grid gap-2 md:col-span-2 xl:col-span-3">
                        <Label for="iban">IBAN</Label>
                        <Input
                            id="iban"
                            name="iban"
                            :default-value="propietario.iban ?? ''"
                            class="font-mono uppercase"
                        />
                        <InputError :message="errors.iban" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="bic">BIC</Label>
                        <Input
                            id="bic"
                            name="bic"
                            :default-value="propietario.bic ?? ''"
                            class="font-mono uppercase"
                        />
                        <InputError :message="errors.bic" />
                    </div>

                    <fieldset class="grid gap-2 md:col-span-2">
                        <legend class="text-sm font-medium">
                            Tipo de ficha
                        </legend>
                        <div class="flex flex-wrap gap-5">
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="tipo"
                                    value="1"
                                    :checked="propietario.tipo === '1'"
                                />
                                Propietario
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="radio"
                                    name="tipo"
                                    value="2"
                                    :checked="propietario.tipo === '2'"
                                />
                                Inquilino
                            </label>
                        </div>
                        <InputError :message="errors.tipo" />
                    </fieldset>

                    <fieldset class="grid gap-2 md:col-span-2">
                        <legend class="text-sm font-medium">Recibos</legend>
                        <div class="flex flex-wrap gap-5">
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
                                    :checked="propietario.domiciliar_recibos"
                                />
                                Domiciliar recibos
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input
                                    type="hidden"
                                    name="enviar_email"
                                    value="0"
                                />
                                <input
                                    type="checkbox"
                                    name="enviar_email"
                                    value="1"
                                    :checked="propietario.enviar_email"
                                />
                                Recibo electrónico
                            </label>
                        </div>
                    </fieldset>

                    <div class="grid gap-2 md:col-span-2 xl:col-span-4">
                        <Label for="observaciones">Observaciones</Label>
                        <Textarea
                            id="observaciones"
                            name="observaciones"
                            :default-value="propietario.observaciones ?? ''"
                            class="min-h-32"
                        />
                        <InputError :message="errors.observaciones" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t p-4">
                    <Button type="submit" :disabled="processing">
                        Guardar cambios
                    </Button>
                    <Button
                        v-if="canInvite && propietario.emails"
                        type="button"
                        variant="outline"
                        @click="invitar"
                    >
                        {{
                            propietario.acceso_web
                                ? 'Reenviar invitación de acceso'
                                : 'Invitar al acceso web'
                        }}
                    </Button>
                </div>
            </section>
        </Form>

        <section v-else class="overflow-hidden rounded-xl border bg-card">
            <div class="border-b p-4 font-semibold">Datos del propietario</div>
            <dl class="grid gap-x-8 gap-y-4 p-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <dt class="text-xs text-muted-foreground">Nombre</dt>
                    <dd>{{ propietario.nombre }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">DNI / NIF</dt>
                    <dd>{{ propietario.nif || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Cónyuge</dt>
                    <dd>{{ propietario.conyuge || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Tipo de ficha</dt>
                    <dd>{{ tipoFicha(propietario.tipo) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-muted-foreground">Dirección</dt>
                    <dd>{{ propietario.direccion || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Código postal</dt>
                    <dd>{{ propietario.codigo_postal || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Población</dt>
                    <dd>{{ propietario.poblacion || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Provincia</dt>
                    <dd>{{ propietario.provincia || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">País</dt>
                    <dd>{{ propietario.pais || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Teléfono</dt>
                    <dd>{{ propietario.telefono || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">
                        Teléfono de trabajo
                    </dt>
                    <dd>{{ propietario.telefono_trabajo || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Móvil</dt>
                    <dd>{{ propietario.movil || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Email</dt>
                    <dd class="break-all">{{ propietario.emails || '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-muted-foreground">IBAN</dt>
                    <dd class="font-mono break-all">
                        {{ propietario.iban || '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">BIC</dt>
                    <dd class="font-mono">{{ propietario.bic || '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">
                        Recibo electrónico
                    </dt>
                    <dd>{{ propietario.enviar_email ? 'Sí' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">
                        Domiciliar recibos
                    </dt>
                    <dd>{{ propietario.domiciliar_recibos ? 'Sí' : 'No' }}</dd>
                </div>
                <div class="sm:col-span-2 xl:col-span-4">
                    <dt class="text-xs text-muted-foreground">Observaciones</dt>
                    <dd class="whitespace-pre-wrap">
                        {{ propietario.observaciones || '—' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-xl border bg-card">
            <div class="border-b p-4 font-semibold">Comunidades y partes</div>
            <Link
                v-for="parte in propietario.partes"
                :key="parte.id"
                :href="canManage ? showComunidad(parte.comunidad.id) : '#'"
                :class="[
                    'flex items-center border-b p-4 last:border-0',
                    canManage ? 'hover:bg-muted/50' : 'pointer-events-none',
                ]"
            >
                <div class="flex-1">
                    <p class="font-medium">{{ parte.comunidad.nombre }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ parte.comunidad.codigo }}
                    </p>
                </div>
                <span class="rounded-full bg-muted px-3 py-1 text-xs">
                    {{ parte.codigo }} · {{ parte.descripcion }}
                </span>
            </Link>
            <p
                v-if="!propietario.partes.length"
                class="p-8 text-center text-muted-foreground"
            >
                No tiene partes relacionadas.
            </p>
        </section>
    </main>
</template>
