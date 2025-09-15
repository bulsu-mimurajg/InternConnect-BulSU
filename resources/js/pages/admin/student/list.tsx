import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { useState } from 'react';

// breadcrumbs is unused, so we'll remove it

interface Student {
    id: number | string;
    student_number: string;
    last_name: string;
    first_name: string;
    middle_name?: string;
    section: string;
    specialization?: string;
    is_active: boolean;
}

interface UnverifiedUser {
    id: number | string;
    username: string;
    email: string;
    section: string;
    status: string;
    created_at: string;
}

interface Props {
    students: Student[];
    unverifiedUsers?: UnverifiedUser[];
    archivedStudents?: Student[];
    archivedUnverifiedUsers?: UnverifiedUser[];
}

export default function StudentList({ students, unverifiedUsers = [], archivedStudents = [], archivedUnverifiedUsers = [] }: Props) {
    const [showUnverified, setShowUnverified] = useState(false);
    const [showArchived, setShowArchived] = useState(false);
    const handleEdit = (studentId: number | string) => {
        router.get(`/student/${studentId}/edit`);
    };

    const handleArchive = (studentId: number | string) => {
        if (confirm('Are you sure you want to archive this student?')) {
            router.patch(`/student/${studentId}/archive`);
        }
    };

    const handleEditUnverified = (userId: number | string) => {
        router.get(`/student/unverified/${userId}/edit`);
    };

    const handleArchiveUnverified = (userId: number | string) => {
        if (confirm('Are you sure you want to archive this unverified user?')) {
            router.patch(`/student/unverified/${userId}/archive`);
        }
    };

    const handleShowUnverified = () => {
        setShowUnverified(!showUnverified);
        setShowArchived(false); // Reset archived view when switching to unverified
    };

    const handleShowArchived = () => {
        setShowArchived(!showArchived);
        setShowUnverified(false); // Reset unverified view when switching to archived
    };

    const handleRestore = (studentId: number | string) => {
        if (confirm('Are you sure you want to restore this student?')) {
            router.patch(`/student/${studentId}/restore`);
        }
    };

    const handleRestoreUnverified = (userId: number | string) => {
        if (confirm('Are you sure you want to restore this unverified user?')) {
            router.patch(`/student/unverified/${userId}/restore`);
        }
    };

    return (
        <AdminLayout>
            <Head title="Student List" />

            <div className="space-y-6">
                {/* Header with Show Unverified and Show Archived buttons */}
                <div className="flex justify-between items-center">
                    <h1 className="text-2xl font-bold text-gray-900">
                        {showArchived 
                            ? 'Archived Student Accounts' 
                            : showUnverified 
                                ? 'Unverified Student Accounts' 
                                : 'Verified Students'
                        }
                    </h1>
                    <div className="flex gap-3">
                        <button
                            onClick={handleShowUnverified}
                            className={`px-4 py-2 rounded-md font-medium transition-colors ${
                                showUnverified 
                                    ? 'bg-gray-500 hover:bg-gray-600 text-white' 
                                    : 'bg-orange-500 hover:bg-orange-600 text-white'
                            }`}
                        >
                            {showUnverified ? 'Show Verified Students' : 'Show Unverified'}
                        </button>
                        <button
                            onClick={handleShowArchived}
                            className={`px-4 py-2 rounded-md font-medium transition-colors ${
                                showArchived 
                                    ? 'bg-gray-500 hover:bg-gray-600 text-white' 
                                    : 'bg-red-500 hover:bg-red-600 text-white'
                            }`}
                        >
                            {showArchived ? 'Show Active Students' : 'Show Archived'}
                        </button>
                    </div>
                </div>

                <div className="overflow-auto max-h-[60vh]">
                    {showArchived ? (
                        // Archived students and unverified users table
                        <div className="space-y-6">
                            {/* Archived Students */}
                            <div>
                                <h2 className="text-lg font-semibold text-gray-800 mb-3">Archived Students</h2>
                                <table className="w-full bg-white shadow-md">
                                    <thead className="border-b-2 border-gray-200">
                                        <tr>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Last Name</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">First Name</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Middle Initial</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Student Number</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Section</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Specialization</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {archivedStudents.map((stud) => (
                                            <tr key={stud.id} className="border-b border-gray-200 hover:bg-[#f3f3f3]">
                                                <td className="p-3 text-sm font-normal">{stud.last_name}</td>
                                                <td className="p-3 text-sm font-normal">{stud.first_name}</td>
                                                <td className="p-3 text-sm font-normal">{stud.middle_name ? stud.middle_name.charAt(0).toUpperCase() + '.' : ''}</td>
                                                <td className="p-3 text-sm font-normal">{stud.student_number}</td>
                                                <td className="p-3 text-sm font-normal">{stud.section}</td>
                                                <td className="p-3 text-sm font-normal">{stud.specialization || ''}</td>
                                                <td className="p-3 text-sm font-normal">
                                                    <button
                                                        onClick={() => handleRestore(stud.id)}
                                                        className="text-sm bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded transition-colors">
                                                        Restore
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                {archivedStudents.length === 0 && (
                                    <p className="text-gray-500 text-center py-4">No archived students found.</p>
                                )}
                            </div>

                            {/* Archived Unverified Users */}
                            <div>
                                <h2 className="text-lg font-semibold text-gray-800 mb-3">Archived Unverified Users</h2>
                                <table className="w-full bg-white shadow-md">
                                    <thead className="border-b-2 border-gray-200">
                                        <tr>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Username</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Email</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Section</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Status</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Created At</th>
                                            <th className="p-3 text-sm font-semibold tracking-wide text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {archivedUnverifiedUsers.map((user) => (
                                            <tr key={user.id} className="border-b border-gray-200 hover:bg-[#f3f3f3]">
                                                <td className="p-3 text-sm font-normal">{user.username}</td>
                                                <td className="p-3 text-sm font-normal">{user.email}</td>
                                                <td className="p-3 text-sm font-normal">{user.section}</td>
                                                <td className="p-3 text-sm font-normal">
                                                    <span className="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                        {user.status}
                                                    </span>
                                                </td>
                                                <td className="p-3 text-sm font-normal">
                                                    {new Date(user.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="p-3 text-sm font-normal">
                                                    <button
                                                        onClick={() => handleRestoreUnverified(user.id)}
                                                        className="text-sm bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded transition-colors">
                                                        Restore
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                                {archivedUnverifiedUsers.length === 0 && (
                                    <p className="text-gray-500 text-center py-4">No archived unverified users found.</p>
                                )}
                            </div>
                        </div>
                    ) : showUnverified ? (
                        // Unverified users table
                        <table className="w-full bg-white shadow-md">
                            <thead className="border-b-2 border-gray-200">
                                <tr>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Username</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Email</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Section</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Status</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Created At</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {unverifiedUsers.map((user) => (
                                    <tr key={user.id} className="border-b border-gray-200 hover:bg-[#f3f3f3]">
                                        <td className="p-3 text-sm font-normal">{user.username}</td>
                                        <td className="p-3 text-sm font-normal">{user.email}</td>
                                        <td className="p-3 text-sm font-normal">{user.section}</td>
                                        <td className="p-3 text-sm font-normal">
                                            <span className="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">
                                                {user.status}
                                            </span>
                                        </td>
                                        <td className="p-3 text-sm font-normal">
                                            {new Date(user.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="p-3 text-sm font-normal">
                                            <button
                                                onClick={() => handleEditUnverified(user.id)}
                                                className="mr-3 text-sm bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded transition-colors">
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => handleArchiveUnverified(user.id)}
                                                className="text-sm bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded transition-colors">
                                                Archive
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : (
                        // Verified students table
                        <table className="w-full bg-white shadow-md">
                            <thead className="border-b-2 border-gray-200">
                                <tr>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Last Name</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">First Name</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Middle Initial</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Student Number</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Section</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Specialization</th>
                                    <th className="p-3 text-sm font-semibold tracking-wide text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {students.map((stud) => (
                                    <tr key={stud.id} className="border-b border-gray-200 hover:bg-[#f3f3f3]">
                                        <td className="p-3 text-sm font-normal">{stud.last_name}</td>
                                        <td className="p-3 text-sm font-normal">{stud.first_name}</td>
                                        <td className="p-3 text-sm font-normal">{stud.middle_name ? stud.middle_name.charAt(0).toUpperCase() + '.' : ''}</td>
                                        <td className="p-3 text-sm font-normal">{stud.student_number}</td>
                                        <td className="p-3 text-sm font-normal">{stud.section}</td>
                                        <td className="p-3 text-sm font-normal">{stud.specialization || ''}</td>
                                        <td className="p-3 text-sm font-normal">
                                            <button
                                                onClick={() => handleEdit(stud.id)}
                                                className="mr-3 text-sm bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded transition-colors">
                                                Edit
                                            </button>
                                            <button
                                                onClick={() => handleArchive(stud.id)}
                                                className="text-sm bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded transition-colors">
                                                Archive
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
