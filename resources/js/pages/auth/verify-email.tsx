import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle, Mail, AlertCircle } from 'lucide-react';

interface VerifyEmailProps {
    token: string;
    email: string;
}

export default function VerifyEmail({ token, email }: VerifyEmailProps) {
    const [isVerifying, setIsVerifying] = useState(false);
    const [isVerified, setIsVerified] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const { post, processing } = useForm({
        token: token,
    });

    const handleVerification = async () => {
        setIsVerifying(true);
        setError(null);

        try {
            await post(route('verify-account.verify'), {
                onSuccess: () => {
                    setIsVerified(true);
                },
                onError: (errors) => {
                    setError(errors.verification || 'Verification failed. Please try again.');
                },
                onFinish: () => {
                    setIsVerifying(false);
                }
            });
        } catch {
            setError('An unexpected error occurred. Please try again.');
            setIsVerifying(false);
        }
    };

    if (isVerified) {
        return (
            <>
                <Head title="Email Verified" />
                <div className="min-h-screen flex items-center justify-center bg-background p-4">
                    <Card className="w-full max-w-md">
                        <CardHeader className="text-center space-y-4">
                            <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                <CheckCircle className="h-6 w-6 text-green-600" />
                            </div>
                            <div className="space-y-2">
                                <CardTitle className="text-xl font-semibold">
                                    Email Verified
                                </CardTitle>
                                <CardDescription>
                                    Your account is pending adviser approval
                                </CardDescription>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Alert>
                                <Mail className="h-4 w-4" />
                                <AlertDescription className="text-sm">
                                    You'll receive an email notification once approved. You can then log in to access the system.
                                </AlertDescription>
                            </Alert>
                            <Button 
                                onClick={() => window.location.href = route('login')}
                                className="w-full"
                            >
                                Go to Login
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Verify Email" />
            <div className="min-h-screen flex items-center justify-center bg-background p-4">
                <Card className="w-full max-w-md">
                    <CardHeader className="text-center space-y-4">
                        <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary/10">
                            <Mail className="h-6 w-6 text-primary" />
                        </div>
                        <div className="space-y-2">
                            <CardTitle className="text-xl font-semibold">
                                Verify Your Email
                            </CardTitle>
                            <CardDescription>
                                Complete your registration by verifying your email address
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="text-center space-y-2">
                            <p className="text-sm text-muted-foreground">
                                Verification link sent to:
                            </p>
                            <p className="font-medium text-foreground">{email}</p>
                        </div>

                        {error && (
                            <Alert variant="destructive">
                                <AlertCircle className="h-4 w-4" />
                                <AlertDescription className="text-sm">{error}</AlertDescription>
                            </Alert>
                        )}

                        <Button 
                            onClick={handleVerification}
                            disabled={isVerifying || processing}
                            className="w-full"
                        >
                            {isVerifying || processing ? 'Verifying...' : 'Verify Email'}
                        </Button>

                        <div className="text-center">
                            <p className="text-xs text-muted-foreground">
                                Didn't receive the email? Check your spam folder or{' '}
                                <a href={route('register')} className="text-primary hover:text-primary/80 underline">
                                    try registering again
                                </a>
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}