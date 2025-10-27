import FormStepCounter from '@/components/form/form-step-counter';
import PersonalInfo from '@/components/form/student/personal-info';
import Summary from '@/components/form/student/summary';
import QuizQuestions from '@/components/form/student/quiz-questions';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import React, { useState, useEffect, useMemo, useCallback } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router, usePage } from '@inertiajs/react';
import { FormFieldsProvider } from '@/contexts/FormFieldsContext';

interface AdditionalInfo {
    id: number;
    info_name: string;
    is_active: boolean;
}

interface PageProps extends Record<string, unknown> {
    additionalInfos?: AdditionalInfo[];
}

interface Category {
    name: string;
    questions: Array<{
        id: number;
        question: string;
        subcategory_name: string;
        choices: Array<{
            id: number;
            choice_text: string;
            is_correct: boolean;
        }>;
    }>;
}

export default function StudentForm() {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { additionalInfos = [] } = usePage<PageProps>().props;

    // Fetch categories
    const [categories, setCategories] = useState<Category[]>([]);
    const [categoriesLoading, setCategoriesLoading] = useState(true);

    useEffect(() => {
        const fetchCategories = async () => {
            try {
                const response = await fetch('/assessment/categories-quiz');
                const data = await response.json();
                setCategories(data);
            } catch (err) {
                console.error('Failed to fetch categories:', err);
            } finally {
                setCategoriesLoading(false);
            }
        };
        
        fetchCategories();
    }, []);

    // Register all quiz fields when categories are loaded
    useEffect(() => {
        if (categories.length === 0) return;
        
        const allFields: string[] = [];
        categories.forEach(category => {
            const categoryNameClean = category.name.toLowerCase().replace(/[+\/\s-]/g, '_');
            category.questions.forEach(question => {
                const fieldName = `${categoryNameClean}_${question.id}`;
                if (!allFields.includes(fieldName)) {
                    allFields.push(fieldName);
                }
            });
        });
        
        console.log('Registering all quiz fields upfront:', allFields);
        setDynamicFields(prev => ({ ...prev, quiz: allFields }));
    }, [categories]);

    // Dynamic field tracking
    const [dynamicFields, setDynamicFields] = useState<{
        quiz: string[];
    }>({
        quiz: [],
    });

    // Quiz sub-step tracking
    const [quizSubStep, setQuizSubStep] = useState(0);

    // Create additional info field names
    const additionalInfoFields = additionalInfos.map(info =>
        info.info_name.toLowerCase().replace(/[ -]/g, '_')
    );

    // Create main steps (only 3 steps)
    const steps = useMemo(() => [
        {
            id: 'Step 1',
            name: 'Additional Information',
            fields: [...additionalInfoFields],
        },
        {
            id: 'Step 2',
            name: 'Quiz',
            fields: [...dynamicFields.quiz],
        },
        { id: 'Step 3', name: 'Submission' },
    ], [additionalInfoFields, dynamicFields]);
    // Create dynamic validation schema
    const createFormSchema = useCallback(() => {

        // Add additional info fields to validation schema
        const additionalInfoSchema: Record<string, z.ZodTypeAny> = {};
        additionalInfoFields.forEach(field => {
            additionalInfoSchema[field] = z.string().optional().refine(val => val && val.trim() !== '', 'Question is required.');
        });

        // Add dynamic fields for quiz questions
        const quizSchema: Record<string, z.ZodTypeAny> = {};
        dynamicFields.quiz.forEach(field => {
            quizSchema[field] = z.string().min(1, 'Please select an answer.');
        });

        // Ensure we always have at least one field in the schema
        const schemaFields = {
            ...additionalInfoSchema,
            ...quizSchema,
        };

        // If no fields are present, add a dummy field to prevent empty schema
        if (Object.keys(schemaFields).length === 0) {
            schemaFields['dummy'] = z.string();
        }

        return z.object(schemaFields);
    }, [additionalInfoFields, dynamicFields]);

    const FormSchema = useMemo(() => createFormSchema(), [createFormSchema]);

    // Create default values object
    const createDefaultValues = useCallback(() => {
        const defaultValues: Record<string, string> = {};

        // Initialize additional info fields
        additionalInfoFields.forEach(field => {
            defaultValues[field] = '';
        });

        // Initialize quiz fields
        dynamicFields.quiz.forEach(field => {
            defaultValues[field] = '';
        });

        return defaultValues;
    }, [additionalInfoFields, dynamicFields]);

    const defaultValues = useMemo(() => createDefaultValues(), [createDefaultValues]);

    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: 'onSubmit',
        defaultValues,
    });

    // Update form default values when dynamic fields change
    useEffect(() => {
        const currentValues = form.getValues();

        // Only add new fields that don't exist yet, preserve all existing values
        const newFields: Record<string, string> = {};
        Object.keys(defaultValues).forEach(key => {
            if (currentValues[key] === undefined || currentValues[key] === '') {
                newFields[key] = defaultValues[key];
            }
        });

        // Only update if there are new fields to add
        if (Object.keys(newFields).length > 0) {
            // Use setValue to add new fields without resetting existing ones
            Object.entries(newFields).forEach(([key, value]) => {
                form.setValue(key as Path<z.infer<typeof FormSchema>>, value);
            });
        }
    }, [dynamicFields, defaultValues, form]);

    const navigateToStep = (step: number) => {
        setCurrentStep(step);
    };

    function onSubmit(values: z.infer<typeof FormSchema>) {
        // Manual validation check
        const allValues = form.getValues();
        const allFields = Object.keys(FormSchema.shape);
        const missingFields = allFields.filter(field => {
            const value = allValues[field];
            return !value || (typeof value === 'string' && value.trim() === '');
        });

        if (missingFields.length > 0) {
            alert(`Please complete the following fields: ${missingFields.join(', ')}`);
            return;
        }

        setIsSubmitting(true);

        // Remove dummy field if it exists
        const cleanValues = { ...values };
        if (cleanValues.dummy !== undefined) {
            delete cleanValues.dummy;
        }

        // Debug: log what's being submitted
        console.log('Submitting form with values:', cleanValues);
        console.log('Form values keys:', Object.keys(cleanValues));
        console.log('Expected quiz field count:', dynamicFields.quiz.length);
        console.log('Expected quiz fields:', dynamicFields.quiz);

        router.post('/assessment', cleanValues as Record<string, string>, {
            onSuccess: () => {
                setIsSubmitting(false);
            },
            onError: (errors) => {
                console.error('Form submission failed:', errors);
                setIsSubmitting(false);
            },
            onFinish: () => {
                setIsSubmitting(false);
            }
        });
    }

    const [currentStep, setCurrentStep] = useState(0);
    
    // Navigation for main steps
    const prev = () => {
        if (currentStep === 1 && quizSubStep > 0) {
            // If in quiz step, go to previous quiz sub-step
            setQuizSubStep(prev => prev - 1);
        } else if (currentStep > 0) {
            // If at start of quiz step, go to previous main step
            setCurrentStep(prev => prev - 1);
        }
    };

    const next = async () => {
        // Handle quiz step navigation
        if (currentStep === 1) {
            // Validate current category's questions before moving forward
            const currentCategory = categories[quizSubStep];
            if (currentCategory && currentCategory.questions.length > 0) {
                const categoryNameClean = currentCategory.name.toLowerCase().replace(/[+\/\s-]/g, '_');
                const categoryFields = currentCategory.questions.map(q => `${categoryNameClean}_${q.id}`);
                
                // Validate all fields for current category
                const isValid = await form.trigger(categoryFields as Path<z.infer<typeof FormSchema>>[], { shouldFocus: false });
                
                if (!isValid) {
                    // Find unanswered fields and highlight the first one
                    const unansweredFields: string[] = [];
                    const formValues = form.getValues();

                    categoryFields.forEach((fieldName) => {
                        const formValue = formValues[fieldName as keyof typeof formValues];
                        if (!formValue || (typeof formValue === 'string' && formValue.trim() === '')) {
                            unansweredFields.push(fieldName);
                        }
                    });

                    if (unansweredFields.length > 0) {
                        const firstUnansweredField = unansweredFields[0];
                        
                        // Find the form field container
                        setTimeout(() => {
                            const formItem = document.querySelector(`[data-field-name="${firstUnansweredField}"]`) as HTMLElement;
                            if (formItem) {
                                // Store original styles
                                const originalBorder = formItem.style.border;
                                const originalPadding = formItem.style.padding;
                                const originalBorderRadius = formItem.style.borderRadius;
                                
                                // Scroll to the element first
                                formItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                
                                // Wait for scroll to complete before showing animation
                                setTimeout(() => {
                                    // Add animation class
                                    formItem.classList.add('animate-pulse-unanswered');
                                    
                                    // Add inline styles for better visibility
                                    formItem.style.border = '2px solid hsl(var(--destructive))';
                                    formItem.style.borderRadius = '0.5rem';
                                    formItem.style.padding = '1rem';
                                    formItem.style.transition = 'all 0.3s ease';
                                    
                                    // Use React Hook Form's watch with subscription for immediate updates
                                    const subscription = form.watch((value, { name, type }) => {
                                        if (name === firstUnansweredField) {
                                            const currentValue = value[firstUnansweredField as keyof typeof value];
                                            if (currentValue && String(currentValue).trim() !== '') {
                                                formItem.classList.remove('animate-pulse-unanswered');
                                                formItem.style.border = originalBorder;
                                                formItem.style.padding = originalPadding;
                                                formItem.style.borderRadius = originalBorderRadius;
                                                subscription.unsubscribe();
                                            }
                                        }
                                    });
                                    
                                    // Remove animation after a delay (fallback)
                                    setTimeout(() => {
                                        formItem.classList.remove('animate-pulse-unanswered');
                                        formItem.style.border = originalBorder;
                                        formItem.style.padding = originalPadding;
                                        formItem.style.borderRadius = originalBorderRadius;
                                        subscription.unsubscribe();
                                    }, 10000);
                                }, 600); // Wait for smooth scroll to complete
                            }
                        }, 100);
                    }
                    return;
                }
            }
            
            // If validated, check if there are more quiz categories
            if (quizSubStep < categories.length - 1) {
                setQuizSubStep(prev => prev + 1);
                return;
            } else {
                // All quiz sub-steps completed, move to next main step
                setCurrentStep(2);
                return;
            }
        }

        const currentStepData = steps[currentStep];
        const fields = currentStepData.fields;

        // Use Zod validation to trigger validation errors
        const isValid = await form.trigger(fields as Path<z.infer<typeof FormSchema>>[], { shouldFocus: false });

        if (isValid) {
            setCurrentStep((prev) => prev + 1);
        } else {
            // Find unanswered fields in current step and highlight the first one
            const unansweredFields: string[] = [];
            const formValues = form.getValues();

            fields?.forEach((fieldName) => {
                const formValue = formValues[fieldName as keyof typeof formValues];
                if (!formValue ||
                    (typeof formValue === 'string' &&
                     formValue.trim() === '')) {
                    unansweredFields.push(fieldName);
                }
            });

            if (unansweredFields.length > 0) {
                // Focus on the first unanswered question only
                const firstUnansweredField = unansweredFields[0];
                setTimeout(() => {
                    const element = document.querySelector(`input[name="${firstUnansweredField}"], [data-field="${firstUnansweredField}"]`) as HTMLElement;
                    if (element) {
                        element.classList.add('animate-pulse-unanswered');
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.focus();

                        setTimeout(() => {
                            element.classList.remove('animate-pulse-unanswered');
                        }, 1000);
                    }
                }, 100);
            }
        }
    };

    // Memoize setter functions to prevent infinite loops
    const setLanguageProficiencyFields = useCallback((fields: string[]) => {
        // No-op for removed step
    }, []);

    const setTechnicalSkillFields = useCallback((fields: string[]) => {
        // No-op for removed step
    }, []);

    const setSoftSkillFields = useCallback((fields: string[]) => {
        // No-op for removed step
    }, []);

    const setQuizFields = useCallback((fields: string[]) => {
        console.log('Registering quiz fields:', fields);
        setDynamicFields(prev => {
            // Merge new fields with existing ones, avoiding duplicates
            const existingFields = new Set(prev.quiz);
            const newFields = fields.filter(field => !existingFields.has(field));
            const updatedFields = [...prev.quiz, ...newFields];
            console.log('Updated quiz fields:', updatedFields);
            return { ...prev, quiz: updatedFields };
        });
    }, []);

    if (categoriesLoading) {
        return (
            <div className="flex items-center justify-center h-full">
                <div className="text-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                    <p className="text-muted-foreground">Loading assessment questions...</p>
                </div>
            </div>
        );
    }

    return (
        <FormFieldsProvider
            setLanguageProficiencyFields={setLanguageProficiencyFields}
            setTechnicalSkillFields={setTechnicalSkillFields}
            setSoftSkillFields={setSoftSkillFields}
            setQuizFields={setQuizFields}
            onNavigateToStep={navigateToStep}
        >
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={currentStep} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="flex flex-col h-full">
                    <div className="flex-1 p-4 overflow-y-auto">
                        <div className="mb-4 rounded-md border border-border bg-muted/50 p-3 text-sm text-muted-foreground">
                            Answer honestly — your responses reflect your competency. Inaccurate answers may reduce the quality of matches and recommendations the system can provide.
                        </div>
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)}>
                                {currentStep === 0 && <PersonalInfo />}
                                {currentStep === 1 && categories.length > 0 && (
                                    <div>
                                        {/* Sub-step indicator for quiz categories */}
                                        <div className="mb-6 p-4 bg-muted/30 border border-border rounded-lg">
                                            <div className="flex items-center justify-between mb-3">
                                                <div className="flex items-center gap-3">
                                                    <div>
                                                        <div className="text-xs text-muted-foreground uppercase tracking-wide">Quiz Progress</div>
                                                        <div className="font-semibold text-base">{categories[quizSubStep]?.name}</div>
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-xs text-muted-foreground">Category</div>
                                                    <div className="font-semibold">{quizSubStep + 1} / {categories.length}</div>
                                                </div>
                                            </div>
                                            <div className="flex gap-2">
                                                {categories.map((_, index) => (
                                                    <div
                                                        key={index}
                                                        className={`h-2 flex-1 rounded transition-all ${
                                                            index === quizSubStep 
                                                                ? 'bg-primary' 
                                                                : index < quizSubStep 
                                                                    ? 'bg-primary/60' 
                                                                    : 'bg-muted'
                                                        }`}
                                                    />
                                                ))}
                                            </div>
                                        </div>
                                        <QuizQuestions category={categories[quizSubStep]} />
                                    </div>
                                )}
                                {currentStep === 2 && (
                                    <div className="space-y-6">
                                        <h2 className="text-xl font-semibold">Review Your Answers</h2>
                                        <Summary />
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
                        {currentStep < steps.length - 1 && (
                            <div className="flex gap-2">
                                {currentStep > 0 && (
                                    <Button onClick={prev} variant="outline">
                                        Previous
                                    </Button>
                                )}
                                <Button onClick={next}>
                                    Next
                                </Button>
                            </div>
                        )}
                        {currentStep === steps.length - 1 && (
                            <div className="flex gap-2">
                                <Button onClick={prev} variant="outline">
                                    Previous
                                </Button>
                                <Button
                                    type="button"
                                    disabled={isSubmitting}
                                    onClick={async (e) => {
                                        e.preventDefault();
                                        try {
                                            await form.handleSubmit(onSubmit)();
                                        } catch (error) {
                                            console.error('Form submission error:', error);
                                        }
                                    }}
                                >
                                    {isSubmitting ? 'Submitting...' : 'Submit'}
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </FormFieldsProvider>
    );
}

