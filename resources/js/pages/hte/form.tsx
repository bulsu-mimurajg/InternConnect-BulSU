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

interface DeadlineInfo {
    id: number;
    title: string;
    category: string;
    end_date: string;
}

interface Props {
    deadlineActive: boolean;
    deadlineInfo: DeadlineInfo | null;
    isFormSubmitted: boolean;
}

export default function HTEFormPage({ deadlineActive, deadlineInfo, isFormSubmitted }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Form" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {!deadlineActive && !isFormSubmitted && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <p className="font-medium">No current Deadline or Deadline is expired</p>
                        <p className="text-sm">
                            You cannot submit the HTE form at this time. Please contact the administrator.
                        </p>
                    </div>
                )}

                <HTEForm isFormSubmitted={isFormSubmitted} />
            </div>
        </AppLayout>
    );
}
