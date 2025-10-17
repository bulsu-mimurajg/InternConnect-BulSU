import { useFormContext } from 'react-hook-form';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Category {
    id: number;
    category_name: string;
    subCategories?: Array<{
        id: number;
        subcategory_name: string;
        questions?: Array<unknown>;
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

    // Get validation status for weight totals
    const getWeightStatus = (total: number) => {
        if (total === 100) return { status: 'valid', color: 'text-green-600', bgColor: 'bg-green-50', borderColor: 'border-green-200' };
        if (total > 100) return { status: 'exceeded', color: 'text-red-600', bgColor: 'bg-red-50', borderColor: 'border-red-200' };
        return { status: 'incomplete', color: 'text-yellow-600', bgColor: 'bg-yellow-50', borderColor: 'border-yellow-200' };
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

            {/* Assessment Criteria Summary */}
            {categories.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-lg">Assessment Criteria & Weight Allocation</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-6">
                        {/* Detailed Category Breakdown */}
                        <div className="space-y-4">
                            {categories.map((category: Category) => {
                                const categoryTotal = calculateCategoryTotal(category.id);
                                const weightStatus = getWeightStatus(categoryTotal);
                                
                                return (
                                    <div key={category.id} className={`rounded-lg border-2 ${weightStatus.borderColor} ${weightStatus.bgColor} p-4 transition-all duration-200`}>
                                        <div className="flex items-center justify-between mb-4">
                                            <h4 className="text-lg font-medium text-foreground">{category.category_name}</h4>
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
                                                        weightStatus.status === 'exceeded' ? 'bg-red-500' : 'bg-yellow-500'
                                                    }`}
                                                    style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Subcategory Details */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            {category.subCategories?.map((subcat: { id: number; subcategory_name: string; questions?: Array<unknown> }) => {
                                                const weight = getSubcategoryWeight(subcat.id);
                                                const questionCount = subcat.questions ? subcat.questions.length : 0;
                                                
                                                return (
                                                    <div key={subcat.id} className="bg-background/50 p-3 rounded-lg border border-border/50">
                                                        <div className="text-center space-y-2">
                                                            <div className="text-lg font-bold text-primary">
                                                                {weight}%
                                                            </div>
                                                            <div className="text-sm font-medium text-foreground">
                                                                {subcat.subcategory_name}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {questionCount} question{questionCount !== 1 ? 's' : ''}
                                                            </div>
                                                            {/* Weight Bar */}
                                                            <div className="w-full bg-muted rounded-full h-1.5">
                                                                <div 
                                                                    className="bg-primary h-1.5 rounded-full transition-all duration-300"
                                                                    style={{ width: `${weight}%` }}
                                                                ></div>
                                                            </div>
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
