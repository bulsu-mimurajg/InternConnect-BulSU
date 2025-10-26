import FormStepCounter from '@/components/form/form-step-counter';
import BasicInformation from '@/components/form/hte/basic-information';
import Criteria from '@/components/form/hte/criteria';
import InternshipOffered from '@/components/form/hte/internship-offered';
import ReviewAndSubmit from '@/components/form/hte/review-and-submit';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router, usePage } from '@inertiajs/react';

// Form validation schema
const FormSchema = z.object({
    // Basic Information
    companyName: z.string().min(1, 'Company name is required'),
    contactPerson: z.string().min(1, 'Contact person is required'),
    email: z.string().email('Valid email is required'),
    // Prefer E.164 with +countrycode; length 6-15 digits as per ITU E.164
    phone: z
        .string()
        .min(1, 'Phone number is required')
        .transform((val) => val.replace(/\D/g, ''))
        .refine((digits) => digits.length >= 6 && digits.length <= 15, {
            message: 'Include country code, e.g., +639171234567',
        }),
    address: z.string().min(1, 'Address is required'),
    
    // Internship Offered
    position: z.string().min(1, 'Position is required'),
    department: z.string().min(1, 'Department is required'),
    numberOfInterns: z.string().min(1, 'Number of interns is required'),
    // duration removed
    
    // Assessment Responses (Likert scale 1-5)
    assessmentResponses: z.record(z.string(), z.number().min(1).max(5)),
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

interface HTEFormProps {
    isFormSubmitted?: boolean;
}

export default function HTEForm({ isFormSubmitted = false }: HTEFormProps) {
    const { flash } = usePage<{ flash: { success?: string; error?: string; warning?: string } }>().props;
    const [isSubmitted, setIsSubmitted] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    
    // Lift categories data and state to parent component
    const [categories, setCategories] = useState<Category[]>([]);
    const [expandedCategories, setExpandedCategories] = useState<Set<number>>(new Set());
    const [expandedSubcategories, setExpandedSubcategories] = useState<Set<number>>(new Set());
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
            assessmentResponses: {},
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
            
            setCategories(data);
            setDataFetched(true);
            setCategoriesLoading(false);
        } catch {
            setCategoriesLoading(false);
        }
    }, [dataFetched]);

    useEffect(() => {
        fetchCategories();
    }, [fetchCategories]);

    // Check for success message on mount and form submission prop
    useEffect(() => {
        if (flash?.success || isFormSubmitted) {
            setIsSubmitted(true);
        }
    }, [flash?.success, isFormSubmitted]);

    function onSubmit(values: FormData) {
        setIsSubmitting(true);
        // Normalize phone to E.164 with + prefix before submit
        const normalizedDigits = (values.phone || '').replace(/\D/g, '');
        const payload = { ...values, phone: `+${normalizedDigits}` };
        
        router.post('/hte/submit', payload, {
            onSuccess: () => {
                setIsSubmitted(true);
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
                // Error handling is now done through visual feedback
            }
        });
    }

    const [currentStep, setCurrentStep] = useState(0);

    // Check if all questions have been answered
    const areAllQuestionsAnswered = useMemo(() => {
        if (currentStep !== 2 || categories.length === 0) return true;
        
        const responses = form.watch('assessmentResponses') || {};
        let totalQuestions = 0;
        let answeredQuestions = 0;
        
        categories.forEach((category) => {
            category.subCategories.forEach((subcat) => {
                subcat.questions.forEach((question) => {
                    totalQuestions++;
                    if (responses[`question_${question.id}`]) {
                        answeredQuestions++;
                    }
                });
            });
        });
        
        console.log('Assessment validation:', { totalQuestions, answeredQuestions, responses });
        
        return totalQuestions > 0 && answeredQuestions === totalQuestions;
    }, [currentStep, categories, form]);

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
                fieldsToValidate = ['position', 'department', 'numberOfInterns'];
                break;
            case 2: // Criteria
                // Check if all questions are answered
                if (!areAllQuestionsAnswered) {
                    return; // Prevent progression if not all questions answered
                }
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
                                <div className="pt-4">
                                    <Button 
                                        onClick={() => router.visit('/hte/profile')}
                                        className="bg-blue-600 hover:bg-blue-700"
                                    >
                                        Go to Profile
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
                <div className="flex flex-col h-full">
                    <div className="flex-1 p-4 overflow-y-auto">
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
                                        setExpandedCategories={setExpandedCategories}
                                        setExpandedSubcategories={setExpandedSubcategories}
                                    />
                                )}
                                {currentStep === 3 && (
                                    <>
                                        <ReviewAndSubmit categories={categories} />
                                        <div className="flex justify-between items-center mt-6">
                                            <div className="text-sm text-muted-foreground">
                                                Review your information and submit the form
                                            </div>
                                            <div className="flex gap-2">
                                                <Button onClick={prev} variant="outline">
                                                    Previous
                                                </Button>
                                                <Button 
                                                    type="submit" 
                                                    disabled={isSubmitting}
                                                >
                                                    {isSubmitting ? 'Submitting...' : 'Submit'}
                                                </Button>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </form>
                        </Form>
                    </div>
                    <div className="border-t border-border bg-background p-4 flex justify-between items-center">
                        <div className="text-sm text-gray-600">
                            {currentStep < steps.length - 1 && (
                                <span>
                                    {currentStep === 2 && !areAllQuestionsAnswered 
                                        ? "Please answer all questions before proceeding" 
                                        : "Please complete all required fields before proceeding"
                                    }
                                </span>
                            )}
                        </div>
                        {currentStep < steps.length - 1 && (
                            <div className="flex gap-2">
                                <Button onClick={prev} disabled={currentStep === 0} variant="outline">
                                    Previous
                                </Button>
                                <Button 
                                    onClick={next} 
                                    disabled={currentStep === steps.length - 1 || (currentStep === 2 && !areAllQuestionsAnswered)}
                                    className={currentStep === 2 && !areAllQuestionsAnswered ? "opacity-50" : ""}
                                >
                                    Next
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
