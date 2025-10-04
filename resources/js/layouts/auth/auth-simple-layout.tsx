import { CictLogoIcon } from '@/components/app-logo-icon';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
    wide?: boolean;
}

export default function AuthSimpleLayout({ children, title, description, wide = false }: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center relative">
            {/* Background Image */}
            <div 
                className="fixed inset-0 bg-cover bg-center bg-no-repeat opacity-50 dark:opacity-25" 
                style={{backgroundImage: 'url(/images/pimentel.jpg)'}} 
            />
            {/* Minimal Background Overlay */}
            <div className="fixed inset-0 bg-[#FDFDFC]/75 dark:bg-[#0a0a0a]/80" />
            
            {/* Content Container */}
            <div className="relative z-10 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 w-full">
                <div className={`w-full ${wide ? 'max-w-2xl' : 'max-w-sm'}`}>
                    <div className="rounded-lg bg-orange-50/10 backdrop-blur-sm p-6 pb-8 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-orange-950/8 dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                        <div className="flex flex-col gap-8">
                            <div className="flex flex-col items-center gap-4">
                                <Link href={route('home')} className="flex flex-col items-center gap-2 font-medium">
                                    <div className="mb-1 flex h-22 w-auto items-center justify-center gap-4 rounded-md">
                                        <img 
                                            src="/bulsu_logo_svg.svg" 
                                            alt="BulSU Logo" 
                                            className="h-22 w-auto"
                                        />
                                        <CictLogoIcon className="size-22 fill-current text-[var(--foreground)] dark:text-white" />
                                    </div>
                                    <span className="sr-only">{title}</span>
                                </Link>

                                <div className="space-y-2 text-center">
                                    <h1 className="text-xl font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{title}</h1>
                                    <p className="text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">{description}</p>
                                </div>
                            </div>
                            {children}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
