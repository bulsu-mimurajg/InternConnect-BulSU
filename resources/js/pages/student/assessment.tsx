import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
// import StudentForm from '@/components/form/student/form';
import FormStepCounter from '@/components/form/form-step-counter';
import PersonalInfo from '@/components/form/student/personal-info';
import SoftSkill from '@/components/form/student/soft-skill';
import Summary from '@/components/form/student/summary';
import TechnicalSkill from '@/components/form/student/technical-skill';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { zodResolver } from '@hookform/resolvers/zod';
import { useState } from 'react';
import { Path, useForm } from 'react-hook-form';
import { z } from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment',
        href: '/',
    },
];

const stepOneFields = ['firstName', 'lastName', 'middleName', 'suffix'];
const stepTwoFields = ['cplusplus', 'csharp', 'java', 'python', 'mysql'];
const stepThreeFields = ['explain_complex_ideas', 'collaboration'];

const steps = [
    {
        id: 'Step 1',
        name: 'Personal Information',
        fields: stepOneFields,
    },
    {
        id: 'Step 2',
        name: 'Technical Skills',
        fields: stepTwoFields,
    },
    {
        id: 'Step 3',
        name: 'Soft Skills',
        fields: stepThreeFields,
    },
    { id: 'Step 4', name: 'Submission' },
];

const FormSchema = z.object({
    firstName: z.string().min(1, { message: 'Required.' }),
    lastName: z.string().min(1, { message: 'Required.' }),
    middleName: z.string(),
    suffix: z.string(),
    ...Object.fromEntries(
        stepTwoFields.map((field) => [
            field,
            z.enum(["1", "2", "3", "4", "5"], {
                error: 'Please select a skill level',
            }),
        ]),
    ),
});

export default function Assessment() {
    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {
            firstName: 'a',
            lastName: 'a',
            middleName: 'a',
            suffix: 'a',
            ...Object.fromEntries(stepTwoFields.map((field) => [field, undefined])),
        },
    });

    function onSubmit(values: z.infer<typeof FormSchema>) {
        console.log(values);
    }

    const [currentStep, setCurrentStep] = useState(0);
    const prev = () => {
        if (currentStep > 0) {
            setCurrentStep((prev) => prev - 1);
        }
    };

    const next = async () => {
        const fields = steps[currentStep].fields as Path<z.infer<typeof FormSchema>>[];
        const isValid = await form.trigger(fields, { shouldFocus: true });

        if (isValid) {
            setCurrentStep((prev) => prev + 1);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assessment" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-hidden rounded-xl p-4">
                {/*<StudentForm />*/}
                <div className="flex justify-center">
                    <FormStepCounter steps={steps} currentStep={currentStep} />
                </div>
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <div className="p-4">
                        <div className="">
                            <Form {...form}>
                                <form onSubmit={form.handleSubmit(onSubmit)}>
                                    {currentStep === 0 && <PersonalInfo />}
                                    {currentStep === 1 && <TechnicalSkill />}
                                    {currentStep === 2 && <SoftSkill />}
                                    {currentStep === 3 && (
                                        <div className="space-y-6">
                                            <h2 className="text-xl font-semibold">Review Your Answers</h2>
                                            <Summary />
                                            <Button type="submit">Submit</Button>
                                        </div>
                                    )}
                                </form>
                            </Form>
                        </div>
                        <div className="mt-4 flex justify-end gap-2">
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
        </AppLayout>
    );
}
