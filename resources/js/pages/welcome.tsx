import AppLogoIcon from '@/components/app-logo-icon';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { UserCheck, BriefcaseBusinessIcon, SquareKanbanIcon, NotepadTextIcon } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center relative bg-background">
                {/* Background Image */}
                <div 
                    className="fixed inset-0 bg-cover bg-center bg-no-repeat opacity-50 dark:opacity-25" 
                    style={{backgroundImage: 'url(/images/pimentel.jpg)'}} 
                />
                {/* Minimal Background Overlay */}
                <div className="fixed inset-0 bg-[#FDFDFC]/75 dark:bg-[#0a0a0a]/80" />
                
                {/* Content Container */}
                <div className="relative z-10 flex min-h-screen flex-col items-center p-6 text-foreground lg:justify-center lg:p-8 w-full">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-5xl">
                    <div className="flex items-center justify-between">
                        <AppLogoIcon className="h-8 w-auto" />
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
                                        Welcome to InternConnect BSIT
                                    </h1>
                                    <p className="text-muted-foreground text-lg">
                                        Streamlining the Internship Process for students of <br /> Bachelor of Science in Information Technology 
                                    </p>
                                </div>
                            </div>

                            {/* How It Works Section */}
                            <div className="space-y-8 mb-12">
                                <div className="text-center space-y-4">
                                    <h2 className="text-2xl md:text-3xl font-bold text-foreground">How It Works</h2>
                                    <p className="text-muted-foreground">
                                        Simple steps to find your perfect internship match
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <NotepadTextIcon className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">1. Assessment</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Students complete skill assessments across various IT competencies.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <SquareKanbanIcon className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">2. Matching</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Our deterministic weighted scoring algorithm analyzes skills to match students with suitable HTEs.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <UserCheck className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">3. Endorsement</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                The SIP Coordinator endorses students for HTE's to review and place.
                                            </CardDescription>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-card border-border">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-destructive/10 flex items-center justify-center mb-3">
                                                <BriefcaseBusinessIcon className="h-5 w-5 text-destructive" />
                                            </div>
                                            <CardTitle className="text-lg text-foreground">4. Placement</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-muted-foreground">
                                                Students are placed with their matched HTEs to begin their internship journey and gain experience.
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
