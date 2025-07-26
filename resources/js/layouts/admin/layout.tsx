import type { PropsWithChildren } from 'react';
import Heading from '@/components/heading';
import { Separator } from '@/components/ui/separator';

export default function AdminLayout({ children }: PropsWithChildren) {
    return(
        <div className="px-4 py-6">
            <div className="flex justify-between">
                <Heading title="Student Management" description="Manage student list, matches, and placements." />
            </div>

            <Separator className="my-6 md:hidden" />

            <div>
                <section>{children}</section>
            </div>
        </div>
    )
}
