import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { LineChart, BarChart, PieChart, AreaChart } from '@/components/charts';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { 
    UsersIcon, 
    CheckCircleIcon, 
    BriefcaseIcon, 
    BuildingIcon, 
    BarChart3Icon,
    UserCheckIcon,
    TargetIcon,
    ActivityIcon,
    TrendingUpIcon,
    TrendingDownIcon,
    PieChartIcon,
    LineChartIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin-dashboard',
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
}

interface RecentActivity {
    id: number;
    name?: string;
    student_name?: string;
    company_name?: string;
    position?: string;
    contact_person?: string;
    student_number?: string;
    section?: string;
    created_at: string;
    type: 'student_registration' | 'hte_registration' | 'placement';
}

interface PlacementOverview {
    byStatus: Record<string, number>;
    byCompany: Array<{
        company: string;
        count: number;
    }>;
    bySection: Array<{
        section: string;
        count: number;
    }>;
}

interface SectionStats {
    section: string;
    totalStudents: number;
    completedAssessments: number;
    placedStudents: number;
    completionRate: number;
    placementRate: number;
}

interface HTEStats {
    id: number;
    company_name: string;
    contact_person: string;
    email: string;
    is_submit: boolean;
    totalInternships: number;
    activeInternships: number;
    totalSlots: number;
    created_at: string;
}

interface AdminDashboardProps {
    stats: DashboardStats;
    recentActivity: RecentActivity[];
    placementOverview: PlacementOverview;
    sectionStats: SectionStats[];
    hteStats: HTEStats[];
    placementAnalytics: any;
    studentAnalytics: any;
    hteAnalytics: any;
    sectionAnalytics: any;
    assessmentTrends: any[];
    placementTrends: any[];
}

