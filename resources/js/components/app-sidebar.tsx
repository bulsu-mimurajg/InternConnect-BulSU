import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookCheckIcon,
    ClipboardIcon,
    ClipboardListIcon,
    HeadsetIcon,
    InfoIcon,
    PersonStandingIcon,
    PrinterIcon,
    UserIcon,
    UsersIcon,
    PlusIcon,
    CalendarIcon,
    FileTextIcon, Check
} from 'lucide-react';
import AppLogo from './app-logo';

const roleBasedNav: Record<string, { main: NavItem[]; footer: NavItem[] }> = {
    admin: {
        main: [

            { title: 'Dashboard', href: '/admin-dashboard', icon: ClipboardIcon },
            {
                title: 'Student',
                href: '/student',
                icon: ClipboardListIcon,
                subNav: [
                    { title: 'List', href: '/student/list' },
                    { title: 'Match', href: '/student/matched' },
                    { title: 'Endorsed', href: '/student/endorsed' },
                    { title: 'Place', href: '/student/placed' },
                ],
            },
            { title: 'HTE', href: '/hte', icon: UserIcon },
            { title: 'Adviser', href: '/adviser', icon: PersonStandingIcon },
            { title: 'Forms', href: '/admin/forms', icon: FileTextIcon },
            { title: 'Additional Info', href: '/admin/additional-info', icon: PlusIcon },
            { title: 'Events', href: '/admin/events', icon: CalendarIcon },
            { title: 'Reports', href: '/report', icon: PrinterIcon },
        ],
        footer: [],
    },
    hte: {
        main: [
            { title: 'Dashboard', href: '/hte/dashboard', icon: ClipboardIcon },
            { title: 'Form', href: '/form', icon: ClipboardListIcon },
            { title: 'Profile', href: '/hte/profile', icon: UserIcon }
        ],
        footer: [],
    },
    adviser: {
        main: [
            { title: 'Dashboard', href: '/adviser/dashboard', icon: ClipboardIcon },
            { title: 'Student Verification', href: '/student-verification', icon: Check },
            { title: 'Student List', href: '/adviser/student-list', icon: UserIcon },
        ],
        footer: [],
    },
    student: {
        main: [
            { title: 'Dashboard', href: '/student/dashboard', icon: ClipboardIcon },
            { title: 'Assessment', href: '/assessment', icon: BookCheckIcon },
            { title: 'Profile', href: '/student-profile', icon: UserIcon },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: HeadsetIcon },
        ],
    },
    guest: {
        main: [],
        footer: [],
    },
};

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;

    const role = auth.role ?? 'guest';
    let nav = roleBasedNav[role] ?? roleBasedNav['guest'];

    // For HTE users, show Dashboard and Profile if they already have an HTE
    if (role === 'hte' && auth.user.hte) {
        nav = {
            ...nav,
            main: [
                { title: 'Dashboard', href: '/hte/dashboard', icon: ClipboardIcon },
                { title: 'Add Internship', href: '/hte/add-internship', icon: PlusIcon },
                { title: 'Student Endorsements', href: '/hte/endorsement-table', icon: Check },
                { title: 'Placed Students', href: '/hte/placed-students', icon: UsersIcon },
                { title: 'Profile', href: '/hte/profile', icon: UserIcon }
            ]
        };
    }

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
                <NavFooter items={nav.footer || []} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
