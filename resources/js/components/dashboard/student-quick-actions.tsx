import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    BookOpen,
    User,
    Target,
    TrendingUp,
    Settings,
    HelpCircle,
    Bell,
    Star
} from 'lucide-react';
import { Link } from '@inertiajs/react';

interface CurrentMatch {
    internship: {
        position_title: string;
        company_name: string;
    };
    status: string;
    match_score: number;
}

interface StudentQuickActionsProps {
    hasSubmitted: boolean;
    performance?: {
        overall_average: number;
        total_questions: number;
    };
    currentMatch?: CurrentMatch | null;
}

export function StudentQuickActions({
    hasSubmitted,
    performance,
    currentMatch
}: StudentQuickActionsProps) {
    const getPerformanceLevel = (score: number) => {
        if (score >= 4.5) return 'Excellent';
        if (score >= 4) return 'Very Good';
        if (score >= 3.5) return 'Good';
        if (score >= 3) return 'Average';
        if (score >= 2.5) return 'Below Average';
        return 'Needs Improvement';
    };

    const getPerformanceColor = (score: number) => {
        if (score >= 4.5) return 'text-green-600';
        if (score >= 4) return 'text-green-500';
        if (score >= 3.5) return 'text-blue-500';
        if (score >= 3) return 'text-yellow-500';
        if (score >= 2.5) return 'text-orange-500';
        return 'text-red-500';
    };

    return (
        <div className="grid gap-4 md:grid-cols-2">
            {/* Assessment Status */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <BookOpen className="h-5 w-5" />
                        Assessment Status
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    {!hasSubmitted ? (
                        <div className="text-center py-4">
                            <div className="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-lg mb-4">
                                <BookOpen className="mx-auto h-8 w-8 text-yellow-600 mb-2" />
                                <p className="text-yellow-800 dark:text-yellow-200 font-medium">
                                    Assessment Not Completed
                                </p>
                            </div>
                            <p className="text-sm text-muted-foreground mb-4">
                                Complete your skills assessment to unlock internship opportunities
                            </p>
                            <Button asChild className="w-full">
                                <Link href="/assessment">
                                    <BookOpen className="h-4 w-4 mr-2" />
                                    Take Assessment
                                </Link>
                            </Button>
                        </div>
                    ) : (
                        <div className="text-center py-4">
                            <div className="bg-green-100 dark:bg-green-900 p-3 rounded-lg mb-4">
                                <Star className="mx-auto h-8 w-8 text-green-600 mb-2" />
                                <p className="text-green-800 dark:text-green-200 font-medium">
                                    Assessment Completed!
                                </p>
                            </div>
                            {performance && (
                                <div className="mb-4">
                                    <div className={`text-2xl font-bold ${getPerformanceColor(performance.overall_average)}`}>
                                        {performance.overall_average}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {getPerformanceLevel(performance.overall_average)}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {performance.total_questions} questions completed
                                    </p>
                                </div>
                            )}
                            <Button asChild variant="outline" className="w-full">
                                <Link href="/student-profile">
                                    <User className="h-4 w-4 mr-2" />
                                    View Results
                                </Link>
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>

            {/* Quick Actions */}
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Target className="h-5 w-5" />
                        Quick Actions
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3">
                    <Button asChild variant="outline" className="w-full justify-start h-auto p-3">
                        <Link href="/student-profile">
                            <User className="h-4 w-4 mr-3" />
                            <div className="text-left">
                                <div className="font-medium">View Profile</div>
                                <div className="text-xs text-muted-foreground">
                                    Check your assessment results
                                </div>
                            </div>
                        </Link>
                    </Button>

                    {hasSubmitted && (
                        <>
                            <Button asChild variant="outline" className="w-full justify-start h-auto p-3">
                                <Link href="/matched">
                                    <Target className="h-4 w-4 mr-3" />
                                    <div className="text-left">
                                        <div className="font-medium">View Matches</div>
                                        <div className="text-xs text-muted-foreground">
                                            See your compatibility scores
                                        </div>
                                    </div>
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="w-full justify-start h-auto p-3">
                                <Link href="/assessment">
                                    <TrendingUp className="h-4 w-4 mr-3" />
                                    <div className="text-left">
                                        <div className="font-medium">View Opportunities</div>
                                        <div className="text-xs text-muted-foreground">
                                            Browse available internships
                                        </div>
                                    </div>
                                </Link>
                            </Button>
                        </>
                    )}

                    <Button asChild variant="outline" className="w-full justify-start h-auto p-3">
                        <Link href="/settings">
                            <Settings className="h-4 w-4 mr-3" />
                            <div className="text-left">
                                <div className="font-medium">Settings</div>
                                <div className="text-xs text-muted-foreground">
                                    Manage your account
                                </div>
                            </div>
                        </Link>
                    </Button>

                    <Button asChild variant="outline" className="w-full justify-start h-auto p-3">
                        <Link href="/contact">
                            <HelpCircle className="h-4 w-4 mr-3" />
                            <div className="text-left">
                                <div className="font-medium">Get Help</div>
                                <div className="text-xs text-muted-foreground">
                                    Contact support
                                </div>
                            </div>
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            {/* Current Status */}
            {currentMatch && (
                <Card className="md:col-span-2">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Bell className="h-5 w-5" />
                            Current Status
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h4 className="font-semibold text-blue-900 dark:text-blue-100">
                                        Internship Match Found!
                                    </h4>
                                    <p className="text-blue-700 dark:text-blue-300 text-sm">
                                        {currentMatch.internship.position_title} at {currentMatch.internship.company_name}
                                    </p>
                                    <div className="flex items-center gap-4 mt-2">
                                        <Badge
                                            variant="outline"
                                            className="border-blue-200 text-blue-700 dark:border-blue-800 dark:text-blue-300"
                                        >
                                            {currentMatch.status}
                                        </Badge>
                                        <span className="text-sm text-blue-600 dark:text-blue-400">
                                            Match Score: {currentMatch.match_score}%
                                        </span>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <div className="text-2xl font-bold text-blue-600">
                                        🎯
                                    </div>
                                    <p className="text-xs text-blue-600 dark:text-blue-400">
                                        Matched
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}

            {/* Progress Tracking */}
            {hasSubmitted && performance && (
                <Card className="md:col-span-2">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TrendingUp className="h-5 w-5" />
                            Your Progress
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div className="text-2xl font-bold text-blue-600">
                                    {performance.overall_average}
                                </div>
                                <p className="text-sm text-muted-foreground">Overall Score</p>
                            </div>
                            <div className="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div className="text-2xl font-bold text-green-600">
                                    {performance.total_questions}
                                </div>
                                <p className="text-sm text-muted-foreground">Questions Answered</p>
                            </div>
                            <div className="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div className="text-2xl font-bold text-purple-600">
                                    {currentMatch ? '✓' : '⏳'}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    {currentMatch ? 'Matched' : 'Pending Match'}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
