import HTEForm from '@/components/form/hte/form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Form',
        href: '/hte/form',
    },
];

export default function HTEFormPage() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Form" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <HTEForm />
            </div>
        </AppLayout>
    );
}
