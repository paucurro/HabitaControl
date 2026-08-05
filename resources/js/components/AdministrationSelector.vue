<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Building2 } from '@lucide/vue';
import { update } from '@/routes/contexto/administracion';

type AdministrationContext = {
    isSuperuser: boolean;
    selectedId: number | null;
    selectedName: string | null;
    options: Array<{ id: number; nombre: string }>;
};

const page = usePage<{ administrationContext: AdministrationContext }>();

function seleccionar(event: Event): void {
    const value = (event.target as HTMLSelectElement).value;
    router.put(
        update.url(),
        { administracion_id: value === '' ? null : Number(value) },
        { preserveScroll: false },
    );
}
</script>

<template>
    <label
        v-if="page.props.administrationContext.isSuperuser"
        class="flex min-w-44 items-center gap-2"
    >
        <Building2 class="size-4 shrink-0 text-muted-foreground" />
        <select
            :value="page.props.administrationContext.selectedId ?? ''"
            class="h-9 min-w-0 flex-1 rounded-md border bg-background px-2 text-sm"
            aria-label="Administración activa"
            @change="seleccionar"
        >
            <option value="">Seleccionar administración</option>
            <option
                v-for="administracion in page.props.administrationContext
                    .options"
                :key="administracion.id"
                :value="administracion.id"
            >
                {{ administracion.nombre }}
            </option>
        </select>
    </label>
    <div
        v-else-if="page.props.administrationContext.selectedName"
        class="hidden items-center gap-2 text-sm text-muted-foreground xl:flex"
    >
        <Building2 class="size-4" />{{
            page.props.administrationContext.selectedName
        }}
    </div>
</template>
