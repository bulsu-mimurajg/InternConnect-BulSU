import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Progress } from '@/components/ui/progress';
import { 
    UserIcon, 
    TargetIcon,
    BarChart3Icon,
    ScaleIcon
} from 'lucide-react';

interface Student {
    student_number?: string;
    first_name?: string;
    last_name?: string;
    section?: string;
    specialization?: string;
    middle_name?: string;
}

interface ScoreBreakdown {
    category: string;
    subcategory: string;
    score: number;
    score_percentage: number;
}

interface QuestionImportanceRating {
    id: number;
    rating: number;
    question?: {
        id: number;
        question: string;
        subcategory?: {
            id: number;
            subcategory_name: string;
            category?: {
                id: number;
                category_name: string;
            };
        };
    };
}

interface SubcategoryRequirement {
    subcategory_name: string;
    category_name: string;
    percentage: number;
    rating: number;
    descriptiveRating: string;
    questionCount: number;
    ratings: number[];
}

interface BestMatch {
    compatibility_score: number;
    internship: {
        position_title: string;
        department: string;
        hte: {
            company_name: string;
        };
        question_importance_ratings?: QuestionImportanceRating[];
    };
}

interface StudentData {
    student?: Student;
    scores_breakdown?: ScoreBreakdown[];
    best_match?: BestMatch;
}

interface StudentDetailsModalProps {
    isOpen: boolean;
    onClose: () => void;
    student: StudentData | null;
}

