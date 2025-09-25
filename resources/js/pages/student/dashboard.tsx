import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { 
    StudentPerformanceChart,
    StudentInternshipOpportunities
} from '@/components/dashboard';
import { 
    Target,
    AlertCircle,
    Building2,
    Briefcase,
    TrendingUp,
    CheckCircle,
    Clock,
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
        id: number;
        position_title: string;
        company_name: string;
        department: string;
        description?: string;
        requirements?: string;
        start_date?: string;
        end_date?: string;
        location?: string;
    };
    match_score: number;
    status: string;
    created_at: string;
    updated_at: string;
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
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 md:p-6">
                {/* Hello Header */}
                <div className="bg-card border rounded-lg p-6">
                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div className="space-y-2">
                            <h1 className="text-2xl font-bold">Hello, {student.first_name}!</h1>
                            <div className="flex flex-col sm:flex-row sm:items-center gap-2 text-sm text-muted-foreground">
                                <span className="flex items-center gap-1">
                                    <span className="font-medium">Student Number:</span>
                                    <span className="font-mono bg-muted px-2 py-1 rounded text-xs">{student.student_number}</span>
                                </span>
                                <span className="hidden sm:inline">•</span>
                                <span className="flex items-center gap-1">
                                    <span className="font-medium">Section:</span>
                                    <span className="font-medium">{student.section || 'Not specified'}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Match Status */}
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Match Status</CardTitle>
                        {currentMatch ? (
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        ) : (
                            <Clock className="h-4 w-4 text-yellow-500" />
                        )}
                    </CardHeader>
                    <CardContent>
                        {currentMatch ? (
                            <div className="space-y-4">
                                {/* Status Header */}
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <div className="h-2 w-2 rounded-full bg-green-500"></div>
                                        <span className="text-lg font-semibold text-green-700">Matched</span>
                                    </div>
                                    <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                        <TrendingUp className="h-3 w-3" />
                                        <span>{currentMatch.match_score}% Match</span>
                                    </div>
                                </div>

                                {/* Internship Details */}
                                <div className="space-y-3">
                                    <div className="flex items-start gap-3">
                                        <Building2 className="h-5 w-5 text-muted-foreground mt-0.5 flex-shrink-0" />
                                        <div className="min-w-0 flex-1">
                                            <p className="font-medium truncate">{currentMatch.internship.company_name}</p>
                                            <p className="text-sm text-muted-foreground">{currentMatch.internship.department}</p>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-start gap-3">
                                        <Briefcase className="h-5 w-5 text-muted-foreground mt-0.5 flex-shrink-0" />
                                        <div className="min-w-0 flex-1">
                                            <p className="font-medium truncate">{currentMatch.internship.position_title}</p>
                                            {currentMatch.internship.location && (
                                                <p className="text-sm text-muted-foreground truncate">{currentMatch.internship.location}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Dates */}
                                {(currentMatch.internship.start_date || currentMatch.internship.end_date) && (
                                    <div className="flex items-start gap-3">
                                        <Clock className="h-5 w-5 text-muted-foreground mt-0.5 flex-shrink-0" />
                                        <div className="text-sm min-w-0 flex-1">
                                            {currentMatch.internship.start_date && currentMatch.internship.end_date ? (
                                                <span className="block">
                                                    <span className="font-medium">Duration:</span> {new Date(currentMatch.internship.start_date).toLocaleDateString()} - {new Date(currentMatch.internship.end_date).toLocaleDateString()}
                                                </span>
                                            ) : currentMatch.internship.start_date ? (
                                                <span className="block">
                                                    <span className="font-medium">Starts:</span> {new Date(currentMatch.internship.start_date).toLocaleDateString()}
                                                </span>
                                            ) : currentMatch.internship.end_date ? (
                                                <span className="block">
                                                    <span className="font-medium">Ends:</span> {new Date(currentMatch.internship.end_date).toLocaleDateString()}
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>
                                )}

                                {/* Status Badge */}
                                <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <span className="text-sm font-medium">Status:</span>
                                    <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium w-fit ${
                                        currentMatch.status === 'approved' 
                                            ? 'bg-green-100 text-green-800' 
                                            : currentMatch.status === 'pending'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-gray-100 text-gray-800'
                                    }`}>
                                        {currentMatch.status.charAt(0).toUpperCase() + currentMatch.status.slice(1)}
                                    </span>
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <div className="h-2 w-2 rounded-full bg-yellow-500"></div>
                                    <span className="text-lg font-semibold text-yellow-700">Pending</span>
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Waiting for internship placement. Your profile is being matched with available opportunities.
                                </p>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Performance Chart Component */}
                {performance && (
                    <StudentPerformanceChart 
                        overallAverage={performance.overall_average}
                        categoryScores={performance.category_scores}
                        totalQuestions={performance.total_questions}
                    />
                )}

                {/* Possible Internships Component - Only show if not already matched */}
                {!currentMatch && (
                    <StudentInternshipOpportunities 
                        internships={possibleInternships}
                        currentMatch={currentMatch}
                    />
                )}
            </div>
        </AppLayout>
    );
}
