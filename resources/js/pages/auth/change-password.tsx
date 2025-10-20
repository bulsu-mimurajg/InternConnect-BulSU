import React, { useState, useRef } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '@/layouts/auth-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle, EyeIcon, EyeOffIcon } from 'lucide-react';
import InputError from '@/components/input-error';

export default function ChangePassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    const passwordInputRef = useRef<HTMLInputElement>(null);
    const confirmPasswordInputRef = useRef<HTMLInputElement>(null);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.change'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Change Password" description="You must change your password before continuing">
            <Head title="Change Password" />

            <form onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="flex flex-col gap-2">
                        <Label htmlFor="password">New Password</Label>
                        <div className="relative">
                            <Input
                                ref={passwordInputRef}
                                id="password"
                                type={showPassword ? "text" : "password"}
                                required
                                tabIndex={1}
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                disabled={processing}
                                placeholder="New password"
                                className="pr-10"
                                autoFocus
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute right-0 top-0 h-9 w-9 px-0 hover:bg-transparent"
                                onClick={() => {
                                    setShowPassword(!showPassword);
                                    // Refocus the password input after toggling
                                    setTimeout(() => passwordInputRef.current?.focus(), 0);
                                }}
                                tabIndex={-1}
                                aria-label={showPassword ? "Hide password" : "Show password"}
                            >
                                {showPassword ? (
                                    <EyeOffIcon className="h-4 w-4 text-muted-foreground" />
                                ) : (
                                    <EyeIcon className="h-4 w-4 text-muted-foreground" />
                                )}
                            </Button>
                        </div>
                        <div className="space-y-1">
                            <InputError message={errors.password} />
                        </div>
                    </div>

                    <div className="flex flex-col gap-2">
                        <Label htmlFor="password_confirmation">Confirm New Password</Label>
                        <div className="relative">
                            <Input
                                ref={confirmPasswordInputRef}
                                id="password_confirmation"
                                type={showConfirmPassword ? "text" : "password"}
                                required
                                tabIndex={2}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                disabled={processing}
                                placeholder="Confirm new password"
                                className="pr-10"
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                className="absolute right-0 top-0 h-9 w-9 px-0 hover:bg-transparent"
                                onClick={() => {
                                    setShowConfirmPassword(!showConfirmPassword);
                                    // Refocus the confirm password input after toggling
                                    setTimeout(() => confirmPasswordInputRef.current?.focus(), 0);
                                }}
                                tabIndex={-1}
                                aria-label={showConfirmPassword ? "Hide confirm password" : "Show confirm password"}
                            >
                                {showConfirmPassword ? (
                                    <EyeOffIcon className="h-4 w-4 text-muted-foreground" />
                                ) : (
                                    <EyeIcon className="h-4 w-4 text-muted-foreground" />
                                )}
                            </Button>
                        </div>
                        <div className="space-y-1">
                            <InputError message={errors.password_confirmation} />
                        </div>
                    </div>

                    <div className="flex flex-col gap-2">
                        <p className="text-xs text-[#706f6c] dark:text-[#A1A09A] text-justify">
                            Password must be at least 8 characters, have at least one uppercase letter, have at least one lowercase letter, have at least one number,
                            and have at least one special character (@$!%*?&).
                        </p>
                    </div>

                    <Button type="submit" className="mt-2 w-full" disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Change Password
                    </Button>
                </div>
            </form>
        </AuthLayout>
    );
}
