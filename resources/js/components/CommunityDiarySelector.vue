<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Check, ChevronDown, Search } from '@lucide/vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxPortal,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { ref, watch } from 'vue';
import { show } from '@/routes/diario';

export type CommunityDiaryOption = {
    id: number;
    codigo: string;
    nombre: string;
};

const props = defineProps<{
    comunidades: CommunityDiaryOption[];
    selectedCommunityId?: number;
}>();

const selectedCommunity = ref<CommunityDiaryOption>();

watch(
    () => [props.comunidades, props.selectedCommunityId] as const,
    () => {
        selectedCommunity.value = props.comunidades.find(
            (comunidad) => comunidad.id === props.selectedCommunityId,
        );
    },
    { immediate: true },
);

function communityLabel(comunidad?: CommunityDiaryOption): string {
    return comunidad ? `[${comunidad.codigo}] ${comunidad.nombre}` : '';
}

function openCommunity(comunidad?: CommunityDiaryOption): void {
    if (!comunidad || comunidad.id === props.selectedCommunityId) {
        return;
    }

    router.visit(show(comunidad.id));
}
</script>

<template>
    <div class="grid w-full gap-1.5">
        <label for="community-diary-search" class="text-sm font-medium">
            Comunidad
        </label>
        <ComboboxRoot
            v-model="selectedCommunity"
            by="id"
            :disabled="!comunidades.length"
            :reset-model-value-on-clear="false"
            open-on-click
            open-on-focus
            @update:model-value="openCommunity"
        >
            <ComboboxAnchor class="relative w-full">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <ComboboxInput
                    id="community-diary-search"
                    :display-value="communityLabel"
                    :placeholder="
                        comunidades.length
                            ? 'Buscar una comunidad por código o nombre'
                            : 'No hay comunidades disponibles'
                    "
                    class="h-10 w-full rounded-md border border-input bg-background pr-10 pl-9 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                />
                <ComboboxTrigger
                    class="absolute top-0 right-0 flex size-10 items-center justify-center text-muted-foreground outline-none"
                    aria-label="Mostrar comunidades"
                >
                    <ChevronDown class="size-4" />
                </ComboboxTrigger>
            </ComboboxAnchor>

            <ComboboxPortal>
                <ComboboxContent
                    position="popper"
                    :side-offset="4"
                    class="z-50 max-h-[min(20rem,var(--reka-combobox-content-available-height))] w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md"
                >
                    <ComboboxViewport class="max-h-80 overflow-y-auto p-1">
                        <ComboboxEmpty
                            class="px-3 py-6 text-center text-sm text-muted-foreground"
                        >
                            No se ha encontrado ninguna comunidad.
                        </ComboboxEmpty>
                        <ComboboxItem
                            v-for="comunidad in comunidades"
                            :key="comunidad.id"
                            :value="comunidad"
                            :text-value="communityLabel(comunidad)"
                            class="relative flex w-full cursor-default items-center rounded-sm py-2 pr-9 pl-3 text-sm outline-none select-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                        >
                            <span class="truncate">
                                <span class="font-medium"
                                    >[{{ comunidad.codigo }}]</span
                                >
                                {{ comunidad.nombre }}
                            </span>
                            <ComboboxItemIndicator
                                class="absolute right-3 flex size-4 items-center justify-center"
                            >
                                <Check class="size-4" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                    </ComboboxViewport>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>
        <p class="text-xs text-muted-foreground">
            Escribe para buscar. Al seleccionar una comunidad se abrirá su
            diario.
        </p>
    </div>
</template>
