import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Briefcase, 
    Building, 
    GraduationCap, 
    User, 
    MapPin, 
    Calendar,
    ExternalLink,
    Target
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

interface StudentInternshipOpportunitiesProps {
    internships: PossibleInternship[];
    currentMatch: any;
}

export function StudentInternshipOpportunities({ 
    internships, 
    currentMatch 
}: StudentInternshipOpportunitiesProps) {
    if (internships.length === 0) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Briefcase className="h-5 w-5" />
                        Possible Internships
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-center py-8">
                        <Briefcase className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <p className="text-muted-foreground mb-2">No possible internships found</p>
                        <p className="text-sm text-muted-foreground">
                            Complete your assessment to see your top matches
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                        <Briefcase className="h-5 w-5" />
                        Possible Internships
                        <Badge variant="secondary" className="ml-2">
                            Your Top Matches
                        </Badge>
                    </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {internships.map((internship) => (
                        <div 
                            key={internship.id} 
                            className="p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                        >
                            <div className="flex items-start justify-between">
                                <div className="flex-1 space-y-3">
                                    <div>
                                        <h4 className="font-semibold text-lg group-hover:text-blue-600 transition-colors">
                                            {internship.position_title}
                                        </h4>
                                        <div className="flex items-center gap-1 text-muted-foreground">
                                            <Building className="h-4 w-4" />
                                            <span className="font-medium">{internship.company_name}</span>
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                        <div className="flex items-center gap-2">
                                            <GraduationCap className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-muted-foreground">Department:</span>
                                            <span className="font-medium">{internship.department}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <User className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-muted-foreground">Slots:</span>
                                            <span className="font-medium">{internship.slot_count}</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Target className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-muted-foreground">Compatibility:</span>
                                            <Badge 
                                                variant={
                                                    internship.compatibility_score >= 80 ? "default" : 
                                                    internship.compatibility_score >= 70 ? "secondary" : 
                                                    internship.compatibility_score >= 60 ? "outline" : "destructive"
                                                }
                                                className="text-xs"
                                            >
                                                {internship.compatibility_score}%
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="flex flex-col items-end gap-2 ml-4">
                                    <Badge 
                                        variant={
                                            internship.compatibility_score >= 80 ? "default" : 
                                            internship.compatibility_score >= 70 ? "secondary" : 
                                            internship.compatibility_score >= 60 ? "outline" : "destructive"
                                        }
                                        className="text-xs"
                                    >
                                        {internship.compatibility_score}%
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
                
                {/* Current Match Status */}
                {currentMatch && (
                    <div className="mt-6 pt-6 border-t">
                        <div className="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                            <div className="flex items-center gap-2 mb-3">
                                <Target className="h-5 w-5 text-blue-600" />
                                <h4 className="font-semibold text-blue-900 dark:text-blue-100">
                                    Your Current Match
                                </h4>
                            </div>
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <span className="text-blue-700 dark:text-blue-300 font-medium">
                                        {currentMatch.internship.position_title}
                                    </span>
                                    <Badge 
                                        variant="outline" 
                                        className="border-blue-200 text-blue-700 dark:border-blue-800 dark:text-blue-300"
                                    >
                                        {currentMatch.status}
                                    </Badge>
                                </div>
                                <p className="text-blue-600 dark:text-blue-400 text-sm">
                                    {currentMatch.internship.company_name}
                                </p>
                                <div className="flex items-center gap-4 text-sm text-blue-600 dark:text-blue-400">
                                    <span>Match Score: {currentMatch.match_score}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
                
                {/* Action Buttons */}
                <div className="mt-6 pt-6 border-t">
                    <div className="flex justify-center">
                        <Button asChild variant="outline" className="w-full max-w-xs">
                            <Link href="/student-profile">
                                <User className="h-4 w-4 mr-2" />
                                Check Your Profile
                            </Link>
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
