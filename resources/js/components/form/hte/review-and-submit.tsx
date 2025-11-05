import { useFormContext } from 'react-hook-form';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Category {
    id: number;
    category_name: string;
    subCategories?: Array<{
        id: number;
        subcategory_name: string;
        questions?: Array<{
            id: number;
            question: string;
            is_active: boolean;
        }>;
    }>;
}

type Props = {
    categories?: Category[];
};

const LIKERT_LABELS = [
    { value: 1, label: 'Not Important', description: 'Poor (<75%)' },
    { value: 2, label: 'Somewhat Important', description: 'Fair (75-79%)' },
    { value: 3, label: 'Important', description: 'Good (80-89%)' },
    { value: 4, label: 'Very Important', description: 'Very Good (90-95%)' },
    { value: 5, label: 'Most Important', description: 'Excellent (96-100%)' },
];

/**
 * Calculate subcategory percentage from question ratings
 */
function calculateSubcategoryPercentage(questionRatings: number[], questionCount: number): number {
    if (questionCount === 0) return 0;
    const sumOfRatings = questionRatings.reduce((sum, rating) => sum + rating, 0);
    const maxPossibleScore = questionCount * 5;
    return Math.round((sumOfRatings / maxPossibleScore) * 100 * 100) / 100;
}

/**
 * Map percentage to descriptive rating
 */
function getDescriptiveRating(percentage: number): string {
    if (percentage >= 96) return 'Excellent';
    if (percentage >= 90) return 'Very Good';
    if (percentage >= 80) return 'Good';
    if (percentage >= 75) return 'Fair';
    return 'Poor';
}

