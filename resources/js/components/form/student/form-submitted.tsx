import FormStepCounter from '@/components/form/form-step-counter';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';

export default function FormSubmitted() {
    const steps = [
        {
            id: 'Step 1',
            name: 'Personal Information',
        },
        {
            id: 'Step 2',
            name: 'Language Proficiency',
        },
        {
            id: 'Step 3',
            name: 'Technical Skills',
        },
        {
            id: 'Step 4',
            name: 'Soft Skills',
        },
        { id: 'Step 5', name: 'Submission' },
    ];
    const handleViewProfile = () => {
        router.visit('student-profile');
    };

    // If form is submitted, show success state

    return (
        <>
            <div className="flex justify-center">
                <FormStepCounter steps={steps} currentStep={steps.length - 1} />
            </div>
            <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div className="p-4">
                    <div className="space-y-6 text-center">
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
