import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';
import {
    FileTextIcon,
    DownloadIcon,
    UsersIcon,
    TargetIcon,
    UserCheckIcon,
    TrendingUpIcon,
    ClockIcon
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
    hasArchivedSections?: boolean;
    archivedSectionNames?: string[];
}

export default function AdviserReport({
    adviserSection,
    adviserSections,
    currentSectionId,
    hasArchivedSections = false,
    archivedSectionNames = []
}: Props) {
    const [selectedReportType, setSelectedReportType] = useState<string>('');
    const [selectedFormat, setSelectedFormat] = useState<string>('pdf');
    const [selectedSection, setSelectedSection] = useState<string>(currentSectionId?.toString() || '');
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    const reportCategories = [
        {
            title: 'Student Reports',
            reports: [
                {
                    value: 'student-list',
                    label: 'Student List report',
                    description: 'Complete list of students with basic information',
                    icon: UsersIcon,
                    requiresSection: true
                },
                {
                    value: 'student-assessment',
                    label: 'Student Assessment report',
                    description: 'Complete list of students with their details and status',
                    icon: UsersIcon,
                    requiresSection: true
                },
                {
                    value: 'placed-students',
                    label: 'Placed Students report',
                    description: 'Students who have been successfully placed in internships',
                    icon: TargetIcon,
                    requiresSection: true
                },
                {
                    value: 'endorsed-students',
                    label: 'Endorsed Students report',
                    description: 'Students who have been endorsed for internship placement',
                    icon: UserCheckIcon,
                    requiresSection: true
                }
            ]
        },
        {
            title: 'Performance Analytics',
            reports: [
                {
                    value: 'performance-analysis',
                    label: 'Performance Analysis report',
                    description: 'Detailed analysis of student performance and rankings',
                    icon: TrendingUpIcon,
                    requiresSection: true
                }
            ]
        }
    ];

    // Flattened report types for backward compatibility
    const reportTypes = reportCategories.flatMap(category => category.reports);

    const exportFormats = [
        { value: 'pdf', label: 'PDF Document' },
        { value: 'excel', label: 'Excel Spreadsheet' }
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
            default:
                exportRoute = route('adviser.report.export.excel', { reportType: selectedReportType });
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
                    {hasArchivedSections ? (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-amber-600">
                                    <ClockIcon className="h-5 w-5" />
                                    Sections Archived
                                </CardTitle>
                                <CardDescription>
                                    Your assigned sections have been archived and are no longer accessible.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <p className="text-sm text-muted-foreground">
                                        Archived sections: <span className="font-medium">{archivedSectionNames.join(', ')}</span>
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Please contact an administrator to restore access or get assigned to new sections.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
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
                    )}
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reports" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Generate Reports</h1>
                        <p className="text-muted-foreground">
                            Generate comprehensive reports for student assessments, placements, and performance analytics
                        </p>
                    </div>
                </div>

                {/* Section Selection - Only show if adviser has multiple sections */}
                {adviserSections.length > 1 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UsersIcon className="h-5 w-5" />
                                Section Selection
                            </CardTitle>
                            <CardDescription>
                                Select a section to generate section-specific reports
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Select value={selectedSection} onValueChange={setSelectedSection}>
                                <SelectTrigger className="h-10 text-sm">
                                    <SelectValue placeholder="Choose a section" />
                                </SelectTrigger>
                                <SelectContent className="max-h-60">
                                    <SelectItem value="all">
                                        All Sections
                                    </SelectItem>
                                    {adviserSections.map((section) => (
                                        <SelectItem key={section.section_id} value={section.section_id.toString()}>
                                            {section.section_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </CardContent>
                    </Card>
                )}

                {/* report Generation Form */}
                <div className="grid gap-6 grid-cols-1">
                    {/* report Type Selection */}
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
                                <SelectContent className="max-h-80">
                                    {reportCategories.map((category) => (
                                        <div key={category.title}>
                                            <div className="px-2 py-1.5 text-xs font-semibold text-muted-foreground uppercase tracking-wide">
                                                {category.title}
                                            </div>
                                            {category.reports.map((type) => (
                                                <SelectItem key={type.value} value={type.value} className="py-3">
                                                    <div className="flex flex-col items-start">
                                                        <div className="font-semibold text-sm flex items-center gap-2">
                                                            <type.icon className="h-4 w-4" />
                                                            {type.label}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground mt-1">{type.description}</div>
                                                    </div>
                                                </SelectItem>
                                            ))}
                                        </div>
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


                {/* Generate report Button */}
                <Card>
                    <CardContent>
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold">Ready to Generate Report</h3>
                                <p className="text-sm text-muted-foreground">
                                    {selectedReportType ?
                                        (adviserSections.length > 1 && selectedSection ?
                                            (selectedSection === 'all' ?
                                                `Generate ${reportTypes.find(t => t.value === selectedReportType)?.label} for all sections in ${exportFormats.find(f => f.value === selectedFormat)?.label} format` :
                                                `Generate ${reportTypes.find(t => t.value === selectedReportType)?.label} for ${adviserSections.find(s => s.section_id.toString() === selectedSection)?.section_name} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format`
                                            ) :
                                            `Generate ${reportTypes.find(t => t.value === selectedReportType)?.label} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format`
                                        ) :
                                        'Select a report type to continue'
                                    }
                                </p>
                            </div>
                            <Button
                                onClick={handleGenerateReport}
                                disabled={!selectedReportType || isGenerating || (adviserSections.length > 1 && !selectedSection)}
                                className="flex items-center gap-2"
                            >
                                <DownloadIcon className="h-4 w-4" />
                                {isGenerating ? 'Generating...' : 'Generate report'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </AppLayout>
    );
}
