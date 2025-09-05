import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { GraduationCap, TrendingUp, Target } from 'lucide-react';

interface CategoryScore {
    name: string;
    average_score: number;
    questions_count: number;
}

interface StudentPerformanceChartProps {
    overallAverage: number;
    categoryScores: CategoryScore[];
    totalQuestions: number;
}

export function StudentPerformanceChart({ 
    overallAverage, 
    categoryScores, 
    totalQuestions 
}: StudentPerformanceChartProps) {
    const getScoreColor = (score: number) => {
        if (score >= 4) return 'text-green-600';
        if (score >= 3) return 'text-yellow-600';
        return 'text-red-600';
    };

    const getScoreBadgeColor = (score: number) => {
        if (score >= 4) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (score >= 3) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    };

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
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <GraduationCap className="h-5 w-5" />
                    Performance Overview
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
                {/* Overall Performance */}
                <div className="text-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 rounded-lg">
                    <div className="flex items-center justify-center gap-2 mb-2">
                        <TrendingUp className="h-5 w-5 text-blue-600" />
                        <span className="text-sm font-medium text-blue-700 dark:text-blue-300">
                            Overall Performance
                        </span>
                    </div>
                    <div className={`text-4xl font-bold ${getScoreColor(overallAverage)} mb-1`}>
                        {overallAverage}
                    </div>
                    <div className={`text-sm font-medium ${getPerformanceColor(overallAverage)} mb-2`}>
                        {getPerformanceLevel(overallAverage)}
                    </div>
                    <p className="text-xs text-blue-600 dark:text-blue-400">
                        Based on {totalQuestions} questions across {categoryScores.length} categories
                    </p>
                </div>

                {/* Category Breakdown */}
                <div className="space-y-4">
                    <h4 className="font-medium text-sm text-muted-foreground">Category Breakdown</h4>
                    {categoryScores.map((category, index) => (
                        <div key={index} className="space-y-2">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <Target className="h-4 w-4 text-muted-foreground" />
                                    <span className="font-medium text-sm">{category.name}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge className={getScoreBadgeColor(category.average_score)}>
                                        {category.average_score}
                                    </Badge>
                                    <span className="text-xs text-muted-foreground">
                                        {category.questions_count} Q
                                    </span>
                                </div>
                            </div>
                            <Progress 
                                value={(category.average_score / 5) * 100} 
                                className="h-2"
                            />
                            <div className="flex items-center justify-between text-xs">
                                <span className="text-muted-foreground">
                                    {getPerformanceLevel(category.average_score)}
                                </span>
                                <span className="text-muted-foreground">
                                    {Math.round((category.average_score / 5) * 100)}% of max score
                                </span>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Performance Insights */}
                <div className="pt-4 border-t">
                    <h4 className="font-medium text-sm text-muted-foreground mb-3">Performance Insights</h4>
                    <div className="grid gap-3 text-sm">
                        {overallAverage >= 4 && (
                            <div className="flex items-center gap-2 text-green-700 dark:text-green-300">
                                <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span>Excellent overall performance! You're well-prepared for internships.</span>
                            </div>
                        )}
                        {overallAverage >= 3 && overallAverage < 4 && (
                            <div className="flex items-center gap-2 text-blue-700 dark:text-blue-300">
                                <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span>Good performance. Consider focusing on areas with lower scores.</span>
                            </div>
                        )}
                        {overallAverage < 3 && (
                            <div className="flex items-center gap-2 text-orange-700 dark:text-orange-300">
                                <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                <span>Focus on improving skills in lower-scoring categories.</span>
                            </div>
                        )}
                        
                        {categoryScores.some(cat => cat.average_score < 3) && (
                            <div className="flex items-center gap-2 text-yellow-700 dark:text-yellow-300">
                                <div className="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <span>Some categories need attention. Review and practice those skills.</span>
                            </div>
                        )}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
