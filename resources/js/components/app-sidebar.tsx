import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type NavGroup, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    ClipboardIcon,
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
    NotepadTextIcon, UserRoundIcon, MonitorCogIcon, GavelIcon, StepBackIcon
} from 'lucide-react';
import AppLogo from './app-logo';

const roleBasedNav: Record<string, { main: NavItem[]; groups?: NavGroup[]; footer: NavItem[] }> = {
    admin: {
        main: [],
        groups: [
            {
                title: 'Overview & Analytics',
                items: [
                    { title: 'Dashboard', href: '/admin-dashboard', icon: ChartNoAxesColumnIcon },
                    { title: 'Audit Logs', href: '/admin/logs', icon: MonitorCogIcon },
                    { title: 'Reports', href: '/report', icon: PrinterIcon },
                ]
            },
            {
                title: 'Student Management',
                items: [
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
                    {
                        title: 'Form',
                        href: '/form',
                        icon: NotepadTextIcon,
                        subNav: [
                            { title: 'Additional Info Tab', href: '/forms/additional-info'},
                            { title: 'Student Assessment', href: '/forms/assessment'},
                        ],
                    },
                ]
            },
            {
                title: 'Partner Management',
                items: [
                    { title: 'HTE', href: '/hte', icon: BriefcaseBusinessIcon },
                    { title: 'Adviser', href: '/adviser', icon: GavelIcon },
                ]
            },
            {
                title: 'Events',
                items: [
                    { title: 'Events', href: '/admin/events', icon: CalendarIcon },
                ]
            },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: StepBackIcon },
        ],
    },
    hte: {
        main: [
            { title: 'Dashboard', href: '/hte/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Form', href: '/form', icon: NotepadTextIcon },
            { title: 'Profile', href: '/hte/profile', icon: UserRoundIcon }
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: StepBackIcon },
        ],
    },
    adviser: {
        main: [
            { title: 'Dashboard', href: '/adviser/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Student Verification', href: '/student-verification', icon: Check },
            { title: 'Student List', href: '/adviser/student-list', icon: GraduationCapIcon },
            { title: 'Report', href: '/adviser/report', icon: PrinterIcon },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: StepBackIcon },
        ],
    },
    student: {
        main: [
            { title: 'Dashboard', href: '/student/dashboard', icon: ChartNoAxesColumnIcon },
            { title: 'Assessment', href: '/assessment', icon: NotepadTextIcon },
            { title: 'Profile', href: '/student-profile', icon: UserRoundIcon },
        ],
        footer: [
            { title: 'About', href: '/about', icon: InfoIcon },
            { title: 'Contact', href: '/contact', icon: StepBackIcon },
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
                <NavMain items={nav.main} groups={nav.groups} role={role} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={nav.footer || []} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
