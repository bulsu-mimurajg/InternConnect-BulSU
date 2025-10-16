import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import ReportGenerator from '@/components/reports/ReportGenerator';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/reports',
    },
];

interface Section {
    section_id: number;
    section_name: string;
}

interface HTE {
    id: number;
    company_name: string;
}

interface Internship {
    id: number;
    position_title: string;
    department: string;
    hte_id: number;
    hte?: {
        id: number;
        company_name: string;
    };
}

interface ReportType {
    type: string;
    name: string;
    description: string;
    icon: string;
    requires_section: boolean;
    requires_hte: boolean;
    requires_internship?: boolean;
}

interface ReportCategory {
    title: string;
    reports: ReportType[];
}

interface Props {
    sections: Section[];
    htes: HTE[];
    internships: Internship[];
    reportCategories: ReportCategory[];
    userRole: string;
}

interface ReportGenerationParams {
    reportType: string;
    format: 'pdf' | 'excel';
    sectionId?: string;
    hteId?: string;
    internshipId?: string;
}

export default function AdminReport({ sections, htes, internships, reportCategories, userRole }: Props) {
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    const handleGenerateReport = (params: ReportGenerationParams) => {
        setIsGenerating(true);

        // Build the export URL based on parameters
        let exportRoute;

        if (params.format === 'pdf') {
            exportRoute = route('reports.pdf', { reportType: params.reportType });
        } else {
            exportRoute = route('reports.excel', { reportType: params.reportType });
        }

        // Add query parameters
        const queryParams = new URLSearchParams();
        if (params.sectionId) {
            queryParams.append('section_id', params.sectionId);
        }
        if (params.hteId) {
            queryParams.append('hte_id', params.hteId);
        }
        if (params.internshipId) {
            queryParams.append('internship_id', params.internshipId);
        }

        if (queryParams.toString()) {
            exportRoute += '?' + queryParams.toString();
        }

        // Open the export URL in a new window/tab
        window.open(exportRoute, '_blank');

        // Reset generating state after a delay
        setTimeout(() => {
            setIsGenerating(false);
        }, 2000);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Reports" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Admin Reports</h1>
                        <p className="text-muted-foreground">
                            Generate comprehensive reports for students, placements, and system analytics
                        </p>
                    </div>
                </div>

                {/* report Generator */}
                <ReportGenerator
                    reportCategories={reportCategories}
                    sections={sections}
                    htes={htes}
                    internships={internships}
                    userRole={userRole}
                    onGenerateReport={handleGenerateReport}
                    isGenerating={isGenerating}
                />
            </div>
        </AppLayout>
    );
}
