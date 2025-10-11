import { CictLogoIcon, BulSULogoIcon } from '@/components/app-logo-icon';
import AppLogoIcon from '@/components/app-logo-icon';
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
            {/* Background Overlay */}
            <div className="fixed inset-0 bg-background/40 dark:bg-background/60" />
            
            {/* Content Container */}
            <div className="relative z-10 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 w-full">
                <div className={`w-full ${wide ? 'max-w-2xl' : 'max-w-sm'}`}>
                    <div className="rounded-lg bg-card/40 backdrop-blur-sm p-6 pb-8 shadow-[inset_0px_0px_0px_1px_hsl(var(--border))]">
                        <div className="flex flex-col gap-8">
                            <div className="flex flex-col items-center gap-4">
                                <Link href={route('home')} className="flex flex-col items-center gap-2 font-medium">
                                    <div className="mb-1 flex h-16 md:h-20 lg:h-24 w-auto items-center justify-center rounded-md">
                                        <BulSULogoIcon className="h-20 w-20 md:h-24 md:w-24 lg:h-28 lg:w-28 object-contain flex-shrink-0" />
                                        <AppLogoIcon className="h-10 w-10 md:h-14 md:w-14 lg:h-18 lg:w-18 object-contain flex-shrink-0" />
                                        <div className="w-2 md:w-3 lg:w-4"></div>
                                        <CictLogoIcon className="h-12 w-12 md:h-16 md:w-16 lg:h-20 lg:w-20 fill-current text-[var(--foreground)] dark:text-white flex-shrink-0" />
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
