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
    const [currentStep, setCurrentStep] = useState<number>(0);

    const [expandedCategories, setExpandedCategories] = useState<Set<number>>(new Set());
    const [expandedSubcategories, setExpandedSubcategories] = useState<Set<number>>(new Set());
    const [expandedQuestions, setExpandedQuestions] = useState<Set<number>>(new Set());
    const [initialExpansionDone, setInitialExpansionDone] = useState(false);

    // Auto-expand Technical Skill and Soft Skill categories when data is loaded
    useEffect(() => {
        if (!initialExpansionDone && categories && categories.length > 0) {
            const technicalSkillCategory = categories.find(cat =>
                cat.category_name.toLowerCase().includes('technical skill')
            );
            const softSkillCategory = categories.find(cat =>
                cat.category_name.toLowerCase().includes('soft skill')
            );

            const categoryIdsToExpand: number[] = [];
            const subcategoryIdsToExpand: number[] = [];

            if (technicalSkillCategory) {
                categoryIdsToExpand.push(technicalSkillCategory.id);
                subcategoryIdsToExpand.push(...technicalSkillCategory.subCategories.map(sub => sub.id));
            }

            if (softSkillCategory) {
                categoryIdsToExpand.push(softSkillCategory.id);
                subcategoryIdsToExpand.push(...softSkillCategory.subCategories.map(sub => sub.id));
            }

            if (categoryIdsToExpand.length > 0) {
                setExpandedCategories(new Set(categoryIdsToExpand));
                setExpandedSubcategories(new Set(subcategoryIdsToExpand));
                setInitialExpansionDone(true);
            }
        }
    }, [categories, initialExpansionDone]);

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
                            <form onSubmit={(e) => {
                                e.preventDefault();
                                // Never auto-submit, only allow explicit button clicks
                            }}>
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

                                        {/* Internship Details Summary */}
                                        <div className="bg-muted/50 p-6 rounded-lg">
                                            <h3 className="font-medium mb-6 text-foreground text-center">Internship Details</h3>
                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div className="text-center">
                                                    <label className="text-sm font-medium text-foreground">Position</label>
                                                    <p className="text-sm text-muted-foreground mt-2">{form.watch('position')}</p>
                                                </div>
                                                <div className="text-center">
                                                    <label className="text-sm font-medium text-foreground">Department</label>
                                                    <p className="text-sm text-muted-foreground mt-2">{form.watch('department')}</p>
                                                </div>
                                                <div className="text-center">
                                                    <label className="text-sm font-medium text-foreground">Number of Interns</label>
                                                    <p className="text-sm text-muted-foreground mt-2">{form.watch('numberOfInterns')}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Enhanced Assessment Criteria Summary */}
                                        {categories.length > 0 && (
                                            <div className="bg-muted/50 p-4 rounded-lg">
                                                <h3 className="font-medium mb-4 text-foreground">Assessment Criteria & Weight Allocation</h3>

                                                {/* Overall Weight Summary */}
                                                <div className="mb-6 p-4 bg-card rounded-lg border border-border">
                                                    <h4 className="font-medium text-card-foreground mb-3">Overall Weight Distribution</h4>
                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                        {categories.map((category: Category) => {
                                                            const categoryTotal = calculateCategoryTotal(category.id);
                                                            const getWeightStatus = (categoryTotal: number) => {
                                                                if (categoryTotal === 100) {
                                                                    return { status: 'valid', bgColor: 'bg-green-50 dark:bg-green-950/30', borderColor: 'border-green-200 dark:border-green-800', color: 'text-green-600 dark:text-green-400' };
                                                                } else if (categoryTotal > 100) {
                                                                    return { status: 'exceeded', bgColor: 'bg-red-50 dark:bg-red-950/30', borderColor: 'border-red-200 dark:border-red-800', color: 'text-red-600 dark:text-red-400' };
                                                                } else {
                                                                    return { status: 'incomplete', bgColor: 'bg-yellow-50 dark:bg-yellow-950/30', borderColor: 'border-yellow-200 dark:border-yellow-800', color: 'text-yellow-600 dark:text-yellow-400' };
                                                                }
                                                            };
                                                            const weightStatus = getWeightStatus(categoryTotal);

                                                            return (
                                                                <div key={category.id} className={`p-3 rounded-lg border ${weightStatus.bgColor} ${weightStatus.borderColor}`}>
                                                                    <div className="text-center">
                                                                        <div className={`text-lg font-bold ${weightStatus.color}`}>
                                                                            {categoryTotal}%
                                                                        </div>
                                                                        <div className="text-sm text-muted-foreground">{category.category_name}</div>
                                                                        <div className={`text-xs font-medium mt-2 px-2 py-1 rounded ${
                                                                            weightStatus.status === 'valid' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' :
                                                                            weightStatus.status === 'exceeded' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' :
                                                                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300'
                                                                        }`}>
                                                                            {weightStatus.status === 'valid' ? 'Complete' :
                                                                             weightStatus.status === 'exceeded' ? 'Exceeded' : 'Incomplete'}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </div>

                                                {/* Final Validation Message */}
                                                <div className="mt-6 p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                                                    <h4 className="font-medium text-blue-900 dark:text-blue-100 mb-2">Weight Allocation Validation</h4>
                                                    <div className="text-sm text-blue-800 dark:text-blue-200">
                                                        {categories.every(cat => calculateCategoryTotal(cat.id) === 100) ? (
                                                            <div className="flex items-center gap-2 text-green-700 dark:text-green-300">
                                                                <span>✓</span>
                                                                <span>All categories have proper weight distribution (100% each)</span>
                                                            </div>
                                                        ) : (
                                                            <div className="space-y-2">
                                                                <div className="flex items-center gap-2 text-red-700 dark:text-red-300">
                                                                    <span>⚠️</span>
                                                                    <span>Some categories need weight adjustment before submission</span>
                                                                </div>
                                                                <ul className="ml-6 list-disc space-y-1">
                                                                    {categories.map((cat: Category) => {
                                                                        const total = calculateCategoryTotal(cat.id);
                                                                        if (total !== 100) {
                                                                            return (
                                                                                <li key={cat.id} className="text-red-600 dark:text-red-400">
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
                                    </div>
                                )}

                                {/* Navigation - Always visible */}
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-muted-foreground">
                                        {currentStep < steps.length - 1 && (
                                            <span>Please complete all required fields before proceeding</span>
                                        )}
                                    </div>
                                    <div className="flex gap-2">
                                        <Button type="button" onClick={prev} disabled={currentStep <= 0} variant="outline">
                                            Previous
                                        </Button>
                                        {currentStep === 1 && !areAllCategoriesValid() ? (
                                            <div className="flex items-center gap-2">
                                                <Button type="button" onClick={next} disabled={true}>
                                                    Next
                                                </Button>
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <HelpCircle className="h-5 w-5 text-muted-foreground hover:text-foreground cursor-help" />
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>Incomplete weights for some category</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            </div>
                                        ) : currentStep === 2 ? (
                                            <Button
                                                type="button"
                                                disabled={isSubmitting}
                                                onClick={() => {
                                                    // Manually trigger form submission
                                                    form.handleSubmit(onSubmit)();
                                                }}
                                            >
                                                {isSubmitting ? 'Updating...' : 'Update Internship'}
                                            </Button>
                                        ) : (
                                            <Button type="button" onClick={next}>
                                                Next
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </form>
                        </Form>
                    </div>
                </div>
            </div>
        </>
    );
}
