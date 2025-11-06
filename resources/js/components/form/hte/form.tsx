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
    
    // Question Ratings
    questionRatings: z.record(z.string(), z.number().min(1).max(5)),
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
    rating?: number | null;
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
    const [expandedQuestions, setExpandedQuestions] = useState<Set<number>>(new Set());
    const [categoriesLoading, setCategoriesLoading] = useState(true);
    const [dataFetched, setDataFetched] = useState(false);
    const [showValidationErrors, setShowValidationErrors] = useState(false);

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
            questionRatings: {},
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
            
            // Filter out categories without subcategories
            const processedData = data.filter((category: { subCategories: Array<unknown> }) => {
                const hasSubCategories = category.subCategories && category.subCategories.length > 0;
                return hasSubCategories;
            });
            
            // Initialize question ratings from existing data if available
            const initialRatings: Record<string, number> = {};
            const allCategoryIds = new Set<number>();
            const allSubcategoryIds = new Set<number>();
            const allSubcategoryIdsWithQuestions = new Set<number>();
            
            processedData.forEach((category: Category) => {
                // Add all category IDs to set for auto-expanding categories
                allCategoryIds.add(category.id);
                
                if (category.subCategories && category.subCategories.length > 0) {
                    category.subCategories.forEach((subcat: SubCategory) => {
                        // Add all subcategory IDs to set for auto-expanding subcategories
                        allSubcategoryIds.add(subcat.id);
                        
                        if (subcat.questions && subcat.questions.length > 0) {
                            // Add subcategory ID to set for auto-expanding questions
                            allSubcategoryIdsWithQuestions.add(subcat.id);
                            
                            subcat.questions.forEach((question: Question) => {
                                if (question.rating !== null && question.rating !== undefined) {
                                    initialRatings[question.id] = question.rating;
                                }
                            });
                        }
                    });
                }
            });
            
            // Set Sets first, then categories - this ensures Sets are populated before Criteria component renders
            setExpandedCategories(allCategoryIds);
            setExpandedSubcategories(allSubcategoryIds);
            setExpandedQuestions(allSubcategoryIdsWithQuestions);
            setCategories(processedData);
            
            if (Object.keys(initialRatings).length > 0) {
                Object.entries(initialRatings).forEach(([questionId, rating]) => {
                    form.setValue(`questionRatings.${questionId}`, rating);
                });
            }
            
            setDataFetched(true);
            setCategoriesLoading(false);
        } catch {
            setCategoriesLoading(false);
        }
    }, [dataFetched, form]);

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

    // Watch questionRatings to trigger re-renders when ratings change
    const questionRatings = form.watch('questionRatings') || {};

    // Check if all questions are rated
    const areAllQuestionsRated = useMemo(() => {
        if (currentStep !== 2 || categories.length === 0) return true;
        
        return categories.every((category) => {
            if (!category.subCategories || category.subCategories.length === 0) return true;
            
            return category.subCategories.every((subcat) => {
                if (!subcat.questions || subcat.questions.length === 0) return true;
                
                return subcat.questions.every((question) => {
                    const rating = questionRatings[question.id];
                    return rating !== undefined && rating !== null && rating >= 1 && rating <= 5;
                });
            });
        });
    }, [currentStep, categories, questionRatings]);

    // Clear validation errors when all questions are rated
    React.useEffect(() => {
        if (areAllQuestionsRated && showValidationErrors) {
            setShowValidationErrors(false);
        }
    }, [areAllQuestionsRated, showValidationErrors]);

    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const next = async () => {
        let fieldsToValidate: Path<FormData>[] = [];
        let shouldPreventProgression = false;
        
        switch (currentStep) {
            case 0: // Basic Information
                fieldsToValidate = ['companyName', 'contactPerson', 'email', 'phone', 'address'];
                break;
            case 1: // Internship Offered
                fieldsToValidate = ['position', 'department', 'numberOfInterns'];
                break;
            case 2: { // Criteria
                fieldsToValidate = ['questionRatings'];
                
                // Check if all questions are rated
                const unratedQuestions: Array<{categoryId: number, categoryName: string, subcategoryId: number, subcategoryName: string, questionId: number, questionText: string}> = [];
                
                categories.forEach((category) => {
                    if (category.subCategories && category.subCategories.length > 0) {
                        category.subCategories.forEach((subcat) => {
                            if (subcat.questions && subcat.questions.length > 0) {
                                subcat.questions.forEach((question) => {
                                    const rating = questionRatings[question.id];
                                    if (rating === undefined || rating === null || rating < 1 || rating > 5) {
                                        unratedQuestions.push({
                                            categoryId: category.id,
                                            categoryName: category.category_name,
                                            subcategoryId: subcat.id,
                                            subcategoryName: subcat.subcategory_name,
                                            questionId: question.id,
                                            questionText: question.question
                                        });
                                    }
                                });
                            }
                        });
                    }
                });

                // If there are unrated questions, show validation errors, expand sections, scroll and animate
                if (unratedQuestions.length > 0) {
                    setShowValidationErrors(true);
                    shouldPreventProgression = true;
                    
                    const firstUnratedQuestion = unratedQuestions[0];
                    
                    // Expand the category, subcategory, and questions sections
                    setExpandedCategories((prev) => {
                        const newExpanded = new Set(prev);
                        newExpanded.add(firstUnratedQuestion.categoryId);
                        return newExpanded;
                    });
                    
                    setExpandedSubcategories((prev) => {
                        const newExpanded = new Set(prev);
                        newExpanded.add(firstUnratedQuestion.subcategoryId);
                        return newExpanded;
                    });
                    
                    setExpandedQuestions((prev) => {
                        const newExpanded = new Set(prev);
                        newExpanded.add(firstUnratedQuestion.subcategoryId);
                        return newExpanded;
                    });
                    
                    // Scroll to and animate the question element
                    setTimeout(() => {
                        const questionElement = document.getElementById(`question-rating-${firstUnratedQuestion.questionId}`);
                        if (questionElement) {
                            // Scroll to the element first
                            questionElement.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                            
                            // Wait for scroll to complete before showing animation
                            setTimeout(() => {
                                // Store original styles
                                const originalBorder = questionElement.style.border;
                                const originalPadding = questionElement.style.padding;
                                const originalBorderRadius = questionElement.style.borderRadius;
                                const originalMargin = questionElement.style.margin;
                                
                                // Add animation class
                                questionElement.classList.add('animate-pulse-unanswered');
                                
                                // Add inline styles for better visibility
                                questionElement.style.border = '2px solid hsl(var(--destructive))';
                                questionElement.style.borderRadius = '0.5rem';
                                questionElement.style.padding = '1rem';
                                questionElement.style.margin = '8px 0';
                                questionElement.style.transition = 'all 0.3s ease';
                                
                                // Use React Hook Form's watch with subscription for immediate updates
                                const subscription = form.watch((value, { name }) => {
                                    if (name && name.startsWith('questionRatings.')) {
                                        const questionIdStr = name.split('.')[1];
                                        const questionId = parseInt(questionIdStr);
                                        if (questionId === firstUnratedQuestion.questionId) {
                                            // Get the current rating value from the form
                                            const currentRatings = form.getValues('questionRatings' as Path<FormData>) as Record<string, number> | undefined;
                                            // Try both string and number keys since JavaScript object keys are strings
                                            const currentRating = currentRatings?.[questionId] ?? currentRatings?.[questionIdStr];
                                            if (currentRating !== undefined && currentRating !== null && currentRating >= 1 && currentRating <= 5) {
                                                questionElement.classList.remove('animate-pulse-unanswered');
                                                questionElement.style.border = originalBorder;
                                                questionElement.style.padding = originalPadding;
                                                questionElement.style.borderRadius = originalBorderRadius;
                                                questionElement.style.margin = originalMargin;
                                                subscription.unsubscribe();
                                            }
                                        }
                                    }
                                });
                                
                                // Remove animation after a delay (fallback)
                                setTimeout(() => {
                                    questionElement.classList.remove('animate-pulse-unanswered');
                                    questionElement.style.border = originalBorder;
                                    questionElement.style.padding = originalPadding;
                                    questionElement.style.borderRadius = originalBorderRadius;
                                    questionElement.style.margin = originalMargin;
                                    subscription.unsubscribe();
                                }, 10000);
                            }, 600); // Wait for smooth scroll to complete
                        }
                    }, 300); // Increased delay to ensure sections are expanded
                }
                break;
            }
        }

        // If validation should prevent progression, don't proceed
        if (shouldPreventProgression) {
            return;
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
                                        expandedQuestions={expandedQuestions}
                                        setExpandedCategories={setExpandedCategories}
                                        setExpandedSubcategories={setExpandedSubcategories}
                                        setExpandedQuestions={setExpandedQuestions}
                                        highlightInvalidCategories={showValidationErrors}
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
                                    {currentStep === 2 && !areAllQuestionsRated 
                                        ? "All questions must be rated before proceeding" 
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
                                    disabled={currentStep === steps.length - 1}
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
