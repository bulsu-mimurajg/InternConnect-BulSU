import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SectionSwitcher from '@/components/SectionSwitcher';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import {
    FileTextIcon,
    DownloadIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/adviser/report',
    },
];

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    adviserSection: string | null;
    adviserSections: Section[];
    currentSectionId: number | null;
}

export default function AdviserReport({ 
    adviserSection, 
    adviserSections, 
    currentSectionId 
}: Props) {
    const [selectedReportType, setSelectedReportType] = useState<string>('');
    const [selectedFormat, setSelectedFormat] = useState<string>('pdf');
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    const reportTypes = [
        { value: 'student-list', label: 'Student List Report', description: 'Complete list of students with their details and status' },
        { value: 'assessment-summary', label: 'Assessment Summary Report', description: 'Overview of assessment completion and performance' },
        { value: 'performance-analysis', label: 'Performance Analysis Report', description: 'Detailed analysis of student performance by category' },
        { value: 'progress-report', label: 'Progress Report', description: 'Student progress tracking and completion status' },
        { value: 'endorsed-students', label: 'Endorsed Students Report', description: 'Students who have been endorsed for internship placement' },
        { value: 'placed-students', label: 'Placed Students Report', description: 'Students who have been successfully placed in internships' }
    ];

    const exportFormats = [
        { value: 'pdf', label: 'PDF Document' },
        { value: 'excel', label: 'Excel Spreadsheet' },
        { value: 'csv', label: 'CSV File' }
    ];


    const handleGenerateReport = () => {
        if (!selectedReportType) return;

        setIsGenerating(true);
        
        // Determine the export route based on format and report type
        let exportRoute;
        switch (selectedFormat) {
            case 'pdf':
                exportRoute = route('adviser.report.export.pdf', { reportType: selectedReportType });
                break;
            case 'excel':
                exportRoute = route('adviser.report.export.excel', { reportType: selectedReportType });
                break;
            case 'csv':
                exportRoute = route('adviser.report.export.csv', { reportType: selectedReportType });
                break;
            default:
                exportRoute = route('adviser.report.export.csv', { reportType: selectedReportType });
        }

        // Open the export URL in a new window/tab
        window.open(exportRoute, '_blank');

        // Reset generating state after a delay
        setTimeout(() => {
            setIsGenerating(false);
        }, 2000);
    };

    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Reports" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileTextIcon className="h-5 w-5" />
                                No Section Assigned
                            </CardTitle>
                            <CardDescription>
                                You are not assigned to any section. Please contact the administrator.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Generate Reports</h1>
                        </div>
                        {adviserSections.length > 1 && (
                            <SectionSwitcher 
                                sections={adviserSections}
                                currentSectionId={currentSectionId}
                                showAllSections={true}
                                className="ml-4"
                            />
                        )}
                    </div>
                </div>

                {/* Report Generation Form */}
                <div className="grid gap-6 md:grid-cols-2">
                    {/* Report Type Selection */}
                    <Card className="flex flex-col">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileTextIcon className="h-5 w-5" />
                                Report Type
                            </CardTitle>
                            <CardDescription>
                                Select the type of report you want to generate
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex-1 flex flex-col justify-end">
                            <Select value={selectedReportType} onValueChange={setSelectedReportType}>
                                <SelectTrigger className="h-12 text-base">
                                    <SelectValue placeholder="Choose a report type" />
                                </SelectTrigger>
                                <SelectContent className="max-h-60">
                                    {reportTypes.map((type) => (
                                        <SelectItem key={type.value} value={type.value} className="py-3">
                                            <div className="flex flex-col items-start">
                                                <div className="font-semibold text-sm">{type.label}</div>
                                                <div className="text-xs text-muted-foreground mt-1">{type.description}</div>
                                            </div>
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </CardContent>
                    </Card>

                    {/* Export Options */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <DownloadIcon className="h-5 w-5" />
                                Export Options
                            </CardTitle>
                            <CardDescription>
                                Configure how the report will be exported
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div>
                                <label className="text-sm font-medium">Format</label>
                                <Select value={selectedFormat} onValueChange={setSelectedFormat}>
                                    <SelectTrigger className="mt-1">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {exportFormats.map((format) => (
                                            <SelectItem key={format.value} value={format.value}>
                                                {format.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>
                </div>


                {/* Generate Report Button */}
                <Card>
                    <CardContent className="pt-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold">Ready to Generate Report</h3>
                                <p className="text-sm text-muted-foreground">
                                    {selectedReportType ? 
                                        `Generate ${reportTypes.find(t => t.value === selectedReportType)?.label} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format` :
                                        'Select a report type to continue'
                                    }
                                </p>
                            </div>
                            <Button 
                                onClick={handleGenerateReport}
                                disabled={!selectedReportType || isGenerating}
                                className="flex items-center gap-2"
                            >
                                <DownloadIcon className="h-4 w-4" />
                                {isGenerating ? 'Generating...' : 'Generate Report'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}
