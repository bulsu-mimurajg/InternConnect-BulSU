import EditInternshipForm from '@/components/form/hte/edit-internship-form';
import AppLayout from '@/layouts/app-layout';
import { Head, usePage, Link } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertCircle } from 'lucide-react';

// Local Category type to match what backend sends and form expects
interface Question {
    id: number;
    question: string;
    access: string;
    is_active: boolean;
}

interface SubCategory {
    id: number;
    subcategory_name: string;
    questions?: Question[];
}

interface Category {
    id: number;
    category_name: string;
    subCategories: SubCategory[];
}

interface Internship {
    id: number;
    position: string;
    department: string;
    numberOfInterns: string;
    duration: string;
    startDate: string;
    endDate: string;
    is_active: boolean;
}

interface StudentAssessmentDeadline {
    title: string;
    end_date: string;
}

interface PageProps {
    hte: {
        id: number;
        company_name: string;
        company_address: string;
        company_email: string;
        cperson_fname: string;
        cperson_lname: string;
        cperson_position: string;
        cperson_contactnum: string;
        is_active: boolean;
        created_at: string;
    };
    categories: unknown;
    internship: Internship;
    existingWeights: Record<string, number>;
    studentAssessmentDeadlineActive: boolean;
    studentAssessmentDeadline?: StudentAssessmentDeadline;
    [key: string]: unknown;
}

const breadcrumbs = [
    { title: 'Profile', href: '/hte/profile' },
    { title: 'Edit Internship', href: '#' },
];

export default function EditInternshipPage() {
    const { categories, internship, existingWeights, studentAssessmentDeadlineActive, studentAssessmentDeadline } = usePage<PageProps>().props as PageProps;

    // Show deadline warning if student assessment period is active
    if (studentAssessmentDeadlineActive) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Edit Internship" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-amber-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Student Assessment Period Active</h2>
                                <p className="text-muted-foreground mb-4">
                                    {studentAssessmentDeadline?.title} is currently active (ends {studentAssessmentDeadline?.end_date}).
                                    <br />You cannot edit internships during this period.
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
            <Head title="Edit Internship" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Edit Internship</h1>
                    <p className="text-gray-600">Update your internship details and criteria weights</p>
                </div>
                
                <EditInternshipForm 
                    categories={categories as Category[]}
                    internship={internship}
                    existingWeights={existingWeights}
                />
            </div>
        </AppLayout>
    );
}
