import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Mail, MapPin, Clock, Users, Building2 } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';

export default function Contact() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Contact Us - InternConnect BSIT">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center relative bg-background">
                {/* Background Image */}
                <div 
                    className="fixed inset-0 bg-cover bg-center bg-no-repeat opacity-50 dark:opacity-25" 
                    style={{backgroundImage: 'url(/images/pimentel.jpg)'}} 
                />
                {/* Background Overlay */}
                <div className="fixed inset-0 bg-background/40 dark:bg-background/60" />
                
                {/* Content Container */}
                <div className="relative z-10 flex min-h-screen flex-col items-center p-6 text-foreground lg:justify-center lg:p-8 w-full">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-5xl">
                    <div className="flex items-center justify-between w-full">
                        <Link href={route('home')} className="hover:opacity-80 transition-opacity flex-shrink-0">
                            <AppLogoIcon className="h-8 w-auto md:h-10" />
                        </Link>
                        <nav className="flex items-center justify-end gap-2 md:gap-4 flex-shrink-0">
                            {auth.user ? (
                                <>
                                    {auth.role === 'admin' && (
                                        <Link
                                            prefetch
                                            href={route('admin.dashboard')}
                                            className="inline-block rounded-sm border border-black dark:border-accent-foreground px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'hte' && (
                                        <Link
                                            prefetch
                                            href={route('hte.dashboard')}
                                            className="inline-block rounded-sm border border-border px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'adviser' && (
                                        <Link
                                            prefetch
                                            href={route('adviser.dashboard')}
                                            className="inline-block rounded-sm border border-border px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'student' && (
                                        <Link
                                            prefetch
                                            href={route('student.dashboard')}
                                            className="inline-block rounded-sm border border-border px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                                        >
                                            Dashboard
                                        </Link>
                                    )}
                                </>
                            ) : (
                                <Link
                                    prefetch
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-accent-foreground px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                                >
                                    Login
                                </Link>
                            )}

                            <Link
                                prefetch
                                href={route('about')}
                                className="inline-block rounded-sm border border-transparent px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                            >
                                About
                            </Link>
                            <Link
                                prefetch
                                href={route('contact')}
                                className="inline-block rounded-sm border border-transparent px-3 md:px-5 py-1.5 text-xs md:text-sm leading-normal text-foreground hover:border-foreground/20 hover:bg-foreground/5 whitespace-nowrap"
                            >
                                Contact
                            </Link>
                        </nav>
                    </div>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-10">
                    <main className="w-full max-w-[335px] lg:max-w-5xl">
                        <div className="rounded-br-lg rounded-bl-lg bg-card/40 backdrop-blur-sm p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_hsl(var(--border))] lg:rounded-lg lg:p-20">
                            {/* Hero Section */}
                            <div className="text-center space-y-6 mb-12">
                                <div className="space-y-2">
                                    <h1 className="text-3xl md:text-4xl font-bold text-foreground">
                                        Contact Us
                                    </h1>
                                    <p className="text-muted-foreground text-lg">
                                        Get in touch with the InternConnect BSIT team
                                    </p>
                                </div>
                                
                                <p className="text-md leading-relaxed text-center text-foreground">
                                Have questions about the system? Encountered issues? 
                                You may reach out to us through the channels below.
                                </p>
                            </div>

                            {/* Contact Information Cards */}
                            <div className="space-y-8">
                                <div className="text-center space-y-4">
                                    <h2 className="text-2xl md:text-3xl font-bold text-foreground">Contact Information</h2>
                                    <p className="text-muted-foreground max-w-xxl mx-auto">
                                        Reach out to us through various channels for support and inquiries
                                    </p>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <Card className="bg-white dark:bg-[#161615] border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-[#f53003]/10 dark:bg-[#FF4433]/10 flex items-center justify-center mb-3">
                                                <Mail className="h-5 w-5 text-[#f53003] dark:text-[#FF4433]" />
                                            </div>
                                            <CardTitle className="text-lg text-[#1b1b18] dark:text-[#EDEDEC]">Email Support</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-[#706f6c] dark:text-[#A1A09A] mb-2">
                                                For technical issues and general inquiries
                                            </CardDescription>
                                            <a 
                                                href="mailto:internconnectbulsu@gmail.com"
                                                className="text-[#f53003] dark:text-[#FF4433] font-medium hover:underline"
                                            >
                                                internconnectbulsu@gmail.com
                                            </a>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-white dark:bg-[#161615] border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-[#f53003]/10 dark:bg-[#FF4433]/10 flex items-center justify-center mb-3">
                                                <Building2 className="h-5 w-5 text-[#f53003] dark:text-[#FF4433]" />
                                            </div>
                                            <CardTitle className="text-lg text-[#1b1b18] dark:text-[#EDEDEC]">GitHub Repository</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-[#706f6c] dark:text-[#A1A09A] mb-2">
                                                View source code and documentation
                                            </CardDescription>
                                            <a 
                                                href="https://github.com/bulsu-mimurajg/InternConnect-BulSU"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="text-[#f53003] dark:text-[#FF4433] font-medium hover:underline"
                                            >
                                                View Repository
                                            </a>
                                        </CardContent>
                                    </Card>

                                    <Card className="bg-white dark:bg-[#161615] border-[#e3e3e0] dark:border-[#3E3E3A]">
                                        <CardHeader className="pb-3">
                                            <div className="h-10 w-10 rounded-lg bg-[#f53003]/10 dark:bg-[#FF4433]/10 flex items-center justify-center mb-3">
                                                <Users className="h-5 w-5 text-[#f53003] dark:text-[#FF4433]" />
                                            </div>
                                            <CardTitle className="text-lg text-[#1b1b18] dark:text-[#EDEDEC]">Development Team</CardTitle>
                                        </CardHeader>
                                        <CardContent>
                                            <CardDescription className="text-[#706f6c] dark:text-[#A1A09A] mb-2">
                                                BSIT 4EG2 | Capstone Project
                                            </CardDescription>
                                            <p className="text-[#1b1b18] dark:text-[#EDEDEC] text-sm">
                                                Bulacan State University
                                            </p>
                                        </CardContent>
                                    </Card>
                                </div>

                                <div className="text-center space-y-4 pt-8">
                                    <h3 className="text-xl font-semibold text-foreground">Visit the University Website</h3>
                                    <div className="flex flex-wrap gap-4 justify-center">
                                        <a 
                                            href="https://bulsu.edu.ph/" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center space-x-1 font-medium text-[#f53003] dark:text-[#FF4433] underline underline-offset-4"
                                        >
                                            <span>BulSU Website</span>
                                            <svg
                                                width={10}
                                                height={11}
                                                viewBox="0 0 10 11"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-2.5 w-2.5"
                                            >
                                                <path
                                                    d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                                    stroke="currentColor"
                                                    strokeLinecap="square"
                                                />
                                            </svg>
                                        </a>
                                    </div>
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