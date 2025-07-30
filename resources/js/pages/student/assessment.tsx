import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, subcategory } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import StudentForm from '@/components/form/student/form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment',
        href: '/',
    },
];

export default function Assessment() {

    const { subcategories } = usePage<{ subcategories: subcategory[] }>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                <StudentForm subcategories={subcategories} />
            </div>
        </AppLayout>
    );
}
