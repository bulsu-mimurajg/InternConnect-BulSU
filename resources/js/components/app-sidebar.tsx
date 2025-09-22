import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    ClipboardIcon,
    HeadsetIcon,
    InfoIcon,
    PrinterIcon,
    UserIcon,
    UsersIcon,
    PlusIcon,
    CalendarIcon,
    Check,
    ChartNoAxesColumnIcon,
    BriefcaseBusinessIcon,
    GraduationCapIcon,
    SchoolIcon,
    NotepadTextIcon, UserRoundIcon, MonitorCogIcon
} from 'lucide-react';
import AppLogo from './app-logo';

const roleBasedNav: Record<string, { main: NavItem[]; footer: NavItem[] }> = {
    admin: {
        main: [

            { title: 'Dashboard', href: '/admin-dashboard', icon: ChartNoAxesColumnIcon },
            {
                title: 'Student',
                href: '/student',
                icon: GraduationCapIcon,
                subNav: [
                    { title: 'List', href: '/student/list' },
                    { title: 'Match', href: '/student/matched' },
                    { title: 'Endorsed', href: '/student/endorsed' },
                    { title: 'Place', href: '/student/placed' },
                ],
            },
            { title: 'HTE', href: '/hte', icon: BriefcaseBusinessIcon },
            { title: 'Adviser', href: '/adviser', icon: SchoolIcon },
            {
                title: 'Form',
                href: '/form',
                icon: NotepadTextIcon,
                subNav: [
                    { title: 'Additional Info Tab', href: '/forms/additional-info'},
                    { title: 'Student Assessment', href: '/forms/assessment'},
                ],
            },

            { title: 'Events', href: '/admin/events', icon: CalendarIcon },
            { title: 'Reports', href: '/report', icon: PrinterIcon },
            { title: 'Audit Logs', href: '/admin/logs', icon: MonitorCogIcon },
        ],
        footer: [],
    },
    hte: {
        main: [
            { title: 'Dashboard', href: '/hte/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Form', href: '/form', icon: NotepadTextIcon },
            { title: 'Profile', href: '/hte/profile', icon: UserRoundIcon }
        ],
        footer: [],
    },
    adviser: {
        main: [
            { title: 'Dashboard', href: '/adviser/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Student Verification', href: '/student-verification', icon: Check },
            { title: 'Student List', href: '/adviser/student-list', icon: GraduationCapIcon },
            { title: 'Report', href: '/adviser/report', icon: PrinterIcon },
        ],
        footer: [],
    },
    student: {
        main: [
            { title: 'Dashboard', href: '/student/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Assessment', href: '/assessment', icon: NotepadTextIcon },
            { title: 'Profile', href: '/student-profile', icon: UserRoundIcon },
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
