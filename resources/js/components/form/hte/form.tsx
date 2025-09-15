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
    const { flash } = usePage<{ flash: { success?: string; error?: string; warning?: string } }>().props;
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
            const processedData = data.filter((category: { subCategories: Array<unknown> }) => {
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
        
        // Debug: Log the form data being sent
        console.log('HTE Form Submission - Form Data:', values);
        console.log('Subcategory Weights:', values.subcategoryWeights);
        console.log('Subcategory Weights Count:', Object.keys(values.subcategoryWeights || {}).length);
        
        // Validate that all subcategory weights are properly set
        const weights = values.subcategoryWeights || {};
        const weightKeys = Object.keys(weights);
        
        if (weightKeys.length === 0) {
            alert('Please set subcategory weights before submitting the form.');
            setIsSubmitting(false);
            return;
        }
        
        // Check if any weights are missing or invalid
        const missingWeights = weightKeys.filter(key => 
            weights[key] === undefined || weights[key] === null || weights[key] < 0
        );
        
        if (missingWeights.length > 0) {
            alert('Some subcategory weights are missing or invalid. Please check all weight fields.');
            setIsSubmitting(false);
            return;
        }
        
        // Keep weights as numbers for form submission
        const formData = {
            ...values,
        };
        
        console.log('Proceeding with form submission...');
        
        router.post('/hte/submit', formData, {
            onSuccess: (page) => {
                console.log('Form submission successful:', page);
                setIsSubmitted(true);
                setIsSubmitting(false);
            },
            onError: (errors) => {
                console.error('Form submission failed:', errors);
                setIsSubmitting(false);
                // Show error message to user
                if (errors && typeof errors === 'object') {
                    const errorMessages = Object.values(errors).flat();
                    alert('Form submission failed: ' + errorMessages.join(', '));
                } else {
                    alert('Form submission failed. Please try again.');
                }
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
            case 2: { // Criteria
                fieldsToValidate = ['subcategoryWeights'];
                
                // Additional validation for criteria step
                const weights = form.watch('subcategoryWeights') || {};
                const weightKeys = Object.keys(weights);
                
                if (weightKeys.length === 0) {
                    alert('Please set subcategory weights before proceeding. Click "Redistribute Weights Evenly" if needed.');
                    return;
                }
                
                // Check if any weights are missing
                const missingWeights = weightKeys.filter(key => {
                    const weight = weights[key];
                    return weight === undefined || weight === null || weight < 0;
                });
                
                if (missingWeights.length > 0) {
                    alert('Some subcategory weights are missing. Please ensure all subcategories have weights assigned.');
                    return;
                }
                break;
            }
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
                                <div className="pt-4">
                                    <Button 
                                        onClick={() => router.visit('/hte/dashboard')}
                                        className="bg-blue-600 hover:bg-blue-700"
                                    >
                                        Go to Dashboard
                                    </Button>
                                </div>
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
            
            {/* Warning Message */}
            {flash?.warning && (
                <div className="mb-4 rounded-md bg-yellow-50 border border-yellow-200 p-4">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm text-yellow-800">
                                {flash.warning}
                            </p>
                        </div>
                    </div>
                </div>
            )}
            
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
