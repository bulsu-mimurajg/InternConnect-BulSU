import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AuthLayout from '@/layouts/auth-layout';

type Section = {
    section_id: number;
    section_name: string;
};

type RegisterForm = {
    first_name: string;
    last_name: string;
    middle_name: string;
    username: string;
    email: string;
    contact_number: string;
    password: string;
    password_confirmation: string;
    section_id: string;
};

interface RegisterProps {
    sections: Section[];
}

export default function Register({ sections }: RegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        first_name: '',
        last_name: '',
        middle_name: '',
        username: '',
        email: '',
        contact_number: '',
        password: '',
        password_confirmation: '',
        section_id: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Create an account" description="Enter your details below to create your account" wide>
            <Head title="Register" />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    {/* Name fields row */}
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-3 md:items-start">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="given-name"
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                disabled={processing}
                                placeholder="First Name"
                            />
                            <div className="space-y-1">
                                <div></div>
                                <InputError message={errors.first_name} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                type="text"
                                required
                                tabIndex={2}
                                autoComplete="family-name"
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                disabled={processing}
                                placeholder="Last Name"
                            />
                            <div className="space-y-1">
                                <div></div>
                                <InputError message={errors.last_name} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="middle_name">Middle Name</Label>
                            <Input
                                id="middle_name"
                                type="text"
                                tabIndex={3}
                                autoComplete="additional-name"
                                value={data.middle_name}
                                onChange={(e) => setData('middle_name', e.target.value)}
                                disabled={processing}
                                placeholder="Middle Name"
                            />
                            <div className="space-y-1">
                                <div></div>
                                <InputError message={errors.middle_name} />
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-start">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="username">Username</Label>
                            <Input
                                id="username"
                                type="text"
                                required
                                tabIndex={4}
                                autoComplete="username"
                                value={data.username}
                                onChange={(e) => {
                                    const value = e.target.value.replace(/\D/g, '');
                                    if (value.length <= 10) {
                                        setData('username', value);
                                    }
                                }}
                                disabled={processing}
                                placeholder="Ex. 2022100488"
                                maxLength={10}
                            />
                            <div className="space-y-1">
                                <p className="text-xs text-muted-foreground">Use your student number</p>
                                <InputError message={errors.username} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={5}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                                placeholder="email@example.com"
                            />
                            <div className="space-y-1">
                                <div></div>
                                <InputError message={errors.email} />
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-start">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="section">Section</Label>
                            <Select value={data.section_id} onValueChange={(value) => setData('section_id', value)} disabled={processing}>
                                <SelectTrigger tabIndex={6}>
                                    <SelectValue placeholder="Select your section" />
                                </SelectTrigger>
                                <SelectContent>
                                    {sections.length === 0 ? (
                                        <SelectItem value="" disabled>
                                            No sections available
                                        </SelectItem>
                                    ) : (
                                        sections.map((section) => (
                                            <SelectItem key={section.section_id} value={section.section_id.toString()}>
                                                {section.section_name}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectContent>
                            </Select>
                            <p className="text-xs text-muted-foreground">Use your current section</p>
                            <InputError message={errors.section_id} />
                        </div>
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="contact_number">Contact Number</Label>
                            <Input
                                id="contact_number"
                                type="tel"
                                required
                                tabIndex={7}
                                autoComplete="tel"
                                value={data.contact_number}
                                onChange={(e) => {
                                    const value = e.target.value.replace(/\D/g, '');
                                    if (value.length <= 11) {
                                        setData('contact_number', value);
                                    }
                                }}
                                disabled={processing}
                                placeholder="09XXXXXXXXX"
                                pattern="[0-9]{11}"
                                maxLength={11}
                            />
                            <div className="space-y-1">
                                <p className="text-xs text-muted-foreground">Enter your 11-digit mobile number</p>
                                <InputError message={errors.contact_number} />
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-start">
                        <div className="flex flex-col gap-2">
                            <Label htmlFor="password">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={8}
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                disabled={processing}
                                placeholder="Password"
                            />
                            <div className="space-y-1">
                                <p className="text-xs text-muted-foreground">
                                    Must be at least 8 characters, have at least one uppercase letter, one lowercase letter, one number,
                                    and one special character (@$!%*?&).
                                </p>
                                <InputError message={errors.password} />
                            </div>
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label htmlFor="password_confirmation">Confirm password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                tabIndex={9}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                disabled={processing}
                                placeholder="Confirm password"
                            />
                            <div className="space-y-1">
                                <div></div>
                                <InputError message={errors.password_confirmation} />
                            </div>
                        </div>
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={10} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Create account
                    </Button>
                </div>

                <div className="text-center text-sm text-muted-foreground">
                    Already have an account?{' '}
                    <TextLink href={route('login')} tabIndex={11}>
                        Log in
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
