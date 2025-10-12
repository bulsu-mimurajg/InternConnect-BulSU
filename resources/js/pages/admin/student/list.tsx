import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';
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
    is_submit: boolean;
    email?: string;
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
        assessment?: string;
    };
}

export default function StudentList({ students, unverifiedUsers = [], archivedStudents = [], archivedUnverifiedUsers = [], section_options = [], filters = {} }: Props) {
    const { flash } = usePage<{ flash: { message?: string; error?: string } }>().props;
    const [showUnverified, setShowUnverified] = useState(false);
    const [showArchived, setShowArchived] = useState(false);
    const [showFilters, setShowFilters] = useState(false);
    const [showArchiveDialog, setShowArchiveDialog] = useState(false);
    const [selectedStudent, setSelectedStudent] = useState<Student | null>(null);
    const [selectedUnverifiedUser, setSelectedUnverifiedUser] = useState<UnverifiedUser | null>(null);
    const [localFilters, setLocalFilters] = useState({
        search: filters.search || '',
        section: filters.section || 'all',
        status: filters.status || 'all',
        assessment: filters.assessment || 'all',
    });

    // Use section options from backend
    const availableSections = section_options.length > 0 
        ? section_options.map(s => s.name)
        : [];
    const handleEdit = (studentId: number | string) => {
        router.get(`/student/${studentId}/edit`);
    };

    const handleArchive = (student: Student) => {
        // Check if student is already archived
        if (!student.is_active) {
            // Show error message or prevent action
            return;
        }
        
        setSelectedStudent(student);
        setSelectedUnverifiedUser(null);
        setShowArchiveDialog(true);
    };

    const handleEditUnverified = (userId: number | string) => {
        router.get(`/student/unverified/${userId}/edit`);
    };

    const handleArchiveUnverified = (user: UnverifiedUser) => {
        // Check if user is already archived
        if (user.status === 'archived') {
            // Show error message or prevent action
            return;
        }
        
        setSelectedUnverifiedUser(user);
        setSelectedStudent(null);
        setShowArchiveDialog(true);
    };

    const confirmArchive = () => {
        if (selectedStudent) {
            router.patch(`/student/${selectedStudent.id}/archive`, {}, {
                onSuccess: () => {
                    // Reload all student-related data to update matched/endorsed lists
                    router.reload({ 
                        only: [
                            'students', 
                            'archivedStudents',
                            'matchedStudents',
                            'unplacedStudents',
                            'endorsed_students',
                            'statistics',
                            'filters'
                        ] 
                    });
                },
                onError: (errors) => {
                    // Error handling is done by backend redirect with flash message
                    console.error('Archive error:', errors);
                }
            });
        } else if (selectedUnverifiedUser) {
            router.patch(`/student/unverified/${selectedUnverifiedUser.id}/archive`, {}, {
                onSuccess: () => {
                    router.reload({ only: ['unverifiedUsers', 'archivedUnverifiedUsers'] });
                },
                onError: (errors) => {
                    // Error handling is done by backend redirect with flash message
                    console.error('Archive error:', errors);
                }
            });
        }
        setShowArchiveDialog(false);
        setSelectedStudent(null);
        setSelectedUnverifiedUser(null);
    };

    const cancelArchive = () => {
        setShowArchiveDialog(false);
        setSelectedStudent(null);
        setSelectedUnverifiedUser(null);
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

    const handleFilterChange = (filterType: 'search' | 'section' | 'status' | 'assessment', value: string) => {
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
        if (newFilters.assessment && newFilters.assessment !== 'all') {
            params.assessment = newFilters.assessment;
        }

        router.get('/student/list', params, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ search: '', section: 'all', status: 'all', assessment: 'all' });
        
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
        const matchesAssessment = localFilters.assessment === 'all' || 
                                 (localFilters.assessment === 'submitted' && student.is_submit) ||
                                 (localFilters.assessment === 'pending' && !student.is_submit);
        return matchesSearch && matchesSection && matchesStatus && matchesAssessment;
    });

    const filteredUnverifiedUsers = unverifiedUsers.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || user.section === localFilters.section;
        const matchesStatus = localFilters.status === 'all' || user.status === localFilters.status;
        // Unverified users don't have assessment data, so only show them if assessment filter is 'all'
        const matchesAssessment = localFilters.assessment === 'all';
        return matchesSearch && matchesSection && matchesStatus && matchesAssessment;
    });

    const filteredArchivedStudents = archivedStudents.filter(student => {
        const matchesSearch = student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || student.section === localFilters.section;
        const matchesAssessment = localFilters.assessment === 'all' || 
                                 (localFilters.assessment === 'submitted' && student.is_submit) ||
                                 (localFilters.assessment === 'pending' && !student.is_submit);
        return matchesSearch && matchesSection && matchesAssessment;
    });

    const filteredArchivedUnverifiedUsers = archivedUnverifiedUsers.filter(user => {
        const matchesSearch = user.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            user.email.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || user.section === localFilters.section;
        const matchesStatus = localFilters.status === 'all' || user.status === localFilters.status;
        // Archived unverified users don't have assessment data, so only show them if assessment filter is 'all'
        const matchesAssessment = localFilters.assessment === 'all';
        return matchesSearch && matchesSection && matchesStatus && matchesAssessment;
    });

    // Pagination hooks for each data type with auto-reset on filter changes
    const studentsPagination = usePagination({
        data: filteredStudents,
        itemsPerPage: 10,
        resetTrigger: localFilters, // Auto-reset when filters change
    });

    const unverifiedPagination = usePagination({
        data: filteredUnverifiedUsers,
        itemsPerPage: 10,
        resetTrigger: localFilters,
    });

    const archivedStudentsPagination = usePagination({
        data: filteredArchivedStudents,
        itemsPerPage: 10,
        resetTrigger: localFilters,
    });

    const archivedUnverifiedPagination = usePagination({
        data: filteredArchivedUnverifiedUsers,
        itemsPerPage: 10,
        resetTrigger: localFilters,
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

                {/* Error/Success Messages */}
                {flash?.error && (
                    <Alert variant="destructive">
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}
                {flash?.message && (
                    <Alert>
                        <AlertDescription>{flash.message}</AlertDescription>
                    </Alert>
                )}

                {/* Filters Section */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Filter className="h-5 w-5" />
                                Filters & Search
                            </CardTitle>
                            <CardDescription>
                                Filter students by searching their name or student number, section, status, or assessment status 
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
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

                                {/* Assessment Status Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="assessment-filter">Assessment</Label>
                                    <Select
                                        value={localFilters.assessment}
                                        onValueChange={(value) => handleFilterChange('assessment', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Assessments" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Assessments</SelectItem>
                                            <SelectItem value="submitted">Submitted</SelectItem>
                                            <SelectItem value="pending">Pending</SelectItem>
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
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Specialization</th>
                                                    <th className="text-center p-3 font-medium text-muted-foreground">Assessment</th>
                                                    <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {archivedStudentsPagination.paginatedData.map((stud) => (
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
                                                        <td className="p-3 text-sm text-muted-foreground">
                                                            {stud.email || '-'}
                                                        </td>
                                                        <td className="p-3 text-sm">
                                                            {stud.section}
                                                        </td>
                                                        <td className="p-3 text-sm text-muted-foreground">
                                                            {stud.specialization || '-'}
                                                        </td>
                                                        <td className="p-3 text-center">
                                                            <Badge 
                                                                variant={stud.is_submit ? "default" : "secondary"}
                                                                className={stud.is_submit 
                                                                    ? "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200" 
                                                                    : "bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                                                                }
                                                            >
                                                                {stud.is_submit ? 'Submitted' : 'Pending'}
                                                            </Badge>
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

                        {/* Pagination for Archived Students */}
                        <Pagination
                            currentPage={archivedStudentsPagination.currentPage}
                            totalPages={archivedStudentsPagination.totalPages}
                            onPageChange={archivedStudentsPagination.handlePageChange}
                            showSummary={true}
                            totalItems={filteredArchivedStudents.length}
                            itemsPerPage={10}
                        />

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
                                                    <th className="text-center p-3 font-medium text-muted-foreground w-16">#</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Username</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Status</th>
                                                    <th className="text-left p-3 font-medium text-muted-foreground">Created</th>
                                                    <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {archivedUnverifiedPagination.paginatedData.map((user, index) => (
                                                    <tr key={user.id} className="border-b hover:bg-muted/50 transition-colors">
                                                        <td className="text-center p-3 font-mono text-sm text-muted-foreground">
                                                            {getRowNumber(archivedUnverifiedPagination.currentPage, 10, index)}
                                                        </td>
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

                        {/* Pagination for Archived Unverified Users */}
                        <Pagination
                            currentPage={archivedUnverifiedPagination.currentPage}
                            totalPages={archivedUnverifiedPagination.totalPages}
                            onPageChange={archivedUnverifiedPagination.handlePageChange}
                            showSummary={true}
                            totalItems={filteredArchivedUnverifiedUsers.length}
                            itemsPerPage={10}
                        />
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
                                                <th className="text-center p-3 font-medium text-muted-foreground w-16">#</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Username</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Status</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Created</th>
                                                <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {unverifiedPagination.paginatedData.map((user, index) => (
                                                <tr key={user.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="text-center p-3 font-mono text-sm text-muted-foreground">
                                                        {getRowNumber(unverifiedPagination.currentPage, 10, index)}
                                                    </td>
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
                                                                onClick={() => handleArchiveUnverified(user)}
                                                                disabled={user.status === 'archived'}
                                                                className={user.status === 'archived'
                                                                    ? "text-muted-foreground cursor-not-allowed" 
                                                                    : "text-orange-600 hover:text-orange-700"
                                                                }
                                                            >
                                                                <ArchiveIcon className="h-4 w-4 mr-2" />
                                                                {user.status === 'archived' ? 'Already Archived' : 'Archive'}
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

                {/* Pagination for Unverified Users */}
                {showUnverified && (
                    <Pagination
                        currentPage={unverifiedPagination.currentPage}
                        totalPages={unverifiedPagination.totalPages}
                        onPageChange={unverifiedPagination.handlePageChange}
                        showSummary={true}
                        totalItems={filteredUnverifiedUsers.length}
                        itemsPerPage={10}
                    />
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
                                                <th className="text-center p-3 font-medium text-muted-foreground w-16">#</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Name</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Student ID</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Email</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Section</th>
                                                <th className="text-left p-3 font-medium text-muted-foreground">Specialization</th>
                                                <th className="text-center p-3 font-medium text-muted-foreground">Assessment</th>
                                                <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {studentsPagination.paginatedData.map((stud, index) => (
                                                <tr key={stud.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="text-center p-3 font-mono text-sm text-muted-foreground">
                                                        {getRowNumber(studentsPagination.currentPage, 10, index)}
                                                    </td>
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
                                                    <td className="p-3 text-sm text-muted-foreground">
                                                        {stud.email || '-'}
                                                    </td>
                                                    <td className="p-3 text-sm">
                                                        {stud.section}
                                                    </td>
                                                    <td className="p-3 text-sm text-muted-foreground">
                                                        {stud.specialization || '-'}
                                                    </td>
                                                    <td className="p-3 text-center">
                                                        <Badge 
                                                            variant={stud.is_submit ? "default" : "secondary"}
                                                            className={stud.is_submit 
                                                                ? "bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200" 
                                                                : "bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                                                            }
                                                        >
                                                            {stud.is_submit ? 'Submitted' : 'Pending'}
                                                        </Badge>
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
                                                                onClick={() => handleArchive(stud)}
                                                                disabled={!stud.is_active}
                                                                className={!stud.is_active 
                                                                    ? "text-muted-foreground cursor-not-allowed" 
                                                                    : "text-orange-600 hover:text-orange-700"
                                                                }
                                                            >
                                                                <ArchiveIcon className="h-4 w-4 mr-2" />
                                                                {!stud.is_active ? 'Already Archived' : 'Archive'}
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

                {/* Pagination for Verified Students */}
                {!showArchived && !showUnverified && (
                    <Pagination
                        currentPage={studentsPagination.currentPage}
                        totalPages={studentsPagination.totalPages}
                        onPageChange={studentsPagination.handlePageChange}
                        showSummary={true}
                        totalItems={filteredStudents.length}
                        itemsPerPage={10}
                    />
                )}

                {/* Archive Confirmation Dialog */}
                <Dialog open={showArchiveDialog} onOpenChange={setShowArchiveDialog}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <ArchiveIcon className="h-5 w-5 text-orange-600" />
                                Archive {selectedStudent ? 'Student' : 'User'}
                            </DialogTitle>
                            <DialogDescription>
                                {selectedStudent ? (
                                    <>
                                        Are you sure you want to archive <strong>{selectedStudent.first_name} {selectedStudent.last_name}</strong>?
                                        <br />
                                        <span className="text-sm text-muted-foreground mt-1 block">
                                            Student ID: {selectedStudent.student_number}
                                        </span>
                                        <br />
                                        <div className="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                                            <p className="text-sm text-red-800 dark:text-red-200 font-medium">
                                                Warning: Already endorsed or placed students will be removed and this action cannot be undone.
                                            </p>
                                            <p className="text-xs text-red-700 dark:text-red-300 mt-1">
                                                This will free up slots for other students.
                                            </p>
                                        </div>
                                    </>
                                ) : selectedUnverifiedUser ? (
                                    <>
                                        Are you sure you want to archive <strong>{selectedUnverifiedUser.username}</strong>?
                                        <br />
                                        <span className="text-sm text-muted-foreground mt-1 block">
                                            Email: {selectedUnverifiedUser.email}
                                        </span>
                                    </>
                                ) : null}
                                <br />
                                <span className="text-sm text-amber-600 dark:text-amber-400 mt-2 block">
                                    This action can be undone by restoring the {selectedStudent ? 'student' : 'user'} later.
                                </span>
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter className="gap-2">
                            <Button
                                variant="outline"
                                onClick={cancelArchive}
                            >
                                Cancel
                            </Button>
                            <Button
                                variant="destructive"
                                onClick={confirmArchive}
                                className="bg-orange-600 hover:bg-orange-700"
                            >
                                <ArchiveIcon className="h-4 w-4 mr-2" />
                                Archive {selectedStudent ? 'Student' : 'User'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AdminLayout>
    );
}
