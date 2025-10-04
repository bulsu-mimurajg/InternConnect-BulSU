import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/layouts/admin/layout';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
    Building2,
    Briefcase,
    GraduationCap,
    Star,
    AlertCircle,
    CheckCircle2,
    UsersIcon,
    TargetIcon,
    InfoIcon,
    ArrowUpDownIcon,
} from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Endorsed Students',
        href: '/student/endorsed',
    },
];

interface EndorsedStudent {
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
    endorsement_status: 'pending' | 'endorsed' | 'rejected';
    placement_status: 'pending' | 'approved' | 'rejected';
    compatibility_score: number;
    endorsement_date?: string;
    created_at: string;
}

interface SectionOption {
    name: string;
    total_endorsements: number;
}

interface InternshipOption {
    id: number;
    position_title: string;
    department: string;
    company_name: string;
    total_endorsements: number;
}

interface Props {
    endorsed_students: EndorsedStudent[];
    section_options: SectionOption[];
    internship_options: InternshipOption[];
    filters: {
        section: string | null;
        internship: string | null;
        search: string | null;
        status: string | null;
    };
}

export default function StudentEndorsed({ 
    endorsed_students = [], 
    section_options = [], 
    internship_options = [], 
    filters 
}: Props) {
    const [showFilters, setShowFilters] = useState(false);
    const [localFilters, setLocalFilters] = useState({
        section: filters.section || 'all',
        internship: filters.internship || 'all',
        search: filters.search || '',
        status: filters.status || 'all',
    });

    // Update local filters when props change
    useEffect(() => {
        setLocalFilters({
            section: filters.section || 'all',
            internship: filters.internship || 'all',
            search: filters.search || '',
            status: filters.status || 'all'
        });
    }, [filters]);

    // Filter endorsed students based on local filters
    const filteredEndorsedStudents = endorsed_students.filter(endorsement => {
        const matchesSection = localFilters.section === 'all' || endorsement.student.section === localFilters.section;
        const matchesInternship = localFilters.internship === 'all' || 
                                 endorsement.internship.id.toString() === localFilters.internship;
        const matchesSearch = localFilters.search === '' || 
                             endorsement.student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             endorsement.student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             endorsement.student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesStatus = localFilters.status === 'all' || 
                             (localFilters.status === 'pending_hte' && endorsement.placement_status === 'pending') ||
                             (localFilters.status === 'approved_hte' && endorsement.placement_status === 'approved') ||
                             (localFilters.status === 'rejected_hte' && endorsement.placement_status === 'rejected');
        
        return matchesSection && matchesInternship && matchesSearch && matchesStatus;
    });

    // Pagination hook
    const endorsedPagination = usePagination({
        data: filteredEndorsedStudents,
        itemsPerPage: 10,
        resetTrigger: localFilters, // Auto-reset when filters change
    });

    const handleFilterChange = (filterType: 'section' | 'internship' | 'search' | 'status', value: string) => {
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
        if (newFilters.status && newFilters.status !== 'all') {
            params.append('status', newFilters.status);
        }

        router.get('/student/endorsed', params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ section: 'all', internship: 'all', search: '', status: 'all' });
        router.get('/student/endorsed', {}, {
            preserveState: true,
            replace: true
        });
    };

    const getPlacementStatusColor = (status: string) => {
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

    const getPlacementStatusIcon = (status: string) => {
        switch (status) {
            case 'approved':
                return <CheckCircle2 className="h-4 w-4" />;
            case 'rejected':
                return <XCircleIcon className="h-4 w-4" />;
            case 'pending':
            default:
                return <ClockIcon className="h-4 w-4" />;
        }
    };

    const getPlacementStatusText = (status: string) => {
        switch (status) {
            case 'approved':
                return 'Approved by HTE';
            case 'rejected':
                return 'Rejected by HTE';
            case 'pending':
            default:
                return 'Pending HTE Review';
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

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Endorsed Students" />
            
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Endorsed Students</h1>
                        <p className="text-muted-foreground">
                            View and track students who have been endorsed for internships and their HTE approval status
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
                    </div>
                </div>

                {/* Filters Section */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FilterIcon className="h-5 w-5" />
                                Filters & Search
                            </CardTitle>
                            <CardDescription>
                                Filter endorsed students by section, internship, status, or search by name
                            </CardDescription>
                        </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                                {section.name} ({section.total_endorsements})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Internship Filter */}
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
                                                {internship.position_title} - {internship.company_name} ({internship.total_endorsements})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Status Filter */}
                            <div className="space-y-2">
                                <Label htmlFor="status-filter">HTE Status</Label>
                                <Select
                                    value={localFilters.status}
                                    onValueChange={(value) => handleFilterChange('status', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All Statuses" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Statuses</SelectItem>
                                        <SelectItem value="pending_hte">Pending HTE Review</SelectItem>
                                        <SelectItem value="approved_hte">Approved by HTE</SelectItem>
                                        <SelectItem value="rejected_hte">Rejected by HTE</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

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

                            {/* Clear Filters */}
                            <div className="space-y-2">
                                <Label>&nbsp;</Label>
                                <Button
                                    variant="outline"
                                    onClick={clearFilters}
                                    className="w-full"
                                >
                                    <ArrowUpDownIcon className="h-4 w-4 mr-2" />
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                )}

                {/* Endorsed Students Table */}
                {filteredEndorsedStudents.length === 0 ? (
                    <Card>
                        <CardContent className="text-center py-12">
                            <UsersIcon className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                            <h3 className="text-lg font-medium mb-2">
                                No Endorsed Students
                            </h3>
                            <p className="text-muted-foreground">
                                No students have been endorsed for internships yet.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TargetIcon className="h-5 w-5" />
                                Endorsed Students
                            </CardTitle>
                            <CardDescription>
                                Students who have been endorsed for internships and their HTE approval status
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-center px-2 py-2 text-xs font-medium text-muted-foreground w-12">#</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Student</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Student ID</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Section</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Specialization</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Position</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Company</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Compatibility</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">HTE Status</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Endorsed Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {endorsedPagination.paginatedData.map((endorsement, index) => (
                                            <tr key={endorsement.id} className="border-b hover:bg-muted/50 transition-colors">
                                                <td className="text-center px-2 py-2 font-mono text-xs text-muted-foreground">
                                                    {getRowNumber(endorsedPagination.currentPage, 10, index)}
                                                </td>
                                                <td className="px-2 py-2">
                                                    <div>
                                                        <div className="font-medium text-sm">
                                                            {endorsement.student.last_name}, {endorsement.student.first_name}
                                                        </div>
                                                        {endorsement.student.middle_name && (
                                                            <div className="text-xs text-muted-foreground">
                                                                {endorsement.student.middle_name}
                                                            </div>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <span className="font-mono text-xs">
                                                        {endorsement.student.student_number}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <Badge variant="outline" className="text-xs">
                                                        {endorsement.student.section}
                                                    </Badge>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <span className="text-xs text-muted-foreground">
                                                        {endorsement.student.specialization || '-'}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <span className="font-medium text-sm">
                                                        {endorsement.internship.position_title}
                                                    </span>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <span className="text-xs">
                                                        {endorsement.internship.hte.company_name}
                                                    </span>
                                                </td>
                <td className="px-2 py-2">
                    <span className="font-medium text-sm">
                        {Math.round(endorsement.compatibility_score)}% | {getGradePoint(endorsement.compatibility_score)}
                    </span>
                </td>
                                                <td className="px-2 py-2">
                                                    <Badge className={`${getPlacementStatusColor(endorsement.placement_status)} text-xs`}>
                                                        {getPlacementStatusText(endorsement.placement_status)}
                                                    </Badge>
                                                </td>
                                                <td className="px-2 py-2">
                                                    <span className="text-xs text-muted-foreground">
                                                        {endorsement.endorsement_date 
                                                            ? new Date(endorsement.endorsement_date).toLocaleDateString()
                                                            : new Date(endorsement.created_at).toLocaleDateString()
                                                        }
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Pagination */}
                {filteredEndorsedStudents.length > 0 && (
                    <Pagination
                        currentPage={endorsedPagination.currentPage}
                        totalPages={endorsedPagination.totalPages}
                        onPageChange={endorsedPagination.handlePageChange}
                        showSummary={true}
                        totalItems={filteredEndorsedStudents.length}
                        itemsPerPage={10}
                    />
                )}
            </div>
        </AdminLayout>
    );
}