export default function AdminDashboard({ 
    stats, 
    recentActivity, 
    placementOverview, 
    sectionStats, 
    hteStats,
    placementAnalytics,
    studentAnalytics,
    hteAnalytics,
    sectionAnalytics,
    assessmentTrends,
    placementTrends
}: AdminDashboardProps) {
    const getActivityIcon = (type: string) => {
        switch (type) {
            case 'student_registration':
                return <UsersIcon className="h-4 w-4 text-blue-500" />;
            case 'hte_registration':
                return <BuildingIcon className="h-4 w-4 text-green-500" />;
            case 'placement':
                return <CheckCircleIcon className="h-4 w-4 text-purple-500" />;
            default:
                return <ActivityIcon className="h-4 w-4 text-gray-500" />;
        }
    };

    const getActivityDescription = (activity: RecentActivity) => {
        switch (activity.type) {
            case 'student_registration':
                return `${activity.name} (${activity.student_number}) from ${activity.section} registered`;
            case 'hte_registration':
                return `${activity.name} registered with contact person ${activity.contact_person}`;
            case 'placement':
                return `${activity.student_name} placed at ${activity.company_name} as ${activity.position}`;
            default:
                return 'Unknown activity';
        }
    };

    // Prepare data for charts
    const placementStatusData = Object.entries(placementOverview.byStatus).map(([status, count]) => ({
        name: status.charAt(0).toUpperCase() + status.slice(1),
        value: count,
        count: count
    }));

    const sectionPerformanceData = sectionAnalytics.map((section: any) => ({
        section: section.section,
        completionRate: section.completionRate,
        placementRate: section.placementRate,
        totalStudents: section.totalStudents
    }));

    const topCompaniesData = placementAnalytics.companyPlacements.slice(0, 8).map((company: any) => ({
        company: company.company.length > 15 ? company.company.substring(0, 15) + '...' : company.company,
        successRate: company.successRate,
        filledSlots: company.filledSlots,
        totalSlots: company.totalSlots
    }));

    const assessmentTrendData = assessmentTrends.map(trend => ({
        date: new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        completionRate: trend.completionRate,
        completed: trend.completed,
        total: trend.total
    }));

    const placementTrendData = placementTrends.map(trend => ({
        date: new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        approved: trend.approved,
        pending: trend.pending,
        rejected: trend.rejected,
        approvalRate: trend.approvalRate
    }));

    const categoryScoresData = studentAnalytics.categoryScores.map((category: any) => ({
        category: category.category,
        avgScore: category.avgScore,
        totalAssessments: category.totalAssessments
    }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Analytics Dashboard</h1>
                        <p className="text-muted-foreground">
                            Comprehensive insights and trends for the SIP system
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <TrendingUpIcon className="h-5 w-5 text-green-500" />
                        <span className="text-sm text-muted-foreground">Live Data</span>
                    </div>
                </div>

                {/* Key Metrics Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <div className="flex items-center space-x-1 text-xs text-muted-foreground">
                                <TrendingUpIcon className="h-3 w-3 text-green-500" />
                                <span>{stats.completionRate}% completion rate</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Placed Students</CardTitle>
                            <UserCheckIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.placedStudents}</div>
                            <div className="flex items-center space-x-1 text-xs text-muted-foreground">
                                <TrendingUpIcon className="h-3 w-3 text-green-500" />
                                <span>{stats.placementRate}% placement rate</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Active HTEs</CardTitle>
                            <BuildingIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.activeHTEs}</div>
                            <div className="flex items-center space-x-1 text-xs text-muted-foreground">
                                <span>{stats.totalHTEs} total registered</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Available Slots</CardTitle>
                            <BriefcaseIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalSlots}</div>
                            <div className="flex items-center space-x-1 text-xs text-muted-foreground">
                                <span>{stats.totalInternships} internships</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Analytics Grid */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Assessment Completion Trends */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <LineChartIcon className="h-5 w-5" />
                                <span>Assessment Completion Trends</span>
                            </CardTitle>
                            <CardDescription>
                                Daily assessment completion rates over the last 30 days
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <AreaChart 
                                data={assessmentTrendData}
                                dataKey="completionRate"
                                xAxisKey="date"
                                color="#3b82f6"
                                height={250}
                            />
                        </CardContent>
                    </Card>

                    {/* Placement Status Distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <PieChartIcon className="h-5 w-5" />
                                <span>Placement Status Distribution</span>
                            </CardTitle>
                            <CardDescription>
                                Current distribution of student placements by status
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <PieChart 
                                data={placementStatusData}
                                dataKey="value"
                                nameKey="name"
                                height={250}
                                colors={['#10b981', '#f59e0b', '#ef4444']}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* Section Performance & Company Performance */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Section Performance Comparison */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <BarChart3Icon className="h-5 w-5" />
                                <span>Section Performance</span>
                            </CardTitle>
                            <CardDescription>
                                Completion and placement rates by section
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <BarChart 
                                data={sectionPerformanceData}
                                dataKey="completionRate"
                                xAxisKey="section"
                                color="#8b5cf6"
                                height={300}
                            />
                        </CardContent>
                    </Card>

                    {/* Top Performing Companies */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <TrendingUpIcon className="h-5 w-5" />
                                <span>Top Performing Companies</span>
                            </CardTitle>
                            <CardDescription>
                                Companies with highest placement success rates
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <BarChart 
                                data={topCompaniesData}
                                dataKey="successRate"
                                xAxisKey="company"
                                color="#10b981"
                                height={300}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* Placement Trends & Category Performance */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Placement Trends */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <LineChartIcon className="h-5 w-5" />
                                <span>Placement Trends</span>
                            </CardTitle>
                            <CardDescription>
                                Daily placement approvals over the last 30 days
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <LineChart 
                                data={placementTrendData}
                                dataKey="approved"
                                xAxisKey="date"
                                color="#10b981"
                                height={250}
                            />
                        </CardContent>
                    </Card>

                    {/* Assessment Category Performance */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <BarChart3Icon className="h-5 w-5" />
                                <span>Category Performance</span>
                            </CardTitle>
                            <CardDescription>
                                Average scores by assessment category
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <BarChart 
                                data={categoryScoresData}
                                dataKey="avgScore"
                                xAxisKey="category"
                                color="#f59e0b"
                                height={250}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity & Quick Actions */}
                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Recent Activity */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <ActivityIcon className="h-5 w-5" />
                                <span>Recent Activity</span>
                            </CardTitle>
                            <CardDescription>
                                Latest system activities and updates
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentActivity.length > 0 ? (
                                    recentActivity.slice(0, 6).map((activity) => (
                                        <div key={`${activity.type}-${activity.id}`} className="flex items-center space-x-4">
                                            {getActivityIcon(activity.type)}
                                            <div className="flex-1 space-y-1">
                                                <p className="text-sm font-medium leading-none">
                                                    {getActivityDescription(activity)}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {activity.created_at}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">No recent activity</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Quick Actions */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Common administrative tasks
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                <Link
                                    href="/student/list"
                                    className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <UsersIcon className="h-5 w-5" />
                                    <div>
                                        <p className="text-sm font-medium">Manage Students</p>
                                        <p className="text-xs text-muted-foreground">View and edit records</p>
                                    </div>
                                </Link>

                                <Link
                                    href="/student/matched"
                                    className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <TargetIcon className="h-5 w-5" />
                                    <div>
                                        <p className="text-sm font-medium">View Matches</p>
                                        <p className="text-xs text-muted-foreground">Review matches</p>
                                    </div>
                                </Link>

                                <Link
                                    href="/hte"
                                    className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <BuildingIcon className="h-5 w-5" />
                                    <div>
                                        <p className="text-sm font-medium">Manage HTEs</p>
                                        <p className="text-xs text-muted-foreground">HTE information</p>
                                    </div>
                                </Link>

                                <Link
                                    href="/report"
                                    className="flex items-center space-x-3 p-3 border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <BarChart3Icon className="h-5 w-5" />
                                    <div>
                                        <p className="text-sm font-medium">Generate Reports</p>
                                        <p className="text-xs text-muted-foreground">Create reports</p>
                                    </div>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
