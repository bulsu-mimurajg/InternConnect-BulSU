import FormStepCounter from '@/components/form/form-step-counter';
import PersonalInfo from '@/components/form/student/personal-info';
import SoftSkill from '@/components/form/student/soft-skill';
import Summary from '@/components/form/student/summary';
import TechnicalSkill from '@/components/form/student/technical-skill';
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

export default function StudentForm() {
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { additionalInfos = [] } = usePage<PageProps>().props;

    // Dynamic field tracking
    const [dynamicFields, setDynamicFields] = useState<{
        technicalSkills: string[];
        softSkills: string[];
    }>({
        technicalSkills: [],
        softSkills: [],
    });

    // Create additional info field names
    const additionalInfoFields = additionalInfos.map(info =>
        info.info_name.toLowerCase().replace(/[ -]/g, '_')
    );

    const steps = useMemo(() => [
        {
            id: 'Step 1',
            name: 'Additional Information',
            fields: [...additionalInfoFields],
        },
        {
            id: 'Step 2',
            name: 'Technical Skills',
            fields: [...dynamicFields.technicalSkills],
        },
        {
            id: 'Step 3',
            name: 'Soft Skills',
            fields: [...dynamicFields.softSkills],
        },
        { id: 'Step 4', name: 'Submission' },
    ], [additionalInfoFields, dynamicFields]);
    // Create dynamic validation schema
    const createFormSchema = useCallback(() => {

        // Add additional info fields to validation schema
        const additionalInfoSchema: Record<string, z.ZodTypeAny> = {};
        additionalInfoFields.forEach(field => {
            additionalInfoSchema[field] = z.string().optional().refine(val => val && val.trim() !== '', 'Question is required.');
        });

        // Add dynamic fields for technical skills
        const technicalSchema: Record<string, z.ZodTypeAny> = {};
        dynamicFields.technicalSkills.forEach(field => {
            technicalSchema[field] = z.string().min(1, 'Please select a rating.');
        });

        // Add dynamic fields for soft skills
        const softSchema: Record<string, z.ZodTypeAny> = {};
        dynamicFields.softSkills.forEach(field => {
            softSchema[field] = z.string().min(1, 'Please select a rating.');
        });

        // Ensure we always have at least one field in the schema
        const schemaFields = {
            ...additionalInfoSchema,
            ...technicalSchema,
            ...softSchema,
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

        // Initialize technical skills fields
        dynamicFields.technicalSkills.forEach(field => {
            defaultValues[field] = '';
        });

        // Initialize soft skills fields
        dynamicFields.softSkills.forEach(field => {
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
    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const next = async () => {
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
    const setTechnicalSkillFields = useCallback((fields: string[]) => {
        setDynamicFields(prev => ({ ...prev, technicalSkills: fields }));
    }, []);

    const setSoftSkillFields = useCallback((fields: string[]) => {
        setDynamicFields(prev => ({ ...prev, softSkills: fields }));
    }, []);

    return (
        <FormFieldsProvider
            setTechnicalSkillFields={setTechnicalSkillFields}
            setSoftSkillFields={setSoftSkillFields}
            onNavigateToStep={navigateToStep}
        >
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={currentStep} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="flex flex-col h-full">
                    <div className="flex-1 p-4 overflow-y-auto">
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)}>
                                {currentStep === 0 && <PersonalInfo />}
                                {currentStep === 1 && <TechnicalSkill />}
                                {currentStep === 2 && <SoftSkill />}
                                {currentStep === 3 && (
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

