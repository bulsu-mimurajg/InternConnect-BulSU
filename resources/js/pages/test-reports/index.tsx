import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
  FileTextIcon, 
  DownloadIcon, 
  EyeIcon, 
  GlobeIcon, 
  BuildingIcon, 
  TrendingUpIcon, 
  UsersIcon, 
  TargetIcon, 
  UserCheckIcon, 
  UserIcon,
  ClipboardListIcon,
  AlertCircleIcon
} from 'lucide-react';

interface ReportConfig {
  name: string;
  description: string;
  category: string;
  icon: string;
  requires_section: boolean;
  requires_hte: boolean;
  requires_internship: boolean;
  roles: string[];
}

interface TestSection {
  id: number;
  name: string;
}

interface TestHTE {
  id: number;
  company_name: string;
}

interface TestInternship {
  id: number;
  position_title: string;
  hte_id: number;
}

interface Props {
  availableReports: Record<string, ReportConfig>;
  testSections: TestSection[];
  testHTEs: TestHTE[];
  testInternships: TestInternship[];
  userRole: string;
}

const iconMap: Record<string, React.ComponentType<{ className?: string }>> = {
  GlobeIcon,
  BuildingIcon,
  TrendingUpIcon,
  UsersIcon,
  TargetIcon,
  UserCheckIcon,
  UserIcon,
  ClipboardListIcon,
};

