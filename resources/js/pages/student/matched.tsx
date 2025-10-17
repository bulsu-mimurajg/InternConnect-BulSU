import React from 'react';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { 
    User, 
    Briefcase,
    Target,
    AlertCircle,
    Building,
    TrendingUp,
    Award,
    CheckCircle
} from 'lucide-react';
import { Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'My Matches',
        href: '/matched',
    },
];

interface CompatibilityScore {
    id: number;
    internship: {
        id: number;
        position_title: string;
        company_name: string;
        department: string;
        slot_count: number;
        is_active: boolean;
    };
    compatibility_score: number;
    rank: number;
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
    is_submit: boolean;
}

interface StudentMatchedProps {
    student: Student | null;
    compatibilityScores: CompatibilityScore[];
    currentMatch: CurrentMatch | null;
    hasSubmitted: boolean;
}

export default function StudentMatched({ 
    student, 
    compatibilityScores, 
    currentMatch, 
    hasSubmitted 
}: StudentMatchedProps) {
    if (!student) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="My Matches" />
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
                <Head title="My Matches" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your skills assessment to view your matches.
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

    const topMatch = compatibilityScores.length > 0 ? compatibilityScores[0] : null;
    const averageScore = compatibilityScores.length > 0 
        ? Math.round(compatibilityScores.reduce((sum, score) => sum + score.compatibility_score, 0) / compatibilityScores.length)
        : 0;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Matches" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">My Internship Matches</h1>
                        <p className="text-muted-foreground">
                            View your compatibility scores and potential internship opportunities
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href="/dashboard">
                            <TrendingUp className="h-4 w-4 mr-2" />
                            Back to Dashboard
                        </Link>
                    </Button>
                </div>

                {/* Current Match Status */}
                {currentMatch && (
                    <Card className="border-green-200 bg-green-50">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-green-800">
                                <CheckCircle className="h-5 w-5" />
                                Current Placement
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <h3 className="font-semibold text-green-800">{currentMatch.internship.position_title}</h3>
                                    <p className="text-sm text-green-700">{currentMatch.internship.company_name}</p>
                                </div>
                                <div className="text-right">
                                    <Badge variant="secondary" className="bg-green-100 text-green-800">
                                        {currentMatch.status}
                                    </Badge>
                                    <p className="text-sm text-green-700 mt-1">
                                        Match Score: {currentMatch.match_score}%
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Summary Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Matches</CardTitle>
                            <Target className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{compatibilityScores.length}</div>
                            <p className="text-xs text-muted-foreground">
                                Available internships
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Top Match Score</CardTitle>
                            <Award className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {topMatch ? `${topMatch.compatibility_score}%` : 'N/A'}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Best compatibility
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Average Score</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{averageScore}%</div>
                            <p className="text-xs text-muted-foreground">
                                Across all matches
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Status</CardTitle>
                            <CheckCircle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {currentMatch ? 'Placed' : 'Available'}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {currentMatch ? 'Internship assigned' : 'Open for placement'}
                            </p>
                        </CardContent>
                    </Card>


                </div>

                {/* Compatibility Scores Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Compatibility Scores</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Your compatibility scores for available internship positions, ranked from highest to lowest.
                        </p>
                    </CardHeader>
                    <CardContent>
                        {compatibilityScores.length === 0 ? (
                            <div className="text-center py-8">
                                <Briefcase className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                                <p className="text-muted-foreground">No internship matches found.</p>
                                <p className="text-sm text-muted-foreground mt-2">
                                    This might be due to no active internships or your skills not matching current requirements.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {compatibilityScores.map((score, index) => (
                                    <div key={score.id} className="border rounded-lg p-4">
                                        <div className="flex items-center justify-between mb-3">
                                            <div className="flex items-center gap-3">
                                                <Badge variant={index === 0 ? "default" : "secondary"}>
                                                    #{score.rank}
                                                </Badge>
                                                <div>
                                                    <h3 className="font-semibold">{score.internship.position_title}</h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        {score.internship.company_name} • {score.internship.department}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-2xl font-bold text-green-600">
                                                    {score.compatibility_score}%
                                                </div>

                                            </div>
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between text-sm">
                                                <span>Compatibility</span>
                                                <span>{score.compatibility_score}%</span>
                                            </div>
                                            <Progress value={score.compatibility_score} className="h-2" />
                                        </div>
                                        
                                        <div className="flex items-center justify-between mt-3 pt-3 border-t">
                                            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                                <Building className="h-4 w-4" />
                                                <span>{score.internship.company_name}</span>
                                            </div>
                                            <Badge 
                                                variant={score.internship.is_active ? "default" : "secondary"}
                                                className={score.internship.is_active ? "bg-green-100 text-green-800" : ""}
                                            >
                                                {score.internship.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Student Info */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <User className="h-5 w-5" />
                            Student Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Student Number</p>
                                <p className="font-medium">{student.student_number}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Name</p>
                                <p className="font-medium">
                                    {student.first_name} {student.middle_name} {student.last_name}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Section</p>
                                <p className="font-medium">{student.section || 'Not specified'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">Specialization</p>
                                <p className="font-medium">{student.specialization || 'Not specified'}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