export default function ReviewAndSubmit({ categories = [] }: Props) {
    const { watch } = useFormContext();
    const formData = watch();
    const questionRatings = formData.questionRatings || {};

    // Calculate subcategory percentage
    const getSubcategoryPercentage = (subcategory: { questions?: Array<{ id: number }> }) => {
        if (!subcategory.questions || subcategory.questions.length === 0) return 0;
        
        const ratings = subcategory.questions
            .map(q => questionRatings[q.id])
            .filter(r => r !== undefined && r !== null && r >= 1 && r <= 5) as number[];

        if (ratings.length === 0) return 0;
        
        return calculateSubcategoryPercentage(ratings, subcategory.questions.length);
    };

    // Calculate category percentage
    const getCategoryPercentage = (category: Category) => {
        let allRatings: number[] = [];
        let totalQuestions = 0;

        category.subCategories?.forEach(subcat => {
            if (subcat.questions && subcat.questions.length > 0) {
                const ratings = subcat.questions
                    .map(q => questionRatings[q.id])
                    .filter(r => r !== undefined && r !== null && r >= 1 && r <= 5) as number[];
                allRatings.push(...ratings);
                totalQuestions += subcat.questions.length;
            }
        });

        if (allRatings.length === 0 || totalQuestions === 0) return 0;
        
        return calculateSubcategoryPercentage(allRatings, totalQuestions);
    };

    // Get rating status color
    const getRatingStatusColor = (percentage: number) => {
        if (percentage >= 96) return { text: 'text-green-600 dark:text-green-400', bg: 'bg-green-50 dark:bg-green-950/30', border: 'border-green-200 dark:border-green-800' };
        if (percentage >= 90) return { text: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-50 dark:bg-blue-950/30', border: 'border-blue-200 dark:border-blue-800' };
        if (percentage >= 80) return { text: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-50 dark:bg-yellow-950/30', border: 'border-yellow-200 dark:border-yellow-800' };
        if (percentage >= 75) return { text: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-50 dark:bg-orange-950/30', border: 'border-orange-200 dark:border-orange-800' };
        return { text: 'text-red-600 dark:text-red-400', bg: 'bg-red-50 dark:bg-red-950/30', border: 'border-red-200 dark:border-red-800' };
    };

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
                                <span className="text-sm font-medium text-muted-foreground">Number of Interns</span>
                                <p className="text-sm text-foreground">{formData.numberOfInterns}</p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            {/* Assessment Criteria Summary */}
            {categories.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Assessment Criteria & Question Ratings</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        <div className="space-y-4">
                            {categories.map((category: Category) => {
                                const categoryPercentage = getCategoryPercentage(category);
                                const categoryRating = getDescriptiveRating(categoryPercentage);
                                const statusColors = getRatingStatusColor(categoryPercentage);
                                
                                return (
                                    <div key={category.id} className={`rounded-lg border-2 ${statusColors.border} ${statusColors.bg} p-4 transition-all duration-200`}>
                                        <div className="flex items-center justify-between mb-4">
                                            <h4 className="text-lg font-medium text-foreground">{category.category_name}</h4>
                                            <Badge variant="secondary" className="text-xs">
                                                Passing Threshold: {categoryPercentage > 0 ? `${categoryPercentage}%` : 'Not Rated'}
                                            </Badge>
                                        </div>
                                        
                                        {/* Category Summary */}
                                        <div className="mb-4">
                                            <div className="flex items-center justify-between text-xs text-muted-foreground mb-2">
                                                <span>Category Passing Threshold</span>
                                                <span className={`font-semibold ${statusColors.text}`}>
                                                    {categoryPercentage > 0 ? `${categoryPercentage}%` : 'Not Rated'} ({categoryRating})
                                                </span>
                                            </div>
                                            <div className="w-full bg-muted rounded-full h-2">
                                                <div 
                                                    className={`h-2 rounded-full transition-all duration-300 ${
                                                        categoryPercentage >= 96 ? 'bg-green-500' : 
                                                        categoryPercentage >= 90 ? 'bg-blue-500' :
                                                        categoryPercentage >= 80 ? 'bg-yellow-500' :
                                                        categoryPercentage >= 75 ? 'bg-orange-500' : 'bg-red-500'
                                                    }`}
                                                    style={{ width: `${Math.min(categoryPercentage, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Subcategory Details */}
                                        <div className="space-y-3">
                                            {category.subCategories?.map((subcat) => {
                                                const subcategoryPercentage = getSubcategoryPercentage(subcat);
                                                const subcategoryRating = getDescriptiveRating(subcategoryPercentage);
                                                const subStatusColors = getRatingStatusColor(subcategoryPercentage);
                                                const questionCount = subcat.questions ? subcat.questions.length : 0;
                                                
                                                return (
                                                    <div key={subcat.id} className="bg-background/50 p-3 rounded-lg border border-border/50">
                                                        <div className="flex items-center justify-between mb-2">
                                                            <div className="font-medium text-sm">{subcat.subcategory_name}</div>
                                                            <div className={`text-sm font-semibold ${subStatusColors.text}`}>
                                                                {subcategoryPercentage > 0 ? `${subcategoryPercentage}%` : 'Not Rated'} ({subcategoryRating})
                                                            </div>
                                                        </div>
                                                        <div className="text-xs text-muted-foreground mb-2">
                                                            {questionCount} question{questionCount !== 1 ? 's' : ''}
                                                        </div>
                                                        {/* Percentage Bar */}
                                                        <div className="w-full bg-muted rounded-full h-1.5">
                                                            <div 
                                                                className={`h-1.5 rounded-full transition-all duration-300 ${
                                                                    subcategoryPercentage >= 96 ? 'bg-green-500' : 
                                                                    subcategoryPercentage >= 90 ? 'bg-blue-500' :
                                                                    subcategoryPercentage >= 80 ? 'bg-yellow-500' :
                                                                    subcategoryPercentage >= 75 ? 'bg-orange-500' : 'bg-red-500'
                                                                }`}
                                                                style={{ width: `${subcategoryPercentage}%` }}
                                                            ></div>
                                                        </div>
                                                        {/* Question Ratings Summary */}
                                                        {subcat.questions && subcat.questions.length > 0 && (
                                                            <div className="mt-2 text-xs text-muted-foreground">
                                                                Question Ratings: {subcat.questions.map((q, idx) => {
                                                                    const rating = questionRatings[q.id];
                                                                    return idx === 0 ? rating || '?' : `, ${rating || '?'}`;
                                                                }).join('')}
                                                            </div>
                                                        )}
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
