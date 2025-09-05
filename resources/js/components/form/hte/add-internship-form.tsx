import FormStepCounter from '@/components/form/form-step-counter';
import InternshipOffered from '@/components/form/hte/internship-offered';
import Criteria from '@/components/form/hte/criteria';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState, useEffect, useCallback } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

// Form validation schema for internship only
const FormSchema = z.object({
    // Internship Offered
    position: z.string().min(1, 'Position is required'),
    department: z.string().min(1, 'Department is required'),
    numberOfInterns: z.string().min(1, 'Number of interns is required'),
    duration: z.string().min(1, 'Duration is required'),
    startDate: z.string().min(1, 'Start date is required'),
    endDate: z.string().min(1, 'End date is required'),

    
    // Weights
    subcategoryWeights: z.record(z.string(), z.number().min(0).max(100)),
});

type FormData = z.infer<typeof FormSchema>;

// Types for categories data
interface Category {
    id: number;
    category_name: string;
    subCategories: SubCategory[];
}

interface SubCategory {
    id: number;
    subcategory_name: string;
    questions: Question[];
}

interface Question {
    id: number;
    question: string;
    access: string;
    is_active: boolean;
}

interface AddInternshipFormProps {
    hte: {
        id: number;
        company_name: string;
        company_address: string;
        company_email: string;
        cperson_fname: string;
        cperson_lname: string;
        cperson_position: string;
        cperson_contactnum: string;
        is_active: boolean;
        created_at: string;
    };
    categories: Category[];
}

interface PageProps {
    props: AddInternshipFormProps;
    [key: string]: any;
}

