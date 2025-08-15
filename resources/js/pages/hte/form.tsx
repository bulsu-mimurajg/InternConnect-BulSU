import HTEForm from '@/components/form/hte/form';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { UserIcon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Form',
        href: '/hte/form',
    },
];

interface HTEFormPageProps {
    hte?: {
        id: number;
        company_name: string;
        company_address: string;
        company_email: string;
        cperson_fname: string;
        cperson_lname: string;
        cperson_position: string;
        cperson_contactnum: string;
        is_active: boolean;
        is_submit: boolean;
        created_at: string;
    };
    hasSubmitted?: boolean;
}

export default function HTEFormPage() {
    const { hte, hasSubmitted } = usePage<HTEFormPageProps>().props;

    // If HTE has already submitted, show view profile option
    if (hte && hte.is_submit) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Form" />
                <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 overflow-x-auto">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight">HTE Form</h1>
                        <p className="text-muted-foreground">
                            You have already submitted your HTE form. View your profile below.
                        </p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Form Already Submitted</CardTitle>
                            <CardDescription>
                                Your HTE form has been successfully submitted and cannot be modified.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="text-center py-8">
                                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900 mb-4">
                                    <svg className="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p className="text-gray-600 dark:text-gray-400 mb-4">
                                    Thank you for submitting your HTE form. Your internship opportunity has been recorded.
                                </p>
                                <Button asChild>
                                    <a href="/hte/profile">
                                        <UserIcon className="mr-2 h-4 w-4" />
                                        View Profile
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    // If HTE exists but hasn't submitted yet, show draft message
    if (hte && !hte.is_submit) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="HTE Form" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                    <div className="space-y-2">
                        <h1 className="text-3xl font-bold tracking-tight">HTE Form</h1>
                        <p className="text-muted-foreground">
                            You have a draft HTE form. Complete and submit it to finalize your application.
                        </p>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Draft Form Available</CardTitle>
                            <CardDescription>
                                You have started filling out your HTE form but haven't submitted it yet.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="text-center py-8">
                                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                                    <svg className="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p className="text-gray-600 dark:text-gray-400 mb-4">
                                    Continue filling out your form to complete your HTE application.
                                </p>
                                <Button asChild>
                                    <a href="/form">
                                        Continue Form
                                    </a>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Form" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <HTEForm />
            </div>
        </AppLayout>
    );
}
