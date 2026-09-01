<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ajustes de apariencia',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Ajustes de apariencia" />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Apariencia"
            description="Configura la apariencia de tu cuenta"
        />
        <AppearanceTabs />
    </div>
</template>
