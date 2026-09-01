<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import CommunityDiarySelector from '@/components/CommunityDiarySelector.vue';
import { Card, CardContent } from '@/components/ui/card';

type Comunidad = {
    id: number;
    codigo: string;
    nombre: string;
};

defineProps<{ comunidades: Comunidad[] }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Diario', href: '/diario' }],
        title: 'Diario',
        description: 'Selecciona la comunidad cuyo diario quieres consultar.',
    },
});
</script>

<template>
    <div class="contents">
        <Head title="Diario" />

        <main class="flex flex-1 flex-col p-4 md:p-8">
            <Card class="w-full">
                <CardContent>
                    <CommunityDiarySelector :comunidades="comunidades" />
                </CardContent>
            </Card>
        </main>
    </div>
</template>
