import type { NavItem } from '@/types';
import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { Separator } from '@/components/ui/separator';

const subNavBarItems: NavItem[] = [
    {
        title: 'List',
        href: '/student/list',
        icon: null,
    },
    {
        title: 'Matched',
        href: '/student/matched',
        icon: null,
    },
    {
        title: 'Placed',
        href: '/student/placed',
        icon: null,
    },
];

export default function AdminLayout({ children }: PropsWithChildren) {

    const currentPath = window.location.pathname;

    return(
        <div className="px-4 py-6">
            <div className="flex justify-between">
                <Heading title="Student Management" description="Manage student list, matches, and placements." />
                <div>
                    <nav className="flex justify-center items-center text-center space-x-4">
                        {subNavBarItems.map((item, index) => (
                            <Button
                                key={`${item.href}-${index}`}
                                size="lg"
                                variant="ghost"
                                asChild
                                className={cn('flex-1 text-center overflow-x-hidden transition-all duration-200', {
                                    'border-b-2 border-orange-500': currentPath === item.href,
                                    'hover:bg-gray-200 hover:border-b-2 hover:border-orange-500': currentPath !== item.href,
                                })}
                            >
                                <Link href={item.href} prefetch>
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </div>
            </div>

            <Separator className="my-6 md:hidden" />

            <div className="flex-1 md:max-w-2xl">
                <section className="max-w-xl space-y-12">{children}</section>
            </div>
        </div>
    )
}
