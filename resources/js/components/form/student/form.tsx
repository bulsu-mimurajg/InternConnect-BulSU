import FormStepCounter from '@/components/form/form-step-counter';
import PersonalInfo from '@/components/form/student/personal-info';
import SoftSkill from '@/components/form/student/soft-skill';
import Summary from '@/components/form/student/summary';
import TechnicalSkill from '@/components/form/student/technical-skill';
import LanguageProficiency from '@/components/form/student/language-proficiency';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState, useEffect } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';
import { router, usePage } from '@inertiajs/react';
import { FormFieldsProvider } from '@/contexts/FormFieldsContext';
import { subcategory } from '@/types';

type Props = {
    subcategories: subcategory[];
    hasSubmitted: boolean;
};

export default function StudentForm({ subcategories, hasSubmitted }: Props) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;
    const [isSubmitted, setIsSubmitted] = useState(hasSubmitted);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Check for success message on mount or if hasSubmitted is true
    useEffect(() => {
        if (flash?.success || hasSubmitted) {
            setIsSubmitted(true);
        }
    }, [flash?.success, hasSubmitted]);

    // Dynamic field tracking
    const [dynamicFields, setDynamicFields] = useState<{
        languageProficiency: string[];
        technicalSkills: string[];
        softSkills: string[];
    }>({
        languageProficiency: [],
        technicalSkills: [],
        softSkills: [],
    });

    const stepOneFields = ['firstName', 'lastName', 'middleName', 'suffix', 'province', 'city', 'zip'];

    const steps = [
        {
            id: 'Step 1',
            name: 'Personal Information',
            fields: stepOneFields,
        },
        {
            id: 'Step 2',
            name: 'Language Proficiency',
            fields: () => dynamicFields.languageProficiency,
        },
        {
            id: 'Step 3',
            name: 'Technical Skills',
            fields: () => dynamicFields.technicalSkills,
        },
        {
            id: 'Step 4',
            name: 'Soft Skills',
            fields: () => dynamicFields.softSkills,
        },
        { id: 'Step 5', name: 'Submission' },
    ];
    // Create dynamic validation schema
    const createFormSchema = () => {
        const baseSchema = {
            firstName: z.string().min(1, 'First name is required'),
            lastName: z.string().min(1, 'Last name is required'),
            middleName: z.string().optional(),
            suffix: z.string().optional(),
            province: z.string().optional(),
            city: z.string().optional(),
            zip: z.string().optional(),
        };

        // Add dynamic fields for language proficiency
        const languageSchema: Record<string, any> = {};
        dynamicFields.languageProficiency.forEach(field => {
            languageSchema[field] = z.string().min(1, 'This field is required');
        });

        // Add dynamic fields for technical skills
        const technicalSchema: Record<string, any> = {};
        dynamicFields.technicalSkills.forEach(field => {
            technicalSchema[field] = z.string().min(1, 'This field is required');
        });

        // Add dynamic fields for soft skills
        const softSchema: Record<string, any> = {};
        dynamicFields.softSkills.forEach(field => {
            softSchema[field] = z.string().min(1, 'This field is required');
        });

        return z.object({
            ...baseSchema,
            ...languageSchema,
            ...technicalSchema,
            ...softSchema,
        });
    };

    const FormSchema = createFormSchema();

    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {},
    });

    function onSubmit(values: z.infer<typeof FormSchema>) {
        setIsSubmitting(true);
        router.post('/assessment', values, {
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
        const currentStepData = steps[currentStep];
        const fields = typeof currentStepData.fields === 'function' 
            ? currentStepData.fields() 
            : currentStepData.fields;
        
        const isValid = await form.trigger(fields as Path<z.infer<typeof FormSchema>>[], { shouldFocus: true });

        if (isValid) {
            setCurrentStep((prev) => prev + 1);
        }
    };

    const handleViewProfile = () => {
        router.visit('/settings/profile');
    };

    // If form is submitted, show success state
    if (isSubmitted) {
        return (
            <>
                <div className="flex justify-center">
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
                                <h2 className="text-2xl font-semibold text-gray-900 dark:text-white">Assessment Submitted Successfully!</h2>
                                <p className="text-gray-600 dark:text-gray-400">
                                    Thank you for completing your assessment. Your responses have been recorded and will be used for internship matching.
                                </p>
                            </div>
                            <Button onClick={handleViewProfile} className="bg-blue-600 hover:bg-blue-700">
                                View Profile
                            </Button>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    return (
        <FormFieldsProvider
            setLanguageProficiencyFields={(fields) => setDynamicFields(prev => ({ ...prev, languageProficiency: fields }))}
            setTechnicalSkillFields={(fields) => setDynamicFields(prev => ({ ...prev, technicalSkills: fields }))}
            setSoftSkillFields={(fields) => setDynamicFields(prev => ({ ...prev, softSkills: fields }))}
        >
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={currentStep} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="p-4">
                    <div className="">
                        <Form {...form}>
                            <form onSubmit={form.handleSubmit(onSubmit)}>
                                {currentStep === 0 && <PersonalInfo />}
                                {currentStep === 1 && <LanguageProficiency />}
                                {currentStep === 2 && <TechnicalSkill />}
                                {currentStep === 3 && <SoftSkill />}
                                {currentStep === 4 && (
                                    <div className="space-y-6">
                                        <h2 className="text-xl font-semibold">Review Your Answers</h2>
                                        <Summary />
                                        <Button type="submit" disabled={isSubmitting}>
                                            {isSubmitting ? 'Submitting...' : 'Submit'}
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
                            <Button onClick={prev} disabled={currentStep === 0}>
                                Previous
                            </Button>
                            <Button onClick={next} disabled={currentStep === steps.length - 1}>
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </FormFieldsProvider>
    );
}

