import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { 
    StudentPerformanceChart,
    StudentInternshipOpportunities,
    StudentQuickActions
} from '@/components/dashboard';
import { 
    User, 
    BookOpen, 
    TrendingUp,
    Briefcase,
    Target,
    AlertCircle,
} from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface CategoryScore {
    name: string;
    average_score: number;
    questions_count: number;
}

interface Performance {
    overall_average: number;
    total_questions: number;
    category_scores: CategoryScore[];
}

interface PossibleInternship {
    id: number;
    position_title: string;
    company_name: string;
    department: string;
    slot_count: number;
    is_active: boolean;
    compatibility_score: number;
}

interface CurrentMatch {
    id: number;
    internship: {
        position_title: string;
        company_name: string;
    };
    match_score: number;
    status: string;
}

interface Student {
    id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    middle_name: string | null;
    section: string | null;
    specialization: string | null;
    has_submitted_assessment: boolean;
}

interface DashboardProps {
    student: Student | null;
    performance: Performance | null;
    possibleInternships: PossibleInternship[];
    currentMatch: CurrentMatch | null;
    hasSubmitted: boolean;
}

export default function StudentDashboard({ 
    student, 
    performance, 
    possibleInternships, 
    currentMatch, 
    hasSubmitted 
}: DashboardProps) {
    if (!student) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Student Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                                <p className="text-muted-foreground">No student profile found.</p>
                                <p className="text-sm text-muted-foreground mt-2">
                                    Please contact your administrator to set up your student profile.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    if (!hasSubmitted) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Student Dashboard" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your skills assessment to access the dashboard.
                                </p>
                                <Button asChild>
                                    <Link href="/assessment">
                                        Take Assessment
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Welcome Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Welcome back, {student.first_name}!</h1>
                        <p className="text-muted-foreground">
                            Student Number: {student.student_number} • Section: {student.section || 'Not specified'}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button asChild variant="outline">
                            <Link href="/matched">
                                <Target className="h-4 w-4 mr-2" />
                                View Matches
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href="/student-profile">
                                <User className="h-4 w-4 mr-2" />
                                View Profile
                            </Link>
                        </Button>
                    </div>
                </div>

                {/* Performance Overview */}
                {performance && (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Overall Average</CardTitle>
                                <TrendingUp className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className={`text-2xl font-bold ${
                                    performance.overall_average >= 4 ? 'text-green-600' : 
                                    performance.overall_average >= 3 ? 'text-yellow-600' : 'text-red-600'
                                }`}>
                                    {performance.overall_average}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Based on {performance.total_questions} questions
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Categories</CardTitle>
                                <BookOpen className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{performance.category_scores.length}</div>
                                <p className="text-xs text-muted-foreground">
                                    Skill categories assessed
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Possible Internships</CardTitle>
                                <Briefcase className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{possibleInternships.length}</div>
                                <p className="text-xs text-muted-foreground">
                                    Top matches
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Match Status</CardTitle>
                                <Target className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {currentMatch ? 'Matched' : 'Pending'}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {currentMatch ? 'Internship assigned' : 'Waiting for placement'}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Performance Chart Component */}
                {performance && (
                    <StudentPerformanceChart 
                        overallAverage={performance.overall_average}
                        categoryScores={performance.category_scores}
                        totalQuestions={performance.total_questions}
                    />
                )}

                {/* Possible Internships Component */}
                <StudentInternshipOpportunities 
                    internships={possibleInternships}
                    currentMatch={currentMatch}
                />

                {/* Quick Actions Component */}
                <StudentQuickActions 
                    hasSubmitted={hasSubmitted}
                    performance={performance}
                    currentMatch={currentMatch}
                />
            </div>
        </AppLayout>
    );
}
