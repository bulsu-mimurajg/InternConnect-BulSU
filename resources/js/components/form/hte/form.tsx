import FormStepCounter from '@/components/form/form-step-counter';
import BasicInformation from '@/components/form/hte/basic-information';
import Criteria from '@/components/form/hte/criteria';
import InternshipOffered from '@/components/form/hte/internship-offered';
import ReviewAndSubmit from '@/components/form/hte/review-and-submit';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState, useEffect, useCallback } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router, usePage } from '@inertiajs/react';

// Form validation schema
const FormSchema = z.object({
    // Basic Information
    companyName: z.string().min(1, 'Company name is required'),
    contactPerson: z.string().min(1, 'Contact person is required'),
    email: z.string().email('Valid email is required'),
    phone: z.string().min(1, 'Phone number is required'),
    address: z.string().min(1, 'Address is required'),
    
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

export default function HTEForm() {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    // Lift categories data and state to parent component
    const [categories, setCategories] = useState<Category[]>([]);
    const [expandedCategories, setExpandedCategories] = useState<Set<number>>(new Set());
    const [expandedSubcategories, setExpandedSubcategories] = useState<Set<number>>(new Set());
    const [expandedQuestions, setExpandedQuestions] = useState<Set<number>>(new Set());
    const [categoriesLoading, setCategoriesLoading] = useState(true);
    const [dataFetched, setDataFetched] = useState(false);

    const steps = [
        { id: 'Step 1', name: 'Basic Information' },
        { id: 'Step 2', name: 'Internship Offered' },
        { id: 'Step 3', name: 'Criteria' },
        { id: 'Step 4', name: 'Submission' },
    ];

    const form = useForm<FormData>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {
            companyName: '',
            contactPerson: '',
            email: '',
            phone: '',
            address: '',
            position: '',
            department: '',
            numberOfInterns: '',
            duration: '',
            startDate: '',
            endDate: '',
            subcategoryWeights: {},
        },
    });

    // Fetch categories data once when component mounts
    const fetchCategories = useCallback(async () => {
        if (dataFetched) return;
        
        try {
            const response = await fetch('/hte/categories', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Categories data received:', data);
            
            // Filter out categories without subcategories
            const processedData = data.filter((category: any) => {
                const hasSubCategories = category.subCategories && category.subCategories.length > 0;
                return hasSubCategories;
            });
            
            setCategories(processedData);
            
            // Initialize equal weights for all subcategories
            processedData.forEach((category: Category) => {
                if (category.subCategories && category.subCategories.length > 0) {
                    const equalWeight = Math.round(100 / category.subCategories.length);
                    const remainder = 100 % category.subCategories.length;
                    
                    category.subCategories.forEach((subcat: SubCategory, index: number) => {
                        const weight = index < remainder ? equalWeight + 1 : equalWeight;
                        form.setValue(`subcategoryWeights.${subcat.id}`, weight);
                    });
                }
            });
            
            setDataFetched(true);
            setCategoriesLoading(false);
        } catch (error) {
            console.error('Error fetching categories:', error);
            setCategoriesLoading(false);
        }
    }, [dataFetched, form]);

    useEffect(() => {
        fetchCategories();
    }, [fetchCategories]);

    // Check for success message on mount
    useEffect(() => {
        if (flash?.success) {
            setIsSubmitted(true);
        }
    }, [flash?.success]);

    function onSubmit(values: FormData) {
        setIsSubmitting(true);
        
        // Keep weights as numbers for form submission
        const formData = {
            ...values,
        };
        
        // Debug: Log the form data being sent
        console.log('HTE Form Submission - Form Data:', formData);
        console.log('Subcategory Weights:', formData.subcategoryWeights);
        console.log('Subcategory Weights Count:', Object.keys(formData.subcategoryWeights).length);
        
        router.post('/hte/submit', formData, {
            onSuccess: () => {
                setIsSubmitted(true);
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
            case 0: // Basic Information
                fieldsToValidate = ['companyName', 'contactPerson', 'email', 'phone', 'address'];
                break;
            case 1: // Internship Offered
                fieldsToValidate = ['position', 'department', 'numberOfInterns', 'duration', 'startDate', 'endDate'];
                break;
            case 2: // Criteria
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

    // If form is submitted, show success state
    if (isSubmitted) {
        return (
            <>
                <div className="flex justify-center overflow-y-auto">
                    <FormStepCounter steps={steps} currentStep={steps.length - 1} />
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="p-4">
                        <div className="text-center space-y-6">
                            <div className="space-y-4">
                                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                    <svg className="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <h2 className="text-2xl font-semibold text-gray-900 dark:text-white">HTE Form Submitted Successfully!</h2>
                                <p className="text-gray-600 dark:text-gray-400">
                                    Thank you for submitting your HTE form. Your internship opportunity has been recorded and will be available for student matching.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </>
        );
    }

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
                                {currentStep === 0 && <BasicInformation />}
                                {currentStep === 1 && <InternshipOffered />}
                                {currentStep === 2 && (
                                    <Criteria 
                                        categories={categories}
                                        loading={categoriesLoading}
                                        expandedCategories={expandedCategories}
                                        expandedSubcategories={expandedSubcategories}
                                        expandedQuestions={expandedQuestions}
                                        setExpandedCategories={setExpandedCategories}
                                        setExpandedSubcategories={setExpandedSubcategories}
                                        setExpandedQuestions={setExpandedQuestions}
                                    />
                                )}
                                {currentStep === 3 && <ReviewAndSubmit isSubmitting={isSubmitting} categories={categories} />}
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
