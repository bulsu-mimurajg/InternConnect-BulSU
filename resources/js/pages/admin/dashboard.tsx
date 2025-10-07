import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart, PieChart, AreaChart } from '@/components/charts';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {
    BriefcaseIcon,
    BarChart3Icon,
    UserCheckIcon,
    TrendingUpIcon,
    PieChartIcon,
    LineChartIcon,
    GraduationCapIcon,
    BriefcaseBusinessIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
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

interface SectionAnalytics {
    section: string;
    totalStudents: number;
    completedAssessments: number;
    placedStudents: number;
    completionRate: number;
    placementRate: number;
}

interface AssessmentTrend {
    date: string;
    completionRate: number;
    completed: number;
    total: number;
}


interface SectionPerformanceData {
    section: string;
    completionRate: number;
    placementRate: number;
    totalStudents: number;
}

interface AssessmentTrendData {
    date: string;
    completionRate: number;
    completed: number;
    total: number;
}

interface AdminDashboardProps {
    stats: DashboardStats;
    placementOverview: PlacementOverview;
    sectionStats: SectionStats[];
    hteStats: HTEStats[];
    sectionAnalytics: SectionAnalytics[];
    assessmentTrends: AssessmentTrend[];
}

export default function AdminDashboard({
    stats,
    placementOverview,
    hteStats,
    sectionAnalytics,
    assessmentTrends
}: AdminDashboardProps) {
    // Prepare data for charts
    const placementStatusData = Object.entries(placementOverview.byStatus).map(([status, count], index) => {
        const colors = ['#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'];
        return {
            name: status.charAt(0).toUpperCase() + status.slice(1),
            value: count,
            color: colors[index % colors.length]
        };
    });

    const totalPlacements = placementStatusData.reduce((sum, item) => sum + item.value, 0);

    const sectionPerformanceData: SectionPerformanceData[] = sectionAnalytics.map((section: SectionAnalytics) => ({
        section: section.section,
        completionRate: section.completionRate,
        placementRate: section.placementRate,
        totalStudents: section.totalStudents
    }));

    const assessmentTrendData: AssessmentTrendData[] = assessmentTrends.map((trend: AssessmentTrend) => ({
        date: new Date(trend.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
        completionRate: trend.completionRate,
        completed: trend.completed,
        total: trend.total
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
                        <span className="text-sm text-muted-foreground">Current Data</span>
                    </div>
                </div>

                {/* Key Metrics Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Students</CardTitle>
                            <GraduationCapIcon className="h-4 w-4 text-muted-foreground" />
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
                            <BriefcaseBusinessIcon className="h-4 w-4 text-muted-foreground" />
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
                        <CardContent className="h-full">
                            <div className="w-full h-full">
                                <AreaChart
                                    data={assessmentTrendData}
                                    dataKey="completionRate"
                                    xAxisKey="date"
                                    color="#3b82f6"
                                    height={350}
                                />
                            </div>
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
                                totalWeight={totalPlacements}
                                showLabels={true}
                                showTooltip={true}
                                showLegend={true}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* Section Performance & HTE Overview */}
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
                            <div className="w-full">
                                <BarChart
                                    data={sectionPerformanceData}
                                    dataKey="completionRate"
                                    xAxisKey="section"
                                    color="#3b82f6"
                                    height={300}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* HTE Overview */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <BriefcaseBusinessIcon className="h-5 w-5" />
                                <span>HTE Overview</span>
                            </CardTitle>
                            <CardDescription>
                                Host Training Establishments and their offerings
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {hteStats.filter(h => h.activeInternships > 0).length > 0 ? (
                                    hteStats
                                        .filter(h => h.activeInternships > 0)
                                        .slice(0, 5)
                                        .map((hte) => (
                                        <div key={hte.id} className="flex items-center justify-between p-3 border rounded-lg">
                                            <div className="flex-1">
                                                <p className="font-medium text-sm">{hte.company_name}</p>
                                                <p className="text-xs text-muted-foreground">{hte.contact_person}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-sm font-medium">{hte.activeInternships}</p>
                                                <p className="text-xs text-muted-foreground">active internships</p>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <p className="text-sm text-muted-foreground text-center py-4">
                                        No HTE data available
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
