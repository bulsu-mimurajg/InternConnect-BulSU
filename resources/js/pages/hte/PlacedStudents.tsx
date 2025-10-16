import React, { useState, useEffect, useMemo } from 'react';
import { Head, router, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';
import { 
    UserIcon, 
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    SearchIcon,
    FilterIcon,
    AlertCircle
} from 'lucide-react';

interface PlacedStudent {
    id: number;
    student: {
        id: number;
        student_number: string;
        first_name: string;
        last_name: string;
        middle_name?: string;
        section: string;
        specialization?: string;
    };
    internship: {
        id: number;
        position_title: string;
        department: string;
        hte: {
            company_name: string;
        };
    };
    status: 'pending' | 'approved' | 'rejected';
    compatibility_score: number;
    placement_date?: string;
    created_at: string;
}

interface SectionOption {
    name: string;
    total_placements: number;
}

interface InternshipOption {
    id: number;
    position_title: string;
    department: string;
    total_placements: number;
}

interface Props {
    placed_students: PlacedStudent[];
    section_options: SectionOption[];
    internship_options: InternshipOption[];
    filters: {
        section: string | null;
        internship: string | null;
        search: string | null;
    };
    hteId: number;
    showSubmissionPrompt: boolean;
}

export default function PlacedStudents({ 
    placed_students = [], 
    section_options = [], 
    internship_options = [], 
    filters,
    showSubmissionPrompt
}: Props) {
    const [localFilters, setLocalFilters] = useState({
        section: filters.section || 'all',
        internship: filters.internship || 'all',
        search: filters.search || '',
    });
    const [isFiltersOpen, setIsFiltersOpen] = useState(false);

    // Update local filters when props change
    useEffect(() => {
        setLocalFilters({
            section: filters.section || 'all',
            internship: filters.internship || 'all',
            search: filters.search || ''
        });
    }, [filters]);

    const handleFilterChange = (filterType: 'section' | 'internship' | 'search', value: string) => {
        const newFilters = { ...localFilters, [filterType]: value };
        setLocalFilters(newFilters);

        // Apply filters immediately
        const params = new URLSearchParams();
        if (newFilters.section && newFilters.section !== 'all') {
            params.append('section', newFilters.section);
        }
        if (newFilters.internship && newFilters.internship !== 'all') {
            params.append('internship', newFilters.internship);
        }
        if (newFilters.search) {
            params.append('search', newFilters.search);
        }

        router.get('/hte/placed-students', params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ section: 'all', internship: 'all', search: '' });
        router.get('/hte/placed-students', {}, {
            preserveState: true,
            replace: true
        });
    };

    // Filter placed students based on local filters
    const filteredPlacedStudents = placed_students.filter(placement => {
        const matchesSection = localFilters.section === 'all' || placement.student.section === localFilters.section;
        const matchesInternship = localFilters.internship === 'all' || 
                                 placement.internship.id.toString() === localFilters.internship;
        const matchesSearch = localFilters.search === '' || 
                             placement.student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             placement.student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             placement.student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        
        return matchesSection && matchesInternship && matchesSearch;
    });

    // Create a stable reset trigger for pagination
    const resetTrigger = useMemo(() => 
        `${localFilters.section}-${localFilters.internship}-${localFilters.search}`,
        [localFilters.section, localFilters.internship, localFilters.search]
    );

    // Pagination hook with auto-reset on filter changes
    const placedPagination = usePagination({
        data: filteredPlacedStudents,
        itemsPerPage: 10,
        resetTrigger: resetTrigger,
    });

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'approved':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            case 'rejected':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            case 'pending':
            default:
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'approved':
                return <CheckCircleIcon className="h-4 w-4" />;
            case 'rejected':
                return <XCircleIcon className="h-4 w-4" />;
            case 'pending':
            default:
                return <ClockIcon className="h-4 w-4" />;
        }
    };

    const getGradePoint = (score: number) => {
        if (score >= 96.50) return '1.00';
        if (score >= 93.50) return '1.25';
        if (score >= 90.50) return '1.50';
        if (score >= 87.50) return '1.75';
        if (score >= 84.50) return '2.00';
        if (score >= 81.50) return '2.25';
        if (score >= 78.50) return '2.50';
        if (score >= 75.50) return '2.75';
        if (score >= 75.00) return '3.00';
        return '5.00';
    };

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout>
                <Head title="Placed Students" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to view placed students.
                                </p>
                                <Button asChild>
                                    <Link href="/form">
                                        Take Assessment
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <Head title="Placed Students" />
            
            <div className="flex h-full flex-1 flex-col gap-4 md:gap-6 rounded-xl p-4 md:p-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Placed Students</h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-1">
                            View students who have been placed in your internships
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="default"
                            onClick={() => setIsFiltersOpen(!isFiltersOpen)}
                        >
                            <FilterIcon className="h-4 w-4" />
                            Filters
                        </Button>
                    </div>
                </div>

                {/* Filters */}
                {isFiltersOpen && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FilterIcon className="h-5 w-5" />
                                Filters
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {/* First Row: Search, Section, Clear Filters */}
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {/* Search Filter */}
                                    <div className="space-y-2">
                                        <Label htmlFor="search-filter">Search</Label>
                                        <div className="relative">
                                            <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
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
                                                        {section.name} ({section.total_placements})
                                                    </SelectItem>
                                                ))}
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
                                            Clear Filters
                                        </Button>
                                    </div>
                                </div>

                                {/* Second Row: Internship Filter (Full Width) */}
                                <div className="space-y-2">
                                    <Label htmlFor="internship-filter">Internship</Label>
                                    <Select
                                        value={localFilters.internship}
                                        onValueChange={(value) => handleFilterChange('internship', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Internships" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Internships</SelectItem>
                                            {internship_options.map((internship) => (
                                                <SelectItem key={internship.id} value={internship.id.toString()}>
                                                    {internship.position_title} - {internship.department} ({internship.total_placements})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Placed Students Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Placed Students</CardTitle>
                        <CardDescription>
                            {filteredPlacedStudents.length} student{filteredPlacedStudents.length !== 1 ? 's' : ''} placed in your internships
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredPlacedStudents.length === 0 ? (
                            <div className="text-center py-8">
                                <UserIcon className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No Placed Students</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    No students have been placed in your internships yet.
                                </p>
                            </div>
                        ) : (
                            <>
                                {/* Mobile/Tablet Card View */}
                                <div className="block lg:hidden space-y-3 md:space-y-4">
                                    {placedPagination.paginatedData.map((placement) => (
                                        <Card key={placement.id} className="p-4 md:p-6 transition-all duration-200 hover:shadow-md">
                                            {/* Header with student info and score */}
                                            <div className="flex items-start justify-between mb-4">
                                                <div className="flex-1 min-w-0">
                                                    <div className="font-semibold text-base md:text-lg text-foreground mb-1">
                                                        {placement.student.first_name} {placement.student.middle_name} {placement.student.last_name}
                                                    </div>
                                                    <div className="text-sm text-muted-foreground font-mono mb-1">
                                                        {placement.student.student_number}
                                                    </div>
                                                    <div className="flex items-center gap-2 flex-wrap">
                                                        <Badge variant="secondary" className="text-xs">
                                                            <UserIcon className="h-3 w-3 mr-1" />
                                                            {placement.student.section}
                                                        </Badge>
                                                        {placement.student.specialization && (
                                                            <span className="text-xs text-muted-foreground">
                                                                {placement.student.specialization}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex flex-col items-end gap-1 ml-2">
                                                    <span className="font-semibold text-sm md:text-base bg-primary/10 text-primary px-2 py-1 rounded-md">
                                                        {Math.round(placement.compatibility_score)}%
                                                    </span>
                                                    <span className="text-xs text-muted-foreground font-mono">
                                                        {getGradePoint(placement.compatibility_score)}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Internship Details */}
                                            <div className="bg-muted/50 rounded-lg p-3 md:p-4 mb-4">
                                                <div className="space-y-2">
                                                    <div>
                                                        <div className="font-medium text-sm md:text-base text-foreground">
                                                            {placement.internship.position_title}
                                                        </div>
                                                        <div className="text-xs md:text-sm text-muted-foreground">
                                                            {placement.internship.department}
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm text-foreground">{placement.internship.hte.company_name}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Footer with status and date */}
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div className="flex items-center gap-2 flex-wrap">
                                                    <Badge className={`text-xs ${getStatusColor(placement.status)}`}>
                                                        <div className="flex items-center gap-1">
                                                            {getStatusIcon(placement.status)}
                                                            {placement.status.charAt(0).toUpperCase() + placement.status.slice(1)}
                                                        </div>
                                                    </Badge>
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    <span className="inline-flex items-center gap-1">
                                                        <ClockIcon className="h-3 w-3" />
                                                        {placement.placement_date 
                                                            ? new Date(placement.placement_date).toLocaleDateString()
                                                            : new Date(placement.created_at).toLocaleDateString()
                                                        }
                                                    </span>
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>

                                {/* Desktop Table View */}
                                <div className="hidden lg:block overflow-x-auto">
                                    <table className="w-full min-w-[800px]">
                                        <thead>
                                            <tr className="border-b border-gray-200">
                                                <th className="text-center py-3 px-2 font-semibold text-sm w-16">#</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-48">Student</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden xl:table-cell w-32">Student Number</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden lg:table-cell w-24">Section</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden xl:table-cell w-32">Specialization</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-48">Position</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden lg:table-cell w-32">Department</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-24">Score</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-24">Status</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden xl:table-cell w-20">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {placedPagination.paginatedData.map((placement, index) => (
                                            <tr key={placement.id} className="border-b border-gray-100 hover:bg-muted/50 transition-colors">
                                                <td className="text-center py-3 px-2 font-mono text-sm text-muted-foreground">
                                                    {getRowNumber(placedPagination.currentPage, 10, index)}
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="min-w-0">
                                                        <div className="font-medium text-sm truncate">
                                                            {placement.student.first_name} {placement.student.middle_name} {placement.student.last_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2 font-mono text-xs hidden xl:table-cell">
                                                    <span className="truncate block">{placement.student.student_number}</span>
                                                </td>
                                                <td className="py-3 px-2 hidden lg:table-cell">
                                                    <Badge variant="secondary" className="text-xs">
                                                        {placement.student.section}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-2 hidden xl:table-cell">
                                                    <span className="text-xs truncate block">{placement.student.specialization || 'N/A'}</span>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="min-w-0">
                                                        <div className="font-medium text-xs truncate block">{placement.internship.position_title}</div>
                                                        <div className="text-xs text-muted-foreground truncate">
                                                            {placement.internship.hte.company_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2 hidden lg:table-cell">
                                                    <span className="text-xs truncate block">{placement.internship.department}</span>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <span className="font-medium text-xs">{Math.round(placement.compatibility_score)}% | {getGradePoint(placement.compatibility_score)}</span>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <Badge className={`text-xs ${getStatusColor(placement.status)}`}>
                                                        <div className="flex items-center gap-1">
                                                            {getStatusIcon(placement.status)}
                                                            <span className="hidden xl:inline">{placement.status.charAt(0).toUpperCase() + placement.status.slice(1)}</span>
                                                        </div>
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-2 text-xs text-muted-foreground hidden xl:table-cell">
                                                    {placement.placement_date 
                                                        ? new Date(placement.placement_date).toLocaleDateString()
                                                        : new Date(placement.created_at).toLocaleDateString()
                                                    }
                                                </td>
                                            </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        )}
                        
                        {/* Pagination */}
                        {filteredPlacedStudents.length > 0 && (
                            <Pagination
                                currentPage={placedPagination.currentPage}
                                totalPages={placedPagination.totalPages}
                                onPageChange={placedPagination.handlePageChange}
                                showSummary={true}
                                totalItems={filteredPlacedStudents.length}
                                itemsPerPage={10}
                            />
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
