import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/app-layout';
import SectionSwitcher from '@/components/SectionSwitcher';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    FileTextIcon,
    DownloadIcon,
    UsersIcon,
    BarChart3Icon,
    CalendarIcon,
    FilterIcon
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
    const [includeCharts, setIncludeCharts] = useState<boolean>(true);
    const [includeDetails, setIncludeDetails] = useState<boolean>(true);
    const [dateRange, setDateRange] = useState<string>('all');
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    const reportTypes = [
        { value: 'student-list', label: 'Student List Report', description: 'Complete list of students with their details and status' },
        { value: 'assessment-summary', label: 'Assessment Summary Report', description: 'Overview of assessment completion and performance' },
        { value: 'performance-analysis', label: 'Performance Analysis Report', description: 'Detailed analysis of student performance by category' },
        { value: 'progress-report', label: 'Progress Report', description: 'Student progress tracking and completion status' },
        { value: 'statistical-summary', label: 'Statistical Summary Report', description: 'Statistical analysis and trends' },
        { value: 'monthly-report', label: 'Monthly Activity Report', description: 'Monthly submission trends and activity' }
    ];

    const exportFormats = [
        { value: 'pdf', label: 'PDF Document' },
        { value: 'excel', label: 'Excel Spreadsheet' },
        { value: 'csv', label: 'CSV File' }
    ];

    const dateRanges = [
        { value: 'all', label: 'All Time' },
        { value: 'current-month', label: 'Current Month' },
        { value: 'last-month', label: 'Last Month' },
        { value: 'last-3-months', label: 'Last 3 Months' },
        { value: 'last-6-months', label: 'Last 6 Months' },
        { value: 'current-year', label: 'Current Year' }
    ];

    const handleGenerateReport = () => {
        if (!selectedReportType) return;

        setIsGenerating(true);
        
        router.post(route('adviser.generate-report'), {
            report_type: selectedReportType,
            format: selectedFormat,
            include_charts: includeCharts,
            include_details: includeDetails,
            date_range: dateRange,
            section_id: currentSectionId
        }, {
            onFinish: () => setIsGenerating(false)
        });
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
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Generate Reports</h1>
                            <p className="text-muted-foreground">
                                Section: {adviserSection}
                            </p>
                        </div>
                        {currentSectionId && (
                            <SectionSwitcher 
                                sections={adviserSections}
                                currentSectionId={currentSectionId}
                                className="ml-4"
                            />
                        )}
                    </div>
                </div>

                {/* Report Generation Form */}
                <div className="grid gap-6 md:grid-cols-2">
                    {/* Report Type Selection */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileTextIcon className="h-5 w-5" />
                                Report Type
                            </CardTitle>
                            <CardDescription>
                                Select the type of report you want to generate
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Select value={selectedReportType} onValueChange={setSelectedReportType}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Choose a report type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {reportTypes.map((type) => (
                                        <SelectItem key={type.value} value={type.value}>
                                            <div>
                                                <div className="font-medium">{type.label}</div>
                                                <div className="text-xs text-muted-foreground">{type.description}</div>
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
                        <CardContent className="space-y-4">
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

                            <div>
                                <label className="text-sm font-medium">Date Range</label>
                                <Select value={dateRange} onValueChange={setDateRange}>
                                    <SelectTrigger className="mt-1">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {dateRanges.map((range) => (
                                            <SelectItem key={range.value} value={range.value}>
                                                {range.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Report Options */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FilterIcon className="h-5 w-5" />
                            Report Options
                        </CardTitle>
                        <CardDescription>
                            Customize what to include in your report
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center space-x-6">
                            <div className="flex items-center space-x-2">
                                <Checkbox 
                                    id="include-charts" 
                                    checked={includeCharts} 
                                    onCheckedChange={(checked) => setIncludeCharts(checked === true)}
                                />
                                <label htmlFor="include-charts" className="text-sm font-medium">
                                    Include Charts and Graphs
                                </label>
                            </div>
                            <div className="flex items-center space-x-2">
                                <Checkbox 
                                    id="include-details" 
                                    checked={includeDetails} 
                                    onCheckedChange={(checked) => setIncludeDetails(checked === true)}
                                />
                                <label htmlFor="include-details" className="text-sm font-medium">
                                    Include Detailed Information
                                </label>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Generate Report Button */}
                <Card>
                    <CardContent className="pt-6">
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

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common report generation tasks
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <Button 
                                variant="outline" 
                                className="h-auto p-4 flex-col gap-2"
                                onClick={() => {
                                    setSelectedReportType('student-list');
                                    setSelectedFormat('excel');
                                }}
                            >
                                <UsersIcon className="h-6 w-6" />
                                <span>Student List</span>
                                <span className="text-xs text-muted-foreground">
                                    Export student roster to Excel
                                </span>
                            </Button>

                            <Button 
                                variant="outline" 
                                className="h-auto p-4 flex-col gap-2"
                                onClick={() => {
                                    setSelectedReportType('assessment-summary');
                                    setSelectedFormat('pdf');
                                }}
                            >
                                <BarChart3Icon className="h-6 w-6" />
                                <span>Assessment Summary</span>
                                <span className="text-xs text-muted-foreground">
                                    Generate assessment overview PDF
                                </span>
                            </Button>

                            <Button 
                                variant="outline" 
                                className="h-auto p-4 flex-col gap-2"
                                onClick={() => {
                                    setSelectedReportType('monthly-report');
                                    setSelectedFormat('pdf');
                                    setDateRange('current-month');
                                }}
                            >
                                <CalendarIcon className="h-6 w-6" />
                                <span>Monthly Report</span>
                                <span className="text-xs text-muted-foreground">
                                    Current month activity report
                                </span>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
