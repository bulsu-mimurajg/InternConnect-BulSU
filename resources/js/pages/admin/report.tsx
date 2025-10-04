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
    BarChart3Icon,
    BuildingIcon,
    TargetIcon,
    UserCheckIcon,
    ClipboardListIcon,
    TrendingUpIcon,
    GlobeIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Reports',
        href: '/admin/report',
    },
];

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    sections: Section[];
}

export default function AdminReport({ sections }: Props) {
    const [selectedReportType, setSelectedReportType] = useState<string>('');
    const [selectedFormat, setSelectedFormat] = useState<string>('pdf');
    const [selectedSection, setSelectedSection] = useState<string>('');
    const [isGenerating, setIsGenerating] = useState<boolean>(false);

    // Report types organized by categories - matching backend implementation
    const reportCategories = [
        {
            title: 'System Overview',
            reports: [
                {
                    value: 'comprehensive',
                    label: 'Comprehensive Report',
                    description: 'Complete system overview with all sections, students, placements, and HTE performance',
                    icon: GlobeIcon,
                    requiresSection: false
                }
            ]
        },
        {
            title: 'Student Reports',
            reports: [
                {
                    value: 'student-list',
                    label: 'Student List Report',
                    description: 'Complete list of students with basic information for a specific section',
                    icon: UsersIcon,
                    requiresSection: true
                },
                {
                    value: 'student-assessment',
                    label: 'Student Assessment Report',
                    description: 'Detailed list of students with assessment status and scores for a specific section',
                    icon: UsersIcon,
                    requiresSection: true
                },
            ]
        },
        {
            title: 'Placement Reports',
            reports: [
                {
                    value: 'all-placements',
                    label: 'All Placements Report',
                    description: 'Complete overview of all student placements across the system',
                    icon: TargetIcon,
                    requiresSection: false
                },
                {
                    value: 'placed-students',
                    label: 'Placed Students Report',
                    description: 'Students who have been successfully placed in internships for a specific section',
                    icon: TargetIcon,
                    requiresSection: true
                }
            ]
        },
        {
            title: 'Performance Analytics',
            reports: [
                {
                    value: 'assessment-summary',
                    label: 'Assessment Summary Report',
                    description: 'Overview of assessment completion and performance by category for a specific section',
                    icon: BarChart3Icon,
                    requiresSection: true
                },
                {
                    value: 'performance-analysis',
                    label: 'Performance Analysis Report',
                    description: 'Detailed analysis of student performance and rankings for a specific section',
                    icon: TrendingUpIcon,
                    requiresSection: true
                },
                {
                    value: 'hte-performance',
                    label: 'HTE Performance Report',
                    description: 'Host Training Establishment performance and utilization rates across all HTEs',
                    icon: BuildingIcon,
                    requiresSection: false
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
        if (reportInfo?.requiresSection && selectedSection && selectedSection !== 'all') {
            // Section-specific report
            switch (selectedFormat) {
                case 'pdf':
                    exportRoute = route('report.section.export.pdf', {
                        sectionId: selectedSection,
                        reportType: selectedReportType
                    });
                    break;
                case 'excel':
                    exportRoute = route('report.section.export.excel', {
                        sectionId: selectedSection,
                        reportType: selectedReportType
                    });
                    break;
                default:
                    exportRoute = route('report.section.export.excel', {
                        sectionId: selectedSection,
                        reportType: selectedReportType
                    });
            }
        } else {
            // General report (including "all" sections)
            switch (selectedFormat) {
                case 'pdf':
                    exportRoute = route('report.general.export.pdf', {
                        reportType: selectedReportType
                    });
                    break;
                case 'excel':
                    exportRoute = route('report.general.export.excel', {
                        reportType: selectedReportType
                    });
                    break;
                default:
                    exportRoute = route('report.general.export.excel', {
                        reportType: selectedReportType
                    });
            }
        }

        // Open the export URL in a new window/tab
        console.log('Export Route:', exportRoute);
        window.open(exportRoute, '_blank');

        // Reset generating state after a delay
        setTimeout(() => {
            setIsGenerating(false);
        }, 2000);
    };

    const getSelectedReportInfo = () => {
        return reportTypes.find(t => t.value === selectedReportType);
    };

    const getSelectedSectionInfo = () => {
        return sections.find(s => s.section_id.toString() === selectedSection);
    };

    const isGenerateDisabled = () => {
        if (!selectedReportType || !selectedFormat) return true;
        const reportInfo = getSelectedReportInfo();
        if (reportInfo?.requiresSection && !selectedSection) return true;
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
                            Generate comprehensive reports for students, placements, and system analytics
                        </p>
                    </div>
                </div>

                {/* Section Selection - Only show if report requires section */}
                {getSelectedReportInfo()?.requiresSection && (
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
                                    {sections.map((section) => (
                                        <SelectItem key={section.section_id} value={section.section_id.toString()}>
                                            {section.section_name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </CardContent>
                    </Card>
                )}

                {/* Report Generation Form */}
                <div className="grid gap-6 grid-cols-1">
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

                {/* Generate Report Button */}
                <Card>
                    <CardContent>
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold">Ready to Generate Report</h3>
                                <p className="text-sm text-muted-foreground">
                                    {selectedReportType ?
                                        (getSelectedReportInfo()?.requiresSection && selectedSection ?
                                            (selectedSection === 'all' ?
                                                `Generate ${getSelectedReportInfo()?.label} for all sections in ${exportFormats.find(f => f.value === selectedFormat)?.label} format` :
                                                `Generate ${getSelectedReportInfo()?.label} for ${getSelectedSectionInfo()?.section_name} in ${exportFormats.find(f => f.value === selectedFormat)?.label} format`
                                            ) :
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
                                {isGenerating ? 'Generating...' : 'Generate Report'}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
