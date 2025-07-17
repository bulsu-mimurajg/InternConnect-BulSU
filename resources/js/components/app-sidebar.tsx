import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookCheckIcon,
    BriefcaseBusinessIcon,
    ClipboardIcon,
    ClipboardListIcon,
    HeadsetIcon,
    InfoIcon,
    PrinterIcon,
    UserIcon
} from 'lucide-react';
import AppLogo from './app-logo';

export const roleBasedNav: Record<string, { main: NavItem[]; footer: NavItem[] }> = {
    admin: {
        main: [
            { title: 'Student', href: '/student', icon: ClipboardIcon },
            { title: 'Users', href: '/users', icon: UserIcon },
            { title: 'Placement', href: '/placement', icon: BriefcaseBusinessIcon },
            { title: 'Reports', href: '/report', icon: PrinterIcon },
        ],
        footer: [],
    },
    hte: {
        main: [{ title: 'Form', href: '/form', icon: ClipboardListIcon }],
        footer: [],
    },
    student: {
        main: [
            { title: 'Assessment', href: '/assessment', icon: BookCheckIcon },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: HeadsetIcon },
        ],
    }
};

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;

    const role = auth.role ?? 'guest'; // always one role
    const nav = roleBasedNav[role] ?? roleBasedNav['guest'];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={nav.main} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={nav.footer} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
