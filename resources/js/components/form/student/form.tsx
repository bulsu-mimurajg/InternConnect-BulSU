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
import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { router } from '@inertiajs/react';
import { AssessmentProps, Category, SubCategory } from '@/types';

// type Question = {
//     id: number;
//     text: string;
// };
//
// type Subcategory = {
//     title: string;
//     questions: Question[];
// };
//
// type Step = {
//     id: number;
//     name: string;
//     subcategory: Subcategory[];
// };

export default function StudentForm({data}: AssessmentProps) {
    const {categories, subcategories, questions} = data;

    const structure =  categories.map(category => {
        const subcategory = subcategories
            .filter(subcategory => subcategory.category_id === category.id)
            .map(subcategory => ({
                ...subcategory,
                questions: questions.filter(question => question.subcategory_id === subcategory.id)
            }));

        return {
            ...category,
            subcategories: subcategory
        }
    });

    const steps = structure.map(({ id, category_name }) => ({ id, category_name }));
    steps.push({id: steps.length + 1, category_name: 'Submission'})

    console.log(steps)

    // const steps: Category[] = [];
    //
    // categories.forEach(category => {
    //     const temp = {
    //         id: category.id,
    //         category_name: category.category_name
    //     }
    //
    //     steps.push(temp);
    // })
    //
    // console.log(steps)

    // const subcategorylist: SubCategory[] = [];
    //
    // steps.forEach(category => {
    //     const temp = subcategories
    //         .filter(subcategory => subcategory.id === category.id)
    //
    // })


    // const structure =  categories.map(category => {
    //     const subcategory = subcategories
    //         .filter(subcategory => subcategory.category_id === category.id)
    //         .map(subcategory => ({
    //             ...subcategory,
    //             questions: questions.filter(question => question.subcategory_id === subcategory.id)
    //         }));
    //
    //     return {
    //         ...category,
    //         subcategories: subcategory
    //     }
    // });
    //
    // console.log(structure)
    //
    // const steps: Step[] = [];
    //
    // structure.forEach(category => {
    //     const categoryStep = {
    //         id: category.id,
    //         name: category.category_name,
    //         subcategories: []
    //     };
    //
    //     category.subcategories.forEach(subcategory => {
    //         const subcategoryStep = {
    //             title: subcategory.subcategory_name,
    //             questions: subcategory.questions
    //         };
    //
    //         // Push each subcategory object into the category's subcategories array
    //         categoryStep.subcategories.push(subcategoryStep);
    //     });
    //
    //     // Push the category object with its subcategories into the steps array
    //     steps.push(categoryStep);    });
    //
    // console.log(steps)

    const [isSubmitting, setIsSubmitting] = useState(false);

    const stepOneFields = ['firstName', 'lastName', 'middleName', 'suffix', 'province', 'city', 'zip'];

    // const steps = [
    //     {
    //         id: 'Step 1',
    //         name: 'Personal Information',
    //         fields: stepOneFields,
    //     },
    //     {
    //         id: 'Step 2',
    //         name: 'Language Proficiency',
    //         fields: [],
    //
    //     },
    //     {
    //         id: 'Step 3',
    //         name: 'Technical Skills',
    //         fields: [],
    //
    //     },
    //     {
    //         id: 'Step 4',
    //         name: 'Soft Skills',
    //         fields: [],
    //     },
    //     { id: 'Step 5', name: 'Submission' },
    // ];


    // Create dynamic validation schema
    // const createFormSchema = () => {
    //     const baseSchema = {
    //         firstName: z.string().min(1, 'First name is required'),
    //         lastName: z.string().min(1, 'Last name is required'),
    //         middleName: z.string().optional(),
    //         suffix: z.string().optional(),
    //         province: z.string().optional(),
    //         city: z.string().optional(),
    //         zip: z.string().optional(),
    //     };
    //
    //     // Add dynamic fields for language proficiency
    //     // const languageSchema: Record<string, any> = {};
    //     // dynamicFields.languageProficiency.forEach(field => {
    //     //     languageSchema[field] = z.string().min(1, 'This field is required');
    //     // });
    //     //
    //     // // Add dynamic fields for technical skills
    //     // const technicalSchema: Record<string, any> = {};
    //     // dynamicFields.technicalSkills.forEach(field => {
    //     //     technicalSchema[field] = z.string().min(1, 'This field is required');
    //     // });
    //     //
    //     // // Add dynamic fields for soft skills
    //     // const softSchema: Record<string, any> = {};
    //     // dynamicFields.softSkills.forEach(field => {
    //     //     softSchema[field] = z.string().min(1, 'This field is required');
    //     // });
    //
    //     return z.object({
    //         ...baseSchema,
    //         // ...languageSchema,
    //         // ...technicalSchema,
    //         // ...softSchema,
    //     });
    // };

    // const FormSchema = createFormSchema();

    const allQuestions = structure.flatMap(category =>
        category.subcategories.flatMap(subcategory =>
        subcategory.questions.map(({text}) => ({text})))
    );

    console.log(allQuestions)

    const FormSchema = z.object({
    });

    const form = useForm<z.infer<typeof FormSchema>>({
        resolver: zodResolver(FormSchema),
        mode: 'onChange',
        defaultValues: {},
    });

    function onSubmit(values: z.infer<typeof FormSchema>) {
        setIsSubmitting(true);
        router.post('/assessment', values, {
            onSuccess: () => {
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
        // const fields = steps[currentStep].fields as Path<z.infer<typeof FormSchema>>[];
        // const isValid = await form.trigger(fields, { shouldFocus: true });

        // if (isValid) {
        //     setCurrentStep((prev) => prev + 1);
        // }

        setCurrentStep((prev) => prev + 1);

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
                                {currentStep === 0 && <></>}
                                {currentStep === 1 && <></>}
                                {currentStep === 2 && <></>}
                                {currentStep === 3 && <></>}
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
        </>
    );
}

