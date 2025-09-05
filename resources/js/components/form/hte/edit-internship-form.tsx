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

// Form validation schema for internship only
const FormSchema = z.object({
    position: z.string().min(1, 'Position is required'),
    department: z.string().min(1, 'Department is required'),
    numberOfInterns: z.string().min(1, 'Number of interns is required'),
    duration: z.string().min(1, 'Duration is required'),
    startDate: z.string().min(1, 'Start date is required'),
    endDate: z.string().min(1, 'End date is required'),
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
    hte: any;
    categories: Category[];
    internship: {
        id: number;
        position: string;
        department: string;
        numberOfInterns: string;
        duration: string;
        startDate: string;
        endDate: string;
        is_active: boolean;
    };
    existingWeights: Record<string, number>;
}

export default function EditInternshipForm({ hte, categories, internship, existingWeights }: EditInternshipFormProps) {
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
            duration: internship.duration,
            startDate: internship.startDate,
            endDate: internship.endDate,
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
        
        switch (currentStep) {
            case 0:
                fieldsToValidate = ['position', 'department', 'numberOfInterns', 'duration', 'startDate', 'endDate'];
                break;
            case 1:
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
                                        categories={categories}
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
                            <Button onClick={next} disabled={currentStep === 3}>
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
