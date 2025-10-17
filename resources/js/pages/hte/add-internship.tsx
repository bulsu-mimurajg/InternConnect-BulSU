import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, Link } from '@inertiajs/react';
import AddInternshipForm from '@/components/form/hte/add-internship-form';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add Internship',
        href: '/hte/add-internship',
    },
];

interface Props {
    showSubmissionPrompt: boolean;
    studentAssessmentDeadlineActive: boolean;
    studentAssessmentDeadline?: {
        title: string;
        end_date: string;
    };
    [key: string]: unknown;
}

export default function AddInternshipPage() {
    const { showSubmissionPrompt, studentAssessmentDeadlineActive, studentAssessmentDeadline } = usePage<Props>().props;

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Add Internship" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 dark:text-yellow-400 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to add internship opportunities.
                                </p>
                                <Button asChild>
                                    <Link href="/form">
                                        Take Assessment
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    // Show deadline warning if student assessment period is active
    if (studentAssessmentDeadlineActive) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Add Internship" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-amber-500 dark:text-amber-400 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Student Assessment Period Active</h2>
                                <p className="text-muted-foreground mb-4">
                                    {studentAssessmentDeadline?.title} is currently active (ends {studentAssessmentDeadline?.end_date}).
                                    <br />You cannot add new internships during this period.
                                </p>
                                <div className="flex justify-center gap-3">
                                    <Button variant="outline" asChild>
                                        <Link href="/hte/profile">
                                            Back to Profile
                                        </Link>
                                    </Button>
                                    <Button variant="outline" asChild>
                                        <Link href="/hte/dashboard">
                                            Go to Dashboard
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Internship" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
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
