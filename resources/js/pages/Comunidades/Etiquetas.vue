<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { show as showComunidad } from '@/routes/comunidades';
const props = defineProps<{
    comunidad: { id: number; nombre: string };
    partes: Array<{
        id: number;
        codigo: string;
        propietarios: Array<{
            id: number;
            nombre: string;
            direccion?: string;
            codigo_postal?: string;
            poblacion?: string;
            provincia?: string;
        }>;
    }>;
}>();
defineOptions({ layout: { breadcrumbs: [{ title: 'Etiquetas', href: '#' }] } });
setLayoutProps({
    title: 'Etiquetas de sobres',
    description: props.comunidad.nombre,
});
const printLabels = () => window.print();
</script>
<template>
    <Head :title="`Etiquetas · ${comunidad.nombre}`" />
    <main class="p-4 md:p-8 print:p-0">
        <div class="mb-6 flex items-center justify-between gap-4 print:hidden">
            <CommunityBackLink :href="showComunidad.url(comunidad.id)">
                {{ comunidad.nombre }}
            </CommunityBackLink>
            <button
                class="rounded-md bg-primary px-4 py-2 text-primary-foreground"
                @click="printLabels"
            >
                Imprimir
            </button>
        </div>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 print:grid-cols-2">
            <article v-for="parte in partes" :key="parte.id" class="contents">
                <div
                    v-for="owner in parte.propietarios"
                    :key="owner.id"
                    class="flex min-h-36 break-inside-avoid flex-col justify-center border p-6 text-sm"
                >
                    <strong>{{ owner.nombre }}</strong
                    ><span>{{ owner.direccion }}</span
                    ><span>{{ owner.codigo_postal }} {{ owner.poblacion }}</span
                    ><span>{{ owner.provincia }}</span
                    ><small class="mt-2 text-muted-foreground"
                        >Parte {{ parte.codigo }}</small
                    >
                </div>
            </article>
        </div>
    </main>
</template>
