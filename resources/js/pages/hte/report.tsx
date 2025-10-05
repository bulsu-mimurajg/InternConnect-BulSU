import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    FileTextIcon,
    DownloadIcon,
    UsersIcon,
    BuildingIcon,
    TargetIcon,
    ClipboardListIcon,
    TrendingUpIcon,
    BriefcaseIcon,
    AlertCircle,
    BarChart3Icon,
    UserCheckIcon,
    GlobeIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/hte/report',
    },
];

interface Internship {
    id: number;
    position_title: string;
    department: string;
    slot_count: number;
    is_active: boolean;
}

interface Props {
    internships: Internship[];
    showSubmissionPrompt: boolean;
}

export default function HteReport({ internships, showSubmissionPrompt }: Props) {
    const [selectedReportType, setSelectedReportType] = useState<string>('');
    const [selectedFormat, setSelectedFormat] = useState<string>('pdf');
    const [selectedInternship, setSelectedInternship] = useState<string>('');
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Generate Reports" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to access reports.
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

    // HTE-specific report types organized by categories - matching Admin format
    const reportCategories = [
        {
            title: 'Company Overview',
            reports: [
                {
                    value: 'company-overview',
                    label: 'Company Overview report',
                    description: 'Complete overview of your company profile, internships, and placement statistics',
                    icon: BuildingIcon,
                    requiresInternship: false
                }
            ]
        },
        {
            title: 'Student Reports',
            reports: [
                {
                    value: 'placed-students',
                    label: 'Placed Students report',
                    description: 'List of students placed in your internships with their details and performance',
                    icon: UsersIcon,
                    requiresInternship: false
                },
                {
                    value: 'student-compatibility',
                    label: 'Student Compatibility report',
                    description: 'Analysis of student compatibility scores and matching criteria',
                    icon: TrendingUpIcon,
                    requiresInternship: true
                }
            ]
        },
        {
            title: 'Internship Analytics',
            reports: [
                {
                    value: 'internship-performance',
                    label: 'Internship Performance report',
                    description: 'Detailed analysis of specific internship performance and student placements',
                    icon: TargetIcon,
                    requiresInternship: true
                },
                {
                    value: 'internship-slots',
                    label: 'Internship Slots report',
                    description: 'Overview of available slots and utilization rates across all internships',
                    icon: ClipboardListIcon,
                    requiresInternship: false
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

        // Get the selected report type info
        const reportInfo = reportTypes.find(r => r.value === selectedReportType);

        // Determine the export route based on format and report type
        let exportRoute;
        if (reportInfo?.requiresInternship && selectedInternship) {
            // Internship-specific report
            switch (selectedFormat) {
                case 'pdf':
                    exportRoute = route('hte.report.internship.export.pdf', {
                        internshipId: selectedInternship,
                        reportType: selectedReportType
                    });
                    break;
                case 'excel':
                    exportRoute = route('hte.report.internship.export.excel', {
                        internshipId: selectedInternship,
                        reportType: selectedReportType
                    });
                    break;
                default:
                    exportRoute = route('hte.report.internship.export.excel', {
                        internshipId: selectedInternship,
                        reportType: selectedReportType
                    });
            }
        } else {
            // General HTE report
            switch (selectedFormat) {
                case 'pdf':
                    exportRoute = route('hte.report.general.export.pdf', {
                        reportType: selectedReportType
                    });
                    break;
                case 'excel':
                    exportRoute = route('hte.report.general.export.excel', {
                        reportType: selectedReportType
                    });
                    break;
                default:
                    exportRoute = route('hte.report.general.export.excel', {
                        reportType: selectedReportType
                    });
            }
        }

        // Open the export URL in a new window/tab
        window.open(exportRoute, '_blank');

        // Reset generating state after a delay
        setTimeout(() => {
            setIsGenerating(false);
        }, 2000);
    };

    const getSelectedReportInfo = () => {
        return reportTypes.find(t => t.value === selectedReportType);
    };

    const getSelectedInternshipInfo = () => {
        return internships.find(i => i.id.toString() === selectedInternship);
    };

    const isGenerateDisabled = () => {
        if (!selectedReportType || !selectedFormat) return true;
        const reportInfo = getSelectedReportInfo();
        if (reportInfo?.requiresInternship && !selectedInternship) return true;
        return false;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Generate Reports" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Generate Reports</h1>
                        <p className="text-muted-foreground">
                            Generate comprehensive reports for your company's internship programs and student placements
                        </p>
                    </div>
                </div>

                {/* Internship Selection - Only show if report requires internship */}
                {getSelectedReportInfo()?.requiresInternship && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BriefcaseIcon className="h-5 w-5" />
                                Internship Selection
                            </CardTitle>
                            <CardDescription>
                                Select an internship to generate internship-specific reports
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                                <SelectTrigger className="h-12 text-base">
                                    <SelectValue placeholder="Choose an internship" />
                                </SelectTrigger>
                                <SelectContent className="max-h-60">
                                    {internships.filter(internship => internship.is_active).map((internship) => (
                                        <SelectItem key={internship.id} value={internship.id.toString()}>
                                            {internship.position_title} - {internship.department}
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
                                        (getSelectedReportInfo()?.requiresInternship && selectedInternship ?
                                            `Generate ${getSelectedReportInfo()?.label} for ${getSelectedInternshipInfo()?.position_title} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format` :
                                            `Generate ${getSelectedReportInfo()?.label} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format`
                                        ) :
                                        'Select a report type to continue'
                                    }
                                </p>
                            </div>
                            <Button
                                onClick={handleGenerateReport}
                                disabled={isGenerateDisabled() || isGenerating}
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
