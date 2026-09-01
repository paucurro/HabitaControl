<script setup lang="ts">
import { Form, Head, router, setLayoutProps, usePoll } from '@inertiajs/vue3';
import { Database, Play, RefreshCw } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store, update } from '@/routes/sistema/sincronizacion';

type Administracion = {
    id: number;
    nombre: string;
    db_host: string | null;
    db_port: number | null;
    db_origen: string | null;
    db_usuario: string | null;
    tiene_password: boolean;
    sync_estado: string;
    sync_started_at: string | null;
    sync_finished_at: string | null;
    sync_resultado: Record<string, number> | null;
    sync_error: string | null;
};

defineProps<{ administraciones: Administracion[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Sincronización', href: '/sistema/sincronizacion' },
        ],
    },
});

setLayoutProps({
    title: 'Sincronización de datos',
    description: 'Importación incremental desde las bases de datos originales',
});

usePoll(5000, { only: ['administraciones'] });

function sincronizarTodas(): void {
    router.post(store.url(), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Sincronización de datos" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <section
            class="flex flex-col gap-4 rounded-xl border bg-card p-5 md:flex-row md:items-center md:justify-between"
        >
            <div class="flex gap-3">
                <Database class="mt-0.5 size-5 shrink-0" />
                <div>
                    <h2 class="font-semibold">Importación incremental</h2>
                    <p class="mt-1 max-w-3xl text-sm text-muted-foreground">
                        Solo crea registros que todavía no existen según su
                        identificador y base de origen. También repara las
                        fechas del diario usando el timestamp
                        <code>fecha_diario</code>.
                    </p>
                </div>
            </div>
            <Button type="button" class="shrink-0" @click="sincronizarTodas">
                <RefreshCw class="size-4" /> Sincronizar todas
            </Button>
        </section>

        <section
            v-for="administracion in administraciones"
            :key="administracion.id"
            class="rounded-xl border bg-card p-5"
        >
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">
                        {{ administracion.nombre }}
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Estado:
                        <span class="font-medium text-foreground">{{
                            administracion.sync_estado
                        }}</span>
                        <span v-if="administracion.sync_finished_at">
                            · {{ administracion.sync_finished_at }}</span
                        >
                    </p>
                </div>
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="administracion.sync_estado === 'procesando'"
                    @click="
                        router.post(
                            store.url(administracion.id),
                            {},
                            { preserveScroll: true },
                        )
                    "
                >
                    <Play class="size-4" /> Sincronizar
                </Button>
            </div>

            <Form
                v-bind="update.form(administracion.id)"
                #default="{ errors, processing }"
                class="grid gap-4 md:grid-cols-2 lg:grid-cols-5"
            >
                <div class="grid gap-2">
                    <Label :for="`host-${administracion.id}`">Servidor</Label>
                    <Input
                        :id="`host-${administracion.id}`"
                        name="db_host"
                        :default-value="administracion.db_host ?? '127.0.0.1'"
                    />
                    <InputError :message="errors.db_host" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`port-${administracion.id}`">Puerto</Label>
                    <Input
                        :id="`port-${administracion.id}`"
                        name="db_port"
                        type="number"
                        :default-value="administracion.db_port ?? 3306"
                    />
                    <InputError :message="errors.db_port" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`database-${administracion.id}`"
                        >Base de datos</Label
                    >
                    <Input
                        :id="`database-${administracion.id}`"
                        name="db_origen"
                        required
                        :default-value="administracion.db_origen ?? ''"
                    />
                    <InputError :message="errors.db_origen" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`user-${administracion.id}`">Usuario</Label>
                    <Input
                        :id="`user-${administracion.id}`"
                        name="db_usuario"
                        :default-value="administracion.db_usuario ?? ''"
                        autocomplete="off"
                    />
                    <InputError :message="errors.db_usuario" />
                </div>
                <div class="grid gap-2">
                    <Label :for="`password-${administracion.id}`"
                        >Contraseña</Label
                    >
                    <Input
                        :id="`password-${administracion.id}`"
                        name="db_password"
                        type="password"
                        :placeholder="
                            administracion.tiene_password
                                ? 'Guardada; dejar vacío para conservar'
                                : 'Opcional'
                        "
                        autocomplete="new-password"
                    />
                    <InputError :message="errors.db_password" />
                </div>
                <div class="md:col-span-2 lg:col-span-5">
                    <Button type="submit" :disabled="processing"
                        >Guardar configuración</Button
                    >
                </div>
            </Form>

            <p
                v-if="administracion.sync_error"
                class="mt-4 rounded-md bg-destructive/10 p-3 text-sm text-destructive"
            >
                {{ administracion.sync_error }}
            </p>
            <dl
                v-if="administracion.sync_resultado"
                class="mt-4 grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="(valor, clave) in administracion.sync_resultado"
                    :key="clave"
                    class="flex justify-between rounded-md bg-muted px-3 py-2"
                >
                    <dt>{{ String(clave).replaceAll('_', ' ') }}</dt>
                    <dd class="font-semibold">{{ valor }}</dd>
                </div>
            </dl>
        </section>
    </main>
</template>
