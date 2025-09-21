import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
    Star
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
}

export default function PlacedStudents({ 
    placed_students = [], 
    section_options = [], 
    internship_options = [], 
    filters,
    hteId 
}: Props) {
    const [localFilters, setLocalFilters] = useState({
        section: filters.section || 'all',
        internship: filters.internship || 'all',
        search: filters.search || '',
    });

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
        <AppLayout>
            <Head title="Placed Students" />
            
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Placed Students</h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-1">
                            View students who have been placed in your internships
                        </p>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FilterIcon className="h-5 w-5" />
                            Filters
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
                                                {internship.position_title} - {internship.department} ({internship.total_placements})
                                            </SelectItem>
                                        ))}
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
                                    Clear Filters
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Results Summary */}
                <div className="flex items-center justify-between">
                    <p className="text-sm text-gray-600 dark:text-gray-400">
                        Showing {placed_students.length} placed student{placed_students.length !== 1 ? 's' : ''}
                    </p>
                </div>

                {/* Placed Students Table */}
                {placed_students.length === 0 ? (
                    <Card>
                        <CardContent className="text-center py-12">
                            <UserIcon className="h-12 w-12 mx-auto text-gray-400 mb-4" />
                            <h3 className="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                No Placed Students
                            </h3>
                            <p className="text-gray-600 dark:text-gray-400">
                                No students have been placed in your internships yet.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle>Placed Students</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b border-gray-200">
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Student Number</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Section</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Specialization</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Position</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Department</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Compatibility</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Placed Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {placed_students.map((placement) => (
                                            <tr key={placement.id} className="border-b border-gray-100">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <UserIcon className="h-4 w-4 text-muted-foreground" />
                                                        <div>
                                                            <div className="font-medium">
                                                                {placement.student.first_name} {placement.student.last_name}
                                                            </div>
                                                            {placement.student.middle_name && (
                                                                <div className="text-sm text-muted-foreground">
                                                                    {placement.student.middle_name}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <GraduationCap className="h-4 w-4 text-muted-foreground" />
                                                        <span className="font-mono text-sm">
                                                            {placement.student.student_number}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge variant="secondary">
                                                        {placement.student.section}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span className="text-sm">
                                                        {placement.student.specialization || 'N/A'}
                                                    </span>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <Briefcase className="h-4 w-4 text-muted-foreground" />
                                                        <span className="font-medium">
                                                            {placement.internship.position_title}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm">
                                                            {placement.internship.department}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-1">
                                                        <Star className="h-4 w-4 text-yellow-500" />
                                                        <span className="font-medium">
                                                            {placement.compatibility_score}%
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge className={getStatusColor(placement.status)}>
                                                        <div className="flex items-center gap-1">
                                                            {getStatusIcon(placement.status)}
                                                            {placement.status.charAt(0).toUpperCase() + placement.status.slice(1)}
                                                        </div>
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <span className="text-sm text-muted-foreground">
                                                        {placement.placement_date 
                                                            ? new Date(placement.placement_date).toLocaleDateString()
                                                            : new Date(placement.created_at).toLocaleDateString()
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
            </div>
        </AppLayout>
    );
}
