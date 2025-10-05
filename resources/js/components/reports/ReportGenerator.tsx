import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import {
    FileTextIcon,
    DownloadIcon,
    UsersIcon,
    BuildingIcon,
    TargetIcon,
    UserCheckIcon,
    ClipboardListIcon,
    TrendingUpIcon,
    GlobeIcon,
    BarChart3Icon
} from 'lucide-react';
import { useState, useEffect } from 'react';

// Icon mapping for report types
const iconMap = {
    UsersIcon,
    BuildingIcon,
    TargetIcon,
    UserCheckIcon,
    ClipboardListIcon,
    TrendingUpIcon,
    GlobeIcon,
    BarChart3Icon,
    FileTextIcon,
};

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

interface ReportGeneratorProps {
    reportCategories: ReportCategory[];
    sections: Section[];
    htes: HTE[];
    internships?: Internship[];
    userRole: string;
    onGenerateReport: (params: ReportGenerationParams) => void;
    isGenerating?: boolean;
}

interface ReportGenerationParams {
    reportType: string;
    format: 'pdf' | 'excel';
    sectionId?: string;
    hteId?: string;
    internshipId?: string;
}

export default function ReportGenerator({
    reportCategories,
    sections,
    htes,
    internships = [],
    userRole,
    onGenerateReport,
    isGenerating = false
}: ReportGeneratorProps) {
    const [selectedReportType, setSelectedReportType] = useState<string>('');
    const [selectedFormat, setSelectedFormat] = useState<'pdf' | 'excel'>('pdf');
    const [selectedSection, setSelectedSection] = useState<string>('');
    const [selectedHTE, setSelectedHTE] = useState<string>('');
    const [selectedInternship, setSelectedInternship] = useState<string>('');

    // Auto-select single section for advisers
    useEffect(() => {
        if (userRole === 'adviser' && sections.length === 1) {
            setSelectedSection(sections[0].section_id.toString());
        }
    }, [userRole, sections]);

    // Flattened report types for easier access
    const reportTypes = reportCategories.flatMap(category => category.reports);

    const exportFormats = [
        { value: 'pdf', label: 'PDF Document' },
        { value: 'excel', label: 'Excel Spreadsheet' }
    ];

    const getSelectedReportInfo = (): ReportType | undefined => {
        return reportTypes.find(t => t.type === selectedReportType);
    };

    const getSelectedSectionInfo = (): Section | undefined => {
        return sections.find(s => s.section_id.toString() === selectedSection);
    };

    const getSelectedHTEInfo = (): HTE | undefined => {
        return htes.find(h => h.id.toString() === selectedHTE);
    };

    const getSelectedInternshipInfo = (): Internship | undefined => {
        return internships.find(i => i.id.toString() === selectedInternship);
    };

    const isGenerateDisabled = (): boolean => {
        if (!selectedReportType || !selectedFormat) return true;

        const reportInfo = getSelectedReportInfo();
        if (!reportInfo) return true;

        // For advisers with only 1 section, section is auto-selected, so don't require manual selection
        const hasAutoSelectedSection = userRole === 'adviser' && sections.length === 1;
        
        if (reportInfo.requires_section && !selectedSection && !hasAutoSelectedSection) return true;
        if (reportInfo.requires_hte && !selectedHTE) return true;
        if (reportInfo.requires_internship && !selectedInternship) return true;

        return false;
    };

    const handleGenerateReport = () => {
        if (isGenerateDisabled()) return;

        const reportInfo = getSelectedReportInfo();
        if (!reportInfo) return;

        const params: ReportGenerationParams = {
            reportType: selectedReportType,
            format: selectedFormat,
        };

        if (reportInfo.requires_section) {
            // Use auto-selected section for advisers with only 1 section, otherwise use selected section
            const sectionToUse = (userRole === 'adviser' && sections.length === 1) 
                ? sections[0].section_id.toString() 
                : selectedSection;
            if (sectionToUse) {
                params.sectionId = sectionToUse;
            }
        }

        if (reportInfo.requires_hte && selectedHTE) {
            params.hteId = selectedHTE;
        }

        if (reportInfo.requires_internship && selectedInternship) {
            params.internshipId = selectedInternship;
        }

        onGenerateReport(params);
    };

    const generateDescription = (): string => {
        if (!selectedReportType) {
            return 'Select a report type to continue';
        }

        const reportInfo = getSelectedReportInfo();
        if (!reportInfo) return '';

        const formatLabel = exportFormats.find(f => f.value === selectedFormat)?.label || 'PDF Document';
        let description = `Generate ${reportInfo.name} in ${formatLabel} format`;

        if (reportInfo.requires_section && selectedSection) {
            if (selectedSection === 'all') {
                description += ' for all sections';
            } else {
                const sectionInfo = getSelectedSectionInfo();
                description += ` for ${sectionInfo?.section_name || 'selected section'}`;
            }
        }

        if (reportInfo.requires_hte && selectedHTE) {
            const hteInfo = getSelectedHTEInfo();
            description += ` for ${hteInfo?.company_name || 'selected HTE'}`;
        }

        return description;
    };

    return (
        <div className="space-y-6">
            {/* Section Selection */}
            {getSelectedReportInfo()?.requires_section && !(userRole === 'adviser' && sections.length === 1) && (
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

            {/* HTE Selection */}
            {getSelectedReportInfo()?.requires_hte && (
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <BuildingIcon className="h-5 w-5" />
                            HTE Selection
                        </CardTitle>
                        <CardDescription>
                            Select a Host Training Establishment for HTE-specific reports
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Select value={selectedHTE} onValueChange={setSelectedHTE}>
                            <SelectTrigger className="h-10 text-sm">
                                <SelectValue placeholder="Choose an HTE" />
                            </SelectTrigger>
                            <SelectContent className="max-h-60">
                                {htes.map((hte) => (
                                    <SelectItem key={hte.id} value={hte.id.toString()}>
                                        {hte.company_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>
            )}

            {/* Internship Selection */}
            {getSelectedReportInfo()?.requires_internship && (
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <ClipboardListIcon className="h-5 w-5" />
                            Internship Selection
                        </CardTitle>
                        <CardDescription>
                            Select a specific internship or all internships for detailed reporting
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                            <SelectTrigger className="h-10 text-sm">
                                <SelectValue placeholder="Choose an internship" />
                            </SelectTrigger>
                            <SelectContent className="max-h-60">
                                <SelectItem value="all">
                                    All Internships
                                </SelectItem>
                                {internships && internships.map((internship) => {
                                    return (
                                        <SelectItem key={internship.id} value={internship.id.toString()}>
                                            {internship.position_title} - {internship.department}
                                            {internship.hte && ` (${internship.hte.company_name})`}
                                        </SelectItem>
                                    );
                                })}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>
            )}

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
                                    {category.reports.map((type) => {
                                        const IconComponent = iconMap[type.icon as keyof typeof iconMap] || FileTextIcon;
                                        return (
                                            <SelectItem key={type.type} value={type.type} className="py-3">
                                                <div className="flex flex-col items-start">
                                                    <div className="font-semibold text-sm flex items-center gap-2">
                                                        <IconComponent className="h-4 w-4" />
                                                        {type.name}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground mt-1">
                                                        {type.description}
                                                    </div>
                                                </div>
                                            </SelectItem>
                                        );
                                    })}
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
                        <Select value={selectedFormat} onValueChange={(value: 'pdf' | 'excel') => setSelectedFormat(value)}>
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

            {/* Generate report Button */}
            <Card>
                <CardContent>
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-lg font-semibold">Ready to Generate Report</h3>
                            <p className="text-sm text-muted-foreground">
                                {generateDescription()}
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
    );
}
