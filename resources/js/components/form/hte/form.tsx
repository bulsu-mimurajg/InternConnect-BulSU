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
    phone: z.string().min(1, 'Phone number is required'),
    address: z.string().min(1, 'Address is required'),
    
    // Internship Offered
    position: z.string().min(1, 'Position is required'),
    department: z.string().min(1, 'Department is required'),
    numberOfInterns: z.string().min(1, 'Number of interns is required'),
    // duration removed
    
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
    const [lockedSubcategories, setLockedSubcategories] = useState<Set<number>>(new Set());

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
            
            // Filter out categories without subcategories
            const processedData = data.filter((category: { subCategories: Array<unknown> }) => {
                const hasSubCategories = category.subCategories && category.subCategories.length > 0;
                return hasSubCategories;
            });
            
            setCategories(processedData);
            
            // Initialize equal weights for all subcategories
            processedData.forEach((category: Category) => {
                if (category.subCategories && category.subCategories.length > 0) {
                    const subcategoryCount = category.subCategories.length;
                    const baseWeight = Math.floor(100 / subcategoryCount);
                    const remainder = 100 % subcategoryCount;
                    
                    // Distribute weights evenly, with remainder distributed to first subcategories
                    category.subCategories.forEach((subcat: SubCategory, index: number) => {
                        const weight = index < remainder ? baseWeight + 1 : baseWeight;
                        form.setValue(`subcategoryWeights.${subcat.id}`, weight);
                    });
                }
            });
            
            setDataFetched(true);
            setCategoriesLoading(false);
        } catch (error) {
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
        
        router.post('/hte/submit', values, {
            onSuccess: (page) => {
                setIsSubmitted(true);
                setIsSubmitting(false);
            },
            onError: (errors) => {
                setIsSubmitting(false);
                // Error handling is now done through visual feedback
            }
        });
    }

    const [currentStep, setCurrentStep] = useState(0);

    // Check if all categories have valid weights (total 100% and no unset weights for unlocked subcategories)
    const areAllWeightsValid = useMemo(() => {
        if (currentStep !== 2 || categories.length === 0) return true;
        
        const weights = form.watch('subcategoryWeights') || {};
        
        return categories.every((category) => {
            if (!category.subCategories || category.subCategories.length === 0) return true;
            
            const categoryWeights = category.subCategories.map((subcat) => {
                const weight = weights[subcat.id];
                // Check if weight exists and is a valid number > 0
                return (weight !== undefined && weight !== null && !isNaN(Number(weight)) && Number(weight) > 0) ? Number(weight) : 0;
            });
            const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);
            
            // Check if total is 100% AND no unlocked weights are unset (0)
            const hasUnsetUnlockedWeights = category.subCategories.some((subcat, index) => {
                const weight = categoryWeights[index];
                return !lockedSubcategories.has(subcat.id) && weight === 0;
            });
            
            return totalWeight === 100 && !hasUnsetUnlockedWeights;
        });
    }, [currentStep, categories, form.watch('subcategoryWeights'), lockedSubcategories]);

    // Check if all categories total 100% (allows navigation even with unset weights)
    const areAllCategoriesAt100Percent = useMemo(() => {
        if (currentStep !== 2 || categories.length === 0) return true;
        
        const weights = form.watch('subcategoryWeights') || {};
        
        return categories.every((category) => {
            if (!category.subCategories || category.subCategories.length === 0) return true;
            
            const categoryWeights = category.subCategories.map((subcat) => {
                const weight = weights[subcat.id];
                return (weight !== undefined && weight !== null && !isNaN(Number(weight))) ? Number(weight) : 0;
            });
            const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);
            
            return totalWeight === 100;
        });
    }, [currentStep, categories, form.watch('subcategoryWeights')]);

    // Clear validation errors when weights become valid
    React.useEffect(() => {
        if (areAllWeightsValid && showValidationErrors) {
            setShowValidationErrors(false);
        }
    }, [areAllWeightsValid, showValidationErrors]);

    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const toggleSubcategoryLock = useCallback((subcategoryId: number) => {
        setLockedSubcategories((prev) => {
            const newLocked = new Set(prev);
            if (newLocked.has(subcategoryId)) {
                newLocked.delete(subcategoryId);
            } else {
                newLocked.add(subcategoryId);
            }
            return newLocked;
        });
    }, []);

    const next = async () => {
        let fieldsToValidate: Path<FormData>[] = [];
        
        switch (currentStep) {
            case 0: // Basic Information
                fieldsToValidate = ['companyName', 'contactPerson', 'email', 'phone', 'address'];
                break;
            case 1: // Internship Offered
                fieldsToValidate = ['position', 'department', 'numberOfInterns'];
                break;
            case 2: { // Criteria
                fieldsToValidate = ['subcategoryWeights'];
                
                // Check if each category totals 100%
                const weights = form.watch('subcategoryWeights') || {};
                const categoriesWithInvalidTotals: Array<{name: string, total: number, missing: number}> = [];
                const categoriesWithUnsetWeights: Array<{name: string, unsetSubcategories: Array<{id: number, name: string}>}> = [];
                
                categories.forEach((category) => {
                    if (category.subCategories && category.subCategories.length > 0) {
                        const categoryWeights = category.subCategories.map((subcat) => {
                            const weight = weights[subcat.id];
                            return (weight !== undefined && weight !== null && !isNaN(Number(weight))) ? Number(weight) : 0;
                        });
                        const totalWeight = categoryWeights.reduce((sum, weight) => sum + weight, 0);
                        
                        // Check if total is not 100%
                        if (totalWeight !== 100) {
                            categoriesWithInvalidTotals.push({
                                name: category.category_name,
                                total: totalWeight,
                                missing: 100 - totalWeight
                            });
                        }
                        
                        // Check for unset unlocked subcategories
                        const unsetSubcategories = category.subCategories.filter((subcat, index) => {
                            const weight = categoryWeights[index];
                            return !lockedSubcategories.has(subcat.id) && (weight === 0 || weight === undefined || weight === null);
                        });
                        
                        if (unsetSubcategories.length > 0) {
                            categoriesWithUnsetWeights.push({
                                name: category.category_name,
                                unsetSubcategories: unsetSubcategories.map(subcat => ({
                                    id: subcat.id,
                                    name: subcat.subcategory_name
                                }))
                            });
                        }
                    }
                });

                // If totals are not 100%, show validation errors and focus on first invalid category
                if (categoriesWithInvalidTotals.length > 0) {
                    setShowValidationErrors(true);
                    
                    const firstInvalidCategory = categoriesWithInvalidTotals[0];
                    setTimeout(() => {
                        const categoryCard = document.getElementById(`category-card-${firstInvalidCategory.name.toLowerCase().replace(/\s+/g, '-')}`);
                        if (categoryCard) {
                            categoryCard.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                        }
                    }, 100);
                    
                    return;
                }
                
                // If totals are 100% but there are unset subcategories, focus on the first unset one
                if (categoriesWithUnsetWeights.length > 0) {
                    const firstCategoryWithUnset = categoriesWithUnsetWeights[0];
                    const firstUnsetSubcategory = firstCategoryWithUnset.unsetSubcategories[0];
                    
                    setTimeout(() => {
                        const inputElement = document.getElementById(`weight-${firstUnsetSubcategory.id}`) as HTMLInputElement;
                        if (inputElement) {
                            inputElement.focus();
                            inputElement.select();
                        }
                    }, 100);
                    
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
                                        lockedSubcategories={lockedSubcategories}
                                        onToggleSubcategoryLock={toggleSubcategoryLock}
                                    />
                                )}
                                {currentStep === 3 && (
                                    <>
                                        <ReviewAndSubmit isSubmitting={isSubmitting} categories={categories} />
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
                                    {currentStep === 2 && !areAllCategoriesAt100Percent 
                                        ? "All categories must total 100% before proceeding" 
                                        : currentStep === 2 && !areAllWeightsValid
                                        ? "Some subcategories need weights assigned - click Next to focus on them"
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
                                    disabled={currentStep === steps.length - 1 || (currentStep === 2 && !areAllCategoriesAt100Percent)}
                                    className={currentStep === 2 && !areAllCategoriesAt100Percent ? "opacity-50" : ""}
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
