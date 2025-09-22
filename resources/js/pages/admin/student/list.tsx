import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { UsersIcon, UserCheckIcon, ArchiveIcon, RotateCcwIcon, EditIcon, UserXIcon } from 'lucide-react';
import { useState } from 'react';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student List',
        href: '/admin/student-list',
    },
];

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
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Student List" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {showArchived
                                ? 'Archived Students'
                                : showUnverified
                                    ? 'Pending Verification'
                                    : 'Student Directory'
                            }
                        </h1>
                        <p className="text-muted-foreground">
                            {showArchived
                                ? 'Manage archived student accounts'
                                : showUnverified
                                    ? 'Review and verify student applications'
                                    : 'View and manage verified students'
                            }
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={handleShowUnverified}
                        >
                            <UserXIcon className="h-4 w-4 mr-2" />
                            {showUnverified ? 'Active Students' : 'Pending'}
                        </Button>
                        <Button
                            variant="outline"
                            onClick={handleShowArchived}
                        >
                            <ArchiveIcon className="h-4 w-4 mr-2" />
                            {showArchived ? 'Active Students' : 'Archived'}
                        </Button>
                    </div>
                </div>

                {/* Archived Students */}
                {showArchived && (
                    <>
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <ArchiveIcon className="h-5 w-5" />
                                    Archived Students
                                </CardTitle>
                                <CardDescription>
                                    Manage archived student accounts
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {archivedStudents.length === 0 ? (
                                    <div className="text-center py-8">
                                        <ArchiveIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-medium mb-2">No archived students found</h3>
                                        <p className="text-muted-foreground">No students have been archived yet.</p>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b">
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Name</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Student ID</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Specialization</th>
                                                    <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {archivedStudents.map((stud) => (
                                                    <tr key={stud.id} className="border-b hover:bg-muted/50 transition-colors">
                                                        <td className="p-3">
                                                            <div>
                                                                <div className="font-medium">
                                                                    {stud.last_name}, {stud.first_name}
                                                                </div>
                                                                {stud.middle_name && (
                                                                    <div className="text-sm text-muted-foreground">
                                                                        {stud.middle_name.charAt(0).toUpperCase()}.
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="p-3 font-mono text-sm">
                                                            {stud.student_number}
                                                        </td>
                                                        <td className="p-3 text-sm">
                                                            {stud.section}
                                                        </td>
                                                        <td className="p-3 text-sm text-muted-foreground">
                                                            {stud.specialization || '-'}
                                                        </td>
                                                        <td className="p-3 text-right">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleRestore(stud.id)}
                                                                className="text-green-600 hover:text-green-700"
                                                            >
                                                                <RotateCcwIcon className="h-4 w-4 mr-2" />
                                                                Restore
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <UserXIcon className="h-5 w-5" />
                                    Archived Unverified Users
                                </CardTitle>
                                <CardDescription>
                                    Manage archived unverified user accounts
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {archivedUnverifiedUsers.length === 0 ? (
                                    <div className="text-center py-8">
                                        <UserXIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-medium mb-2">No archived unverified users found</h3>
                                        <p className="text-muted-foreground">No unverified users have been archived yet.</p>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b">
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Username</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Status</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Created</th>
                                                    <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {archivedUnverifiedUsers.map((user) => (
                                                    <tr key={user.id} className="border-b hover:bg-muted/50 transition-colors">
                                                        <td className="p-3">
                                                            <div className="font-medium">
                                                                {user.username}
                                                            </div>
                                                        </td>
                                                        <td className="p-3 text-sm">
                                                            {user.email}
                                                        </td>
                                                        <td className="p-3 text-sm">
                                                            {user.section}
                                                        </td>
                                                        <td className="p-3">
                                                            <Badge variant="secondary" className="bg-red-100 text-red-800">
                                                                {user.status}
                                                            </Badge>
                                                        </td>
                                                        <td className="p-3 text-sm text-muted-foreground">
                                                            {new Date(user.created_at).toLocaleDateString()}
                                                        </td>
                                                        <td className="p-3 text-right">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleRestoreUnverified(user.id)}
                                                                className="text-green-600 hover:text-green-700"
                                                            >
                                                                <RotateCcwIcon className="h-4 w-4 mr-2" />
                                                                Restore
                                                            </Button>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </>
                )}

                {/* Unverified Users */}
                {showUnverified && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserXIcon className="h-5 w-5" />
                                Pending Verification
                            </CardTitle>
                            <CardDescription>
                                Review and verify student applications
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {unverifiedUsers.length === 0 ? (
                                <div className="text-center py-8">
                                    <UserCheckIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No pending verifications found</h3>
                                    <p className="text-muted-foreground">All student applications have been processed.</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left p-3 font-medium text-muted-foreground">Username</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Status</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Created</th>
                                                <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {unverifiedUsers.map((user) => (
                                                <tr key={user.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="p-3">
                                                        <div className="font-medium">
                                                            {user.username}
                                                        </div>
                                                    </td>
                                                    <td className="p-3 text-sm">
                                                        {user.email}
                                                    </td>
                                                    <td className="p-3 text-sm">
                                                        {user.section}
                                                    </td>
                                                    <td className="p-3">
                                                        <Badge variant="secondary" className="bg-amber-100 text-amber-800">
                                                            {user.status}
                                                        </Badge>
                                                    </td>
                                                    <td className="p-3 text-sm text-muted-foreground">
                                                        {new Date(user.created_at).toLocaleDateString()}
                                                    </td>
                                                    <td className="p-3 text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleEditUnverified(user.id)}
                                                            >
                                                                <EditIcon className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Button>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleArchiveUnverified(user.id)}
                                                                className="text-orange-600 hover:text-orange-700"
                                                            >
                                                                <ArchiveIcon className="h-4 w-4 mr-2" />
                                                                Archive
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}

                {/* Verified Students */}
                {!showArchived && !showUnverified && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UsersIcon className="h-5 w-5" />
                                Student Directory
                            </CardTitle>
                            <CardDescription>
                                View and manage verified students
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {students.length === 0 ? (
                                <div className="text-center py-8">
                                    <UsersIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No students found</h3>
                                    <p className="text-muted-foreground">No verified students are available yet.</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left p-3 font-medium text-muted-foreground">Name</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Student ID</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Specialization</th>
                                                <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {students.map((stud) => (
                                                <tr key={stud.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="p-3">
                                                        <div>
                                                            <div className="font-medium">
                                                                {stud.last_name}, {stud.first_name}
                                                            </div>
                                                            {stud.middle_name && (
                                                                <div className="text-sm text-muted-foreground">
                                                                    {stud.middle_name.charAt(0).toUpperCase()}.
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="p-3 font-mono text-sm">
                                                        {stud.student_number}
                                                    </td>
                                                    <td className="p-3 text-sm">
                                                        {stud.section}
                                                    </td>
                                                    <td className="p-3 text-sm text-muted-foreground">
                                                        {stud.specialization || '-'}
                                                    </td>
                                                    <td className="p-3 text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleEdit(stud.id)}
                                                            >
                                                                <EditIcon className="h-4 w-4 mr-2" />
                                                                Edit
                                                            </Button>
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleArchive(stud.id)}
                                                                className="text-orange-600 hover:text-orange-700"
                                                            >
                                                                <ArchiveIcon className="h-4 w-4 mr-2" />
                                                                Archive
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AdminLayout>
    );
}
