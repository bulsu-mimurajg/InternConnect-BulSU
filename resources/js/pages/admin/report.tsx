import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Progress } from '@/components/ui/progress';
import { BarChart, PieChart, LineChart, AreaChart } from '@/components/charts';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {
    BarChart3Icon,
    UsersIcon,
    BuildingIcon,
    DownloadIcon,
    FileTextIcon,
    TargetIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Report',
        href: '/report',
    },
];

interface DashboardStats {
    totalStudents: number;
    completedAssessments: number;
    placedStudents: number;
    totalHTEs: number;
    activeHTEs: number;
    totalInternships: number;
    totalSlots: number;
    pendingPlacements: number;
    completionRate: number;
    placementRate: number;
    hteParticipationRate: number;
}

interface PlacementAnalytics {
    companyPlacements: Array<{
        company: string;
        totalSlots: number;
        filledSlots: number;
        successRate: number;
    }>;
    departmentPlacements: Array<{
        department: string;
        count: number;
    }>;
    avgCompatibilityScore: number;
}

interface StudentAnalytics {
    assessmentStatus: {
        total: number;
        completed: number;
        pending: number;
        completionRate: number;
    };
    categoryScores: Array<{
        category: string;
        avgScore: number;
        totalAssessments: number;
    }>;
    topStudents: Array<{
        id: number;
        name: string;
        student_number: string;
        section: string;
        avgScore: number;
    }>;
}

interface HTEAnalytics {
    htePerformance: Array<{
        id: number;
        company_name: string;
        contact_person: string;
        is_submit: boolean;
        totalInternships: number;
        activeInternships: number;
        totalSlots: number;
        filledSlots: number;
        utilizationRate: number;
        created_at: string;
    }>;
    registrationTrends: Array<{
        date: string;
        count: number;
    }>;
}

interface SectionAnalytics {
    section: string;
    totalStudents: number;
    completedAssessments: number;
    placedStudents: number;
    completionRate: number;
    placementRate: number;
    avgScore: number;
}

interface AssessmentTrends {
    date: string;
    completed: number;
    total: number;
    completionRate: number;
}

interface PlacementTrends {
    date: string;
    approved: number;
    pending: number;
    rejected: number;
    total: number;
    approvalRate: number;
}

interface ReportProps {
    stats: DashboardStats;
    placementAnalytics: PlacementAnalytics;
    studentAnalytics: StudentAnalytics;
    hteAnalytics: HTEAnalytics;
    sectionAnalytics: SectionAnalytics[];
    assessmentTrends: AssessmentTrends[];
    placementTrends: PlacementTrends[];
}

