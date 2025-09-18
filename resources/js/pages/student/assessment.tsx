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

interface DeadlineInfo {
    id: number;
    title: string;
    category: string;
    end_date: string;
}

type props = {
    hasSubmitted: boolean;
    deadlineActive: boolean;
    deadlineInfo: DeadlineInfo | null;
}

export default function Assessment({ hasSubmitted, deadlineActive, deadlineInfo }: props) {

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                {!deadlineActive && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p className="font-medium">No current Deadline or Deadline is expired</p>
                        <p className="text-sm">
                            You cannot take the assessment at this time. Please contact the administrator.
                        </p>
                    </div>
                )}
                {hasSubmitted ? <FormSubmitted/> : <StudentForm />}
            </div>
        </AppLayout>
    );
}
