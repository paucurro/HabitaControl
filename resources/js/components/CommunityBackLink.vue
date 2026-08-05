<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="inline-flex w-fit items-center gap-2 rounded-md py-1 text-base font-semibold text-brand-value transition-colors hover:text-primary focus-visible:ring-3 focus-visible:ring-ring/50 focus-visible:outline-none"
    >
        <ArrowLeft class="size-5 shrink-0 stroke-[2.5]" />
        <slot />
    </Link>
</template>
