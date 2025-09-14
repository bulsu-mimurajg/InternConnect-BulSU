import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    UsersIcon,
    CheckCircleIcon,
    BriefcaseIcon,
    ClockIcon,
    TrendingUpIcon,
    BarChart3Icon,
    UserCheckIcon,
    BuildingIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardStats {
    totalStudents: number;
    completedAssessments: number;
    placedStudents: number;
    pendingStudents: number;
    averageScore: number;
    completionRate: number;
    placementRate: number;
}

interface RecentAssessment {
    id: number;
    username: string;
    name: string;
    totalScore: number;
    percentage: number;
    submittedAt: string;
    categories: Array<{
        name: string;
        score: number;
    }>;
}

interface PlacementOverview {
    studentsWithPlacements: Array<{
        id: number;
        username: string;
        name: string;
        isPlaced: boolean;
        topMatch?: {
            position: string;
            company: string;
            compatibilityScore: number;
            rank: number;
        };
    }>;
    placementsByCompany: Array<{
        company: string;
        count: number;
        students: string[];
    }>;
    totalPlaced: number;
    totalUnplaced: number;
}

interface Props {
    stats: DashboardStats;
    recentAssessments: RecentAssessment[];
    placementOverview: PlacementOverview;
    adviserSection: string | null;
}

export default function AdviserDashboard({ stats, recentAssessments, adviserSection }: Props) {
    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BarChart3Icon className="h-5 w-5" />
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
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                {/* Section Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground">
                            Section: {adviserSection}
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={route('student-verification')}>
                            Verify Students
                        </Link>
                    </Button>
                </div>

                {/* Notification Banner */}
                {stats.pendingStudents > 0 && (
                    <Card className="border-amber-200 bg-amber-50">
                        <CardContent className="pt-6">
                            <div className="flex items-center gap-3">
                                <ClockIcon className="h-5 w-5 text-amber-600" />
                                <div className="flex-1">
                                    <p className="font-medium text-amber-800">
                                        You have {stats.pendingStudents} student{stats.pendingStudents > 1 ? 's' : ''} waiting for verification
                                    </p>
                                    <p className="text-sm text-amber-700">
                                        Review and approve student requests to grant them access to the system
                                    </p>
                                </div>
                                <Button asChild size="sm" className="bg-amber-600 hover:bg-amber-700">
                                    <Link href={route('student-verification')}>
                                        Review Applications
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Statistics Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.totalStudents}</div>
                            <p className="text-xs text-muted-foreground">
                                In your section
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending Students</CardTitle>
                            <ClockIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendingStudents}</div>
                            <p className="text-xs text-muted-foreground">
                                Need verification
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed Assessments</CardTitle>
                            <CheckCircleIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completedAssessments}</div>
                            <div className="flex items-center space-x-2">
                                <Progress value={stats.completionRate} className="h-2 flex-1" />
                                <span className="text-xs text-muted-foreground">
                                    {stats.completionRate}%
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Placed Students</CardTitle>
                            <BriefcaseIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.placedStudents}</div>
                            <div className="flex items-center space-x-2">
                                <Progress value={stats.placementRate} className="h-2 flex-1" />
                                <span className="text-xs text-muted-foreground">
                                    {stats.placementRate}%
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Average Score</CardTitle>
                            <TrendingUpIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.averageScore}</div>
                            <p className="text-xs text-muted-foreground">
                                Section average
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Grid */}
                <div className="grid gap-6 md:grid-cols-2">
                    {/* Recent Assessments */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BarChart3Icon className="h-5 w-5" />
                                Recent Assessments
                            </CardTitle>
                            <CardDescription>
                                Latest assessment submissions from your students
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentAssessments.length === 0 ? (
                                <div className="text-center py-8 text-muted-foreground">
                                    No assessments completed yet
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {recentAssessments.map((assessment) => (
                                        <div key={assessment.id} className="flex items-center justify-between p-3 border rounded-lg">
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between mb-1">
                                                    <p className="text-sm font-medium truncate">
                                                        {assessment.name}
                                                    </p>
                                                    <Badge variant="secondary" className="text-xs">
                                                        {assessment.percentage}%
                                                    </Badge>
                                                </div>
                                                <p className="text-xs text-muted-foreground mb-2">
                                                    {assessment.username} • {assessment.submittedAt}
                                                </p>
                                                <div className="flex flex-wrap gap-1">
                                                    {assessment.categories.slice(0, 3).map((category, index) => (
                                                        <Badge key={index} variant="outline" className="text-xs">
                                                            {category.name}: {category.score}
                                                        </Badge>
                                                    ))}
                                                    {assessment.categories.length > 3 && (
                                                        <Badge variant="outline" className="text-xs">
                                                            +{assessment.categories.length - 3} more
                                                        </Badge>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Placement Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BuildingIcon className="h-5 w-5" />
                                Placement Overview
                            </CardTitle>
                            <CardDescription>
                                Student placement status and company distribution
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-8 text-muted-foreground">
                                <BuildingIcon className="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
                                <p className="text-sm">
                                    Placement tracking will be available once student matching is implemented.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Quick Actions */}
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>
                            Common tasks and shortcuts
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <Button asChild variant="outline" className="h-auto p-4 flex-col gap-2">
                                <Link href={route('adviser.student-list')}>
                                    <UserCheckIcon className="h-6 w-6" />
                                    <span>View Students</span>
                                    <span className="text-xs text-muted-foreground">
                                        View all students and their details
                                    </span>
                                </Link>
                            </Button>

                            <Button asChild variant="outline" className="h-auto p-4 flex-col gap-2">
                                <Link href={route('student-verification')}>
                                    <BarChart3Icon className="h-6 w-6" />
                                    <span>Verify Students</span>
                                    <span className="text-xs text-muted-foreground">
                                        Approve or reject student requests
                                    </span>
                                </Link>
                            </Button>

                            <Button asChild variant="outline" className="h-auto p-4 flex-col gap-2">
                                <Link href={route('adviser.student-list')}>
                                    <BriefcaseIcon className="h-6 w-6" />
                                    <span>Track Placements</span>
                                    <span className="text-xs text-muted-foreground">
                                        Monitor student placement status
                                    </span>
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
