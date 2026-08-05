<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { BookOpen } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show } from '@/routes/diario';

type Comunidad = {
    id: number;
    codigo: string;
    nombre: string;
};

defineProps<{ comunidades: Comunidad[] }>();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Diario', href: '/diario' }] },
});

const comunidadSeleccionada = ref('');

function abrirDiario(): void {
    if (comunidadSeleccionada.value === '') {
        return;
    }

    router.visit(show(Number(comunidadSeleccionada.value)));
}
</script>

<template>
    <div class="contents">
        <Head title="Diario" />

        <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-semibold">
                    <BookOpen class="size-6" /> Diario
                </h1>
                <p class="text-sm text-muted-foreground">
                    Selecciona la comunidad cuyo diario quieres consultar.
                </p>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>Comunidad</CardTitle>
                    <CardDescription>
                        Podrás cambiar de comunidad en cualquier momento.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        class="flex flex-col gap-3 sm:flex-row"
                        @submit.prevent="abrirDiario"
                    >
                        <label class="grid flex-1 gap-1.5 text-sm">
                            <span class="font-medium"
                                >Selecciona una comunidad</span
                            >
                            <select
                                v-model="comunidadSeleccionada"
                                class="h-10 w-full rounded-md border bg-background px-3 text-sm outline-none focus-visible:ring-3 focus-visible:ring-ring/50"
                                :disabled="!comunidades.length"
                            >
                                <option value="">
                                    Selecciona una comunidad
                                </option>
                                <option
                                    v-for="comunidad in comunidades"
                                    :key="comunidad.id"
                                    :value="comunidad.id"
                                >
                                    [{{ comunidad.codigo }}]
                                    {{ comunidad.nombre }}
                                </option>
                            </select>
                        </label>
                        <Button
                            type="submit"
                            class="sm:self-end"
                            :disabled="comunidadSeleccionada === ''"
                        >
                            Abrir diario
                        </Button>
                    </form>

                    <p
                        v-if="!comunidades.length"
                        class="mt-4 text-sm text-muted-foreground"
                    >
                        No hay comunidades disponibles.
                    </p>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
