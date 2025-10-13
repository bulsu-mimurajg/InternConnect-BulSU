import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';
import SectionSwitcher from '@/components/SectionSwitcher';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import {
    UsersIcon,
    CheckCircleIcon,
    BriefcaseIcon,
    ClockIcon,
    BarChart3Icon,
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
    completionRate: number;
    placementRate: number;
}

interface RecentAssessment {
    id: number;
    username: string;
    name: string;
    section?: string;
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
        section?: string;
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

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    stats: DashboardStats;
    recentAssessments: RecentAssessment[];
    placementOverview: PlacementOverview;
    adviserSection: string | null;
    adviserSections: Section[];
    currentSectionId: number | null;
    hasArchivedSections?: boolean;
    archivedSectionNames?: string[];
}

export default function AdviserDashboard({ stats, recentAssessments, placementOverview, adviserSection, adviserSections, currentSectionId, hasArchivedSections = false, archivedSectionNames = [] }: Props) {
    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    {hasArchivedSections ? (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-amber-600">
                                    <ClockIcon className="h-5 w-5" />
                                    Sections Archived
                                </CardTitle>
                                <CardDescription>
                                    Your assigned sections have been archived and are no longer accessible.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <p className="text-sm text-muted-foreground">
                                        Archived sections: <span className="font-medium">{archivedSectionNames.join(', ')}</span>
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Please contact an administrator to restore access or get assigned to new sections.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
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
                    )}
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="relative">
                {/* Full Background Image - covers entire main content area */}
                <div
                    className="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-70 dark:opacity-50 bg-fixed"
                    style={{backgroundImage: 'url(/images/pimentel.jpg)'}}
                />
                {/* Background Overlay */}
                <div className="absolute inset-0 bg-background/80 dark:bg-background/90" />
                
                {/* Content Container with padding */}
                <div className="relative z-10 flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                {/* Section Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground">
                            Section: {adviserSection}
                        </p>
                    </div>
                    {adviserSections.length > 1 && (
                        <SectionSwitcher 
                            sections={adviserSections}
                            currentSectionId={currentSectionId}
                            showAllSections={true}
                        />
                    )}
                </div>


                {/* Statistics Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
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
                            <div className="text-2xl font-bold">{placementOverview.totalPlaced}</div>
                            <div className="flex items-center space-x-2">
                                <Progress 
                                    value={placementOverview.totalPlaced + placementOverview.totalUnplaced > 0 
                                        ? (placementOverview.totalPlaced / (placementOverview.totalPlaced + placementOverview.totalUnplaced)) * 100 
                                        : 0} 
                                    className="h-2 flex-1" 
                                />
                                <span className="text-xs text-muted-foreground">
                                    {placementOverview.totalPlaced + placementOverview.totalUnplaced > 0 
                                        ? Math.round((placementOverview.totalPlaced / (placementOverview.totalPlaced + placementOverview.totalUnplaced)) * 100)
                                        : 0}%
                                </span>
                            </div>
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
                                                        {assessment.percentage}% Score
                                                    </Badge>
                                                </div>
                                                <p className="text-xs text-muted-foreground mb-2">
                                                    {assessment.username} • {assessment.submittedAt}
                                                    {currentSectionId === null && assessment.section && (
                                                        <span className="text-blue-600"> • {assessment.section}</span>
                                                    )}
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
                            {placementOverview.studentsWithPlacements.length === 0 ? (
                                <div className="text-center py-8 text-muted-foreground">
                                    <BuildingIcon className="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
                                    <p className="text-sm">
                                        No placement data available yet.
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    {/* Placement Summary */}
                                    <div className="grid grid-cols-2 gap-4 mb-4">
                                        <div className="text-center p-3 bg-green-50 rounded-lg">
                                            <div className="text-2xl font-bold text-green-600">
                                                {placementOverview.totalPlaced}
                                            </div>
                                            <div className="text-sm text-green-700">Placed Students</div>
                                        </div>
                                        <div className="text-center p-3 bg-amber-50 rounded-lg">
                                            <div className="text-2xl font-bold text-amber-600">
                                                {placementOverview.totalUnplaced}
                                            </div>
                                            <div className="text-sm text-amber-700">Unplaced Students</div>
                                        </div>
                                    </div>

                                    {/* Students with Placements */}
                                    <div className="space-y-3">
                                        <h4 className="font-medium text-sm text-muted-foreground">Recent Placements</h4>
                                        {placementOverview.studentsWithPlacements
                                            .filter(student => student.isPlaced && student.topMatch)
                                            .slice(0, 5)
                                            .map((student) => (
                                                <div key={student.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                    <div className="flex-1 min-w-0">
                                                        <p className="text-sm font-medium truncate">
                                                            {student.name}
                                                        </p>
                                                        <p className="text-xs text-muted-foreground">
                                                            {student.topMatch?.position} at {student.topMatch?.company}
                                                            {currentSectionId === null && student.section && (
                                                                <span className="text-blue-600"> • {student.section}</span>
                                                            )}
                                                        </p>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="text-sm font-medium">
                                                            {student.topMatch?.compatibilityScore}%
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            Match Score
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                    </div>

                                    {/* Company Distribution */}
                                    {placementOverview.placementsByCompany.length > 0 && (
                                        <div className="space-y-3">
                                            <h4 className="font-medium text-sm text-muted-foreground">Placements by Company</h4>
                                            {placementOverview.placementsByCompany.slice(0, 3).map((company, index) => (
                                                <div key={index} className="flex items-center justify-between p-2 bg-muted/50 rounded">
                                                    <span className="text-sm font-medium">{company.company}</span>
                                                    <span className="text-sm text-muted-foreground">{company.count} students</span>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
        </AppLayout>
    );
}
