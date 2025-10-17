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
                    <div className="text-4xl font-bold text-black dark:text-white mb-1">
                        {overallAverage}
                    </div>
                    <div className="text-sm font-medium text-muted-foreground mb-2">
                        out of 5.0
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
                                    <Badge className="bg-black text-white dark:bg-white dark:text-black">
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
                                </span>
                                <span className="text-muted-foreground">
                                    {Math.round((category.average_score / 5) * 100)}% of max score
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
