<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { CheckCheck, Search, Users, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { show as showComunidad } from '@/routes/comunidades';
import { store } from '@/routes/comunidades/partes';
import { update } from '@/routes/partes';

const props = defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    tiposDeposito: Array<{ id: number; nombre: string }>;
    propietarios: Array<{ id: number; nombre: string; nif?: string }>;
    parte?: {
        id: number;
        codigo: string;
        descripcion?: string;
        tipo_deposito_id?: number | null;
        deposito?: string;
        coeficiente_general?: string;
        orden?: string;
        tomo?: string;
        libro?: string;
        folio?: string;
        finca?: string;
        observaciones?: string;
        propietarios?: Array<{ id: number }>;
    };
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const selectClass =
    'h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';

const textFields = [
    ['orden', 'Orden'],
    ['tomo', 'Tomo'],
    ['libro', 'Libro'],
    ['folio', 'Folio'],
    ['finca', 'Finca'],
] as const;

const propietarioSearch = ref('');
const selectedPropietarios = ref(
    new Set(
        (props.parte?.propietarios ?? []).map((propietario) => propietario.id),
    ),
);

const filteredPropietarios = computed(() => {
    const search = propietarioSearch.value.trim().toLocaleLowerCase('es');

    if (!search) {
        return props.propietarios;
    }

    return props.propietarios.filter((propietario) =>
        `${propietario.nombre} ${propietario.nif ?? ''}`
            .toLocaleLowerCase('es')
            .includes(search),
    );
});

const selectedPropietarioIds = computed(() => [...selectedPropietarios.value]);
const selectedPropietariosList = computed(() =>
    props.propietarios.filter((propietario) =>
        selectedPropietarios.value.has(propietario.id),
    ),
);
const allFilteredPropietariosSelected = computed(
    () =>
        filteredPropietarios.value.length > 0 &&
        filteredPropietarios.value.every((propietario) =>
            selectedPropietarios.value.has(propietario.id),
        ),
);

function togglePropietario(propietarioId: number, selected: boolean): void {
    const nextSelected = new Set(selectedPropietarios.value);

    if (selected) {
        nextSelected.add(propietarioId);
    } else {
        nextSelected.delete(propietarioId);
    }

    selectedPropietarios.value = nextSelected;
}

function selectFilteredPropietarios(): void {
    const nextSelected = new Set(selectedPropietarios.value);

    for (const propietario of filteredPropietarios.value) {
        nextSelected.add(propietario.id);
    }

    selectedPropietarios.value = nextSelected;
}

function clearSelectedPropietarios(): void {
    selectedPropietarios.value = new Set();
}
</script>
<template>
    <Head :title="props.parte ? 'Editar parte' : 'Nueva parte'" />
    <main
        class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 md:p-8"
    >
        <div>
            <CommunityBackLink :href="showComunidad.url(comunidad.id)">
                {{ comunidad.nombre }}
            </CommunityBackLink>
            <h1 class="mt-3 text-2xl font-semibold">
                {{ props.parte ? 'Editar parte' : 'Nueva parte' }}
            </h1>
        </div>
        <Form
            v-bind="
                props.parte
                    ? update.form(props.parte.id)
                    : store.form(comunidad.id)
            "
            #default="{ errors, processing }"
        >
            <Card>
                <CardContent class="grid gap-8 p-5 md:p-8">
                    <input
                        v-if="!props.parte"
                        type="hidden"
                        name="comunidad_id"
                        :value="comunidad.id"
                    />

                    <div class="grid gap-x-8 gap-y-5 md:grid-cols-2">
                        <div class="grid gap-1.5">
                            <Label for="codigo"
                                >Código<span class="text-destructive"
                                    >&nbsp;*</span
                                ></Label
                            >
                            <Input
                                id="codigo"
                                name="codigo"
                                :default-value="props.parte?.codigo ?? ''"
                                :aria-invalid="!!errors.codigo"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.codigo
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="descripcion">Descripción</Label>
                            <Input
                                id="descripcion"
                                name="descripcion"
                                :default-value="props.parte?.descripcion ?? ''"
                                :aria-invalid="!!errors.descripcion"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.descripcion
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="tipo_deposito_id"
                                >Tipo de depósito</Label
                            >
                            <select
                                id="tipo_deposito_id"
                                name="tipo_deposito_id"
                                :class="selectClass"
                                :value="props.parte?.tipo_deposito_id ?? ''"
                            >
                                <option value="">Sin tipo</option>
                                <option
                                    v-for="tipo in tiposDeposito"
                                    :key="tipo.id"
                                    :value="tipo.id"
                                >
                                    {{ tipo.nombre }}
                                </option>
                            </select>
                            <span class="text-xs text-destructive">{{
                                errors.tipo_deposito_id
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="deposito">Depósito</Label>
                            <Input
                                id="deposito"
                                name="deposito"
                                type="number"
                                step="0.0001"
                                :default-value="props.parte?.deposito ?? 0"
                                :aria-invalid="!!errors.deposito"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.deposito
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="coeficiente_general"
                                >Coeficiente general (%)</Label
                            >
                            <Input
                                id="coeficiente_general"
                                name="coeficiente_general"
                                type="number"
                                step="0.00000001"
                                :default-value="
                                    props.parte?.coeficiente_general ?? 0
                                "
                                :aria-invalid="!!errors.coeficiente_general"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.coeficiente_general
                            }}</span>
                        </div>
                    </div>

                    <div
                        class="grid gap-x-8 gap-y-5 md:grid-cols-3 xl:grid-cols-5"
                    >
                        <div
                            v-for="field in textFields"
                            :key="field[0]"
                            class="grid gap-1.5"
                        >
                            <Label :for="field[0]">{{ field[1] }}</Label>
                            <Input
                                :id="field[0]"
                                :name="field[0]"
                                :default-value="props.parte?.[field[0]] ?? ''"
                                :aria-invalid="!!errors[field[0]]"
                            />
                            <span class="text-xs text-destructive">{{
                                errors[field[0]]
                            }}</span>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label for="observaciones">Observaciones</Label>
                        <Textarea
                            id="observaciones"
                            name="observaciones"
                            :default-value="props.parte?.observaciones ?? ''"
                        />
                        <span class="text-xs text-destructive">{{
                            errors.observaciones
                        }}</span>
                    </div>

                    <div class="grid gap-2">
                        <input
                            v-for="propietarioId in selectedPropietarioIds"
                            :key="propietarioId"
                            type="hidden"
                            name="propietario_ids[]"
                            :value="propietarioId"
                        />

                        <div
                            class="overflow-hidden rounded-xl border bg-background shadow-xs"
                        >
                            <div
                                class="flex flex-col justify-between gap-3 border-b bg-muted/20 px-4 py-3 sm:flex-row sm:items-center"
                            >
                                <div class="flex items-start gap-3">
                                    <span
                                        class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"
                                    >
                                        <Users class="size-4" />
                                    </span>
                                    <div class="grid gap-1">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <Label id="propietarios-label"
                                                >Propietarios</Label
                                            >
                                            <span
                                                class="rounded-full border border-primary/20 bg-primary/5 px-2 py-0.5 text-[11px] font-semibold tracking-wide text-primary uppercase"
                                            >
                                                Selección múltiple
                                            </span>
                                        </div>
                                        <p
                                            id="propietarios-help"
                                            class="text-xs text-muted-foreground"
                                        >
                                            Marca las casillas para seleccionar
                                            uno o varios propietarios.
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="w-fit rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary"
                                >
                                    {{ selectedPropietarios.size }}
                                    {{
                                        selectedPropietarios.size === 1
                                            ? 'seleccionado'
                                            : 'seleccionados'
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="selectedPropietariosList.length"
                                class="grid gap-2 border-b bg-primary/[0.03] px-4 py-3"
                            >
                                <p
                                    class="text-xs font-medium text-muted-foreground"
                                >
                                    Selección actual
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="propietario in selectedPropietariosList"
                                        :key="propietario.id"
                                        type="button"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground shadow-xs transition-opacity hover:opacity-90"
                                        :aria-label="`Quitar a ${propietario.nombre}`"
                                        @click="
                                            togglePropietario(
                                                propietario.id,
                                                false,
                                            )
                                        "
                                    >
                                        {{ propietario.nombre }}
                                        <X class="size-3" />
                                    </button>
                                </div>
                            </div>

                            <div
                                class="grid gap-3 border-b p-3 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center"
                            >
                                <div class="relative">
                                    <Search
                                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                    />
                                    <Input
                                        v-model="propietarioSearch"
                                        type="search"
                                        class="pl-9"
                                        placeholder="Buscar por nombre o NIF"
                                        aria-label="Buscar propietarios"
                                    />
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        :disabled="
                                            !filteredPropietarios.length ||
                                            allFilteredPropietariosSelected
                                        "
                                        @click="selectFilteredPropietarios"
                                    >
                                        <CheckCheck class="size-4" />
                                        Seleccionar visibles
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        :disabled="!selectedPropietarios.size"
                                        @click="clearSelectedPropietarios"
                                    >
                                        Quitar selección
                                    </Button>
                                </div>
                            </div>

                            <div
                                role="group"
                                aria-labelledby="propietarios-label"
                                aria-describedby="propietarios-help"
                                class="grid max-h-72 gap-1 overflow-y-auto p-2"
                            >
                                <label
                                    v-for="propietario in filteredPropietarios"
                                    :key="propietario.id"
                                    :for="`propietario-${propietario.id}`"
                                    class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent px-3 py-2.5 transition-colors hover:bg-muted/50"
                                    :class="
                                        selectedPropietarios.has(
                                            propietario.id,
                                        ) &&
                                        'border-primary/30 bg-primary/5 hover:bg-primary/10'
                                    "
                                >
                                    <Checkbox
                                        :id="`propietario-${propietario.id}`"
                                        :model-value="
                                            selectedPropietarios.has(
                                                propietario.id,
                                            )
                                        "
                                        @update:model-value="
                                            togglePropietario(
                                                propietario.id,
                                                $event === true,
                                            )
                                        "
                                    />
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block truncate text-sm font-medium text-brand-value"
                                        >
                                            {{ propietario.nombre }}
                                        </span>
                                        <span
                                            v-if="propietario.nif"
                                            class="block text-xs text-muted-foreground"
                                        >
                                            NIF {{ propietario.nif }}
                                        </span>
                                    </span>
                                    <span
                                        v-if="
                                            selectedPropietarios.has(
                                                propietario.id,
                                            )
                                        "
                                        class="text-xs font-medium text-primary"
                                    >
                                        Seleccionado
                                    </span>
                                </label>

                                <p
                                    v-if="!filteredPropietarios.length"
                                    class="px-3 py-8 text-center text-sm text-muted-foreground"
                                >
                                    No se han encontrado propietarios.
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-destructive">{{
                            errors.propietario_ids
                        }}</span>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-6">
                        <Button variant="outline" as-child>
                            <Link :href="showComunidad(comunidad.id)"
                                >Cancelar</Link
                            >
                        </Button>
                        <Button type="submit" :disabled="processing"
                            >Guardar</Button
                        >
                    </div>
                </CardContent>
            </Card>
        </Form>
    </main>
</template>