export default function TestReportsIndex({ 
  availableReports, 
  testSections, 
  testHTEs, 
  testInternships, 
  userRole 
}: Props) {
  const [selectedSection, setSelectedSection] = useState<string>('');
  const [selectedHTE, setSelectedHTE] = useState<string>('');
  const [selectedInternship, setSelectedInternship] = useState<string>('');
  const [loading, setLoading] = useState<string | null>(null);

  const generateTestPDF = (reportType: string) => {
    setLoading(`pdf-${reportType}`);
    const params = new URLSearchParams();
    
    if (selectedSection) params.append('section_id', selectedSection);
    if (selectedHTE) params.append('hte_id', selectedHTE);
    if (selectedInternship) params.append('internship_id', selectedInternship);

    const url = `/test-reports/pdf/${reportType}?${params.toString()}`;
    window.open(url, '_blank');
    
    setTimeout(() => setLoading(null), 2000);
  };

  const generateTestExcel = (reportType: string) => {
    setLoading(`excel-${reportType}`);
    const params = new URLSearchParams();
    
    if (selectedSection) params.append('section_id', selectedSection);
    if (selectedHTE) params.append('hte_id', selectedHTE);
    if (selectedInternship) params.append('internship_id', selectedInternship);

    const url = `/test-reports/excel/${reportType}?${params.toString()}`;
    window.open(url, '_blank');
    
    setTimeout(() => setLoading(null), 2000);
  };

  const previewTestData = (reportType: string) => {
    setLoading(`preview-${reportType}`);
    const params = new URLSearchParams();
    
    if (selectedSection) params.append('section_id', selectedSection);
    if (selectedHTE) params.append('hte_id', selectedHTE);
    if (selectedInternship) params.append('internship_id', selectedInternship);

    router.get(`/test-reports/preview/${reportType}?${params.toString()}`, {}, {
      onFinish: () => setLoading(null),
    });
  };

  const getIconComponent = (iconName: string) => {
    const IconComponent = iconMap[iconName] || FileTextIcon;
    return <IconComponent className="h-5 w-5" />;
  };

  const getRoleBasedRoute = () => {
    switch (userRole) {
      case 'admin':
        return '/admin/test-reports';
      case 'adviser':
        return '/adviser/test-reports';
      case 'hte':
        return '/hte/test-reports';
      case 'student':
        return '/student/test-reports';
      default:
        return '/test-reports';
    }
  };

  const groupedReports = Object.entries(availableReports).reduce((acc, [key, config]) => {
    if (!acc[config.category]) {
      acc[config.category] = [];
    }
    acc[config.category].push({ key, ...config });
    return acc;
  }, {} as Record<string, Array<{ key: string } & ReportConfig>>);

  return (
    <div className="min-h-screen bg-background">
      <Head title="Test Reports" />
      
      <div className="container mx-auto px-4 py-6 space-y-6">
        {/* Header */}
        <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <h1 className="text-3xl font-bold text-foreground">Test Reports Dashboard</h1>
            <p className="text-muted-foreground mt-2">
              Test all report types with sample data - Role: <Badge variant="secondary">{userRole}</Badge>
            </p>
          </div>
          <div className="flex gap-2">
            <Button 
              variant="outline" 
              onClick={() => router.get(getRoleBasedRoute())}
            >
              Refresh
            </Button>
          </div>
        </div>

        {/* Test Parameters */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertCircleIcon className="h-5 w-5" />
              Test Parameters
            </CardTitle>
            <CardDescription>
              Select test parameters for reports that require specific data
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <label className="text-sm font-medium">Test Section</label>
                <Select value={selectedSection} onValueChange={setSelectedSection}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select a section" />
                  </SelectTrigger>
                  <SelectContent>
                    {testSections.map((section) => (
                      <SelectItem key={section.id} value={section.id.toString()}>
                        {section.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Test HTE</label>
                <Select value={selectedHTE} onValueChange={setSelectedHTE}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select an HTE" />
                  </SelectTrigger>
                  <SelectContent>
                    {testHTEs.map((hte) => (
                      <SelectItem key={hte.id} value={hte.id.toString()}>
                        {hte.company_name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <label className="text-sm font-medium">Test Internship</label>
                <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                  <SelectTrigger>
                    <SelectValue placeholder="Select an internship" />
                  </SelectTrigger>
                  <SelectContent>
                    {testInternships.map((internship) => (
                      <SelectItem key={internship.id} value={internship.id.toString()}>
                        {internship.position_title}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Available Reports */}
        <div className="space-y-6">
          {Object.entries(groupedReports).map(([category, reports]) => (
            <div key={category} className="space-y-4">
              <h2 className="text-2xl font-semibold text-foreground">{category}</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {reports.map((report) => (
                  <Card key={report.key} className="hover:shadow-md transition-shadow">
                    <CardHeader className="pb-3">
                      <CardTitle className="flex items-center gap-2 text-lg">
                        {getIconComponent(report.icon)}
                        {report.name}
                      </CardTitle>
                      <CardDescription className="text-sm">
                        {report.description}
                      </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3">
                      {/* Requirements */}
                      <div className="flex flex-wrap gap-1">
                        {report.requires_section && (
                          <Badge variant="outline" className="text-xs">Section Required</Badge>
                        )}
                        {report.requires_hte && (
                          <Badge variant="outline" className="text-xs">HTE Required</Badge>
                        )}
                        {report.requires_internship && (
                          <Badge variant="outline" className="text-xs">Internship Required</Badge>
                        )}
                      </div>

                      {/* Action Buttons */}
                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => generateTestPDF(report.key)}
                          disabled={loading === `pdf-${report.key}`}
                          className="flex-1"
                        >
                          <DownloadIcon className="h-4 w-4 mr-1" />
                          PDF
                        </Button>
                        <Button
                          size="sm"
                          variant="outline"
                          onClick={() => generateTestExcel(report.key)}
                          disabled={loading === `excel-${report.key}`}
                          className="flex-1"
                        >
                          <DownloadIcon className="h-4 w-4 mr-1" />
                          Excel
                        </Button>
                        <Button
                          size="sm"
                          variant="ghost"
                          onClick={() => previewTestData(report.key)}
                          disabled={loading === `preview-${report.key}`}
                        >
                          <EyeIcon className="h-4 w-4" />
                        </Button>
                      </div>

                      {/* Loading State */}
                      {loading?.startsWith(report.key) && (
                        <div className="text-xs text-muted-foreground text-center">
                          Generating test data...
                        </div>
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            </div>
          ))}
        </div>

        {/* Info Alert */}
        <Alert>
          <AlertCircleIcon className="h-4 w-4" />
          <AlertDescription>
            <strong>Test Mode:</strong> All reports use generated test data. 
            Parameters are optional but may affect the content of reports that require specific sections, HTEs, or internships.
            Files are automatically downloaded with timestamps in the filename.
          </AlertDescription>
        </Alert>
      </div>
    </div>
  );
}
