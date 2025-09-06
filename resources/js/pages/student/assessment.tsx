import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import StudentForm from '@/components/form/student/form';
import FormSubmitted from '@/components/form/student/form-submitted';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment',
        href: '/',
    },
];

type props = {
    hasSubmitted: boolean;
}

export default function Assessment({ hasSubmitted }: props) {

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                {hasSubmitted ? <FormSubmitted/> : <StudentForm />}
            </div>
        </AppLayout>
    );
}
