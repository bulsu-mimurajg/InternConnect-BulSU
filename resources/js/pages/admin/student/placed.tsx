import type { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Place Students',
        href: '/student/placed',
    },
];

export default function StudentPlaced() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Place Students" />

            <AdminLayout>
                <div className="space-y-6">
                    <h1>placed</h1>
                </div>
            </AdminLayout>
        </AppLayout>
    );
}
