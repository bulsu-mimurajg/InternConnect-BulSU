import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import AddInternshipForm from '@/components/form/hte/add-internship-form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/hte/dashboard',
    },
    {
        title: 'Add Internship',
        href: '/hte/add-internship',
    },
];

export default function AddInternshipPage() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Internship" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="space-y-2">
                    <h1 className="text-3xl font-bold tracking-tight">Add New Internship</h1>
                    <p className="text-muted-foreground">
                        Add a new internship opportunity to your company profile.
                    </p>
                </div>
                <AddInternshipForm />
            </div>
        </AppLayout>
    );
}
