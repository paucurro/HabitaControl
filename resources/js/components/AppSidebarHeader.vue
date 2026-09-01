<script setup lang="ts">
import { computed } from 'vue';
import AdministrationSelector from '@/components/AdministrationSelector.vue';
import GlobalSearch from '@/components/GlobalSearch.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        title?: string;
        description?: string;
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const headerTitle = computed(
    () => props.title ?? props.breadcrumbs.at(-1)?.title,
);
</script>

<template>
    <header
        class="sticky top-0 z-30 flex min-h-16 shrink-0 items-center gap-3 border-b border-sidebar-border/70 bg-background px-4 py-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:min-h-14"
    >
        <div class="flex min-w-0 flex-1 basis-0 items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <div v-if="headerTitle" class="min-w-0">
                <h1 class="truncate text-xl font-semibold tracking-tight">
                    {{ headerTitle }}
                </h1>
                <p
                    v-if="description"
                    class="truncate text-xs text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
        </div>
        <div
            class="ml-auto flex min-w-0 flex-1 basis-0 items-center justify-end gap-3"
        >
            <GlobalSearch class="hidden min-w-0 flex-1 md:block" />
            <div class="shrink-0">
                <AdministrationSelector />
            </div>
        </div>
    </header>
</template>
