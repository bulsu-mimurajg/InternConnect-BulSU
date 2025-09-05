import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { AlertTriangle, FileText, ArrowRight } from 'lucide-react';

interface SubmissionPromptProps {
    showPrompt: boolean;
    title?: string;
    description?: string;
    buttonText?: string;
    buttonHref?: string;
}

export function SubmissionPrompt({ 
    showPrompt, 
    title = "Complete Your Assessment Form",
    description = "Please complete the assessment form first to access all features and manage your internships effectively.",
    buttonText = "Complete Form",
    buttonHref = "/form"
}: SubmissionPromptProps) {
    if (!showPrompt) {
        return null;
    }

    return (
        <Alert className="border-amber-200 bg-amber-50 text-amber-800">
            <AlertTriangle className="h-4 w-4" />
            <AlertTitle className="flex items-center gap-2">
                <FileText className="h-4 w-4" />
                {title}
            </AlertTitle>
            <AlertDescription className="mt-2">
                <p className="mb-3">{description}</p>
                <Link href={buttonHref}>
                    <Button size="sm" className="bg-amber-600 hover:bg-amber-700 text-white">
                        {buttonText}
                        <ArrowRight className="h-4 w-4 ml-1" />
                    </Button>
                </Link>
            </AlertDescription>
        </Alert>
    );
}
