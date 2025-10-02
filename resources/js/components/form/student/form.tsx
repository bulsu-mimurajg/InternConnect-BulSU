import FormStepCounter from '@/components/form/form-step-counter';
import PersonalInfo from '@/components/form/student/personal-info';
import SoftSkill from '@/components/form/student/soft-skill';
import Summary from '@/components/form/student/summary';
import TechnicalSkill from '@/components/form/student/technical-skill';
import LanguageProficiency from '@/components/form/student/language-proficiency';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
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
        languageProficiency: string[];
        technicalSkills: string[];
        softSkills: string[];
    }>({
        languageProficiency: [],
        technicalSkills: [],
        softSkills: [],
    });

    // Create additional info field names
    const additionalInfoFields = additionalInfos.map(info => 
        info.info_name.toLowerCase().replace(/[ -]/g, '_')
    );

    const steps = [
        {
            id: 'Step 1',
            name: 'Additional Information',
            fields: [...additionalInfoFields],
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

        // Add additional info fields to validation schema
        const additionalInfoSchema: Record<string, z.ZodString> = {};
        additionalInfoFields.forEach(field => {
            additionalInfoSchema[field] = z.string().min(1, 'This field is required');
        });

        // Add dynamic fields for language proficiency
        const languageSchema: Record<string, z.ZodString> = {};
        dynamicFields.languageProficiency.forEach(field => {
            languageSchema[field] = z.string().min(1, 'This field is required');
        });

        // Add dynamic fields for technical skills
        const technicalSchema: Record<string, z.ZodString> = {};
        dynamicFields.technicalSkills.forEach(field => {
            technicalSchema[field] = z.string().min(1, 'This field is required');
        });

        // Add dynamic fields for soft skills
        const softSchema: Record<string, z.ZodString> = {};
        dynamicFields.softSkills.forEach(field => {
            softSchema[field] = z.string().min(1, 'This field is required');
        });

        // Ensure we always have at least one field in the schema
        const schemaFields = {
            ...additionalInfoSchema,
            ...languageSchema,
            ...technicalSchema,
            ...softSchema,
        };

        // If no fields are present, add a dummy field to prevent empty schema
        if (Object.keys(schemaFields).length === 0) {
            schemaFields['dummy'] = z.string();
        }

        return z.object(schemaFields);
    };

    const FormSchema = createFormSchema();

    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {},
    });

    function onSubmit(values: z.infer<typeof FormSchema>) {
        console.log('Form submission values:', values);
        setIsSubmitting(true);
        
        // Remove dummy field if it exists
        const cleanValues = { ...values };
        if (cleanValues.dummy !== undefined) {
            delete cleanValues.dummy;
        }
        
        router.post('/assessment', cleanValues, {
            onSuccess: (page) => {
                console.log('Form submission successful:', page);
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
        const fields = typeof currentStepData.fields === 'function'
            ? currentStepData.fields()
            : currentStepData.fields;

        const isValid = await form.trigger(fields as Path<z.infer<typeof FormSchema>>[], { shouldFocus: true });

        if (isValid) {
            setCurrentStep((prev) => prev + 1);
        }
    };

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
                <div className="flex flex-col h-full">
                    <div className="flex-1 p-4 overflow-y-auto">
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
                                <Button type="submit" disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
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

