import type { PropsWithChildren } from 'react';
import { Separator } from '@/components/ui/separator';
import { AppSidebar } from '@/components/app-sidebar';
import { AppShell } from '@/components/app-shell';
import { AppContent } from '@/components/app-content';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';

interface AdminLayoutProps extends PropsWithChildren {
    breadcrumbs?: BreadcrumbItem[];
}

export default function AdminLayout({ children, breadcrumbs = [] }: AdminLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden overflow-y-auto">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="px-4 py-6">

                    <Separator className="my-6 md:hidden" />

                    <div>
                        <section>{children}</section>
                    </div>
                </div>
            </AppContent>
        </AppShell>
    );
}
