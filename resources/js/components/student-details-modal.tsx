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

interface SubcategoryWeight {
    weight: number;
    subcategory?: {
        subcategory_name: string;
        category?: {
            name: string;
        };
    };
}

interface BestMatch {
    compatibility_score: number;
    internship: {
        position_title: string;
        department: string;
        hte: {
            company_name: string;
        };
        subcategory_weights?: SubcategoryWeight[];
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

    const getScoreLabel = (score: number) => {
        if (score >= 80) return 'Excellent';
        if (score >= 60) return 'Good';
        return 'Fair';
    };

    const getWeightColor = (weight: number) => {
        if (weight >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        if (weight >= 60) return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200';
        return 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
    };

    if (!student) return null;

    // Debug: Log the data structure
    console.log('Student data:', student);
    console.log('Scores breakdown:', student.scores_breakdown);
    console.log('Best match:', student.best_match);
    console.log('Subcategory weights:', student.best_match?.internship?.subcategory_weights);
    
    // Additional debugging
    console.log('Scores breakdown length:', student.scores_breakdown?.length);
    console.log('Best match internship:', student.best_match?.internship);
    console.log('Best match subcategory weights:', student.best_match?.internship?.subcategory_weights?.length);

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

    // Get internship criteria (weights) grouped by category
    const criteriaByCategory = student.best_match?.internship?.subcategory_weights?.filter(weight => 
        weight && weight.subcategory && weight.subcategory.subcategory_name && weight.weight !== null
    ).reduce((acc: Record<string, SubcategoryWeight[]>, weight: SubcategoryWeight) => {
        const categoryName = weight.subcategory?.category?.category_name || 'General Requirements';
        if (!acc[categoryName]) {
            acc[categoryName] = [];
        }
        acc[categoryName].push(weight);
        return acc;
    }, {}) || {};

    console.log('Scores by category:', scoresByCategory);
    console.log('Criteria by category:', criteriaByCategory);
    console.log('Raw subcategory weights:', student.best_match?.internship?.subcategory_weights);
    console.log('First weight category:', student.best_match?.internship?.subcategory_weights?.[0]?.subcategory?.category);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="max-w-[95vw] lg:max-w-5xl xl:max-w-6xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <UserIcon className="h-5 w-5" />
                        Student Details: {student.student?.first_name} {student.student?.last_name}
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
                                            <span className="text-sm font-medium">Overall Score:</span>
                                            <Badge className={getScoreColor(student.best_match.compatibility_score)}>
                                                {student.best_match.compatibility_score}%
                                            </Badge>
                                            <span className="text-xs text-gray-500">
                                                {getScoreLabel(student.best_match.compatibility_score)}
                                            </span>
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
                                                    Each subcategory has a weight percentage indicating its importance 
                                                    for the specific internship position.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Side-by-Side Comparison */}
                        <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6">
                            {/* Student Scores - Left Side */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2 text-blue-600">
                                        <BarChart3Icon className="h-5 w-5" />
                                        Student Assessment Scores
                                    </CardTitle>
                                    <CardDescription>
                                        Individual scores across all assessment categories
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {student.scores_breakdown && student.scores_breakdown.length > 0 ? (
                                        Object.keys(scoresByCategory).length > 0 ? (
                                            Object.entries(scoresByCategory).map(([category, scores]: [string, ScoreBreakdown[]]) => (
                                                <div key={category} className="border rounded-lg p-4">
                                                    <h4 className="font-semibold text-gray-900 mb-3 text-blue-700">
                                                        {category}
                                                    </h4>
                                                    <div className="space-y-3">
                                                        {scores.map((score: ScoreBreakdown, index: number) => (
                                                            <div key={index} className="space-y-2">
                                                                <div className="flex justify-between items-center">
                                                                    <span className="text-sm font-medium text-gray-700">
                                                                        {score.subcategory}
                                                                    </span>
                                                                    <div className="text-right">
                                                                        <div className="text-sm font-bold text-blue-600">
                                                                            {score.score}/5
                                                                        </div>
                                                                        <div className="text-xs text-gray-500">
                                                                            {Math.round(score.score_percentage)}%
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <Progress 
                                                                    value={score.score_percentage} 
                                                                    className="h-2" 
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="text-center py-8 text-muted-foreground">
                                                <p>Assessment scores found but missing category information.</p>
                                                <p className="text-sm">Please check the assessment data structure.</p>
                                            </div>
                                        )
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <p>No assessment scores found.</p>
                                            <p className="text-sm">Please ensure the student has completed their assessment.</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Internship Criteria - Right Side */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2 text-green-600">
                                        <TargetIcon className="h-5 w-5" />
                                        Internship Requirements
                                    </CardTitle>
                                    <CardDescription>
                                        Weighted criteria and importance levels
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {student.best_match?.internship?.subcategory_weights && student.best_match.internship.subcategory_weights.length > 0 ? (
                                        Object.keys(criteriaByCategory).length > 0 ? (
                                                        Object.entries(criteriaByCategory).map(([category, weights]: [string, SubcategoryWeight[]]) => (
                                                <div key={category} className="border rounded-lg p-4">
                                                    <h4 className="font-semibold text-gray-900 mb-3 text-green-700">
                                                        {category === 'Uncategorized' ? 'General Requirements' : category}
                                                    </h4>
                                                    <div className="space-y-3">
                                                        {weights.map((weight: SubcategoryWeight, index: number) => (
                                                            <div key={index} className="space-y-2">
                                                                <div className="flex justify-between items-center">
                                                                    <span className="text-sm font-medium text-gray-700">
                                                                        {weight.subcategory.subcategory_name}
                                                                    </span>
                                                                    <div className="text-right">
                                                                        <Badge className={getWeightColor(weight.weight)}>
                                                                            {weight.weight}%
                                                                        </Badge>
                                                                        <div className="text-xs text-gray-500 mt-1">
                                                                            Importance
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <Progress 
                                                                    value={weight.weight} 
                                                                    className="h-2 bg-gray-200" 
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="text-center py-8 text-muted-foreground">
                                                <p>Internship criteria found but missing category information.</p>
                                                <p className="text-sm">Debug: {JSON.stringify(Object.keys(criteriaByCategory))}</p>
                                                <p className="text-sm">Please check the internship criteria data structure.</p>
                                            </div>
                                        )
                                    ) : (
                                        <div className="text-center py-8 text-muted-foreground">
                                            <p>No internship criteria found.</p>
                                            <p className="text-sm">Please ensure the internship has defined criteria weights.</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
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
                                    <div>
                                        <label className="text-sm font-medium text-gray-500">Middle Name</label>
                                        <p className="text-sm">{student.student?.middle_name || 'N/A'}</p>
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
                                                    <span className="text-xs text-gray-500">
                                                        {getScoreLabel(student.best_match.compatibility_score)}
                                                    </span>
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
