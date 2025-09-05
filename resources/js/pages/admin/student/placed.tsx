import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AdminLayout from '@/layouts/admin/layout';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    UserIcon, 
    Building2Icon, 
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    SearchIcon,
    FilterIcon
} from 'lucide-react';

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

    return (
        <AdminLayout>
            <Head title="Placed Students" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <Heading 
                        title="Placed Students" 
                        description="View and manage student internship placements."
                    />
                </div>

                {/* Filters Section */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FilterIcon className="h-5 w-5" />
                            Filters & Search
                        </CardTitle>
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
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                    {/* Summary Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Placements</CardTitle>
                                <UserIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{placedStudents.length}</div>
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
                                    {placedStudents.filter(s => s.status && s.status === 'approved').length}
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
                                    {placedStudents.filter(s => s.status && s.status === 'pending').length}
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
                            <CardTitle>Student Placements</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {placedStudents.length === 0 ? (
                                <div className="text-center py-12">
                                    <div className="text-gray-400 mb-4">
                                        <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">No Placements Found</h3>
                                    <p className="text-gray-500">
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
                                            <tr className="border-b border-gray-200">
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Section</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Internship</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Compatibility</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {placedStudents.map((placement) => (
                                                <tr key={placement.id} className="border-b border-gray-100 hover:bg-gray-50">
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <div className="font-medium text-gray-900">
                                                                {placement.student?.last_name || 'N/A'}, {placement.student?.first_name || 'N/A'}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {placement.student?.student_number || 'N/A'}
                                                            </div>
                                                            {placement.student?.middle_name && (
                                                                <div className="text-xs text-gray-400">
                                                                    {placement.student.middle_name}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <Badge variant="outline">{placement.student?.section || 'N/A'}</Badge>
                                                            {placement.student?.specialization && (
                                                                <div className="text-xs text-gray-500 mt-1">
                                                                    {placement.student.specialization}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <div className="font-medium text-gray-900">
                                                                {placement.internship?.position_title || 'N/A'}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {placement.internship?.hte?.company_name || 'N/A'}
                                                            </div>
                                                            <div className="text-xs text-gray-400">
                                                                {placement.internship?.department || 'N/A'}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <Badge variant="outline">
                                                            {placement.compatibility_score || 0}%
                                                        </Badge>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <Badge className={getStatusColor(placement.status || 'pending')}>
                                                            <div className="flex items-center gap-1">
                                                                {getStatusIcon(placement.status || 'pending')}
                                                                {(placement.status || 'pending').charAt(0).toUpperCase() + (placement.status || 'pending').slice(1)}
                                                            </div>
                                                        </Badge>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="text-sm text-gray-500">
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
                </div>
        </AdminLayout>
    );
}
