<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    LayoutGrid,
    Mail,
    UserCog,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as administracionUsuarios } from '@/routes/administracion/usuarios';
import { index as comunicados } from '@/routes/comunicados';
import { index as comunidades } from '@/routes/comunidades';
import { index as diario } from '@/routes/diario';
import { index as propietarios } from '@/routes/propietarios';
import type { NavItem } from '@/types';

const page = usePage<{
    auth: { canManageAdministration: boolean; canViewCommunities: boolean };
    administrationContext: { isSuperuser: boolean; selectedId: number | null };
}>();
const mainNavItems: NavItem[] = [
    { title: 'Panel', href: dashboard(), icon: LayoutGrid },
];

if (page.props.auth.canViewCommunities) {
    mainNavItems.push(
        { title: 'Comunidades', href: comunidades(), icon: Building2 },
        { title: 'Diario', href: diario(), icon: BookOpen },
        { title: 'Propietarios', href: propietarios(), icon: Users },
        { title: 'Comunicados', href: comunicados(), icon: Mail },
    );
} else if (!page.props.administrationContext.isSuperuser) {
    mainNavItems.push({
        title: 'Mis datos',
        href: propietarios(),
        icon: Users,
    });
}

if (page.props.auth.canManageAdministration) {
    mainNavItems.push({
        title: 'Usuarios',
        href: administracionUsuarios(),
        icon: UserCog,
    });
}
</script>
<template>
    <Sidebar collapsible="icon" variant="inset"
        ><SidebarHeader
            ><SidebarMenu
                ><SidebarMenuItem
                    ><SidebarMenuButton size="lg" as-child
                        ><Link :href="dashboard()"
                            ><AppLogo
                                fluid /></Link></SidebarMenuButton></SidebarMenuItem></SidebarMenu></SidebarHeader
        ><SidebarContent><NavMain :items="mainNavItems" /></SidebarContent
        ><SidebarFooter><NavUser /></SidebarFooter></Sidebar
    ><slot />
</template>