export default function StudentDetailsModal({
    isOpen,
    onClose,
    student
}: StudentDetailsModalProps) {
    const [activeTab, setActiveTab] = useState('comparison');

    const getScoreColor = (score: number) => {
        if (score >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (score >= 60) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        return 'bg-red-100 text-red-800 dark:bg-green-900 dark:text-red-200';
    };

    // Note: Using getDescriptiveRating instead for consistency with passing requirements

    /**
     * Calculate subcategory percentage from question ratings
     * Formula: (sum of question ratings) / (number of questions × 5) × 100
     */
    const calculateSubcategoryPercentage = (questionRatings: number[], questionCount: number): number => {
        if (questionCount === 0) return 0;
        const sumOfRatings = questionRatings.reduce((sum, rating) => sum + rating, 0);
        const maxPossibleScore = questionCount * 5;
        return Math.round((sumOfRatings / maxPossibleScore) * 100 * 100) / 100;
    };

    /**
     * Map percentage to Likert scale equivalent (same as HTE form)
     */
    const mapPercentageToRating = (percentage: number): number => {
        if (percentage >= 96) return 5;
        if (percentage >= 90) return 4;
        if (percentage >= 80) return 3;
        if (percentage >= 75) return 2;
        return 1;
    };

    /**
     * Get descriptive rating from percentage (same as HTE form)
     */
    const getDescriptiveRating = (percentage: number): string => {
        if (percentage >= 96) return 'Excellent';
        if (percentage >= 90) return 'Very Good';
        if (percentage >= 80) return 'Good';
        if (percentage >= 75) return 'Fair';
        return 'Poor';
    };

    /**
     * Get color for passing threshold percentage
     */
    const getThresholdColor = (percentage: number) => {
        if (percentage >= 96) return 'text-green-600 dark:text-green-400';
        if (percentage >= 90) return 'text-blue-600 dark:text-blue-400';
        if (percentage >= 80) return 'text-yellow-600 dark:text-yellow-400';
        if (percentage >= 75) return 'text-orange-600 dark:text-orange-400';
        return 'text-red-600 dark:text-red-400';
    };

    /**
     * Get badge color for passing threshold percentage
     */
    const getThresholdBadgeColor = (percentage: number) => {
        if (percentage >= 96) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (percentage >= 90) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        if (percentage >= 80) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        if (percentage >= 75) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
    };

    /**
     * Get background color for threshold indicator fill
     */
    const getThresholdFillColor = (percentage: number) => {
        if (percentage >= 96) return 'bg-green-500';
        if (percentage >= 90) return 'bg-blue-500';
        if (percentage >= 80) return 'bg-yellow-500';
        if (percentage >= 75) return 'bg-orange-500';
        return 'bg-red-500';
    };

    if (!student) return null;

    // Group student scores by category for better organization
    const scoresByCategory = student.scores_breakdown?.filter(score => 
        score && score.category && score.subcategory && score.score !== null
    ).reduce((acc: Record<string, ScoreBreakdown[]>, score: ScoreBreakdown) => {
        const categoryName = score.category || 'Uncategorized';
        if (!acc[categoryName]) {
            acc[categoryName] = [];
        }
        acc[categoryName].push(score);
        return acc;
    }, {}) || {};

    // Process question importance ratings to calculate subcategory requirements
    const processQuestionRatings = (): Record<string, SubcategoryRequirement[]> => {
        const ratings = student.best_match?.internship?.question_importance_ratings || [];
        
        // Group ratings by subcategory
        const subcategoryMap: Record<string, {
            category_name: string;
            subcategory_name: string;
            ratings: number[];
            questionCount: number;
        }> = {};

        ratings.forEach((rating: QuestionImportanceRating) => {
            if (!rating.question?.subcategory) return;
            
            const subcategoryId = rating.question.subcategory.id;
            const subcategoryName = rating.question.subcategory.subcategory_name;
            const categoryName = rating.question.subcategory.category?.category_name || 'Uncategorized';
            
            if (!subcategoryMap[subcategoryId]) {
                subcategoryMap[subcategoryId] = {
                    category_name: categoryName,
                    subcategory_name: subcategoryName,
                    ratings: [],
                    questionCount: 0,
                };
            }
            
            // Only include valid ratings (1-5) and count each rated question
            if (rating.rating >= 1 && rating.rating <= 5) {
                subcategoryMap[subcategoryId].ratings.push(rating.rating);
                subcategoryMap[subcategoryId].questionCount += 1;
            }
        });

        // Convert to requirements grouped by category
        const requirementsByCategory: Record<string, SubcategoryRequirement[]> = {};

        Object.entries(subcategoryMap).forEach(([subcategoryId, data]) => {
            const subcategoryName = data.subcategory_name || 'Unknown';
            
            const percentage = calculateSubcategoryPercentage(data.ratings, data.questionCount);
            const rating = mapPercentageToRating(percentage);
            const descriptiveRating = getDescriptiveRating(percentage);

            const requirement: SubcategoryRequirement = {
                subcategory_name: subcategoryName,
                category_name: data.category_name,
                percentage,
                rating,
                descriptiveRating,
                questionCount: data.questionCount,
                ratings: data.ratings,
            };

            if (!requirementsByCategory[data.category_name]) {
                requirementsByCategory[data.category_name] = [];
            }
            requirementsByCategory[data.category_name].push(requirement);
        });

        return requirementsByCategory;
    };

    const requirementsByCategory = processQuestionRatings();

    // Calculate category-level passing thresholds
    const getCategoryPassingThreshold = (requirements: SubcategoryRequirement[]): number => {
        if (requirements.length === 0) return 0;
        const totalPercentage = requirements.reduce((sum, req) => sum + req.percentage, 0);
        return Math.round((totalPercentage / requirements.length) * 100) / 100;
    };

    // Calculate student's overall category percentage
    const getStudentCategoryPercentage = (scores: ScoreBreakdown[]): number => {
        if (scores.length === 0) return 0;
        const totalPercentage = scores.reduce((sum, score) => sum + score.score_percentage, 0);
        return Math.round((totalPercentage / scores.length) * 100) / 100;
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-[95vw] lg:max-w-5xl xl:max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <UserIcon className="h-5 w-5" />
                        Student Details: {[student.student?.first_name, student.student?.middle_name, student.student?.last_name].filter(Boolean).join(' ')}
                    </DialogTitle>
                </DialogHeader>

                <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
                    <TabsList className="grid w-full grid-cols-1 sm:grid-cols-2 gap-2">
                        <TabsTrigger value="comparison" className="flex items-center gap-2">
                            <ScaleIcon className="h-4 w-4" />
                            Score Comparison
                        </TabsTrigger>
                        <TabsTrigger value="details" className="flex items-center gap-2">
                            <UserIcon className="h-4 w-4" />
                            Student Details
                        </TabsTrigger>
                    </TabsList>

                    {/* Score Comparison Tab */}
                    <TabsContent value="comparison" className="space-y-6">
                        {student.best_match && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2">
                                        <TargetIcon className="h-5 w-5" />
                                        Compatibility Analysis: {student.best_match.internship.position_title}
                                    </CardTitle>
                                    <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-medium">Overall Compatibility:</span>
                                            <Badge className={getScoreColor(student.best_match.compatibility_score)}>
                                                {student.best_match.compatibility_score}%
                                            </Badge>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-medium">Company:</span>
                                            <Badge variant="outline">
                                                {student.best_match.internship.hte.company_name}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-medium">Department:</span>
                                            <Badge variant="outline">
                                                {student.best_match.internship.department}
                                            </Badge>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <h4 className="font-medium text-foreground text-sm">Compatibility Breakdown</h4>
                                        <p className="text-sm text-muted-foreground mb-4">
                                            How the overall compatibility score was calculated
                                        </p>
                                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                            <div className="p-4 bg-blue-50 dark:bg-blue-950/30 rounded-lg">
                                                <h4 className="font-medium text-blue-900 dark:text-blue-100 mb-2">Student Performance</h4>
                                                <p className="text-sm text-blue-700 dark:text-blue-300">
                                                    The student's assessment scores are converted to percentages and then 
                                                    weighted according to the internship's criteria importance.
                                                </p>
                                            </div>
                                            <div className="p-4 bg-green-50 dark:bg-green-950/30 rounded-lg">
                                                <h4 className="font-medium text-green-900 dark:text-green-100 mb-2">Internship Requirements</h4>
                                                <p className="text-sm text-green-700 dark:text-green-300">
                                                    Each question is rated on a Likert scale (1-5) indicating its importance. 
                                                    These ratings determine the minimum passing thresholds students must achieve in each subcategory.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Rating Reference Guide */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Rating Reference Guide</CardTitle>
                                <CardDescription>
                                    Percentage ranges and their equivalent ratings
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                                    <div className="flex items-center gap-2 p-2 rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800">
                                        <div className="text-lg font-bold text-green-600 dark:text-green-400">5</div>
                                        <div className="flex-1">
                                            <div className="text-xs font-medium text-green-900 dark:text-green-100">96-100%</div>
                                            <div className="text-xs text-green-700 dark:text-green-300">Excellent</div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 p-2 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                                        <div className="text-lg font-bold text-blue-600 dark:text-blue-400">4</div>
                                        <div className="flex-1">
                                            <div className="text-xs font-medium text-blue-900 dark:text-blue-100">90-95%</div>
                                            <div className="text-xs text-blue-700 dark:text-blue-300">Very Good</div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 p-2 rounded-lg bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800">
                                        <div className="text-lg font-bold text-yellow-600 dark:text-yellow-400">3</div>
                                        <div className="flex-1">
                                            <div className="text-xs font-medium text-yellow-900 dark:text-yellow-100">80-89%</div>
                                            <div className="text-xs text-yellow-700 dark:text-yellow-300">Good</div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800">
                                        <div className="text-lg font-bold text-orange-600 dark:text-orange-400">2</div>
                                        <div className="flex-1">
                                            <div className="text-xs font-medium text-orange-900 dark:text-orange-100">75-79%</div>
                                            <div className="text-xs text-orange-700 dark:text-orange-300">Fair</div>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 p-2 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                                        <div className="text-lg font-bold text-red-600 dark:text-red-400">1</div>
                                        <div className="flex-1">
                                            <div className="text-xs font-medium text-red-900 dark:text-red-100">Below 75%</div>
                                            <div className="text-xs text-red-700 dark:text-red-300">Poor</div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Category-by-Category Comparison */}
                        <div className="space-y-6">
                            {(() => {
                                // Get all unique categories from both student scores and requirements
                                const allCategories = new Set([
                                    ...Object.keys(scoresByCategory),
                                    ...Object.keys(requirementsByCategory)
                                ]);

                                if (allCategories.size === 0) {
                                    return (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <p>No assessment data available for comparison.</p>
                                        </div>
                                    );
                                }

                                return Array.from(allCategories).map((category) => {
                                    const studentScores = scoresByCategory[category] || [];
                                    const requirements = requirementsByCategory[category] || [];
                                    const categoryThreshold = requirements.length > 0 
                                        ? getCategoryPassingThreshold(requirements) 
                                        : 0;
                                    const studentCategoryPercentage = studentScores.length > 0
                                        ? getStudentCategoryPercentage(studentScores)
                                        : 0;

                                    return (
                                        <Card key={category} className="space-y-4">
                                            {/* Category Header */}
                                            <CardHeader>
                                                <CardTitle className="text-xl font-bold text-gray-900">
                                                    {category === 'Uncategorized' ? 'General Requirements' : category}
                                                </CardTitle>
                                            </CardHeader>
                                            <CardContent>
                                                {/* Side-by-Side Comparison */}
                                                <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6">
                                                    {/* Student Scores - Left */}
                                                    <Card>
                                                        <CardHeader>
                                                            <CardTitle className="text-base flex items-center gap-2 text-blue-600">
                                                                <BarChart3Icon className="h-4 w-4" />
                                                                Student Assessment Scores
                                                            </CardTitle>
                                                        </CardHeader>
                                                        <CardContent className="space-y-4">
                                                        {studentScores.length > 0 ? (
                                                            <>
                                                                <div className="space-y-3">
                                                                    {studentScores.map((score: ScoreBreakdown, index: number) => {
                                                                        const equivalentRating = mapPercentageToRating(score.score_percentage);
                                                                        const descriptiveRating = getDescriptiveRating(score.score_percentage);
                                                                        
                                                                        // Find matching requirement to compare
                                                                        const matchingRequirement = requirements.find((req: SubcategoryRequirement) => 
                                                                            req.subcategory_name === score.subcategory
                                                                        );
                                                                        const passed = matchingRequirement 
                                                                            ? score.score_percentage >= matchingRequirement.percentage 
                                                                            : null;
                                                                        
                                                                        return (
                                                                            <div key={index} className="space-y-2">
                                                                                <div className="flex justify-between items-center">
                                                                                    <span className="text-sm font-medium text-gray-700">
                                                                                        {score.subcategory}
                                                                                    </span>
                                                                                    <div className="text-right">
                                                                                        <div className="text-sm font-bold text-blue-600">
                                                                                            {Math.round(score.score_percentage)}%
                                                                                        </div>
                                                                                        <div className="flex items-center gap-2 justify-end">
                                                                                            {matchingRequirement && (
                                                                                                <span className={`text-xs font-medium ${
                                                                                                    passed 
                                                                                                        ? 'text-green-600 dark:text-green-400' 
                                                                                                        : 'text-red-600 dark:text-red-400'
                                                                                                }`}>
                                                                                                    {passed ? 'Passed' : 'Failed'}
                                                                                                </span>
                                                                                            )}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <Progress 
                                                                                    value={score.score_percentage} 
                                                                                    className="h-2" 
                                                                                />
                                                                            </div>
                                                                        );
                                                                    })}
                                                                </div>
                                                                {/* Overall Category Score */}
                                                                {studentCategoryPercentage > 0 && (
                                                                    <div className="pt-3 border-t">
                                                                        <div className="flex justify-between items-center">
                                                                            <span className="text-sm font-semibold text-gray-900">
                                                                                Overall Category Score
                                                                            </span>
                                                                            <div className="text-right">
                                                                                <div className="text-lg font-bold text-blue-600">
                                                                                    {Math.round(studentCategoryPercentage)}% | {mapPercentageToRating(studentCategoryPercentage)}/5
                                                                                </div>
                                                                                <div className="flex items-center gap-2 justify-end">
                                                                                    {categoryThreshold > 0 && (
                                                                                        <span className={`text-xs font-medium ${
                                                                                            studentCategoryPercentage >= categoryThreshold
                                                                                                ? 'text-green-600 dark:text-green-400' 
                                                                                                : 'text-red-600 dark:text-red-400'
                                                                                        }`}>
                                                                                            {studentCategoryPercentage >= categoryThreshold ? ' Passed' : ' Failed'}
                                                                                        </span>
                                                                                    )}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <Progress 
                                                                            value={studentCategoryPercentage} 
                                                                            className="h-2 mt-2" 
                                                                        />
                                                                    </div>
                                                                )}
                                                            </>
                                                        ) : (
                                                            <div className="text-center py-4 text-muted-foreground text-sm">
                                                                No scores available for this category
                                                            </div>
                                                        )}
                                                    </CardContent>
                                                </Card>

                                                {/* Passing Requirements - Right */}
                                                <Card>
                                                    <CardHeader>
                                                        <CardTitle className="text-base flex items-center gap-2 text-green-600">
                                                            <TargetIcon className="h-4 w-4" />
                                                            Passing Requirements
                                                        </CardTitle>
                                                    </CardHeader>
                                                    <CardContent className="space-y-4">
                                                        {requirements.length > 0 ? (
                                                            <>
                                                                <div className="space-y-3">
                                                                    {requirements.map((requirement: SubcategoryRequirement, index: number) => (
                                                                        <div key={index} className="space-y-2">
                                                                            <div className="flex justify-between items-center">
                                                                                <span className="text-sm font-medium text-gray-700">
                                                                                    {requirement.subcategory_name}
                                                                                </span>
                                                                                <div className="text-right">
                                                                                    <div className={`text-sm font-bold ${getThresholdColor(requirement.percentage)}`}>
                                                                                        {requirement.percentage > 0 ? `${Math.round(requirement.percentage)}%` : 'Not Rated'}
                                                                                    </div>
                                                                                    <div className="text-xs text-gray-500">
                                                                                        Minimum Required
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <Progress 
                                                                                value={requirement.percentage} 
                                                                                className="h-2" 
                                                                            />
                                                                        </div>
                                                                    ))}
                                                                </div>
                                                                {/* Overall Passing Threshold */}
                                                                {categoryThreshold > 0 && (
                                                                    <div className="pt-3 border-t">
                                                                        <div className="flex justify-between items-center">
                                                                            <span className="text-sm font-semibold text-gray-900">
                                                                                Overall Passing Threshold
                                                                            </span>
                                                                            <div className="text-right">
                                                                                <div className={`text-lg font-bold ${getThresholdColor(categoryThreshold)}`}>
                                                                                    {Math.round(categoryThreshold)}%
                                                                                </div>
                                                                                <div className="text-xs text-gray-500">
                                                                                    Overall Required
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <Progress 
                                                                            value={categoryThreshold} 
                                                                            className="h-2 mt-2" 
                                                                        />
                                                                    </div>
                                                                )}
                                                            </>
                                                        ) : (
                                                            <div className="text-center py-4 text-muted-foreground text-sm">
                                                                No requirements defined for this category
                                                            </div>
                                                        )}
                                                    </CardContent>
                                                </Card>
                                            </div>
                                            </CardContent>
                                        </Card>
                                    );
                                });
                            })()}
                        </div>
                    </TabsContent>

                    {/* Student Details Tab */}
                    <TabsContent value="details" className="space-y-6">
                        {/* Student Information */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Student Information</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Full Name</label>
                                        <p className="text-sm">{[student.student?.first_name, student.student?.middle_name, student.student?.last_name].filter(Boolean).join(' ')}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Student Number</label>
                                        <p className="text-sm">{student.student?.student_number}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Section</label>
                                        <p className="text-sm">{student.student?.section}</p>
                                    </div>
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Specialization</label>
                                        <p className="text-sm">{student.student?.specialization || 'N/A'}</p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Best Match Information */}
                        {student.best_match && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2">
                                        <TargetIcon className="h-5 w-5" />
                                        Best Match - {student.best_match.internship.position_title}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">Company</label>
                                                <p className="text-sm font-medium">{student.best_match.internship.hte.company_name}</p>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">Department</label>
                                                <p className="text-sm">{student.best_match.internship.department}</p>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">Position</label>
                                                <p className="text-sm font-medium">{student.best_match.internship.position_title}</p>
                                            </div>
                                            <div>
                                                <label className="text-sm font-medium text-gray-500">Compatibility Score</label>
                                                <div className="flex items-center gap-2">
                                                    <Badge className={getScoreColor(student.best_match.compatibility_score)}>
                                                        {student.best_match.compatibility_score}%
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                    </TabsContent>
                </Tabs>
            </DialogContent>
        </Dialog>
    );
}