export default function Report({
    stats,
    placementAnalytics,
    studentAnalytics,
    hteAnalytics,
    sectionAnalytics,
    assessmentTrends,
    placementTrends,
}: ReportProps) {
    const handleExportPDF = () => {
        window.open('/report/export/pdf', '_blank');
    };

    const handleExportExcel = () => {
        window.open('/report/export/excel', '_blank');
    };

    // Chart data processing
    const getAssessmentStatusChartData = () => [
        { name: 'Completed', value: studentAnalytics.assessmentStatus.completed, color: '#059669' },
        { name: 'Pending', value: studentAnalytics.assessmentStatus.pending, color: '#d97706' }
    ];

    const getPlacementStatusChartData = () => [
        { name: 'Approved', value: stats.placedStudents, color: '#059669' },
        { name: 'Pending', value: stats.pendingPlacements, color: '#d97706' },
        { name: 'Rejected', value: placementTrends.reduce((sum, trend) => sum + trend.rejected, 0), color: '#dc2626' }
    ];

    const getCategoryScoresChartData = () => 
        studentAnalytics.categoryScores.map(category => ({
            name: category.category,
            value: category.avgScore
        }));

    const getCompanyPlacementsChartData = () => 
        placementAnalytics.companyPlacements.slice(0, 8).map(company => ({
            name: company.company.length > 15 ? company.company.substring(0, 15) + '...' : company.company,
            value: company.successRate
        }));

    const getDepartmentPlacementsChartData = () => 
        placementAnalytics.departmentPlacements.map(dept => ({
            name: dept.department,
            value: dept.count
        }));

    const getSectionPerformanceChartData = () => 
        sectionAnalytics.map(section => ({
            name: section.section,
            completionRate: section.completionRate,
            placementRate: section.placementRate,
            avgScore: section.avgScore
        }));

    const getAssessmentTrendsChartData = () => 
        assessmentTrends.slice(-14).map(trend => ({
            date: new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
            completed: trend.completed,
            completionRate: trend.completionRate
        }));

    const getPlacementTrendsChartData = () => 
        placementTrends.slice(-14).map(trend => ({
            date: new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
            approved: trend.approved,
            pending: trend.pending,
            rejected: trend.rejected
        }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Comprehensive Reports" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Comprehensive Reports</h1>
                        <p className="text-muted-foreground">
                            Detailed analytics and insights for the internship management system
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" onClick={handleExportPDF}>
                            <FileTextIcon className="h-4 w-4 mr-2" />
                            Export PDF
                        </Button>
                        <Button variant="outline" onClick={handleExportExcel}>
                            <DownloadIcon className="h-4 w-4 mr-2" />
                            Export Excel
                        </Button>
                    </div>
                </div>

                {/* Key Metrics */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.completionRate}% completed assessments
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Placements</CardTitle>
                            <TargetIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.placedStudents}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.placementRate}% placement rate
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active HTEs</CardTitle>
                            <BuildingIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeHTEs}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.hteParticipationRate}% participation rate
                            </p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Available Slots</CardTitle>
                            <BarChart3Icon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalSlots}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.pendingPlacements} pending placements
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Detailed Reports Tabs */}
                <Tabs defaultValue="overview" className="space-y-4">
                    <TabsList>
                        <TabsTrigger value="overview">Overview</TabsTrigger>
                        <TabsTrigger value="students">Students</TabsTrigger>
                        <TabsTrigger value="placements">Placements</TabsTrigger>
                        <TabsTrigger value="htes">HTEs</TabsTrigger>
                        <TabsTrigger value="sections">Sections</TabsTrigger>
                        <TabsTrigger value="trends">Trends</TabsTrigger>
                    </TabsList>

                    {/* Overview Tab */}
                    <TabsContent value="overview" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Assessment Completion Status</CardTitle>
                                    <CardDescription>
                                        Current status of student assessments
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <PieChart 
                                        data={getAssessmentStatusChartData()} 
                                        height={200}
                                    />
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <CheckCircleIcon className="h-4 w-4 text-green-500" />
                                            <span className="text-sm">Completed</span>
                                        </div>
                                        <span className="font-medium">{studentAnalytics.assessmentStatus.completed}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <ClockIcon className="h-4 w-4 text-yellow-500" />
                                            <span className="text-sm">Pending</span>
                                        </div>
                                        <span className="font-medium">{studentAnalytics.assessmentStatus.pending}</span>
                                    </div>
                                    <Progress value={studentAnalytics.assessmentStatus.completionRate} className="w-full" />
                                    <p className="text-xs text-muted-foreground text-center">
                                        {studentAnalytics.assessmentStatus.completionRate}% completion rate
                                    </p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Placement Status</CardTitle>
                                    <CardDescription>
                                        Current placement distribution
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <PieChart 
                                        data={getPlacementStatusChartData()} 
                                        height={200}
                                    />
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <CheckCircleIcon className="h-4 w-4 text-green-500" />
                                            <span className="text-sm">Approved</span>
                                        </div>
                                        <span className="font-medium">{stats.placedStudents}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <ClockIcon className="h-4 w-4 text-yellow-500" />
                                            <span className="text-sm">Pending</span>
                                        </div>
                                        <span className="font-medium">{stats.pendingPlacements}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <XCircleIcon className="h-4 w-4 text-red-500" />
                                            <span className="text-sm">Rejected</span>
                                        </div>
                                        <span className="font-medium">
                                            {placementTrends.reduce((sum, trend) => sum + trend.rejected, 0)}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>Top Performing Students</CardTitle>
                                <CardDescription>
                                    Students with highest assessment scores
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {studentAnalytics.topStudents.slice(0, 5).map((student, index) => (
                                        <div key={student.id} className="flex items-center justify-between p-3 border rounded-lg">
                                            <div className="flex items-center space-x-3">
                                                <div className="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-primary-foreground text-sm font-medium">
                                                    {index + 1}
                                                </div>
                                                <div>
                                                    <p className="font-medium">{student.name}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {student.student_number} • {student.section}
                                                    </p>
                                                </div>
                                            </div>
                                            <Badge variant="secondary">
                                                {student.avgScore}/5.0
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Students Tab */}
                    <TabsContent value="students" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Assessment Scores by Category</CardTitle>
                                    <CardDescription>
                                        Average scores across different skill categories
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <BarChart 
                                        data={getCategoryScoresChartData()} 
                                        dataKey="value"
                                        color="#2563eb"
                                        height={300}
                                    />
                                    <div className="space-y-4 mt-4">
                                        {studentAnalytics.categoryScores.map((category) => (
                                            <div key={category.category} className="space-y-2">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm font-medium">{category.category}</span>
                                                    <span className="text-sm text-muted-foreground">
                                                        {category.avgScore}/5.0
                                                    </span>
                                                </div>
                                                <Progress value={(category.avgScore / 5) * 100} className="w-full" />
                                                <p className="text-xs text-muted-foreground">
                                                    {category.totalAssessments} assessments
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Assessment Completion</CardTitle>
                                    <CardDescription>
                                        Overall assessment completion statistics
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <PieChart 
                                        data={getAssessmentStatusChartData()} 
                                        height={200}
                                    />
                                    <div className="text-center">
                                        <div className="text-3xl font-bold text-primary">
                                            {studentAnalytics.assessmentStatus.completionRate}%
                                        </div>
                                        <p className="text-sm text-muted-foreground">Completion Rate</p>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4 text-center">
                                        <div>
                                            <div className="text-2xl font-bold">{studentAnalytics.assessmentStatus.completed}</div>
                                            <p className="text-xs text-muted-foreground">Completed</p>
                                        </div>
                                        <div>
                                            <div className="text-2xl font-bold">{studentAnalytics.assessmentStatus.pending}</div>
                                            <p className="text-xs text-muted-foreground">Pending</p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* Placements Tab */}
                    <TabsContent value="placements" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Company Placement Performance</CardTitle>
                                    <CardDescription>
                                        Top performing companies by placement success rate
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <BarChart 
                                        data={getCompanyPlacementsChartData()} 
                                        dataKey="value"
                                        color="#059669"
                                        height={300}
                                    />
                                    <div className="space-y-4 mt-4">
                                        {placementAnalytics.companyPlacements.slice(0, 8).map((company) => (
                                            <div key={company.company} className="space-y-2">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm font-medium truncate">{company.company}</span>
                                                    <span className="text-sm text-muted-foreground">
                                                        {company.successRate}%
                                                    </span>
                                                </div>
                                                <Progress value={company.successRate} className="w-full" />
                                                <p className="text-xs text-muted-foreground">
                                                    {company.filledSlots}/{company.totalSlots} slots filled
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Placements by Department</CardTitle>
                                    <CardDescription>
                                        Distribution of placements across departments
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <PieChart 
                                        data={getDepartmentPlacementsChartData()} 
                                        height={250}
                                    />
                                    <div className="space-y-4 mt-4">
                                        {placementAnalytics.departmentPlacements.map((dept) => (
                                            <div key={dept.department} className="flex items-center justify-between p-2 border rounded">
                                                <span className="text-sm font-medium">{dept.department}</span>
                                                <Badge variant="secondary">{dept.count}</Badge>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>Placement Statistics</CardTitle>
                                <CardDescription>
                                    Key placement metrics and compatibility scores
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div className="text-center">
                                        <div className="text-2xl font-bold text-primary">
                                            {placementAnalytics.avgCompatibilityScore}
                                        </div>
                                        <p className="text-sm text-muted-foreground">Avg Compatibility Score</p>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-2xl font-bold text-primary">
                                            {stats.placedStudents}
                                        </div>
                                        <p className="text-sm text-muted-foreground">Total Placements</p>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-2xl font-bold text-primary">
                                            {stats.placementRate}%
                                        </div>
                                        <p className="text-sm text-muted-foreground">Placement Rate</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* HTEs Tab */}
                    <TabsContent value="htes" className="space-y-4">
                        <Card>
                            <CardHeader>
                                <CardTitle>HTE Performance Overview</CardTitle>
                                <CardDescription>
                                    Host Training Establishment performance and utilization rates
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {hteAnalytics.htePerformance.slice(0, 10).map((hte) => (
                                        <div key={hte.id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="flex items-center space-x-3">
                                                    <BuildingIcon className="h-5 w-5 text-muted-foreground" />
                                                    <div>
                                                        <p className="font-medium">{hte.company_name}</p>
                                                        <p className="text-sm text-muted-foreground">
                                                            {hte.contact_person}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center space-x-4">
                                                <div className="text-right">
                                                    <p className="text-sm font-medium">{hte.utilizationRate}%</p>
                                                    <p className="text-xs text-muted-foreground">Utilization</p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-sm font-medium">{hte.filledSlots}/{hte.totalSlots}</p>
                                                    <p className="text-xs text-muted-foreground">Slots</p>
                                                </div>
                                                <Badge variant={hte.is_submit ? "default" : "secondary"}>
                                                    {hte.is_submit ? "Active" : "Inactive"}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Sections Tab */}
                    <TabsContent value="sections" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Section Performance Comparison</CardTitle>
                                    <CardDescription>
                                        Completion and placement rates by section
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <BarChart 
                                        data={getSectionPerformanceChartData()} 
                                        dataKey="completionRate"
                                        color="#2563eb"
                                        height={300}
                                        title="Completion Rates by Section"
                                    />
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Section Placement Rates</CardTitle>
                                    <CardDescription>
                                        Placement success rates by section
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <BarChart 
                                        data={getSectionPerformanceChartData()} 
                                        dataKey="placementRate"
                                        color="#059669"
                                        height={300}
                                        title="Placement Rates by Section"
                                    />
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle>Section Performance Analysis</CardTitle>
                                <CardDescription>
                                    Performance metrics by student section
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {sectionAnalytics.map((section) => (
                                        <div key={section.section} className="p-4 border rounded-lg space-y-3">
                                            <div className="flex items-center justify-between">
                                                <h3 className="font-medium">{section.section}</h3>
                                                <Badge variant="outline">{section.totalStudents} students</Badge>
                                            </div>
                                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                                <div className="text-center">
                                                    <div className="text-lg font-bold">{section.completionRate}%</div>
                                                    <p className="text-xs text-muted-foreground">Completion Rate</p>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-lg font-bold">{section.placementRate}%</div>
                                                    <p className="text-xs text-muted-foreground">Placement Rate</p>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-lg font-bold">{section.avgScore}</div>
                                                    <p className="text-xs text-muted-foreground">Avg Score</p>
                                                </div>
                                                <div className="text-center">
                                                    <div className="text-lg font-bold">{section.placedStudents}</div>
                                                    <p className="text-xs text-muted-foreground">Placed</p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Trends Tab */}
                    <TabsContent value="trends" className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Assessment Completion Trends</CardTitle>
                                    <CardDescription>
                                        Daily assessment completion over the last 30 days
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <AreaChart 
                                        data={getAssessmentTrendsChartData()} 
                                        dataKey="completed"
                                        xAxisKey="date"
                                        color="#2563eb"
                                        height={250}
                                    />
                                    <div className="space-y-2 mt-4">
                                        {assessmentTrends.slice(-7).map((trend) => (
                                            <div key={trend.date} className="flex items-center justify-between p-2 border rounded">
                                                <span className="text-sm">{new Date(trend.date).toLocaleDateString()}</span>
                                                <div className="flex items-center space-x-2">
                                                    <span className="text-sm font-medium">{trend.completed}</span>
                                                    <span className="text-xs text-muted-foreground">
                                                        ({trend.completionRate}%)
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Placement Trends</CardTitle>
                                    <CardDescription>
                                        Daily placement approvals over the last 30 days
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <LineChart 
                                        data={getPlacementTrendsChartData()} 
                                        dataKeys={[
                                            { key: 'approved', color: '#059669', name: 'Approved' },
                                            { key: 'pending', color: '#d97706', name: 'Pending' },
                                            { key: 'rejected', color: '#dc2626', name: 'Rejected' }
                                        ]}
                                        xAxisKey="date"
                                        height={250}
                                    />
                                    <div className="space-y-2 mt-4">
                                        {placementTrends.slice(-7).map((trend) => (
                                            <div key={trend.date} className="flex items-center justify-between p-2 border rounded">
                                                <span className="text-sm">{new Date(trend.date).toLocaleDateString()}</span>
                                                <div className="flex items-center space-x-2">
                                                    <span className="text-sm font-medium text-green-600">{trend.approved}</span>
                                                    <span className="text-xs text-muted-foreground">
                                                        ({trend.approvalRate}%)
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
