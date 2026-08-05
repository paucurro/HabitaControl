<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import CommunityBackLink from '@/components/CommunityBackLink.vue';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { show as showComunidad } from '@/routes/comunidades';
import { store } from '@/routes/comunidades/tipos-gasto';
import { destroy, update } from '@/routes/tipos-gasto';

type TipoGasto = {
    id: number;
    codigo: string;
    descripcion: string;
    notas?: string;
    excluir_de_liquidacion: boolean;
};

defineProps<{
    comunidad: { id: number; codigo: string; nombre: string };
    tiposGasto: TipoGasto[];
}>();
defineOptions({
    layout: { breadcrumbs: [{ title: 'Comunidades', href: '/comunidades' }] },
});

const showCreate = ref(false);
const editing = ref<TipoGasto | null>(null);

function eliminar(tipo: TipoGasto) {
    if (confirm(`¿Archivar el tipo de gasto "${tipo.descripcion}"?`)) {
        router.delete(destroy(tipo.id).url);
    }
}
</script>
<template>
    <Head title="Tipos de gasto" />
    <main class="flex flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <CommunityBackLink :href="showComunidad.url(comunidad.id)">
                {{ comunidad.nombre }}
            </CommunityBackLink>
            <div class="mt-3 flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Tipos de gasto</h1>
                <Button @click="showCreate = true"
                    ><Plus class="size-4" /> Nuevo tipo de gasto</Button
                >
            </div>
        </div>

        <Card class="overflow-hidden py-0">
            <Table v-if="tiposGasto.length">
                <TableHeader>
                    <TableRow>
                        <TableHead>Código</TableHead>
                        <TableHead>Descripción</TableHead>
                        <TableHead>Excluir de liquidación</TableHead>
                        <TableHead class="text-right">Acciones</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="tipo in tiposGasto" :key="tipo.id">
                        <TableCell class="font-medium">{{
                            tipo.codigo
                        }}</TableCell>
                        <TableCell>{{ tipo.descripcion }}</TableCell>
                        <TableCell>{{
                            tipo.excluir_de_liquidacion ? 'Sí' : 'No'
                        }}</TableCell>
                        <TableCell class="flex justify-end gap-1">
                            <Button
                                size="icon-sm"
                                variant="ghost"
                                @click="editing = tipo"
                                ><Pencil class="size-4"
                            /></Button>
                            <Button
                                size="icon-sm"
                                variant="ghost"
                                class="text-destructive"
                                @click="eliminar(tipo)"
                                ><Trash2 class="size-4"
                            /></Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <p v-else class="p-8 text-center text-muted-foreground">
                Todavía no hay tipos de gasto.
            </p>
        </Card>

        <Dialog v-model:open="showCreate">
            <DialogContent>
                <Form
                    v-bind="store.form(comunidad.id)"
                    @success="showCreate = false"
                    #default="{ errors, processing }"
                >
                    <DialogHeader>
                        <DialogTitle>Nuevo tipo de gasto</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-1.5">
                            <Label for="codigo"
                                >Código<span class="text-destructive"
                                    >&nbsp;*</span
                                ></Label
                            >
                            <Input
                                id="codigo"
                                name="codigo"
                                :aria-invalid="!!errors.codigo"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.codigo
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="descripcion"
                                >Descripción<span class="text-destructive"
                                    >&nbsp;*</span
                                ></Label
                            >
                            <Input
                                id="descripcion"
                                name="descripcion"
                                :aria-invalid="!!errors.descripcion"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.descripcion
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="notas">Notas</Label>
                            <Textarea id="notas" name="notas" />
                        </div>
                        <label
                            for="excluir_de_liquidacion"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="hidden"
                                name="excluir_de_liquidacion"
                                value="0"
                            />
                            <Switch
                                id="excluir_de_liquidacion"
                                name="excluir_de_liquidacion"
                                value="1"
                            />
                            Excluir de liquidación
                        </label>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="processing"
                            >Crear</Button
                        >
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>

        <Dialog
            :open="editing !== null"
            @update:open="
                (value) => {
                    if (!value) editing = null;
                }
            "
        >
            <DialogContent v-if="editing">
                <Form
                    v-bind="update.form(editing.id)"
                    @success="editing = null"
                    #default="{ errors, processing }"
                >
                    <DialogHeader>
                        <DialogTitle>Editar tipo de gasto</DialogTitle>
                    </DialogHeader>
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-1.5">
                            <Label for="edit_codigo"
                                >Código<span class="text-destructive"
                                    >&nbsp;*</span
                                ></Label
                            >
                            <Input
                                id="edit_codigo"
                                name="codigo"
                                :default-value="editing.codigo"
                                :aria-invalid="!!errors.codigo"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.codigo
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="edit_descripcion"
                                >Descripción<span class="text-destructive"
                                    >&nbsp;*</span
                                ></Label
                            >
                            <Input
                                id="edit_descripcion"
                                name="descripcion"
                                :default-value="editing.descripcion"
                                :aria-invalid="!!errors.descripcion"
                            />
                            <span class="text-xs text-destructive">{{
                                errors.descripcion
                            }}</span>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="edit_notas">Notas</Label>
                            <Textarea
                                id="edit_notas"
                                name="notas"
                                :default-value="editing.notas ?? ''"
                            />
                        </div>
                        <label
                            for="edit_excluir_de_liquidacion"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="hidden"
                                name="excluir_de_liquidacion"
                                value="0"
                            />
                            <Switch
                                id="edit_excluir_de_liquidacion"
                                name="excluir_de_liquidacion"
                                value="1"
                                :default-value="editing.excluir_de_liquidacion"
                            />
                            Excluir de liquidación
                        </label>
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="processing"
                            >Guardar</Button
                        >
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>
    </main>
</template>
