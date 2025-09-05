import { Button } from '@/components/ui/button';
import { useFormContext } from 'react-hook-form';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

type Props = {
    isSubmitting: boolean;
    categories?: any[];
};

export default function ReviewAndSubmit({ isSubmitting, categories = [] }: Props) {
    const { watch } = useFormContext();
    const formData = watch();

    // Calculate category totals for the summary
    const calculateCategoryTotal = (categoryId: number) => {
        const category = categories.find(cat => cat.id === categoryId);
        if (!category || !category.subCategories) return 0;
        
        return category.subCategories.reduce((sum: number, subcat: any) => {
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
            <h2 className="text-xl font-semibold">Review and Submit</h2>
            
            {/* Basic Information Summary */}
            <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <h3 className="font-medium mb-4">Basic Information</h3>
                <div className="space-y-3 text-sm">
                    <div><strong>Company:</strong> {formData.companyName}</div>
                    <div><strong>Contact Person:</strong> {formData.contactPerson}</div>
                    <div><strong>Email:</strong> {formData.email}</div>
                    <div><strong>Phone:</strong> {formData.phone}</div>
                    <div><strong>Address:</strong> {formData.address}</div>
                </div>
            </div>

            {/* Internship Details Summary */}
            <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                <h3 className="font-medium mb-4">Internship Details</h3>
                <div className="space-y-3 text-sm">
                    <div><strong>Position:</strong> {formData.position}</div>
                    <div><strong>Department:</strong> {formData.department}</div>
                    <div><strong>Duration:</strong> {formData.duration}</div>
                    <div><strong>Number of Interns:</strong> {formData.numberOfInterns}</div>
                    <div><strong>Start Date:</strong> {formData.startDate}</div>
                    <div><strong>End Date:</strong> {formData.endDate}</div>
                </div>
            </div>

            {/* Enhanced Assessment Criteria Summary */}
            {categories.length > 0 && (
                <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h3 className="font-medium mb-4">Assessment Criteria & Weight Allocation</h3>
                    
                    {/* Overall Weight Summary */}
                    <div className="mb-6 p-4 bg-white rounded-lg border">
                        <h4 className="font-medium text-gray-900 mb-3">Overall Weight Distribution</h4>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {categories.map((category: any) => {
                                const categoryTotal = calculateCategoryTotal(category.id);
                                const weightStatus = getWeightStatus(categoryTotal);
                                
                                return (
                                    <div key={category.id} className={`p-3 rounded-lg border ${weightStatus.bgColor} ${weightStatus.borderColor}`}>
                                        <div className="text-center">
                                            <div className={`text-lg font-bold ${weightStatus.color}`}>
                                                {categoryTotal}%
                                            </div>
                                            <div className="text-sm text-gray-600">{category.category_name}</div>
                                            <Badge 
                                                variant={weightStatus.status === 'valid' ? 'default' : weightStatus.status === 'exceeded' ? 'destructive' : 'secondary'}
                                                className="mt-2"
                                            >
                                                {weightStatus.status === 'valid' ? 'Complete' : 
                                                 weightStatus.status === 'exceeded' ? 'Exceeded' : 'Incomplete'}
                                            </Badge>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Detailed Category Breakdown */}
                    <div className="space-y-4">
                        {categories.map((category: any) => {
                            const categoryTotal = calculateCategoryTotal(category.id);
                            const weightStatus = getWeightStatus(categoryTotal);
                            
                            return (
                                <Card key={category.id} className={`border-2 ${weightStatus.borderColor}`}>
                                    <CardHeader className={`${weightStatus.bgColor}`}>
                                        <CardTitle className="flex items-center justify-between">
                                            <span className="text-lg">{category.category_name}</span>
                                            <div className="flex items-center gap-2">
                                                <span className={`text-sm font-medium ${weightStatus.color}`}>
                                                    Total: {categoryTotal}%
                                                </span>
                                                <Badge 
                                                    variant={weightStatus.status === 'valid' ? 'default' : weightStatus.status === 'exceeded' ? 'destructive' : 'secondary'}
                                                >
                                                    {weightStatus.status === 'valid' ? '✓' : 
                                                     weightStatus.status === 'exceeded' ? '✗' : '!'}
                                                </Badge>
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    
                                    <CardContent className="p-4">
                                        {/* Weight Progress Bar */}
                                        <div className="mb-4">
                                            <div className="flex items-center justify-between text-sm text-gray-600 mb-2">
                                                <span>Weight Distribution</span>
                                                <span>{categoryTotal}/100%</span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-3">
                                                <div 
                                                    className={`h-3 rounded-full transition-all duration-300 ${
                                                        weightStatus.status === 'valid' ? 'bg-green-500' : 
                                                        weightStatus.status === 'exceeded' ? 'bg-red-500' : 'bg-yellow-500'
                                                    }`}
                                                    style={{ width: `${Math.min(categoryTotal, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>

                                        {/* Subcategory Details */}
                                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                            {category.subCategories?.map((subcat: any) => {
                                                const weight = getSubcategoryWeight(subcat.id);
                                                const questionCount = subcat.questions ? subcat.questions.length : 0;
                                                
                                                return (
                                                    <div key={subcat.id} className="bg-gray-50 p-3 rounded-lg border">
                                                        <div className="text-center space-y-2">
                                                            <div className="text-xl font-bold text-blue-600">
                                                                {weight}%
                                                            </div>
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {subcat.subcategory_name}
                                                            </div>
                                                            <div className="text-xs text-gray-600">
                                                                {questionCount} question{questionCount !== 1 ? 's' : ''}
                                                            </div>
                                                            {/* Weight Bar */}
                                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                                <div 
                                                                    className="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                                                    style={{ width: `${weight}%` }}
                                                                ></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>

                                        {/* Category Summary */}
                                        <div className="mt-4 p-3 bg-gray-100 rounded-lg">
                                            <div className="text-sm text-gray-700">
                                                <strong>Summary:</strong> {category.subCategories?.length || 0} subcategories with a total weight allocation of {categoryTotal}%
                                                {weightStatus.status === 'valid' && ' ✓ All weights properly distributed'}
                                                {weightStatus.status === 'exceeded' && ' ⚠️ Weights exceed 100% - please adjust'}
                                                {weightStatus.status === 'incomplete' && ' ⚠️ Weights do not total 100% - please complete'}
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    {/* Final Validation Message */}
                    <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 className="font-medium text-blue-900 mb-2">Weight Allocation Validation</h4>
                        <div className="text-sm text-blue-800">
                            {categories.every(cat => calculateCategoryTotal(cat.id) === 100) ? (
                                <div className="flex items-center gap-2 text-green-700">
                                    <span>✓</span>
                                    <span>All categories have proper weight distribution (100% each)</span>
                                </div>
                            ) : (
                                <div className="space-y-2">
                                    <div className="flex items-center gap-2 text-red-700">
                                        <span>⚠️</span>
                                        <span>Some categories need weight adjustment before submission</span>
                                    </div>
                                    <ul className="ml-6 list-disc space-y-1">
                                        {categories.map((cat: any) => {
                                            const total = calculateCategoryTotal(cat.id);
                                            if (total !== 100) {
                                                return (
                                                    <li key={cat.id} className="text-red-600">
                                                        {cat.category_name}: {total}% (needs {100 - total}% more)
                                                    </li>
                                                );
                                            }
                                            return null;
                                        })}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            <Button type="submit" disabled={isSubmitting} className="w-full">
                {isSubmitting ? 'Submitting...' : 'Submit HTE Form'}
            </Button>
        </div>
    );
}
