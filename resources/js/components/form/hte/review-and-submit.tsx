import { useFormContext } from 'react-hook-form';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Category {
    id: number;
    category_name: string;
    category_type: 'technical' | 'soft_skills';
    subCategories?: Array<{
        id: number;
        subcategory_name: string;
        questions?: Array<{ id: number }>;
    }>;
}

type Props = {
    categories?: Category[];
};

export default function ReviewAndSubmit({ categories = [] }: Props) {
    const { watch } = useFormContext();
    const formData = watch();

    // Calculate category totals for the summary
    const calculateCategoryTotal = (categoryId: number) => {
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return 0;
        
        return category.subCategories.reduce((sum: number, subcat: { id: number }) => {
            const weight = formData.subcategoryWeights?.[subcat.id] || 0;
            return sum + (Number(weight) || 0);
        }, 0);
    };

    // Get weight for a specific subcategory
    const getSubcategoryWeight = (subcategoryId: number) => {
        return formData.subcategoryWeights?.[subcategoryId] || 0;
    };

    // Calculate grade for a specific subcategory based on assessment responses
    const calculateSubcategoryGrade = (subcat: { id: number; subcategory_name: string; questions?: Array<{ id: number }> }) => {
        const questions = subcat.questions || [];
        if (questions.length === 0) return { grade: 0, percentage: 0, sumResponses: 0, totalPossible: 0 };

        let sumResponses = 0;
        let answeredCount = 0;

        questions.forEach((q) => {
            // Assessment responses are stored with 'question_' prefix
            const response = formData.assessmentResponses?.[`question_${q.id}`];
            if (response !== undefined && response !== null) {
                sumResponses += Number(response);
                answeredCount++;
            }
        });

        if (answeredCount === 0) return { grade: 0, percentage: 0, sumResponses: 0, totalPossible: 0 };

        const totalPossible = answeredCount * 5; // Max 5 per question on Likert scale
        const percentage = (sumResponses / totalPossible) * 100;

        // Convert percentage to grade (1-5)
        let grade = 1;
        if (percentage >= 96) grade = 5;
        else if (percentage >= 90) grade = 4;
        else if (percentage >= 80) grade = 3;
        else if (percentage >= 75) grade = 2;

        return { grade, percentage, sumResponses, totalPossible };
    };

    // Calculate overall weighted grade by category type
    const calculateGradeByType = (categoryType: 'technical' | 'soft_skills') => {
        let totalWeightedScore = 0;
        let totalWeight = 0;

        categories
            .filter(cat => cat.category_type === categoryType)
            .forEach(category => {
                category.subCategories?.forEach(subcat => {
                    const weight = getSubcategoryWeight(subcat.id);
                    const { percentage } = calculateSubcategoryGrade(subcat);
                    
                    if (weight > 0) {
                        totalWeightedScore += (percentage * weight) / 100;
                        totalWeight += weight;
                    }
                });
            });

        if (totalWeight === 0) return { grade: 0, percentage: 0 };

        const weightedPercentage = totalWeightedScore;

        // Convert to grade
        let grade = 1;
        if (weightedPercentage >= 96) grade = 5;
        else if (weightedPercentage >= 90) grade = 4;
        else if (weightedPercentage >= 80) grade = 3;
        else if (weightedPercentage >= 75) grade = 2;

        return { grade, percentage: weightedPercentage };
    };

    // Calculate overall weighted grade for the entire assessment
    const calculateOverallGrade = () => {
        let totalWeightedScore = 0;
        let totalWeight = 0;

        categories.forEach(category => {
            category.subCategories?.forEach(subcat => {
                const weight = getSubcategoryWeight(subcat.id);
                const { percentage } = calculateSubcategoryGrade(subcat);
                
                if (weight > 0) {
                    totalWeightedScore += (percentage * weight) / 100;
                    totalWeight += weight;
                }
            });
        });

        if (totalWeight === 0) return { grade: 0, percentage: 0 };

        const weightedPercentage = totalWeightedScore;

        // Convert to grade
        let grade = 1;
        if (weightedPercentage >= 96) grade = 5;
        else if (weightedPercentage >= 90) grade = 4;
        else if (weightedPercentage >= 80) grade = 3;
        else if (weightedPercentage >= 75) grade = 2;

        return { grade, percentage: weightedPercentage };
    };

    // Calculate category grade (average of subcategories in that category)
    const calculateCategoryGrade = (categoryId: number) => {
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return { grade: 0, percentage: 0 };

        let totalPercentage = 0;
        let subcatCount = 0;

        category.subCategories.forEach(subcat => {
            const { percentage } = calculateSubcategoryGrade(subcat);
            if (percentage > 0) {
                totalPercentage += percentage;
                subcatCount++;
            }
        });

        if (subcatCount === 0) return { grade: 0, percentage: 0 };

        const avgPercentage = totalPercentage / subcatCount;

        // Convert to grade
        let grade = 1;
        if (avgPercentage >= 96) grade = 5;
        else if (avgPercentage >= 90) grade = 4;
        else if (avgPercentage >= 80) grade = 3;
        else if (avgPercentage >= 75) grade = 2;

        return { grade, percentage: avgPercentage };
    };

    // Get validation status for weight totals
    const getWeightStatus = (total: number) => {
        if (total === 100) return { status: 'valid', color: 'text-green-600', bgColor: 'bg-green-50 dark:bg-transparent', borderColor: 'border-green-200 dark:border-green-800' };
        if (total > 100) return { status: 'exceeded', color: 'text-red-600', bgColor: 'bg-red-50 dark:bg-transparent', borderColor: 'border-red-200 dark:border-red-800' };
        return { status: 'incomplete', color: 'text-blue-600', bgColor: 'bg-blue-50 dark:bg-transparent', borderColor: 'border-blue-200 dark:border-blue-800' };
    };

    const technicalGrade = calculateGradeByType('technical');
    const softSkillsGrade = calculateGradeByType('soft_skills');
    const technicalCategories = categories.filter(cat => cat.category_type === 'technical');
    const softSkillsCategories = categories.filter(cat => cat.category_type === 'soft_skills');

    return (
        <div className="space-y-6">
            <div className="space-y-2">
                <h2 className="text-xl font-semibold">Review and Submit</h2>
                <p className="text-muted-foreground">Please review your information before submitting the HTE form.</p>
            </div>
            
            {/* Basic Information Summary */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Basic Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Company Name</span>
                                <p className="text-sm text-foreground">{formData.companyName}</p>
                            </div>
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Contact Person</span>
                                <p className="text-sm text-foreground">{formData.contactPerson}</p>
                            </div>
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Email</span>
                                <p className="text-sm text-foreground">{formData.email}</p>
                            </div>
                        </div>
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Phone</span>
                                <p className="text-sm text-foreground">{formData.phone}</p>
                            </div>
                            <div className="md:col-span-1">
                                <span className="text-sm font-medium text-muted-foreground">Address</span>
                                <p className="text-sm text-foreground">{formData.address}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Internship Details Summary */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-lg">Internship Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Position</span>
                                <p className="text-sm text-foreground">{formData.position}</p>
                            </div>
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Department</span>
                                <p className="text-sm text-foreground">{formData.department}</p>
                            </div>
                        </div>
                        <div className="space-y-3">
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Duration</span>
                                <p className="text-sm text-foreground">{formData.duration}</p>
                            </div>
                            <div>
                                <span className="text-sm font-medium text-muted-foreground">Number of Interns</span>
                                <p className="text-sm text-foreground">{formData.numberOfInterns}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Technical Skills Assessment */}
            {technicalCategories.length > 0 && (
                <Card>
                    <CardHeader className="space-y-4">
                        <CardTitle className="text-lg">Technical Skills Assessment</CardTitle>
                        
                        {/* Technical Skills Grade Display */}
                        <div className="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/30 rounded-lg p-6 border-2 border-blue-200 dark:border-blue-800">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-sm font-medium text-muted-foreground mb-1">Technical Skills Grade</h3>
                                    <div className="flex items-baseline gap-3">
                                        <span className="text-5xl font-bold text-blue-600 dark:text-blue-400">
                                            {technicalGrade.grade}
                                        </span>
                                        <span className="text-lg text-muted-foreground">/5</span>
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-2">
                                        Weighted Score: {technicalGrade.percentage.toFixed(2)}%
                                    </p>
                                </div>
                                <Badge 
                                    variant="secondary" 
                                    className="text-lg px-6 py-3 font-bold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300"
                                >
                                    Grade: {technicalGrade.grade}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Detailed Category Breakdown */}
                        <div className="space-y-4">
                            {technicalCategories.map((category: Category) => {
                                const categoryTotal = calculateCategoryTotal(category.id);
                                const weightStatus = getWeightStatus(categoryTotal);
                                const categoryGrade = calculateCategoryGrade(category.id);
                                
                                return (
                                    <div key={category.id} className={`rounded-lg border-2 ${weightStatus.borderColor} ${weightStatus.bgColor} p-4 transition-all duration-200`}>
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="flex items-center gap-3">
                                                <h4 className="text-lg font-medium text-foreground">{category.category_name}</h4>
                                                {categoryGrade.grade > 0 && (
                                                    <Badge variant="secondary" className="font-bold text-base">
                                                        Grade: {categoryGrade.grade}
                                                    </Badge>
                                                )}
                                            </div>
                                            <Badge 
                                                variant={weightStatus.status === 'valid' ? 'default' : weightStatus.status === 'exceeded' ? 'destructive' : 'secondary'}
                                                className="text-xs"
                                            >
                                                {weightStatus.status === 'valid' ? '✓' : 
                                                 weightStatus.status === 'exceeded' ? '✗' : '!'}
                                            </Badge>
                                        </div>
                                        
                                        {/* Weight Progress Bar */}
                                        <div className="mb-4">
                                            <div className="flex items-center justify-between text-xs text-muted-foreground mb-2">
                                                <span>Weight Distribution</span>
                                                <span>{categoryTotal}/100%</span>
                                            </div>
                                            <div className="w-full bg-muted rounded-full h-2">
                                                <div 
                                                    className={`h-2 rounded-full transition-all duration-300 ${
                                                        weightStatus.status === 'valid' ? 'bg-green-500' : 
                                                        weightStatus.status === 'exceeded' ? 'bg-red-500' : 'bg-blue-500'
                                                    }`}
                                                    style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Subcategory Details */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            {category.subCategories?.map((subcat: { id: number; subcategory_name: string; questions?: Array<{ id: number }> }) => {
                                                const weight = getSubcategoryWeight(subcat.id);
                                                const questionCount = subcat.questions ? subcat.questions.length : 0;
                                                const gradeData = calculateSubcategoryGrade(subcat);
                                                
                                                return (
                                                    <div key={subcat.id} className="bg-background/50 p-4 rounded-lg border border-border/50 hover:border-primary/50 transition-all">
                                                        <div className="text-center space-y-3">
                                                            {/* Grade Display - Most Prominent */}
                                                            {gradeData.grade > 0 ? (
                                                                <div className="space-y-1">
                                                                    <div className="flex items-baseline justify-center gap-1">
                                                                        <span className="text-3xl font-bold text-primary">
                                                                            {gradeData.grade}
                                                                        </span>
                                                                        <span className="text-sm text-muted-foreground">/5</span>
                                                                    </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                        {gradeData.percentage.toFixed(1)}%
                                                                    </div>
                                                                </div>
                                                            ) : (
                                                                <div className="text-sm text-muted-foreground">
                                                                    Not answered
                                                                </div>
                                                            )}
                                                            
                                                            <div className="text-sm font-medium text-foreground">
                                                                {subcat.subcategory_name}
                                                            </div>
                                                            
                                                            {/* Weight Info - Secondary */}
                                                            <div className="flex items-center justify-center gap-2 text-xs">
                                                                <Badge variant="outline" className="text-xs">
                                                                    {weight}% weight
                                                                </Badge>
                                                                <span className="text-muted-foreground">
                                                                    {questionCount} Q
                                                                </span>
                                                            </div>
                                                            
                                                            {/* Grade Bar */}
                                                            {gradeData.grade > 0 && (
                                                                <div className="w-full bg-muted rounded-full h-2">
                                                                    <div 
                                                                        className="bg-gradient-to-r from-blue-500 to-indigo-500 h-2 rounded-full transition-all duration-300"
                                                                        style={{ width: `${(gradeData.grade / 5) * 100}%` }}
                                                                    ></div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                    </CardContent>
                </Card>
            )}

            {/* Soft Skills Assessment */}
            {softSkillsCategories.length > 0 && (
                <Card>
                    <CardHeader className="space-y-4">
                        <CardTitle className="text-lg">Soft Skills Assessment</CardTitle>
                        
                        {/* Soft Skills Grade Display */}
                        <div className="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-950/30 dark:to-pink-950/30 rounded-lg p-6 border-2 border-purple-200 dark:border-purple-800">
                            <div className="flex items-center justify-between">
                                <div>
                                    <h3 className="text-sm font-medium text-muted-foreground mb-1">Soft Skills Grade</h3>
                                    <div className="flex items-baseline gap-3">
                                        <span className="text-5xl font-bold text-purple-600 dark:text-purple-400">
                                            {softSkillsGrade.grade}
                                        </span>
                                        <span className="text-lg text-muted-foreground">/5</span>
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-2">
                                        Weighted Score: {softSkillsGrade.percentage.toFixed(2)}%
                                    </p>
                                </div>
                                <Badge 
                                    variant="secondary" 
                                    className="text-lg px-6 py-3 font-bold bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300"
                                >
                                    Grade: {softSkillsGrade.grade}
                                </Badge>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Detailed Category Breakdown */}
                        <div className="space-y-4">
                            {softSkillsCategories.map((category: Category) => {
                                const categoryTotal = calculateCategoryTotal(category.id);
                                const weightStatus = getWeightStatus(categoryTotal);
                                const categoryGrade = calculateCategoryGrade(category.id);
                                
                                return (
                                    <div key={category.id} className={`rounded-lg border-2 ${weightStatus.borderColor} ${weightStatus.bgColor} p-4 transition-all duration-200`}>
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="flex items-center gap-3">
                                                <h4 className="text-lg font-medium text-foreground">{category.category_name}</h4>
                                                {categoryGrade.grade > 0 && (
                                                    <Badge variant="secondary" className="font-bold text-base">
                                                        Grade: {categoryGrade.grade}
                                                    </Badge>
                                                )}
                                            </div>
                                            <Badge 
                                                variant={weightStatus.status === 'valid' ? 'default' : weightStatus.status === 'exceeded' ? 'destructive' : 'secondary'}
                                                className="text-xs"
                                            >
                                                {weightStatus.status === 'valid' ? '✓' : 
                                                 weightStatus.status === 'exceeded' ? '✗' : '!'}
                                            </Badge>
                                        </div>
                                        
                                        {/* Weight Progress Bar */}
                                        <div className="mb-4">
                                            <div className="flex items-center justify-between text-xs text-muted-foreground mb-2">
                                                <span>Weight Distribution</span>
                                                <span>{categoryTotal}/100%</span>
                                            </div>
                                            <div className="w-full bg-muted rounded-full h-2">
                                                <div 
                                                    className={`h-2 rounded-full transition-all duration-300 ${
                                                        weightStatus.status === 'valid' ? 'bg-green-500' : 
                                                        weightStatus.status === 'exceeded' ? 'bg-red-500' : 'bg-blue-500'
                                                    }`}
                                                    style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Subcategory Details */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            {category.subCategories?.map((subcat: { id: number; subcategory_name: string; questions?: Array<{ id: number }> }) => {
                                                const weight = getSubcategoryWeight(subcat.id);
                                                const questionCount = subcat.questions ? subcat.questions.length : 0;
                                                const gradeData = calculateSubcategoryGrade(subcat);
                                                
                                                return (
                                                    <div key={subcat.id} className="bg-background/50 p-4 rounded-lg border border-border/50 hover:border-primary/50 transition-all">
                                                        <div className="text-center space-y-3">
                                                            {/* Grade Display - Most Prominent */}
                                                            {gradeData.grade > 0 ? (
                                                                <div className="space-y-1">
                                                                    <div className="flex items-baseline justify-center gap-1">
                                                                        <span className="text-3xl font-bold text-primary">
                                                                            {gradeData.grade}
                                                                        </span>
                                                                        <span className="text-sm text-muted-foreground">/5</span>
                                                                    </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                        {gradeData.percentage.toFixed(1)}%
                                                                    </div>
                                                                </div>
                                                            ) : (
                                                                <div className="text-sm text-muted-foreground">
                                                                    Not answered
                                                                </div>
                                                            )}
                                                            
                                                            <div className="text-sm font-medium text-foreground">
                                                                {subcat.subcategory_name}
                                                            </div>
                                                            
                                                            {/* Weight Info - Secondary */}
                                                            <div className="flex items-center justify-center gap-2 text-xs">
                                                                <Badge variant="outline" className="text-xs">
                                                                    {weight}% weight
                                                                </Badge>
                                                                <span className="text-muted-foreground">
                                                                    {questionCount} Q
                                                                </span>
                                                            </div>
                                                            
                                                            {/* Grade Bar */}
                                                            {gradeData.grade > 0 && (
                                                                <div className="w-full bg-muted rounded-full h-2">
                                                                    <div 
                                                                        className="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300"
                                                                        style={{ width: `${(gradeData.grade / 5) * 100}%` }}
                                                                    ></div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                    </CardContent>
                </Card>
            )}
        </div>
    );
}
