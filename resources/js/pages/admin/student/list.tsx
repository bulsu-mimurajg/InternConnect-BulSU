import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { UsersIcon, UserCheckIcon, ArchiveIcon, RotateCcwIcon, EditIcon, UserXIcon, Filter, ArrowUpDown, Search } from 'lucide-react';
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

interface SectionOption {
    name: string;
    total_students: number;
}

interface Props {
    students: Student[];
    unverifiedUsers?: UnverifiedUser[];
    archivedStudents?: Student[];
    archivedUnverifiedUsers?: UnverifiedUser[];
    section_options?: SectionOption[];
    filters?: {
        search?: string;
        section?: string;
        status?: string;
    };
}

export default function StudentList({ students, unverifiedUsers = [], archivedStudents = [], archivedUnverifiedUsers = [], section_options = [], filters = {} }: Props) {
    const [showUnverified, setShowUnverified] = useState(false);
    const [showArchived, setShowArchived] = useState(false);
    const [showFilters, setShowFilters] = useState(false);
    const [localFilters, setLocalFilters] = useState({
        search: filters.search || '',
        section: filters.section || 'all',
        status: filters.status || 'all',
    });

    // Use section options from backend
    const availableSections = section_options.length > 0 
        ? section_options.map(s => s.name)
        : [];
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

    const handleFilterChange = (filterType: 'search' | 'section' | 'status', value: string) => {
        const newFilters = { ...localFilters, [filterType]: value };
        setLocalFilters(newFilters);

        // Apply filters immediately
        const params: Record<string, string> = {};
        if (newFilters.search) {
            params.search = newFilters.search;
        }
        if (newFilters.section && newFilters.section !== 'all') {
            params.section = newFilters.section;
        }
        if (newFilters.status && newFilters.status !== 'all') {
            params.status = newFilters.status;
        }

        router.get('/student/list', params, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ search: '', section: 'all', status: 'all' });
        router.get('/student/list', {}, {
            preserveState: true,
            replace: true
        });
    };

    const filteredStudents = students.filter(student => {
        const matchesSearch = student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || student.section === localFilters.section;
        const matchesStatus = localFilters.status === 'all' || 
                             (localFilters.status === 'active' && student.is_active) ||
                             (localFilters.status === 'inactive' && !student.is_active);
        return matchesSearch && matchesSection && matchesStatus;
    });

    const filteredUnverifiedUsers = unverifiedUsers.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || user.section === localFilters.section;
        const matchesStatus = localFilters.status === 'all' || user.status === localFilters.status;
        return matchesSearch && matchesSection && matchesStatus;
    });

    const filteredArchivedStudents = archivedStudents.filter(student => {
        const matchesSearch = student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || student.section === localFilters.section;
        return matchesSearch && matchesSection;
    });

    const filteredArchivedUnverifiedUsers = archivedUnverifiedUsers.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || user.section === localFilters.section;
        const matchesStatus = localFilters.status === 'all' || user.status === localFilters.status;
        return matchesSearch && matchesSection && matchesStatus;
    });

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
                            size="default"
                            onClick={() => setShowFilters(!showFilters)}
                        >
                            <Filter className="h-4 w-4" />
                            Filters
                        </Button>
                        <Button
                            variant="outline"
                            onClick={handleShowUnverified}
                        >
                            <UserXIcon className="h-4 w-4" />
                            {showUnverified ? 'Active Students' : 'Pending'}
                        </Button>
                        <Button
                            variant="outline"
                            onClick={handleShowArchived}
                        >
                            <ArchiveIcon className="h-4 w-4" />
                            {showArchived ? 'Active Students' : 'Archived'}
                        </Button>
                    </div>
                </div>

                {/* Filters Section */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Filter className="h-5 w-5" />
                                Filters & Search
                            </CardTitle>
                            <CardDescription>
                                Filter students by section, status, or search by name or student number
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {/* Search Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="search-filter">Search</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                        <Input
                                            id="search-filter"
                                            placeholder="Search students..."
                                            value={localFilters.search}
                                            onChange={(e) => handleFilterChange('search', e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                </div>

                                {/* Section Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="section-filter">Section</Label>
                                    <Select
                                        value={localFilters.section}
                                        onValueChange={(value) => handleFilterChange('section', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Sections" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Sections</SelectItem>
                                            {section_options.map((section) => (
                                                <SelectItem key={section.name} value={section.name}>
                                                    {section.name} ({section.total_students})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Status Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="status-filter">Status</Label>
                                    <Select
                                        value={localFilters.status}
                                        onValueChange={(value) => handleFilterChange('status', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Status</SelectItem>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                            <SelectItem value="unverified">Unverified</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Clear Filters */}
                                <div className="space-y-2">
                                    <Label>&nbsp;</Label>
                                    <Button
                                        variant="outline"
                                        onClick={clearFilters}
                                        className="w-full"
                                    >
                                        <ArrowUpDown className="h-4 w-4 mr-2" />
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Results Summary */}
                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted-foreground">
                        {showArchived 
                            ? `Showing ${filteredArchivedStudents.length} archived students`
                            : showUnverified 
                                ? `Showing ${filteredUnverifiedUsers.length} unverified users`
                                : `Showing ${filteredStudents.length} students`
                        }
                    </p>
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
                                {filteredArchivedStudents.length === 0 ? (
                                    <div className="text-center py-8">
                                        <ArchiveIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-medium mb-2">No archived students found</h3>
                                        <p className="text-muted-foreground">
                                            {localFilters.search || localFilters.section !== 'all' || localFilters.status !== 'all'
                                                ? 'Try adjusting your search or filter criteria.'
                                                : 'No students have been archived yet.'
                                            }
                                        </p>
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
                                                {filteredArchivedStudents.map((stud) => (
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
                                {filteredArchivedUnverifiedUsers.length === 0 ? (
                                    <div className="text-center py-8">
                                        <UserXIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                        <h3 className="text-lg font-medium mb-2">No archived unverified users found</h3>
                                        <p className="text-muted-foreground">
                                            {localFilters.search || localFilters.section !== 'all' || localFilters.status !== 'all'
                                                ? 'Try adjusting your search or filter criteria.'
                                                : 'No unverified users have been archived yet.'
                                            }
                                        </p>
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
                                                {filteredArchivedUnverifiedUsers.map((user) => (
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
                            {filteredUnverifiedUsers.length === 0 ? (
                                <div className="text-center py-8">
                                    <UserCheckIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No pending verifications found</h3>
                                    <p className="text-muted-foreground">
                                        {localFilters.search || localFilters.section !== 'all' || localFilters.status !== 'all'
                                            ? 'Try adjusting your search or filter criteria.'
                                            : 'All student applications have been processed.'
                                        }
                                    </p>
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
                                            {filteredUnverifiedUsers.map((user) => (
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
                            {filteredStudents.length === 0 ? (
                                <div className="text-center py-8">
                                    <UsersIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No students found</h3>
                                    <p className="text-muted-foreground">
                                        {localFilters.search || localFilters.section !== 'all' || localFilters.status !== 'all'
                                            ? 'Try adjusting your search or filter criteria.'
                                            : 'No verified students are available yet.'
                                        }
                                    </p>
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
                                            {filteredStudents.map((stud) => (
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
