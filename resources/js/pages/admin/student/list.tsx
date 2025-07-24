import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import AdminLayout from '@/layouts/admin/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student List',
        href: '/student/',
    },
];

export default function StudentList() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student List" />

            <AdminLayout>
                <div className="space-y-6">
                    <h1>list</h1>
                </div>
            </AdminLayout>
        </AppLayout>
    );
}
