import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import SectionSwitcher from '@/components/SectionSwitcher';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';
import {
    UsersIcon,
    SearchIcon,
    FilterIcon,
    ClockIcon,
    BarChart3Icon,
    GraduationCapIcon
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student List',
        href: '/student-list',
    },
];

interface Student {
    id: number;
    username: string;
    email: string;
    name: string;
    section: string;
    status: string;
    hasAssessment: boolean;
    assessmentScore: number;
    assessmentPercentage: number;
    assessmentSubmittedAt: string | null;
    isPlaced: boolean;
    placement: {
        position: string;
        company: string;
        compatibilityScore: number;
        rank: number;
    } | null;
    categories: Array<{
        name: string;
        score: number;
    }>;
}

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    students: Student[];
    adviserSection: string | null;
    adviserSections: Section[];
    currentSectionId: number | null;
}

export default function AdviserStudents({ students, adviserSection, adviserSections, currentSectionId }: Props) {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState<string>('all');
    const [assessmentFilter, setAssessmentFilter] = useState<string>('all');
    const [placementFilter, setPlacementFilter] = useState<string>('all');
    const [showFilters, setShowFilters] = useState(false);

    // Filter students based on search and filters
    const filteredStudents = useMemo(() => {
        return students.filter(student => {
            const matchesSearch = student.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                                student.username.toLowerCase().includes(searchTerm.toLowerCase()) ||
                                student.email.toLowerCase().includes(searchTerm.toLowerCase());

            const matchesStatus = statusFilter === 'all' || student.status === statusFilter;
            const matchesAssessment = assessmentFilter === 'all' ||
                                    (assessmentFilter === 'completed' && student.hasAssessment) ||
                                    (assessmentFilter === 'pending' && !student.hasAssessment);
            const matchesPlacement = placementFilter === 'all' ||
                                   (placementFilter === 'placed' && student.isPlaced) ||
                                   (placementFilter === 'unplaced' && !student.isPlaced);

            return matchesSearch && matchesStatus && matchesAssessment && matchesPlacement;
        });
    }, [students, searchTerm, statusFilter, assessmentFilter, placementFilter]);

    // Create a stable reset trigger for pagination
    const resetTrigger = useMemo(() => 
        `${searchTerm}-${statusFilter}-${assessmentFilter}-${placementFilter}`,
        [searchTerm, statusFilter, assessmentFilter, placementFilter]
    );

    // Pagination hook with auto-reset on filter changes
    const studentsPagination = usePagination({
        data: filteredStudents,
        itemsPerPage: 10,
        resetTrigger: resetTrigger,
    });

    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Students" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UsersIcon className="h-5 w-5" />
                                No Section Assigned
                            </CardTitle>
                            <CardDescription>
                                You are not assigned to any section. Please contact the administrator.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'verified':
                return <Badge className="bg-green-100 text-green-800 hover:bg-green-100">Verified</Badge>;
            case 'unverified':
                return <Badge className="bg-gray-100 text-gray-800 hover:bg-gray-100">Unverified</Badge>;
            case 'archived':
                return <Badge className="bg-red-100 text-red-800 hover:bg-red-100">Archived</Badge>;
            default:
                return <Badge className="bg-gray-100 text-gray-800 hover:bg-gray-100">{status}</Badge>;
        }
    };

    const getAssessmentBadge = (hasAssessment: boolean) => {
        if (hasAssessment) {
            return <Badge className="bg-green-100 text-green-800 hover:bg-green-100">Completed</Badge>;
        }
        return <Badge className="bg-gray-100 text-gray-800 hover:bg-gray-100">Pending</Badge>;
    };

    const getPlacementBadge = (isPlaced: boolean) => {
        if (isPlaced) {
            return <Badge className="bg-blue-100 text-blue-800 hover:bg-blue-100">Placed</Badge>;
        }
        return <Badge className="bg-gray-100 text-gray-800 hover:bg-gray-100">Unplaced</Badge>;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Students" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Students</h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-1">
                            Section: {adviserSection} • {students.length} students
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="default"
                            onClick={() => setShowFilters(!showFilters)}
                        >
                            <FilterIcon className="h-4 w-4" />
                            Filters
                        </Button>
                        {adviserSections.length > 1 && (
                            <SectionSwitcher 
                                sections={adviserSections}
                                currentSectionId={currentSectionId}
                                showAllSections={true}
                            />
                        )}
                    </div>
                </div>

                {/* Filters */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FilterIcon className="h-5 w-5" />
                                Filters
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {/* First Row: Search, Status, Assessment, Placement */}
                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div className="space-y-2">
                                        <label htmlFor="search-filter" className="text-sm font-medium">Search</label>
                                        <div className="relative">
                                            <SearchIcon className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                            <Input
                                                id="search-filter"
                                                placeholder="Search students..."
                                                value={searchTerm}
                                                onChange={(e) => setSearchTerm(e.target.value)}
                                                className="pl-10"
                                            />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <label htmlFor="status-filter" className="text-sm font-medium">Status</label>
                                        <Select value={statusFilter} onValueChange={setStatusFilter}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Statuses</SelectItem>
                                                <SelectItem value="verified">Verified</SelectItem>
                                                <SelectItem value="unverified">Unverified</SelectItem>
                                                <SelectItem value="archived">Archived</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <label htmlFor="assessment-filter" className="text-sm font-medium">Assessment</label>
                                        <Select value={assessmentFilter} onValueChange={setAssessmentFilter}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Assessment" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Assessments</SelectItem>
                                                <SelectItem value="completed">Completed</SelectItem>
                                                <SelectItem value="pending">Pending</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <label htmlFor="placement-filter" className="text-sm font-medium">Placement</label>
                                        <Select value={placementFilter} onValueChange={setPlacementFilter}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Placement" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Placements</SelectItem>
                                                <SelectItem value="placed">Placed</SelectItem>
                                                <SelectItem value="unplaced">Unplaced</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Students List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <GraduationCapIcon className="h-5 w-5" />
                            Student List
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {filteredStudents.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                No students found.
                            </div>
                        ) : (
                            <>
                                {/* Mobile/Tablet Card View */}
                                <div className="block lg:hidden space-y-4">
                                    {studentsPagination.paginatedData.map((student) => (
                                        <div key={student.id} className="p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-3 mb-2">
                                                        <h3 className="text-lg font-semibold">{student.name}</h3>
                                                        {getStatusBadge(student.status)}
                                                        {getAssessmentBadge(student.hasAssessment)}
                                                        {getPlacementBadge(student.isPlaced)}
                                                    </div>

                                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                                        <div>
                                                            <p className="text-sm text-muted-foreground">Username</p>
                                                            <p className="font-medium">{student.username}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-sm text-muted-foreground">Email</p>
                                                            <p className="font-medium">{student.email}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-sm text-muted-foreground">Section</p>
                                                            <p className="font-medium">{student.section}</p>
                                                        </div>
                                                    </div>

                                                    {student.hasAssessment && (
                                                        <div className="space-y-3">
                                                            <div className="flex items-center gap-4">
                                                                <div className="flex items-center gap-2">
                                                                    <BarChart3Icon className="h-4 w-4 text-muted-foreground" />
                                                                    <span className="text-sm font-medium">Assessment Score:</span>
                                                                    <span className="font-bold">{student.assessmentPercentage}%</span>
                                                                </div>
                                                                <div className="flex items-center gap-2">
                                                                    <ClockIcon className="h-4 w-4 text-muted-foreground" />
                                                                    <span className="text-sm text-muted-foreground">
                                                                        Submitted: {student.assessmentSubmittedAt}
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <div className="flex flex-wrap gap-2">
                                                                {student.categories.map((category, index) => (
                                                                    <Badge key={index} variant="outline" className="text-xs">
                                                                        {category.name}: {category.score}
                                                                    </Badge>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* Desktop Table View */}
                                <div className="hidden lg:block overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-center py-3 px-4 font-semibold text-sm w-16">#</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Username</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Section</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Assessment</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Placement</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {studentsPagination.paginatedData.map((student, index) => (
                                                <tr key={student.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="text-center py-3 px-4 font-mono text-sm text-muted-foreground">
                                                        {getRowNumber(studentsPagination.currentPage, 10, index)}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="font-medium">{student.name}</div>
                                                    </td>
                                                    <td className="py-3 px-4 text-sm text-muted-foreground">{student.username}</td>
                                                    <td className="py-3 px-4 text-sm text-muted-foreground">{student.email}</td>
                                                    <td className="py-3 px-4 text-sm text-muted-foreground">{student.section}</td>
                                                    <td className="py-3 px-4">{getStatusBadge(student.status)}</td>
                                                    <td className="py-3 px-4">{getAssessmentBadge(student.hasAssessment)}</td>
                                                    <td className="py-3 px-4">{getPlacementBadge(student.isPlaced)}</td>
                                                    <td className="py-3 px-4 text-sm text-muted-foreground">
                                                        {student.hasAssessment ? `${student.assessmentPercentage}%` : '-'}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Pagination */}
                                <Pagination
                                    currentPage={studentsPagination.currentPage}
                                    totalPages={studentsPagination.totalPages}
                                    onPageChange={studentsPagination.handlePageChange}
                                    showSummary={true}
                                    totalItems={filteredStudents.length}
                                    itemsPerPage={10}
                                />
                            </>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
