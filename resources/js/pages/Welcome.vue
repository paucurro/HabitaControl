<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="HabitaControl" />

    <div
        class="flex min-h-screen flex-col overflow-hidden bg-background text-foreground"
    >
        <header class="mx-auto w-full max-w-6xl px-6 py-8 lg:px-8">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-flex rounded-md border border-brand-accent bg-brand-accent px-4 py-2 text-sm leading-normal font-medium text-white hover:bg-brand-accent-hover"
                >
                    Portada
                </Link>

                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex rounded-md border border-border bg-white px-4 py-2 text-sm leading-normal font-medium hover:border-brand-accent hover:text-brand-accent"
                    >
                        Acceder
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-flex rounded-md border border-brand-accent bg-brand-accent px-4 py-2 text-sm leading-normal font-medium text-white hover:bg-brand-accent-hover"
                    >
                        Registrarse
                    </Link>
                </template>
            </nav>
        </header>

        <main
            class="flex flex-1 flex-col items-center justify-center gap-6 px-6 pb-24 text-center"
        >
            <div class="w-full max-w-2xl">
                <img
                    src="/img/habita_control_logo.png"
                    alt="HabitaControl"
                    class="h-auto w-full object-contain"
                />
            </div>

            <section
                class="w-full max-w-2xl rounded-2xl border border-border bg-card px-6 py-5 text-card-foreground"
            >
                <h1 class="text-2xl font-semibold sm:text-3xl">
                    La gestión de tu comunidad, más sencilla
                </h1>
                <p
                    class="mt-3 text-sm leading-6 text-brand-ink-soft sm:text-base"
                >
                    HabitaControl reúne en un solo lugar la información de tu
                    comunidad. Consulta documentación, comunica y sigue
                    incidencias, revisa gestiones pendientes y mantente al día
                    de todo lo que ocurre en tu edificio.
                </p>
            </section>
        </main>
    </div>
</template>
