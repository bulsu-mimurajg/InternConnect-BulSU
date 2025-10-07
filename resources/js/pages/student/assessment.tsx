import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import StudentForm from '@/components/form/student/form';
import FormSubmitted from '@/components/form/student/form-submitted';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertCircle } from 'lucide-react';

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

    // Show deadline warning if student assessment period is NOT active (deadline expired or no deadline)
    if (!deadlineActive) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Assessment" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-red-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Period Not Active</h2>
                                <p className="text-muted-foreground mb-4">
                                    No current deadline or deadline has expired. You cannot take the assessment at this time.
                                    <br />Please contact the administrator for more information.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                {hasSubmitted ? <FormSubmitted/> : <StudentForm />}
            </div>
        </AppLayout>
    );
}
