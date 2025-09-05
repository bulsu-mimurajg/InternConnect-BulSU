import AppLayout from '@/layouts/app-layout';
import { AssessmentProps, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import StudentForm from '@/components/form/student/form';
import StudentFormSubmit from '@/components/form/student-submitted';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment',
        href: '/',
    },
];

export default function Assessment({hasSubmitted, data}: AssessmentProps){
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                {hasSubmitted ? <StudentFormSubmit/> : <StudentForm data={data}/>}
            </div>
        </AppLayout>
    );
}
