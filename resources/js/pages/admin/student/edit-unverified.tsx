import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student List',
        href: '/student/list',
    },
    {
        title: 'Edit Unverified User',
        href: '#',
    },
];

interface User {
    id: number | string;
    username: string;
    email: string;
    status: string;
    section_id: number | null;
    section: string;
    created_at: string;
}

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    user: User;
    sections: Section[];
}

export default function EditUnverifiedUser({ user, sections }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        username: user.username,
        email: user.email,
        section_id: user.section_id || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/student/unverified/${user.id}`);
    };

    return (
        <AdminLayout>
            <Head title="Edit Unverified User" />

            <div className="space-y-6">
                <div className="bg-white shadow-md rounded-lg p-6">
                    <h1 className="text-2xl font-bold text-gray-900 mb-6">Edit Unverified User</h1>
                    
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Username */}
                            <div>
                                <label htmlFor="username" className="block text-sm font-medium text-gray-700 mb-2">
                                    Username
                                </label>
                                <input
                                    type="text"
                                    id="username"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                                {errors.username && (
                                    <p className="mt-1 text-sm text-red-600">{errors.username}</p>
                                )}
                            </div>

                            {/* Email */}
                            <div>
                                <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>

                            {/* Section */}
                            <div className="md:col-span-2">
                                <label htmlFor="section_id" className="block text-sm font-medium text-gray-700 mb-2">
                                    Section
                                </label>
                                <select
                                    id="section_id"
                                    value={data.section_id}
                                    onChange={(e) => setData('section_id', parseInt(e.target.value))}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                >
                                    <option value="">Select a section</option>
                                    {sections.map((section) => (
                                        <option key={section.section_id} value={section.section_id}>
                                            {section.section_name}
                                        </option>
                                    ))}
                                </select>
                                {errors.section_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.section_id}</p>
                                )}
                            </div>
                        </div>

                        {/* User Info Display */}
                        <div className="bg-gray-50 p-4 rounded-md">
                            <h3 className="text-sm font-medium text-gray-700 mb-2">User Information</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span className="font-medium">Status:</span>
                                    <span className="ml-2 px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                                        {user.status}
                                    </span>
                                </div>
                                <div>
                                    <span className="font-medium">Created:</span>
                                    <span className="ml-2">{new Date(user.created_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                        </div>

                        {/* Action Buttons */}
                        <div className="flex justify-end space-x-4">
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors disabled:opacity-50"
                            >
                                {processing ? 'Updating...' : 'Update User'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
