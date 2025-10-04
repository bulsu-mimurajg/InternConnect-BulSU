import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
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
    UsersIcon,
    TargetIcon,
    Building2,
    Briefcase,
    GraduationCap,
    Star,
    ArrowUpDownIcon,
    FileTextIcon,
    ChevronDown,
    ChevronUp
} from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Placed Students',
        href: '/student/placed',
    },
];

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
    title: string;
    company: string;
    department: string;
    total_placements: number;
}

interface Filters {
    sections: SectionOption[];
    internships: InternshipOption[];
    currentSection: string | null;
    currentInternship: string | null;
    currentSearch: string | null;
}

interface Props {
    placedStudents: PlacedStudent[];
    filters: Filters;
}

export default function StudentPlaced({ placedStudents = [], filters }: Props) {
    const [showFilters, setShowFilters] = useState(false);
    const [localFilters, setLocalFilters] = useState({
        section: filters.currentSection || 'all',
        internship: filters.currentInternship || 'all',
        search: filters.currentSearch || '',
    });

    // Update local filters when props change
    useEffect(() => {
        setLocalFilters({
            section: filters.currentSection || 'all',
            internship: filters.currentInternship || 'all',
            search: filters.currentSearch || ''
        });
    }, [filters]);

    // Filter placed students based on local filters
    const filteredPlacedStudents = placedStudents.filter(placement => {
        const matchesSection = localFilters.section === 'all' || placement.student?.section === localFilters.section;
        const matchesInternship = localFilters.internship === 'all' || 
                                 placement.internship?.id.toString() === localFilters.internship;
        const matchesSearch = localFilters.search === '' || 
                             placement.student?.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             placement.student?.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             placement.student?.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        
        return matchesSection && matchesInternship && matchesSearch;
    });

    // Pagination hook
    const placedPagination = usePagination({
        data: filteredPlacedStudents,
        itemsPerPage: 10,
        resetTrigger: localFilters, // Auto-reset when filters change
    });

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

        router.get('/student/placed', params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ section: 'all', internship: 'all', search: '' });
        router.get('/student/placed', {}, {
            preserveState: true,
            replace: true
        });
    };

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

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Placed Students" />
            
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Placed Students</h1>
                        <p className="text-muted-foreground">
                            View and manage student internship placements and their approval status
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
                                Filter placed students by section, internship, or search by name
                            </CardDescription>
                        </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {/* Section Filter */}
                            <div className="space-y-2">
                                <Label htmlFor="section-filter">Section</Label>
                                <Select 
                                    value={localFilters.section} 
                                    onValueChange={(value) => handleFilterChange('section', value)}
                                >
                                    <SelectTrigger id="section-filter">
                                        <SelectValue placeholder="Select section" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Sections</SelectItem>
                                        {filters.sections && Array.isArray(filters.sections) && filters.sections.map((section) => (
                                            <SelectItem key={section.name} value={section.name}>
                                                <div className="flex flex-col">
                                                    <span className="font-medium">{section.name}</span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {section.total_placements} placement{section.total_placements !== 1 ? 's' : ''}
                                                    </span>
                                                </div>
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
                                    <SelectTrigger id="internship-filter">
                                        <SelectValue placeholder="Select internship" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Internships</SelectItem>
                                        {filters.internships && Array.isArray(filters.internships) && filters.internships.map((internship) => (
                                            <SelectItem key={internship.id} value={internship.id.toString()}>
                                                <div className="flex flex-col">
                                                    <span className="font-medium">{internship.title}</span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {internship.company} • {internship.department}
                                                    </span>
                                                    <span className="text-xs text-muted-foreground">
                                                        {internship.total_placements} placement{internship.total_placements !== 1 ? 's' : ''}
                                                    </span>
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Search */}
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <div className="relative">
                                    <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
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

                {/* Summary Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Placements</CardTitle>
                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{filteredPlacedStudents.length}</div>
                            <p className="text-xs text-muted-foreground">
                                Students with placement decisions
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Approved</CardTitle>
                            <CheckCircleIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {filteredPlacedStudents.filter(s => s.status && s.status === 'approved').length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Successfully placed students
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pending</CardTitle>
                            <ClockIcon className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {filteredPlacedStudents.filter(s => s.status && s.status === 'pending').length}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Awaiting decision
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Placements Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <TargetIcon className="h-5 w-5" />
                            Student Placements
                        </CardTitle>
                        <CardDescription>
                            Students who have been placed in internship positions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredPlacedStudents.length === 0 ? (
                            <div className="text-center py-12">
                                <FileTextIcon className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                <h3 className="text-lg font-medium mb-2">No Placements Found</h3>
                                <p className="text-muted-foreground">
                                    {localFilters.section !== 'all' || localFilters.internship !== 'all' || localFilters.search
                                        ? 'Try adjusting your filters or search criteria.'
                                        : 'Students need to be placed through the matching process.'
                                    }
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-center px-2 py-2 text-xs font-medium text-muted-foreground w-12">#</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Student</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Section</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Internship</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Compatibility</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Status</th>
                                            <th className="text-left px-2 py-2 text-xs font-medium text-muted-foreground">Date</th>
                                        </tr>
                                    </thead>
                                        <tbody>
                                            {placedPagination.paginatedData.map((placement, index) => (
                                                <tr key={placement.id} className="border-b hover:bg-muted/50 transition-colors">
                                                    <td className="text-center px-2 py-2 font-mono text-xs text-muted-foreground">
                                                        {getRowNumber(placedPagination.currentPage, 10, index)}
                                                    </td>
                                                    <td className="px-2 py-2">
                                                        <div>
                                                            <div className="font-medium text-sm">
                                                                {placement.student?.last_name || 'N/A'}, {placement.student?.first_name || 'N/A'}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {placement.student?.student_number || 'N/A'}
                                                            </div>
                                                            {placement.student?.middle_name && (
                                                                <div className="text-xs text-muted-foreground">
                                                                    {placement.student.middle_name}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-2 py-2">
                                                        <div>
                                                            <Badge variant="outline" className="text-xs">{placement.student?.section || 'N/A'}</Badge>
                                                            {placement.student?.specialization && (
                                                                <div className="text-xs text-muted-foreground mt-1">
                                                                    {placement.student.specialization}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-2 py-2">
                                                        <div>
                                                            <div className="font-medium text-sm">
                                                                {placement.internship?.position_title || 'N/A'}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {placement.internship?.hte?.company_name || 'N/A'}
                                                            </div>
                                                            <div className="text-xs text-muted-foreground">
                                                                {placement.internship?.department || 'N/A'}
                                                            </div>
                                                        </div>
                                                    </td>
                    <td className="px-2 py-2">
                        <span className="font-medium text-sm">
                            {Math.round(placement.compatibility_score || 0)}% | {getGradePoint(placement.compatibility_score || 0)}
                        </span>
                    </td>
                                                    <td className="px-2 py-2">
                                                        <Badge className={`${getStatusColor(placement.status || 'pending')} text-xs`}>
                                                            {(placement.status || 'pending').charAt(0).toUpperCase() + (placement.status || 'pending').slice(1)}
                                                        </Badge>
                                                    </td>
                                                    <td className="px-2 py-2">
                                                        <div className="text-xs text-muted-foreground">
                                                            {placement.created_at ? new Date(placement.created_at).toLocaleDateString() : 'N/A'}
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

                    {/* Pagination */}
                    <Pagination
                        currentPage={placedPagination.currentPage}
                        totalPages={placedPagination.totalPages}
                        onPageChange={placedPagination.handlePageChange}
                        showSummary={true}
                        totalItems={filteredPlacedStudents.length}
                        itemsPerPage={10}
                    />
            </div>
        </AdminLayout>
    );
}
