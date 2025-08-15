import { Button } from '@/components/ui/button';
import { useFormContext } from 'react-hook-form';

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

            {/* Assessment Criteria Summary */}
            {categories.length > 0 && (
                <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <h3 className="font-medium mb-4">Assessment Criteria Summary</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {categories.map((category: any) => {
                            const categoryTotal = calculateCategoryTotal(category.id);
                            const subcategoryCount = category.subCategories ? category.subCategories.length : 0;
                            const totalQuestions = category.subCategories ? 
                                category.subCategories.reduce((sum: number, subcat: any) => sum + (subcat.questions ? subcat.questions.length : 0), 0) : 0;
                            
                            return (
                                <div key={category.id} className="bg-white p-4 rounded-lg border">
                                    <h4 className="font-medium text-gray-900 mb-2">{category.category_name}</h4>
                                    <div className="space-y-1 text-sm text-gray-600">
                                        <div>Subcategories: {subcategoryCount}</div>
                                        <div>Total Questions: {totalQuestions}</div>
                                        <div className={`font-medium ${
                                            categoryTotal === 100 ? 'text-green-600' : 
                                            categoryTotal > 100 ? 'text-red-600' : 'text-yellow-600'
                                        }`}>
                                            Weight Total: {categoryTotal}%
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}

            <Button type="submit" disabled={isSubmitting} className="w-full">
                {isSubmitting ? 'Submitting...' : 'Submit HTE Form'}
            </Button>
        </div>
    );
}
