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
                <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                    <div className="max-w-md w-full space-y-8">
                        <Card>
                            <CardHeader className="text-center">
                                <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                                    <CheckCircle className="h-6 w-6 text-green-600" />
                                </div>
                                <CardTitle className="text-2xl font-bold text-gray-900">
                                    Email Verified!
                                </CardTitle>
                                <CardDescription className="text-gray-600">
                                    Your account has been successfully created and is pending adviser approval.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <Alert>
                                    <Mail className="h-4 w-4" />
                                    <AlertDescription>
                                        You will receive an email notification once your account is approved by an adviser.
                                        You can then log in to access the system.
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
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Verify Email" />
            <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-md w-full space-y-8">
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                                <Mail className="h-6 w-6 text-blue-600" />
                            </div>
                            <CardTitle className="text-2xl font-bold text-gray-900">
                                Verify Your Email
                            </CardTitle>
                            <CardDescription className="text-gray-600">
                                Please verify your email address to complete your registration.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="text-center">
                                <p className="text-sm text-gray-600 mb-4">
                                    We've sent a verification link to:
                                </p>
                                <p className="font-medium text-gray-900">{email}</p>
                            </div>

                            {error && (
                                <Alert variant="destructive">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{error}</AlertDescription>
                                </Alert>
                            )}

                            <Button 
                                onClick={handleVerification}
                                disabled={isVerifying || processing}
                                className="w-full"
                            >
                                {isVerifying || processing ? 'Verifying...' : 'Verify My Email'}
                            </Button>

                            <div className="text-center">
                                <p className="text-xs text-gray-500">
                                    Didn't receive the email? Check your spam folder or{' '}
                                    <a href={route('register')} className="text-blue-600 hover:text-blue-500">
                                        try registering again
                                    </a>
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}