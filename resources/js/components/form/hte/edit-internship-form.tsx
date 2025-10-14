import FormStepCounter from '@/components/form/form-step-counter';
import InternshipOffered from '@/components/form/hte/internship-offered';
import Criteria from '@/components/form/hte/criteria';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState, useEffect } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router } from '@inertiajs/react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { TooltipProvider } from '@radix-ui/react-tooltip';
import { HelpCircle } from 'lucide-react';

// Form validation schema for internship only
const FormSchema = z.object({
    position: z.string().min(1, 'Position is required'),
    department: z.string().min(1, 'Department is required'),
    numberOfInterns: z.string().min(1, 'Number of interns is required'),
    subcategoryWeights: z.record(z.string(), z.number().min(0).max(100)),
});

type FormData = z.infer<typeof FormSchema>;

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

interface EditInternshipFormProps {
    categories: Category[];
    internship: {
        id: number;
        position: string;
        department: string;
        numberOfInterns: string;
        is_active: boolean;
    };
    existingWeights: Record<string, number>;
}

export default function EditInternshipForm({ categories, internship, existingWeights }: EditInternshipFormProps) {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [currentStep, setCurrentStep] = useState(0);

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
            position: internship.position,
            department: internship.department,
            numberOfInterns: internship.numberOfInterns,
            subcategoryWeights: existingWeights,
        },
    });

    // Initialize weights with existing values
    useEffect(() => {
        if (categories && categories.length > 0) {
            categories.forEach((category: Category) => {
                if (category.subCategories && category.subCategories.length > 0) {
                    category.subCategories.forEach((subcat: SubCategory) => {
                        if (existingWeights[subcat.id] !== undefined) {
                            form.setValue(`subcategoryWeights.${subcat.id}`, existingWeights[subcat.id]);
                        } else {
                            // Initialize with 0 if no existing weight
                            form.setValue(`subcategoryWeights.${subcat.id}`, 0);
                        }
                    });
                }
            });
        }
    }, [categories, existingWeights, form]);

    function onSubmit(values: FormData) {
        setIsSubmitting(true);

        router.put(`/hte/edit-internship/${internship.id}`, values, {
            onSuccess: () => {
                setIsSubmitting(false);
            },
            onError: () => {
                setIsSubmitting(false);
            }
        });
    }

    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const next = async () => {
        let fieldsToValidate: Path<FormData>[] = [];
        let customValidationPassed = true;

        switch (currentStep) {
            case 0:
                fieldsToValidate = ['position', 'department', 'numberOfInterns'];
                break;
            case 1:
                fieldsToValidate = ['subcategoryWeights'];
                // Add custom validation for weight distribution
                customValidationPassed = areAllCategoriesValid();
                break;
        }

        if (fieldsToValidate.length > 0) {
            const isValid = await form.trigger(fieldsToValidate, { shouldFocus: true });
            if (isValid && customValidationPassed) {
                setCurrentStep((prev) => prev + 1);
            }
        } else {
            setCurrentStep((prev) => prev + 1);
        }
    };

    // Calculate total weight for a category
    const calculateCategoryTotal = (categoryId: number) => {
        const category = categories.find(c => c.id === categoryId);
        if (!category) return 0;

        return category.subCategories.reduce((sum, subcat) => {
            const weight = form.watch(`subcategoryWeights.${subcat.id}`) || 0;
            return sum + weight;
        }, 0);
    };

    // Check if all categories have exactly 100% weight distribution
    const areAllCategoriesValid = () => {
        return categories.every(category => {
            const total = calculateCategoryTotal(category.id);
            return total === 100;
        });
    };

    // Lock/unlock subcategory weights
    const [lockedSubcategories, setLockedSubcategories] = useState<Set<number>>(new Set());

    const toggleSubcategoryLock = (subcategoryId: number) => {
        setLockedSubcategories((prev) => {
            const newSet = new Set(prev);
            if (newSet.has(subcategoryId)) {
                newSet.delete(subcategoryId);
            } else {
                newSet.add(subcategoryId);
            }
            return newSet;
        });
    };

    return (
        <>
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={currentStep} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="flex flex-col h-full">
                    <div className="flex-1 p-4 overflow-y-auto">
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)}>
                                {currentStep === 0 && <InternshipOffered />}
                                {currentStep === 1 && (
                                    <Criteria
                                        categories={categories}
                                        loading={false}
                                        expandedCategories={expandedCategories}
                                        expandedSubcategories={expandedSubcategories}
                                        expandedQuestions={expandedQuestions}
                                        setExpandedCategories={setExpandedCategories}
                                        setExpandedSubcategories={setExpandedSubcategories}
                                        setExpandedQuestions={setExpandedQuestions}
                                        lockedSubcategories={lockedSubcategories}
                                        onToggleSubcategoryLock={toggleSubcategoryLock}
                                    />
                                )}
                                {currentStep === 2 && (
                                    <div className="space-y-6">
                                        <div className="text-center space-y-4">
                                            <h2 className="text-2xl font-semibold">Review and Submit</h2>
                                            <p className="text-muted-foreground">
                                                Please review your updated internship details and criteria weights before submitting.
                                            </p>
                                        </div>

                                        <Button
                                            type="submit"
                                            disabled={isSubmitting}
                                            className="w-full"
                                        >
                                            {isSubmitting ? 'Updating...' : 'Update Internship'}
                                        </Button>
                                    </div>
                                )}
                            </form>
                        </Form>
                    </div>
                    <div className="border-t border-border bg-background p-4 flex justify-between items-center">
                        <div className="text-sm text-gray-600">
                            {currentStep < steps.length - 1 && (
                                <span>Please complete all required fields before proceeding</span>
                            )}
                        </div>
                        <div className="flex gap-2">
                            <Button onClick={prev} disabled={currentStep === 0} variant="outline">
                                Previous
                            </Button>
                            <div className="flex items-center gap-2">
                                <Button onClick={next} disabled={currentStep === steps.length - 1}>
                                    Next
                                </Button>
                                {currentStep === 1 && !areAllCategoriesValid() && (
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger asChild>
                                                <HelpCircle className="h-5 w-5 text-gray-400 hover:text-gray-600 cursor-help" />
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>All categories must total exactly 100%</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
