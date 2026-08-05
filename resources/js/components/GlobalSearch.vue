<script setup lang="ts">
import { Link, useHttp, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    FileText,
    Home,
    Search,
    UserRound,
} from '@lucide/vue';
import { computed, onUnmounted, ref, watch } from 'vue';
import { buscar } from '@/routes';

type Resultado = {
    tipo: string;
    titulo: string;
    detalle?: string | null;
    url: string;
};
type SearchResponse = { resultados: Resultado[] };

const page = usePage<{
    administrationContext: { isSuperuser: boolean; selectedId: number | null };
}>();
const search = useHttp<{ q: string; tipo: string }, SearchResponse>({
    q: '',
    tipo: 'todos',
});
const abierto = ref(false);
let debounce: ReturnType<typeof setTimeout> | undefined;

const deshabilitado = computed(
    () =>
        page.props.administrationContext.isSuperuser &&
        page.props.administrationContext.selectedId === null,
);

const iconos = {
    Comunidad: Building2,
    Parte: Home,
    Propietario: UserRound,
    Diario: BookOpen,
};

watch(
    () => [search.q, search.tipo],
    () => {
        clearTimeout(debounce);
        search.cancel();

        if (search.q.trim().length < 2 || deshabilitado.value) {
            abierto.value = false;

            return;
        }

        debounce = setTimeout(async () => {
            await search.get(buscar.url());
            abierto.value = true;
        }, 250);
    },
);

function cerrarDespues(): void {
    setTimeout(() => (abierto.value = false), 150);
}

onUnmounted(() => clearTimeout(debounce));
</script>

<template>
    <div class="relative w-full max-w-2xl">
        <div
            class="flex h-10 overflow-hidden rounded-lg border bg-background shadow-sm focus-within:ring-2 focus-within:ring-ring/40"
        >
            <Search class="m-2.5 size-4 shrink-0 text-muted-foreground" />
            <input
                v-model="search.q"
                type="search"
                :disabled="deshabilitado"
                :placeholder="
                    deshabilitado
                        ? 'Selecciona una administración para buscar'
                        : 'Buscar en IComunidades…'
                "
                class="min-w-0 flex-1 bg-transparent text-sm outline-none disabled:cursor-not-allowed"
                @focus="abierto = search.q.length >= 2"
                @blur="cerrarDespues"
            />
            <select
                v-model="search.tipo"
                class="border-l bg-muted/40 px-2 text-xs outline-none"
                aria-label="Tipo de búsqueda"
            >
                <option value="todos">Todo</option>
                <option value="comunidades">Comunidades</option>
                <option value="partes">Partes</option>
                <option value="propietarios">Propietarios</option>
                <option value="diario">Diario</option>
            </select>
        </div>

        <div
            v-if="abierto"
            class="absolute top-full right-0 left-0 z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-xl border bg-popover p-1 shadow-xl"
        >
            <div
                v-if="search.processing"
                class="flex items-center gap-2 p-4 text-sm text-muted-foreground"
            >
                <Search class="size-4 animate-pulse" />Buscando…
            </div>
            <Link
                v-for="resultado in search.response?.resultados ?? []"
                v-else
                :key="`${resultado.tipo}-${resultado.url}`"
                :href="resultado.url"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-muted"
            >
                <div class="rounded-md bg-primary/10 p-2 text-primary">
                    <component
                        :is="
                            iconos[resultado.tipo as keyof typeof iconos] ??
                            FileText
                        "
                        class="size-4"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium uppercase"
                            >{{ resultado.tipo }}</span
                        >
                        <p class="truncate text-sm font-medium">
                            {{ resultado.titulo }}
                        </p>
                    </div>
                    <p
                        v-if="resultado.detalle"
                        class="truncate text-xs text-muted-foreground"
                    >
                        {{ resultado.detalle }}
                    </p>
                </div>
            </Link>
            <p
                v-if="
                    !search.processing &&
                    search.response &&
                    !search.response.resultados.length
                "
                class="p-5 text-center text-sm text-muted-foreground"
            >
                No se han encontrado resultados.
            </p>
        </div>
    </div>
</template>
