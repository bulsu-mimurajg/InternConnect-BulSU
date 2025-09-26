import EditInternshipForm from '@/components/form/hte/edit-internship-form';
import AppLayout from '@/layouts/app-layout';
import { Head, usePage } from '@inertiajs/react';

interface EditInternshipProps {
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
    categories: Array<{
        id: number;
        category_name: string;
        subCategories: Array<{
            id: number;
            subcategory_name: string;
        }>;
    }>;
    internship: {
        id: number;
        position: string;
        department: string;
        numberOfInterns: string;
        duration: string;
        startDate: string;
        endDate: string;
        is_active: boolean;
    };
    existingWeights: Record<string, number>;
}

interface PageProps {
    props: EditInternshipProps;
    [key: string]: unknown;
}

const breadcrumbs = [
    { label: 'Profile', href: '/hte/profile' },
    { label: 'Edit Internship', href: '#' },
];

export default function EditInternshipPage() {
    const { hte, categories, internship, existingWeights } = usePage<PageProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Internship" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Edit Internship</h1>
                    <p className="text-gray-600">Update your internship details and criteria weights</p>
                </div>
                
                <EditInternshipForm 
                    hte={hte}
                    categories={categories}
                    internship={internship}
                    existingWeights={existingWeights}
                />
            </div>
        </AppLayout>
    );
}
