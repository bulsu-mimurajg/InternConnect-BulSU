import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    GraduationCap,
    User,
    BriefcaseBusinessIcon
} from 'lucide-react';
import { Link } from '@inertiajs/react';

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
    internship: {
        position_title: string;
        company_name: string;
    };
    status: string;
    match_score: number;
}

interface StudentInternshipOpportunitiesProps {
    internships: PossibleInternship[];
    currentMatch: CurrentMatch | null;
}

export function StudentInternshipOpportunities({
    internships,
    currentMatch
}: StudentInternshipOpportunitiesProps) {
    const internshipsArray = Array.isArray(internships) ? internships : [];
    const isShowingAvailable = internshipsArray.some(internship => internship.compatibility_score === 50);
    
    if (internshipsArray.length === 0) {
        return (
            <Card>
                <CardHeader className="pb-3">
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <BriefcaseBusinessIcon className="h-4 w-4" />
                        Internship Opportunities
                    </CardTitle>
                </CardHeader>
                <CardContent className="pt-0">
                    <div className="text-center py-6">
                        <BriefcaseBusinessIcon className="mx-auto h-8 w-8 text-muted-foreground mb-3" />
                        <p className="text-muted-foreground text-sm">
                            Complete your assessment to see matches
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-lg">
                    <BriefcaseBusinessIcon className="h-4 w-4" />
                    {isShowingAvailable ? 'Available Opportunities' : 'Your Top Matches'}
                    <Badge variant="secondary" className="text-xs">
                        {internshipsArray.length}
                    </Badge>
                </CardTitle>
            </CardHeader>
            <CardContent className="pt-0">
                {isShowingAvailable && (
                    <div className="mb-4 p-3 bg-blue-50 dark:bg-blue-950 rounded-md">
                        <p className="text-xs text-blue-700 dark:text-blue-300">
                            Complete assessment for personalized matches
                        </p>
                    </div>
                )}
                
                <div className="space-y-4">
                    {internshipsArray.map((internship) => (
                        <div
                            key={internship.id}
                            className="px-4 py-2 border rounded-lg hover:bg-muted/50 transition-colors"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex-1 min-w-0">
                                    <h4 className="font-medium text-base leading-tight mb-2">
                                        {internship.position_title}
                                    </h4>
                                    <div className="flex items-center gap-1 text-sm text-muted-foreground mb-2">
                                        <BriefcaseBusinessIcon className="h-4 w-4 flex-shrink-0" />
                                        <span className="truncate">{internship.company_name}</span>
                                    </div>
                                    <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1">
                                            <GraduationCap className="h-4 w-4" />
                                            {internship.department}
                                        </span>
                                    </div>
                                </div>
                                <div className="flex flex-col items-end gap-1 self-end">
                                    <Badge
                                        variant={
                                            internship.compatibility_score >= 80 ? "default" :
                                            internship.compatibility_score >= 70 ? "secondary" :
                                            internship.compatibility_score >= 60 ? "outline" : "destructive"
                                        }
                                        className="text-sm flex-shrink-0"
                                    >
                                        {internship.compatibility_score}%
                                    </Badge>
                                    <span className="text-xs text-muted-foreground">
                                        Criteria Match
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {currentMatch && (
                    <div className="mt-4 pt-4 border-t">
                        <div className="bg-blue-50 dark:bg-blue-950 p-3 rounded-md">
                            <div className="flex items-center justify-between mb-2">
                                <h4 className="font-medium text-sm text-blue-900 dark:text-blue-100">
                                    Current Match
                                </h4>
                                <Badge variant="outline" className="text-xs border-blue-200 text-blue-700 dark:border-blue-800 dark:text-blue-300">
                                    {currentMatch.status}
                                </Badge>
                            </div>
                            <p className="text-xs text-blue-600 dark:text-blue-400 mb-1">
                                {currentMatch.internship.position_title} at {currentMatch.internship.company_name}
                            </p>
                            <p className="text-xs text-blue-600 dark:text-blue-400">
                                Match Score: {currentMatch.match_score}%
                            </p>
                        </div>
                    </div>
                )}

                <div className="mt-4 pt-4 border-t">
                    <Button asChild variant="outline" size="sm" className="w-full">
                        <Link href="/student-profile">
                            <User className="h-3 w-3 mr-2" />
                            Check Your Profile
                        </Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
