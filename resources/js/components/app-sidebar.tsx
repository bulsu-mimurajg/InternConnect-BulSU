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
    PersonStandingIcon,
    PrinterIcon,
    UserIcon
} from 'lucide-react';
import AppLogo from './app-logo';

const roleBasedNav: Record<string, { main: NavItem[]; footer: NavItem[] }> = {
    admin: {
        main: [
            {
                title: 'Student',
                href: '',
                icon: ClipboardIcon,
                subNav: [
                    { title: 'List', href: '/student/list' },
                    { title: 'Match', href: '/student/matched' },
                    { title: 'Place', href: '/student/placed' },
                ],
            },
            { title: 'HTE', href: '/hte', icon: UserIcon },
            { title: 'Placement', href: '/placement', icon: BriefcaseBusinessIcon },
            { title: 'Reports', href: '/report', icon: PrinterIcon },
        ],
        footer: [],
    },
    hte: {
        main: [{ title: 'Form', href: '/form', icon: ClipboardListIcon }],
        footer: [],
    },
    adviser: {
        main: [{ title: 'Application', href: '/application', icon: PersonStandingIcon }],
        footer: [],
    },
    student: {
        main: [
            { title: 'Assessment', href: '/assessment', icon: BookCheckIcon },
            { title: 'Profile', href: '/profile', icon: UserIcon },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: HeadsetIcon },
        ],
    },
};

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;

    const role = auth.role ?? 'guest';
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
