import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import AdminLayout from '@/layouts/admin/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student List',
        href: '/student/list',
    },
];

interface Student {
    id: number | string;
    last_name: string;
    first_name: string;
    middle_name?: string;
    section: string;
    specialization?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedStudents {
    data: Student[];
    links: PaginationLink[];
}


export default function StudentList({ students }: { students: PaginatedStudents }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Student List" />

            <AdminLayout>
                <div className="space-y-6">
                    <div className="overflow-auto max-h-[60vh]">
                        <table className="w-full bg-white shadow-md">
                            <thead className="border-b-2 border-gray-200">
                            <tr>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">Last Name</th>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">First Name</th>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">Middle Name</th>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">Section</th>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">Specialization</th>
                                <th className="p-3 text-sm font-semibold tracking-wide text-left">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            {students.data.map((stud) => (
                                <tr key={stud.id} className="border-b border-gray-200 hover:bg-[#f3f3f3]">
                                    <td className="p-3 text-sm font-normal">{stud.last_name}</td>
                                    <td className="p-3 text-sm font-normal">{stud.first_name}</td>
                                    <td className="p-3 text-sm font-normal">{stud.middle_name || ''}</td>
                                    <td className="p-3 text-sm font-normal">{stud.section}</td>
                                    <td className="p-3 text-sm font-normal">{stud.specialization || ''}</td>
                                    <td className="p-3 text-sm font-normal">
                                        <button
                                            className="mr-3 text-sm bg-orange-400 hover:bg-orange-500 text-white py-1 px-2 rounded">
                                            EXPAND
                                        </button>
                                        <button
                                            className="text-sm bg-orange-400 hover:bg-orange-500 text-white py-1 px-5 rounded">
                                            EDIT
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="flex justify-center items-center">
                        {students.links.map((link) => (
                            link.url ? (
                                <Link
                                    key={link.label}
                                    href={link.url}
                                    className={`p-1 mx-1 ${link.active ? 'font-bold text-blue-400 underline' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ) : (
                                <span
                                    key={link.label}
                                    className="cursor-not-allowed text-gray-300"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            )
                        ))}
                    </div>
                </div>
            </AdminLayout>
        </AppLayout>
    );
}
