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
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
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
                                            className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'hte' && (
                                        <Link
                                            prefetch
                                            href={route('hte.dashboard')}
                                            className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'adviser' && (
                                        <Link
                                            prefetch
                                            href={route('adviser.dashboard')}
                                            className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                        >
                                            Dashboard
                                        </Link>
                                    )}

                                    {auth.role === 'student' && (
                                        <Link
                                            prefetch
                                            href={route('student.dashboard')}
                                            className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                        >
                                            Dashboard
                                        </Link>
                                    )}
                                </>
                            ) : (
                                <Link
                                    prefetch
                                    href={route('login')}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Login
                                </Link>
                            )}

                            <Link
                                prefetch
                                href={route('about')}
                                className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                            >
                                About
                            </Link>
                            <Link
                                prefetch
                                href={route('contact')}
                                className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                            >
                                Contact
                            </Link>
                        </nav>
                    </div>
                </header>
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="w-full max-w-[335px] lg:max-w-5xl">
                        <div className="rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-lg lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            {/* Hero Section */}
                            <div className="text-center space-y-6 mb-12">
                                <div className="space-y-2">
                                    <h1 className="text-3xl md:text-4xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Contact Us
                                    </h1>
                                    <p className="text-[#706f6c] dark:text-[#A1A09A] text-lg">
                                        Get in touch with the InternConnect BSIT team
                                    </p>
                                </div>
                                
                                <p className="text-md leading-relaxed text-center text-[#1b1b18] dark:text-[#EDEDEC]">
                                Have questions about the system? Encountered issues? 
                                You may reach out to us through the channels below.
                                </p>
                            </div>

                            {/* Contact Information Cards */}
                            <div className="space-y-8">

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
                                                Validex Team - BSIT Capstone Project
                                            </CardDescription>
                                            <p className="text-[#1b1b18] dark:text-[#EDEDEC] text-sm">
                                                Bulacan State University<br />
                                                Class of 2024
                                            </p>
                                        </CardContent>
                                    </Card>
                                </div>

                                <div className="text-center space-y-4 pt-8">
                                    <h3 className="text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Visit the University Website</h3>
                                    <div className="flex flex-wrap gap-4 justify-center">
                                        <a 
                                            href="https://bulsu.edu.ph/" 
                                            target="_blank" 
                                            rel="noopener noreferrer"
                                            className="inline-flex items-center space-x-1 font-medium text-[#f53003] underline underline-offset-4 dark:text-[#FF4433]"
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
        </>
    );
}