export default function AddInternshipForm() {
    const { hte, categories } = usePage<PageProps>().props;
    const typedCategories = categories as Category[];
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    // State for managing form steps and categories
    const [expandedCategories, setExpandedCategories] = useState<Set<number>>(new Set());
    const [expandedSubcategories, setExpandedSubcategories] = useState<Set<number>>(new Set());
    const [expandedQuestions, setExpandedQuestions] = useState<Set<number>>(new Set());

    const steps = [
        { id: 'Step 1', name: 'Internship Information' },
        { id: 'Step 2', name: 'Criteria' },
        { id: 'Step 3', name: 'Review & Submit' },
    ];

    const form = useForm<FormData>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {
            position: '',
            department: '',
            numberOfInterns: '',
            duration: '',
            startDate: '',
            endDate: '',

            subcategoryWeights: {},
        },
    });

    // Initialize equal weights for all subcategories
    useEffect(() => {
        if (typedCategories && typedCategories.length > 0) {
            typedCategories.forEach((category: Category) => {
                if (category.subCategories && category.subCategories.length > 0) {
                    const equalWeight = Math.round(100 / category.subCategories.length);
                    const remainder = 100 % category.subCategories.length;
                    
                    category.subCategories.forEach((subcat: SubCategory, index: number) => {
                        const weight = index < remainder ? equalWeight + 1 : equalWeight;
                        form.setValue(`subcategoryWeights.${subcat.id}`, weight);
                    });
                }
            });
        }
    }, [typedCategories, form]);

    function onSubmit(values: FormData) {
        setIsSubmitting(true);
        
        // Debug: Log the form data being sent
        console.log('Add Internship Form Submission - Form Data:', values);
        console.log('Subcategory Weights:', values.subcategoryWeights);
        
        router.post('/hte/add-internship', values, {
            onSuccess: () => {
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            }
        });
    }

    const [currentStep, setCurrentStep] = useState(0);

    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const next = async () => {
        let fieldsToValidate: Path<FormData>[] = [];
        
        switch (currentStep) {
            case 0: // Internship Information
                fieldsToValidate = ['position', 'department', 'numberOfInterns', 'duration', 'startDate', 'endDate'];
                break;
            case 1: // Criteria
                fieldsToValidate = ['subcategoryWeights'];
                break;
        }

        if (fieldsToValidate.length > 0) {
            const isValid = await form.trigger(fieldsToValidate, { shouldFocus: true });
            if (isValid) {
                setCurrentStep((prev) => prev + 1);
            }
        } else {
            setCurrentStep((prev) => prev + 1);
        }
    };

    const calculateCategoryTotal = (categoryId: number) => {
        const category = typedCategories.find(c => c.id === categoryId);
        if (!category) return 0;

        return category.subCategories.reduce((sum, subcat) => {
            const weight = form.watch(`subcategoryWeights.${subcat.id}`);
            return sum + weight;
        }, 0);
    };

    const getWeightStatus = (categoryTotal: number) => {
        if (categoryTotal === 100) {
            return { status: 'valid', bgColor: 'bg-green-50', borderColor: 'border-green-200', color: 'text-green-600' };
        } else if (categoryTotal > 100) {
            return { status: 'exceeded', bgColor: 'bg-red-50', borderColor: 'border-red-200', color: 'text-red-600' };
        } else {
            return { status: 'incomplete', bgColor: 'bg-yellow-50', borderColor: 'border-yellow-200', color: 'text-yellow-600' };
        }
    };

    const getSubcategoryWeight = (subcatId: number) => {
        return form.watch(`subcategoryWeights.${subcatId}`);
    };

    return (
        <>
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={currentStep} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="p-4">
                    <div className="">
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)}>
                                {currentStep === 0 && <InternshipOffered />}
                                {currentStep === 1 && (
                                    <Criteria 
                                        categories={typedCategories}
                                        loading={false}
                                        expandedCategories={expandedCategories}
                                        expandedSubcategories={expandedSubcategories}
                                        expandedQuestions={expandedQuestions}
                                        setExpandedCategories={setExpandedCategories}
                                        setExpandedSubcategories={setExpandedSubcategories}
                                        setExpandedQuestions={setExpandedQuestions}
                                    />
                                )}
                                {currentStep === 2 && (
                                    <div className="space-y-6">
                                        <div className="text-center space-y-4">
                                            <h2 className="text-2xl font-semibold">Review and Submit</h2>
                                            <p className="text-muted-foreground">
                                                Please review your internship details and criteria weights before submitting.
                                            </p>
                                        </div>
                                        
                                        {/* Internship Details Summary */}
                                        <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                            <h3 className="font-medium mb-4">Internship Details</h3>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-sm font-medium">Position</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('position')}</p>
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">Department</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('department')}</p>
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">Number of Interns</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('numberOfInterns')}</p>
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">Duration</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('duration')}</p>
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">Start Date</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('startDate')}</p>
                                                </div>
                                                <div>
                                                    <label className="text-sm font-medium">End Date</label>
                                                    <p className="text-sm text-muted-foreground">{form.watch('endDate')}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Enhanced Assessment Criteria Summary */}
                                        {typedCategories.length > 0 && (
                                            <div className="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                                <h3 className="font-medium mb-4">Assessment Criteria & Weight Allocation</h3>
                                                
                                                {/* Overall Weight Summary */}
                                                <div className="mb-6 p-4 bg-white rounded-lg border">
                                                    <h4 className="font-medium text-gray-900 mb-3">Overall Weight Distribution</h4>
                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        {typedCategories.map((category: Category) => {
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
                                                    {typedCategories.map((category: Category) => {
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
                                                                        {category.subCategories?.map((subcat: SubCategory) => {
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
                                                        {typedCategories.every(cat => calculateCategoryTotal(cat.id) === 100) ? (
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
                                                                    {typedCategories.map((cat: Category) => {
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
                                        
                                        <Button 
                                            type="submit" 
                                            disabled={isSubmitting}
                                            className="w-full"
                                        >
                                            {isSubmitting ? 'Submitting...' : 'Submit Internship'}
                                        </Button>
                                    </div>
                                )}
                            </form>
                        </Form>
                    </div>
                    <div className="mt-4 flex justify-between items-center">
                        <div className="text-sm text-gray-600">
                            {currentStep < steps.length - 1 && (
                                <span>Please complete all required fields before proceeding</span>
                            )}
                        </div>
                        <div className="flex gap-2">
                            <Button onClick={prev} disabled={currentStep === 0} variant="outline">
                                Previous
                            </Button>
                            <Button onClick={next} disabled={currentStep === steps.length - 1}>
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
