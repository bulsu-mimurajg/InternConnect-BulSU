import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Match Students',
        href: '/student/matched',
    },
];

export default function StudentMatched() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Match Students" />

            <AdminLayout>
                <div className="space-y-6">
                    <h1>match</h1>
                </div>
            </AdminLayout>
        </AppLayout>
    );
}
