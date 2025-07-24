import FormStepCounter from '@/components/form/form-step-counter';
import { useState } from 'react';

import { Path, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';

import PersonalInfo from '@/components/form/student/personal-info';
import TechnicalSkill from '@/components/form/student/technical-skill';
import SoftSkill from '@/components/form/student/soft-skill';
import Summary from '@/components/form/student/summary';

const stepOneFields = ['firstName', 'lastName', 'middleName', 'suffix'];
const stepTwoFields = ['type'];
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

export const FormSchema = z.object({
    firstName: z.string().min(1, { message: 'Required.' }),
    lastName: z.string().min(1, { message: 'Required.' }),
    middleName: z.string(),
    suffix: z.string(),
});

export default function StudentForm(){
    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: "onChange",
        defaultValues: {
            firstName: '',
            lastName: '',
            middleName: '',
            suffix: '',
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
        <>
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
                                        <Summary values={form.getValues()}/>

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
        </>
    )
}
