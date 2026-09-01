<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { destroy as detenerSuplantacion } from '@/routes/sistema/suplantacion';
import type { BreadcrumbItem } from '@/types';

const page = usePage<{ impersonation: { active: boolean } }>();

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
    description?: string;
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div
                v-if="page.props.impersonation.active"
                class="flex items-center justify-center gap-3 bg-amber-500 px-4 py-2 text-sm font-semibold text-amber-950"
            >
                Estás accediendo como {{ page.props.auth.user.name }}.
                <Link
                    :href="detenerSuplantacion()"
                    method="delete"
                    as="button"
                    class="rounded-md bg-amber-950 px-3 py-1 text-white hover:bg-amber-900"
                >
                    Volver al superadministrador
                </Link>
            </div>
            <AppSidebarHeader
                :breadcrumbs="breadcrumbs"
                :title="title"
                :description="description"
            />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
