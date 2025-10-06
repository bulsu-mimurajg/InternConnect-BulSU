import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, Building2, Target } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function About() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="About InternConnect BSIT">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center relative">
                {/* Background Image */}
                <div 
                    className="fixed inset-0 bg-cover bg-center bg-no-repeat opacity-50 dark:opacity-25" 
                    style={{backgroundImage: 'url(/images/pimentel.jpg)'}} 
                />
                {/* Background Overlay */}
                <div className="fixed inset-0 bg-[#FDFDFC]/75 dark:bg-[#0a0a0a]/80" />
                
                {/* Content Container */}
                <div className="relative z-10 flex min-h-screen flex-col items-center p-6 text-foreground lg:justify-center lg:p-8 w-full">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-5xl">
                    <div className="flex items-center justify-between">
                        <Link href={route('home')} className="hover:opacity-80 transition-opacity">
                            <AppLogoIcon className="h-8 w-auto" />
                        </Link>
                        <nav className="flex items-center justify-end gap-4">
                            {auth.user ? (
                                <>
                                    {auth.role === 'admin' && (
                                        <Link
                                            prefetch
                                            href={route('admin.dashboard')}
                                            className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border/80"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'hte' && (
                                        <Link
                                            prefetch
                                            href={route('hte.dashboard')}
                                            className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border/80"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'adviser' && (
                                        <Link
                                            prefetch
                                            href={route('adviser.dashboard')}
                                            className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border/80"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'student' && (
                                        <Link
                                            prefetch
                                            href={route('student.dashboard')}
                                            className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border/80"
                                        >
                                            Dashboard
                                        </Link>
                                    )}
                                </>
                            ) : (
                                <Link
                                    prefetch
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-border px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border/80"
                                >
                                    Login
                                </Link>
                            )}

                            <Link
                                prefetch
                                href={route('about')}
                                className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border"
                            >
                                About
                            </Link>
                            <Link
                                prefetch
                                href={route('contact')}
                                className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-foreground hover:border-border"
                            >
                                Contact
                            </Link>
                        </nav>
                    </div>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="w-full max-w-[335px] lg:max-w-5xl">
                        <div className="rounded-br-lg rounded-bl-lg bg-card/10 backdrop-blur-sm p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_hsl(var(--border))] lg:rounded-lg lg:p-20">
                            {/* Hero Section */}
                            <div className="text-center space-y-6 mb-12">
                                <div className="space-y-2">
                                    <h1 className="text-3xl md:text-4xl font-bold text-foreground">
                                        About InternConnect BSIT
                                    </h1>
                                    <p className="text-muted-foreground text-lg">
                                        Capstone Project
                                    </p>
                                </div>
                                
                                <p className="text-md leading-relaxed text-center text-foreground">
                                    InternConnect BulSU is an internship skills matching system designed to streamline 
                                    the process of matching Bachelor of Science in Information Technology students with Host Training Establishments (HTEs).
                                </p>
                                <p className="text-md leading-relaxed text-center text-foreground">
                                    Our platform facilitates student assessments, SIP endorsements, and HTE placements for the college of Bachelor of Science in Information Technology at Bulacan State University.
                                </p>
                            </div>

                            {/* Features Section */}
                            <div className="space-y-8">
                                <div className="text-center space-y-4">
                                    <h2 className="text-2xl md:text-3xl font-bold text-foreground">Key Features</h2>
                                    <p className="text-muted-foreground max-w-xxl mx-auto">
                                        Equipping SIP Coordinators, HTEs, Advisers, and Students with tools to streamline the internship process
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <Users className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">Student Management</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Comprehensive student profiles with competency tracking.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <Building2 className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">HTE Integration</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Integration with Host Training Establishments 
                                                for posting opportunities and managing placements.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <Target className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">Smart Matching</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Weighted scoring algorithm that matches students 
                                                with suitable internship opportunities.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
                </div>
            </div>
        </>
    );
}
