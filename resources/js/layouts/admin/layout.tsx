import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Separator } from '@/components/ui/separator';
import { AppSidebar } from '@/components/app-sidebar';
import { AppShell } from '@/components/app-shell';
import { AppContent } from '@/components/app-content';
import { AppSidebarHeader } from '@/components/app-sidebar-header';

export default function AdminLayout({ children }: PropsWithChildren) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden overflow-y-auto">
                <AppSidebarHeader breadcrumbs={[]} />
                <div className="px-4 py-6">
                    <div className="flex justify-between">
                        <Heading title="Student Management" description="Manage student list, matches, and placements." />
                    </div>

                    <Separator className="my-6 md:hidden" />

                    <div>
                        <section>{children}</section>
                    </div>
                </div>
            </AppContent>
        </AppShell>
    );
}
