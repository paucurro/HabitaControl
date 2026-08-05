<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/invitaciones';

defineProps<{
    token: string;
    email: string;
    administracion: string;
    tipo: string;
}>();

defineOptions({
    layout: {
        title: 'Activar acceso',
        description: 'Completa tus datos para acceder a IComunidades',
    },
});
</script>

<template>
    <Head title="Activar acceso" />
    <Form
        v-bind="store.form(token)"
        :reset-on-success="['password', 'password_confirmation']"
        #default="{ errors, processing }"
        class="grid gap-5"
    >
        <div class="rounded-lg bg-muted p-3 text-sm">
            <p class="font-medium">{{ administracion }}</p>
            <p class="text-muted-foreground">
                {{ email }} ·
                {{ tipo === 'propietario' ? 'Propietario' : 'Colaborador' }}
            </p>
        </div>
        <div class="grid gap-2">
            <Label for="name">Nombre completo</Label
            ><Input
                id="name"
                name="name"
                required
                autocomplete="name"
            /><InputError :message="errors.name" />
        </div>
        <div class="grid gap-2">
            <Label for="password">Contraseña</Label
            ><PasswordInput
                id="password"
                name="password"
                required
                autocomplete="new-password"
            /><InputError :message="errors.password" />
        </div>
        <div class="grid gap-2">
            <Label for="password_confirmation">Confirmar contraseña</Label
            ><PasswordInput
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
        </div>
        <Button type="submit" :disabled="processing">{{
            processing ? 'Activando…' : 'Activar mi acceso'
        }}</Button>
    </Form>
</template>
