<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { redirect as socialRedirect } from '@/routes/social';

defineOptions({
    layout: {
        title: 'Accede a tu cuenta',
        description: 'Introduce tu correo electrónico y contraseña',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    socialProviders: string[];
}>();
</script>

<template>
    <Head title="Acceder" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <PasskeyVerify />

    <div v-if="socialProviders.length" class="grid gap-3">
        <Button
            v-if="socialProviders.includes('google')"
            variant="outline"
            as-child
            class="w-full"
        >
            <a :href="socialRedirect.url('google')">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        fill="currentColor"
                        d="M21.6 12.23c0-.71-.06-1.4-.18-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"
                    />
                    <path
                        fill="currentColor"
                        opacity=".75"
                        d="M12 22c2.7 0 4.98-.9 6.63-2.43l-3.24-2.54c-.9.6-2.05.96-3.39.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"
                    />
                    <path
                        fill="currentColor"
                        opacity=".5"
                        d="M6.39 13.86A6 6 0 0 1 6.07 12c0-.65.11-1.28.32-1.86V7.52H3.04A10 10 0 0 0 2 12c0 1.61.39 3.14 1.04 4.48l3.35-2.62Z"
                    />
                    <path
                        fill="currentColor"
                        opacity=".9"
                        d="M12 6.01c1.47 0 2.79.5 3.83 1.5l2.87-2.87A9.62 9.62 0 0 0 12 2a10 10 0 0 0-8.96 5.52l3.35 2.62C7.18 7.77 9.39 6.01 12 6.01Z"
                    />
                </svg>
                Continuar con Google
            </a>
        </Button>

        <Button
            v-if="socialProviders.includes('apple')"
            variant="outline"
            as-child
            class="w-full"
        >
            <a :href="socialRedirect.url('apple')">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        fill="currentColor"
                        d="M17.05 12.54c-.03-2.94 2.4-4.37 2.51-4.44a5.38 5.38 0 0 0-4.23-2.29c-1.78-.19-3.51 1.07-4.42 1.07-.93 0-2.34-1.05-3.86-1.02a5.62 5.62 0 0 0-4.73 2.88c-2.05 3.55-.52 8.77 1.44 11.63.98 1.4 2.12 2.97 3.64 2.92 1.48-.06 2.03-.94 3.82-.94 1.77 0 2.29.94 3.83.9 1.6-.02 2.61-1.41 3.55-2.83a11.6 11.6 0 0 0 1.62-3.3 5.08 5.08 0 0 1-3.17-4.58ZM14.17 3.92A5.16 5.16 0 0 0 15.35.2a5.27 5.27 0 0 0-3.4 1.77 4.9 4.9 0 0 0-1.21 3.58 4.36 4.36 0 0 0 3.43-1.63Z"
                    />
                </svg>
                Continuar con Apple
            </a>
        </Button>

        <div class="relative py-2">
            <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t" />
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-background px-2 text-muted-foreground">o</span>
            </div>
        </div>
    </div>

    <InputError :message="$page.props.errors.social" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Correo electrónico</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Contraseña</Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="5"
                    >
                        ¿Has olvidado tu contraseña?
                    </TextLink>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Contraseña"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Recordarme</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Acceder
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            ¿No tienes una cuenta?
            <TextLink :href="register()" :tabindex="5">Regístrate</TextLink>
        </div>
    </Form>
</template>
