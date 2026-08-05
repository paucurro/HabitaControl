<script setup lang="ts">
withDefaults(
    defineProps<{
        fluid?: boolean;
    }>(),
    {
        fluid: false,
    },
);
</script>

<template>
    <div
        class="flex items-center justify-center overflow-hidden"
        :class="fluid ? 'w-full' : 'w-44'"
    >
        <img
            src="/img/habita_control_logo.png"
            alt="HabitaControl"
            class="h-auto w-full object-contain group-data-[collapsible=icon]:hidden"
        />
        <img
            src="/img/habita_control_icono.png"
            alt="HabitaControl"
            class="hidden size-7 object-contain group-data-[collapsible=icon]:block"
        />
    </div>
</template>
