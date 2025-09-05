import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { 
    UsersIcon, 
    CheckCircleIcon, 
    BriefcaseIcon, 
    BuildingIcon, 
    TrendingUpIcon,
    BarChart3Icon,
    UserCheckIcon,
    ClockIcon,
    FileTextIcon,
    TargetIcon,
    ActivityIcon
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
    hteParticipationRate: number;
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
}

export default function AdminDashboard({ 
    stats, 
    recentActivity, 
    placementOverview, 
    sectionStats, 
    hteStats 
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Admin Dashboard</h1>
                        <p className="text-muted-foreground">
                            Overview of the SIP (Student Internship Program) system
                        </p>
                    </div>
                </div>

                {/* Main Statistics */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.completedAssessments} completed assessments
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Placed Students</CardTitle>
                            <UserCheckIcon className="h-4 w-4 text-muted-foreground" />
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
                            <CardTitle className="text-sm font-medium">Total Slots</CardTitle>
                            <BriefcaseIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalSlots}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.totalInternships} internships available
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Progress Cards */}
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium">Assessment Completion</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span>Completion Rate</span>
                                    <span>{stats.completionRate}%</span>
                                </div>
                                <Progress value={stats.completionRate} className="h-2" />
                                <p className="text-xs text-muted-foreground">
                                    {stats.completedAssessments} of {stats.totalStudents} students
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium">Placement Progress</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span>Placement Rate</span>
                                    <span>{stats.placementRate}%</span>
                                </div>
                                <Progress value={stats.placementRate} className="h-2" />
                                <p className="text-xs text-muted-foreground">
                                    {stats.placedStudents} of {stats.completedAssessments} assessed students
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-sm font-medium">HTE Participation</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <div className="flex justify-between text-sm">
                                    <span>Participation Rate</span>
                                    <span>{stats.hteParticipationRate}%</span>
                                </div>
                                <Progress value={stats.hteParticipationRate} className="h-2" />
                                <p className="text-xs text-muted-foreground">
                                    {stats.activeHTEs} of {stats.totalHTEs} registered HTEs
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Grid */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Recent Activity */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>
                                Latest registrations and placements
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentActivity.length > 0 ? (
                                    recentActivity.map((activity) => (
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

                    {/* Section Statistics */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Section Performance</CardTitle>
                            <CardDescription>
                                Assessment and placement rates by section
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {sectionStats.length > 0 ? (
                                    sectionStats.map((section) => (
                                        <div key={section.section} className="space-y-2">
                                            <div className="flex justify-between items-center">
                                                <span className="text-sm font-medium">{section.section}</span>
                                                <Badge variant="outline">
                                                    {section.totalStudents} students
                                                </Badge>
                                            </div>
                                            <div className="space-y-1">
                                                <div className="flex justify-between text-xs">
                                                    <span>Assessment: {section.completionRate}%</span>
                                                    <span>Placement: {section.placementRate}%</span>
                                                </div>
                                                <div className="flex space-x-1">
                                                    <Progress value={section.completionRate} className="h-1 flex-1" />
                                                    <Progress value={section.placementRate} className="h-1 flex-1" />
                                                </div>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">No section data available</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Bottom Grid */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Top HTEs */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Top HTEs by Slots</CardTitle>
                            <CardDescription>
                                Host Training Establishments with most available slots
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {hteStats.length > 0 ? (
                                    hteStats.map((hte) => (
                                        <div key={hte.id} className="flex items-center justify-between">
                                            <div className="space-y-1">
                                                <p className="text-sm font-medium">{hte.company_name}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {hte.contact_person} • {hte.activeInternships} active internships
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-medium">{hte.totalSlots} slots</p>
                                                <Badge variant={hte.is_submit ? "default" : "secondary"}>
                                                    {hte.is_submit ? "Active" : "Pending"}
                                                </Badge>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground">No HTE data available</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Placement Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Placement Overview</CardTitle>
                            <CardDescription>
                                Distribution of placements by status
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {Object.entries(placementOverview.byStatus).map(([status, count]) => (
                                    <div key={status} className="flex items-center justify-between">
                                        <div className="flex items-center space-x-2">
                                            <div className={`h-2 w-2 rounded-full ${
                                                status === 'approved' ? 'bg-green-500' :
                                                status === 'pending' ? 'bg-yellow-500' :
                                                status === 'rejected' ? 'bg-red-500' : 'bg-gray-500'
                                            }`} />
                                            <span className="text-sm font-medium capitalize">{status}</span>
                                        </div>
                                        <span className="text-sm text-muted-foreground">{count}</span>
                                    </div>
                                ))}
                                
                                {placementOverview.byCompany.length > 0 && (
                                    <div className="pt-4 border-t">
                                        <p className="text-sm font-medium mb-2">Top Companies</p>
                                        <div className="space-y-2">
                                            {placementOverview.byCompany.slice(0, 3).map((company, index) => (
                                                <div key={company.company} className="flex justify-between text-xs">
                                                    <span>{company.company}</span>
                                                    <span>{company.count} placements</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common administrative tasks
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <Link
                                href="/student/list"
                                className="flex items-center space-x-2 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <UsersIcon className="h-5 w-5" />
                                <div>
                                    <p className="text-sm font-medium">Manage Students</p>
                                    <p className="text-xs text-muted-foreground">View and edit student records</p>
                                </div>
                            </Link>

                            <Link
                                href="/student/matched"
                                className="flex items-center space-x-2 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <TargetIcon className="h-5 w-5" />
                                <div>
                                    <p className="text-sm font-medium">View Matches</p>
                                    <p className="text-xs text-muted-foreground">Review student-internship matches</p>
                                </div>
                            </Link>

                            <Link
                                href="/hte"
                                className="flex items-center space-x-2 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <BuildingIcon className="h-5 w-5" />
                                <div>
                                    <p className="text-sm font-medium">Manage HTEs</p>
                                    <p className="text-xs text-muted-foreground">View HTE information</p>
                                </div>
                            </Link>

                            <Link
                                href="/report"
                                className="flex items-center space-x-2 p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                            >
                                <BarChart3Icon className="h-5 w-5" />
                                <div>
                                    <p className="text-sm font-medium">Generate Reports</p>
                                    <p className="text-xs text-muted-foreground">Create system reports</p>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
