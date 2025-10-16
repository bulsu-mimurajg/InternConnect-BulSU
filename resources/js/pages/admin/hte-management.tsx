import React, { useState, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Edit, Archive, Eye, ArchiveRestore, Filter, ChevronRight, ChevronDown, ArrowUpDown, Search, BriefcaseBusinessIcon, Building2Icon, UserIcon, MailIcon, PhoneIcon, MapPinIcon, CalendarIcon, BriefcaseIcon } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface Internship {
    id: number;
    position_title: string;
    department: string;
    placement_description: string;
    slot_count: number;
    is_active: boolean;
    created_at: string;
}

interface HTE {
    id: number;
    user_id: number;
    username: string;
    email: string;
    status: string;
    company_name: string | null;
    company_address: string | null;
    company_email: string | null;
    contact_person: string | null;
    contact_position: string | null;
    contact_number: string | null;
    cperson_fname: string | null;
    cperson_lname: string | null;
    is_active: boolean;
    is_submit: boolean;
    created_at: string;
    updated_at: string;
    internships: Internship[];
}

interface Props {
    htes: HTE[];
    showArchived?: boolean;
    filters?: {
        search?: string;
        status?: string;
        submission?: string;
    };
    deadlineStatus?: {
        student_assessment?: {
            id: number;
            title: string;
            category: string;
            end_date: string;
            formatted_end_date: string;
            time_remaining_hours: number;
            time_remaining_days: number;
            is_active: boolean;
            is_expired: boolean;
        };
        internship_placement?: {
            id: number;
            title: string;
            category: string;
            end_date: string;
            formatted_end_date: string;
            time_remaining_hours: number;
            time_remaining_days: number;
            is_active: boolean;
            is_expired: boolean;
        };
        restrictions: Array<{
            type: string;
            message: string;
            deadline: {
                id: number;
                title: string;
                category: string;
                end_date: string;
            };
            affected_functionality: string[];
        }>;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Management',
        href: '/hte',
    },
];

export default function HTEManagement({ htes, showArchived = false, filters = {}, deadlineStatus }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedHTE, setSelectedHTE] = useState<HTE | null>(null);
    const [showArchivedHTEs, setShowArchivedHTEs] = useState(showArchived);
    const [showFilters, setShowFilters] = useState(false);
    const [expandedRows, setExpandedRows] = useState<Set<number>>(new Set());
    const [isDeadlineModalOpen, setIsDeadlineModalOpen] = useState(false);
    const [isArchiveModalOpen, setIsArchiveModalOpen] = useState(false);
    const [hteToArchive, setHteToArchive] = useState<HTE | null>(null);
    const [isUnarchiveModalOpen, setIsUnarchiveModalOpen] = useState(false);
    const [hteToUnarchive, setHteToUnarchive] = useState<HTE | null>(null);
    const [localFilters, setLocalFilters] = useState({
        search: filters.search || '',
        status: filters.status || 'all',
        submission: filters.submission || 'all',
    });

    const createForm = useForm({
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
    });

    const editForm = useForm({
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        company_name: '',
        company_address: '',
        company_email: '',
        cperson_fname: '',
        cperson_lname: '',
        cperson_position: '',
        cperson_contactnum: '',
    });

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        createForm.post(route('admin.hte.store'), {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
            onError: (errors) => {
                // Handle validation errors
                console.error('HTE creation failed:', errors);
            },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (selectedHTE) {
            editForm.put(route('admin.hte.update', selectedHTE.id), {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setSelectedHTE(null);
                    editForm.reset();
                },
            });
        }
    };

    const handleEdit = (hte: HTE) => {
        setSelectedHTE(hte);
        editForm.setData({
            email: hte.email,
            username: hte.username,
            password: '',
            password_confirmation: '',
            company_name: hte.company_name || '',
            company_address: hte.company_address || '',
            company_email: hte.company_email || '',
            cperson_fname: hte.cperson_fname || '',
            cperson_lname: hte.cperson_lname || '',
            cperson_position: hte.contact_position || '',
            cperson_contactnum: hte.contact_number || '',
        });
        setIsEditDialogOpen(true);
    };

    // Check if HTE management is restricted due to deadlines
    const isHTEArchivingRestricted = useMemo(() => {
        return deadlineStatus?.restrictions.some(restriction => 
            restriction.affected_functionality.includes('hte_archive') ||
            restriction.affected_functionality.includes('hte_restore') ||
            restriction.affected_functionality.includes('hte_management')
        ) || false;
    }, [deadlineStatus]);

    // Get restriction message for HTE management
    const archivingRestrictionMessage = useMemo(() => {
        const restriction = deadlineStatus?.restrictions.find(restriction => 
            restriction.affected_functionality.includes('hte_archive') ||
            restriction.affected_functionality.includes('hte_restore') ||
            restriction.affected_functionality.includes('hte_management')
        );
        return restriction?.message || '';
    }, [deadlineStatus]);

    const handleArchive = (hte: HTE) => {
        // Check if there's an active deadline restriction
        if (isHTEArchivingRestricted) {
            setIsDeadlineModalOpen(true);
            return;
        }

        // Open the archive confirmation modal
        setHteToArchive(hte);
        setIsArchiveModalOpen(true);
    };

    const confirmArchive = () => {
        if (hteToArchive) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.hte.archive', hteToArchive.id), {}, {
                onSuccess: () => {
                    setIsArchiveModalOpen(false);
                    setHteToArchive(null);
                    // Refresh the page to update the list
                    router.reload({ only: ['htes'] });
                },
                onError: (errors) => {
                    console.error('Archive error:', errors);
                    setIsArchiveModalOpen(false);
                    setHteToArchive(null);
                }
            });
        }
    };

    const cancelArchive = () => {
        setIsArchiveModalOpen(false);
        setHteToArchive(null);
    };

    const handleUnarchive = (hte: HTE) => {
        // Open the unarchive confirmation modal
        setHteToUnarchive(hte);
        setIsUnarchiveModalOpen(true);
    };

    const confirmUnarchive = () => {
        if (hteToUnarchive) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.hte.unarchive', hteToUnarchive.id), {}, {
                onSuccess: () => {
                    setIsUnarchiveModalOpen(false);
                    setHteToUnarchive(null);
                    // Refresh the page to update the list
                    router.reload({ only: ['htes'] });
                },
                onError: (errors) => {
                    console.error('Unarchive error:', errors);
                    setIsUnarchiveModalOpen(false);
                    setHteToUnarchive(null);
                }
            });
        }
    };

    const cancelUnarchive = () => {
        setIsUnarchiveModalOpen(false);
        setHteToUnarchive(null);
    };

    const toggleArchivedView = () => {
        setShowArchivedHTEs(!showArchivedHTEs);
        // Navigate to the appropriate view using Inertia router
        const routeName = showArchivedHTEs ? 'admin.hte' : 'admin.hte.archived';
        router.visit(route(routeName));
    };

    const handleFilterChange = (filterType: 'search' | 'status' | 'submission', value: string) => {
        const newFilters = { ...localFilters, [filterType]: value };
        setLocalFilters(newFilters);

        // Apply filters immediately
        const params = new URLSearchParams();
        if (newFilters.search) {
            params.append('search', newFilters.search);
        }
        if (newFilters.status && newFilters.status !== 'all') {
            params.append('status', newFilters.status);
        }
        if (newFilters.submission && newFilters.submission !== 'all') {
            params.append('submission', newFilters.submission);
        }

        const routeName = showArchivedHTEs ? 'admin.hte.archived' : 'admin.hte';
        router.get(route(routeName), params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ search: '', status: 'all', submission: 'all' });
        const routeName = showArchivedHTEs ? 'admin.hte.archived' : 'admin.hte';
        router.get(route(routeName), {}, {
            preserveState: true,
            replace: true
        });
    };

    const toggleRow = (hteId: number) => {
        const newExpandedRows = new Set(expandedRows);
        if (newExpandedRows.has(hteId)) {
            newExpandedRows.delete(hteId);
        } else {
            newExpandedRows.add(hteId);
        }
        setExpandedRows(newExpandedRows);
    };

    const filteredHTEs = htes.filter(hte => {
        // First filter by archived status based on current view
        const matchesArchivedStatus = showArchivedHTEs ? hte.status === 'archived' : hte.status !== 'archived';
        
        if (!matchesArchivedStatus) {
            return false;
        }

        const fullName = `${hte.cperson_fname || ''} ${hte.cperson_lname || ''}`.trim();
        const matchesSearch = hte.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            hte.email.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            (hte.company_name && hte.company_name.toLowerCase().includes(localFilters.search.toLowerCase())) ||
                            (fullName && fullName.toLowerCase().includes(localFilters.search.toLowerCase())) ||
                            (hte.cperson_fname && hte.cperson_fname.toLowerCase().includes(localFilters.search.toLowerCase())) ||
                            (hte.cperson_lname && hte.cperson_lname.toLowerCase().includes(localFilters.search.toLowerCase()));
        const matchesStatus = localFilters.status === 'all' || hte.status === localFilters.status;
        const matchesSubmission = localFilters.submission === 'all' || 
                                (localFilters.submission === 'submitted' && hte.is_submit) ||
                                (localFilters.submission === 'not_submitted' && !hte.is_submit);
        return matchesSearch && matchesStatus && matchesSubmission;
    });

    // Create a stable reset trigger for pagination
    const resetTrigger = useMemo(() => 
        `${localFilters.search}-${localFilters.status}-${localFilters.submission}-${showArchivedHTEs}`,
        [localFilters.search, localFilters.status, localFilters.submission, showArchivedHTEs]
    );

    // Pagination hook with auto-reset on filter changes
    const htePagination = usePagination({
        data: filteredHTEs,
        itemsPerPage: 10,
        resetTrigger: resetTrigger,
    });

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'verified':
                return <Badge variant="default">Verified</Badge>;
            case 'archived':
                return <Badge variant="secondary">Archived</Badge>;
            case 'unverified':
                return <Badge variant="outline">Unverified</Badge>;
            default:
                return <Badge variant="outline">{status}</Badge>;
        }
    };

    const renderHTEDetails = (hte: HTE) => {
        return (
            <div className="p-4 bg-muted/30 border-t">
                <Card className="p-6">
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Company Information */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium flex items-center gap-2">
                                <Building2Icon className="h-4 w-4" />
                                Company Information
                            </h4>
                            <div className="space-y-3">
                                <div className="flex items-start gap-3">
                                    <Building2Icon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Company Name</div>
                                        <div className="text-sm">{hte.company_name || 'Not provided'}</div>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <MapPinIcon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Address</div>
                                        <div className="text-sm">{hte.company_address && hte.company_address.trim() ? hte.company_address : 'Not provided'}</div>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <MailIcon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Company Email</div>
                                        <div className="text-sm">{hte.company_email || 'Not provided'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Contact Information */}
                        <div className="space-y-4">
                            <h4 className="text-sm font-medium flex items-center gap-2">
                                <UserIcon className="h-4 w-4" />
                                Contact Information
                            </h4>
                            <div className="space-y-3">
                                <div className="flex items-start gap-3">
                                    <UserIcon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Contact Person</div>
                                        <div className="text-sm">
                                            {(
                                                hte.contact_person && hte.contact_person.trim()
                                            ) || (
                                                (hte.cperson_fname || hte.cperson_lname) ? `${hte.cperson_fname || ''} ${hte.cperson_lname || ''}`.trim() : ''
                                            ) || 'Not provided'}
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <BriefcaseIcon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Position</div>
                                        <div className="text-sm">{hte.contact_position || 'Not provided'}</div>
                                    </div>
                                </div>
                                <div className="flex items-start gap-3">
                                    <PhoneIcon className="h-4 w-4 text-muted-foreground mt-0.5" />
                                    <div>
                                        <div className="text-xs font-medium text-muted-foreground">Contact Number</div>
                                        <div className="text-sm">{hte.contact_number || 'Not provided'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Internships Section */}
                    <div className="mt-6">
                        <h4 className="text-sm font-medium flex items-center gap-2 mb-4">
                            <BriefcaseIcon className="h-4 w-4" />
                            Internships ({hte.internships.length})
                        </h4>
                        {hte.internships.length === 0 ? (
                            <div className="text-center py-6 text-muted-foreground">
                                <BriefcaseIcon className="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p className="text-sm">No internships created yet</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {hte.internships.map((internship) => (
                                    <Card key={internship.id} className="p-4">
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <h5 className="font-medium text-sm">{internship.position_title}</h5>
                                                <Badge variant={internship.is_active ? "default" : "secondary"}>
                                                    {internship.is_active ? 'Active' : 'Inactive'}
                                                </Badge>
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {internship.department}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {internship.slot_count} slot{internship.slot_count !== 1 ? 's' : ''}
                                            </div>
                                            <div className="text-xs text-muted-foreground line-clamp-2">
                                                {internship.placement_description}
                                            </div>
                                            <div className="text-xs text-muted-foreground flex items-center gap-1">
                                                <CalendarIcon className="h-3 w-3" />
                                                Created {internship.created_at}
                                            </div>
                                        </div>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                </Card>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Management" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Deadline Restriction Alert */}
                {isHTEArchivingRestricted && (
                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <Archive className="h-5 w-5 text-amber-400" />
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-amber-800">
                                    HTE Management Restricted
                                </h3>
                                <div className="mt-2 text-sm text-amber-700">
                                    <p>{archivingRestrictionMessage}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Header Section */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-bold tracking-tight">HTE Management</h1>
                        <p className="text-muted-foreground">
                            Manage Host Training Establishment accounts and their information
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
                            onClick={toggleArchivedView}
                        >
                            {showArchivedHTEs ? (
                                <>
                                    <Eye className="h-4 w-4" />
                                    Show Active
                                </>
                            ) : (
                                <>
                                    <Archive className="h-4 w-4" />
                                    Show Archived
                                </>
                            )}
                        </Button>
                        <Dialog open={isCreateDialogOpen} onOpenChange={setIsCreateDialogOpen}>
                            <DialogTrigger asChild>
                                <Button 
                                    disabled={isHTEArchivingRestricted}
                                >
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add HTE
                                </Button>
                            </DialogTrigger>
                                <DialogContent className="sm:max-w-[425px]">
                                    <DialogHeader>
                                        <DialogTitle>Create New HTE Account</DialogTitle>
                                        <DialogDescription>
                                            Create a new HTE account with email, username and password.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form onSubmit={handleCreateSubmit}>
                                        <div className="grid gap-4 py-4">
                                            <div className="grid grid-cols-4 items-center gap-4">
                                                <Label htmlFor="email" className="text-right">
                                                    Email
                                                </Label>
                                                <div className="col-span-3">
                                                    <Input
                                                        id="email"
                                                        type="email"
                                                        value={createForm.data.email}
                                                        onChange={(e) => createForm.setData('email', e.target.value)}
                                                        className={createForm.errors.email ? 'border-red-500' : ''}
                                                        required
                                                    />
                                                    {createForm.errors.email && (
                                                        <p className="text-red-500 text-xs mt-1">{createForm.errors.email}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-4 items-center gap-4">
                                                <Label htmlFor="username" className="text-right">
                                                    Username
                                                </Label>
                                                <div className="col-span-3">
                                                    <Input
                                                        id="username"
                                                        value={createForm.data.username}
                                                        onChange={(e) => createForm.setData('username', e.target.value)}
                                                        className={createForm.errors.username ? 'border-red-500' : ''}
                                                        required
                                                    />
                                                    {createForm.errors.username && (
                                                        <p className="text-red-500 text-xs mt-1">{createForm.errors.username}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-4 items-center gap-4">
                                                <Label htmlFor="password" className="text-right">
                                                    Password
                                                </Label>
                                                <div className="col-span-3">
                                                    <Input
                                                        id="password"
                                                        type="password"
                                                        value={createForm.data.password}
                                                        onChange={(e) => createForm.setData('password', e.target.value)}
                                                        className={createForm.errors.password ? 'border-red-500' : ''}
                                                        required
                                                    />
                                                    {createForm.errors.password && (
                                                        <p className="text-red-500 text-xs mt-1">{createForm.errors.password}</p>
                                                    )}
                                                </div>
                                            </div>
                                            <div className="grid grid-cols-4 items-center gap-4">
                                                <Label htmlFor="password_confirmation" className="text-right">
                                                    Confirm Password
                                                </Label>
                                                <div className="col-span-3">
                                                    <Input
                                                        id="password_confirmation"
                                                        type="password"
                                                        value={createForm.data.password_confirmation}
                                                        onChange={(e) => createForm.setData('password_confirmation', e.target.value)}
                                                        className={createForm.errors.password_confirmation ? 'border-red-500' : ''}
                                                        required
                                                    />
                                                    {createForm.errors.password_confirmation && (
                                                        <p className="text-red-500 text-xs mt-1">{createForm.errors.password_confirmation}</p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                        <DialogFooter>
                                            <Button type="submit" disabled={createForm.processing}>
                                                {createForm.processing ? 'Creating...' : 'Create HTE'}
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                                </Dialog>
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
                                Filter HTEs by status, submission status, or search by username, email, company name, or contact person
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
                                            placeholder="Search HTEs..."
                                            value={localFilters.search}
                                            onChange={(e) => handleFilterChange('search', e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
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
                                            <SelectItem value="verified">Verified</SelectItem>
                                            <SelectItem value="unverified">Unverified</SelectItem>
                                            <SelectItem value="archived">Archived</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Submission Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="submission-filter">Form Status</Label>
                                    <Select
                                        value={localFilters.submission}
                                        onValueChange={(value) => handleFilterChange('submission', value)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Submission Status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Submission Status</SelectItem>
                                            <SelectItem value="submitted">Submitted</SelectItem>
                                            <SelectItem value="not_submitted">Not Submitted</SelectItem>
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
                
                {/* HTEs Table */}
                <Card>
                    <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                            <BriefcaseBusinessIcon className="h-5 w-5"/>
                            Host Training Establishments
                            </CardTitle>
                        <CardDescription>
                            {showArchivedHTEs 
                                ? 'Archived HTE accounts' 
                                : 'Active HTE accounts and their submission status'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredHTEs.length === 0 ? (
                            <div className="py-12 text-center">
                                <div className="flex flex-col items-center space-y-4">
                                    <Archive className="h-12 w-12 text-muted-foreground" />
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-medium">
                                            {showArchivedHTEs ? 'No archived HTEs found' : 'No HTEs found'}
                                        </h3>
                                        <p className="text-muted-foreground">
                                            {showArchivedHTEs 
                                                ? 'No HTE accounts have been archived yet.' 
                                                : 'Get started by creating your first HTE account.'
                                            }
                                        </p>
                                    </div>
                                    {!showArchivedHTEs && (
                                        <Button 
                                            disabled={isHTEArchivingRestricted}
                                            onClick={() => setIsCreateDialogOpen(true)}
                                        >
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add HTE
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-center py-3 px-4 font-semibold text-sm w-16">#</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Username</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Company</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Contact Person</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Form Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {htePagination.paginatedData.map((hte, index) => {
                                            const isExpanded = expandedRows.has(hte.id);
                                            const hasDetails = hte.internships.length > 0 || hte.company_name || hte.contact_person;
                                            
                                            return (
                                                <React.Fragment key={hte.id}>
                                                    <tr 
                                                        className={`border-b hover:bg-muted/50 transition-colors ${hte.status === 'archived' ? 'opacity-75' : ''} ${hasDetails ? 'cursor-pointer' : ''}`}
                                                        onClick={() => hasDetails && toggleRow(hte.id)}
                                                    >
                                                        <td className="text-center py-3 px-4 font-mono text-sm text-muted-foreground">
                                                            {getRowNumber(htePagination.currentPage, 10, index)}
                                                        </td>
                                                        <td className="py-3 px-4 font-medium">
                                                            <div className="space-y-1">
                                                                <div>{hte.username}</div>
                                                                {hasDetails && (
                                                                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                                                        {isExpanded ? (
                                                                            <ChevronDown className="h-3 w-3" />
                                                                        ) : (
                                                                            <ChevronRight className="h-3 w-3" />
                                                                        )}
                                                                        <Badge variant="outline" className="text-xs">
                                                                            {hte.internships.length} internship{hte.internships.length !== 1 ? 's' : ''}
                                                                        </Badge>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </td>
                                                        <td className="py-3 px-4 text-sm text-muted-foreground">{hte.email}</td>
                                                        <td className="py-3 px-4">{getStatusBadge(hte.status)}</td>
                                                        <td className="py-3 px-4 text-sm text-muted-foreground">
                                                            {hte.company_name || 'Not provided'}
                                                        </td>
                                                        <td className="py-3 px-4 text-sm text-muted-foreground">
                                                            {(
                                                                hte.contact_person && hte.contact_person.trim()
                                                            ) || (
                                                                (hte.cperson_fname || hte.cperson_lname) ? `${hte.cperson_fname || ''} ${hte.cperson_lname || ''}`.trim() : ''
                                                            ) || 'Not provided'}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <Badge variant={hte.is_submit ? "default" : "outline"}>
                                                                {hte.is_submit ? 'Submitted' : 'Not Submitted'}
                                                            </Badge>
                                                        </td>
                                                        <td className="py-3 px-4 text-sm text-muted-foreground">
                                                            {new Date(hte.created_at).toLocaleDateString()}
                                                        </td>
                                                        <td className="py-3 px-4 text-right">
                                                            <div className="flex items-center gap-2 justify-end">
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        handleEdit(hte);
                                                                    }}
                                                                    className="h-8 w-8 p-0"
                                                                >
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                                {hte.status === 'archived' ? (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            handleUnarchive(hte);
                                                                        }}
                                                                        disabled={isHTEArchivingRestricted}
                                                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700 hover:bg-green-100"
                                                                    >
                                                                        <ArchiveRestore className="h-4 w-4" />
                                                                    </Button>
                                                                ) : (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            handleArchive(hte);
                                                                        }}
                                                                        disabled={isHTEArchivingRestricted}
                                                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                                                                    >
                                                                        <Archive className="h-4 w-4" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    {isExpanded && hasDetails && (
                                                        <tr>
                                                            <td colSpan={9} className="p-0">
                                                                {renderHTEDetails(hte)}
                                                            </td>
                                                        </tr>
                                                    )}
                                                </React.Fragment>
                                            );
                                        })}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        
                        {/* Pagination */}
                        {filteredHTEs.length > 0 && (
                            <Pagination
                                currentPage={htePagination.currentPage}
                                totalPages={htePagination.totalPages}
                                onPageChange={htePagination.handlePageChange}
                                showSummary={true}
                                totalItems={filteredHTEs.length}
                                itemsPerPage={10}
                            />
                        )}
                    </CardContent>
                </Card>


                {/* Edit Dialog */}
                <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                    <DialogContent className="sm:max-w-[600px] max-h-[80vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Edit HTE Account</DialogTitle>
                            <DialogDescription>
                                Update HTE account information.
                            </DialogDescription>
                        </DialogHeader>
                        <form onSubmit={handleEditSubmit}>
                            <div className="grid gap-4 py-4">
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-email" className="text-right">
                                        Email
                                    </Label>
                                    <Input
                                        id="edit-email"
                                        type="email"
                                        value={editForm.data.email}
                                        onChange={(e) => editForm.setData('email', e.target.value)}
                                        className="col-span-3"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-username" className="text-right">
                                        Username
                                    </Label>
                                    <Input
                                        id="edit-username"
                                        value={editForm.data.username}
                                        onChange={(e) => editForm.setData('username', e.target.value)}
                                        className="col-span-3"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-password" className="text-right">
                                        Password
                                    </Label>
                                    <Input
                                        id="edit-password"
                                        type="password"
                                        value={editForm.data.password}
                                        onChange={(e) => editForm.setData('password', e.target.value)}
                                        className="col-span-3"
                                        placeholder="Leave blank to keep current password"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-password-confirmation" className="text-right">
                                        Confirm Password
                                    </Label>
                                    <Input
                                        id="edit-password-confirmation"
                                        type="password"
                                        value={editForm.data.password_confirmation}
                                        onChange={(e) => editForm.setData('password_confirmation', e.target.value)}
                                        className="col-span-3"
                                        placeholder="Leave blank to keep current password"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-company-name" className="text-right">
                                        Company Name
                                    </Label>
                                    <Input
                                        id="edit-company-name"
                                        value={editForm.data.company_name}
                                        onChange={(e) => editForm.setData('company_name', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-company-address" className="text-right">
                                        Company Address
                                    </Label>
                                    <Input
                                        id="edit-company-address"
                                        value={editForm.data.company_address}
                                        onChange={(e) => editForm.setData('company_address', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-company-email" className="text-right">
                                        Company Email
                                    </Label>
                                    <Input
                                        id="edit-company-email"
                                        type="email"
                                        value={editForm.data.company_email}
                                        onChange={(e) => editForm.setData('company_email', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-contact-fname" className="text-right">
                                        Contact First Name
                                    </Label>
                                    <Input
                                        id="edit-contact-fname"
                                        value={editForm.data.cperson_fname}
                                        onChange={(e) => editForm.setData('cperson_fname', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-contact-lname" className="text-right">
                                        Contact Last Name
                                    </Label>
                                    <Input
                                        id="edit-contact-lname"
                                        value={editForm.data.cperson_lname}
                                        onChange={(e) => editForm.setData('cperson_lname', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-contact-position" className="text-right">
                                        Contact Position
                                    </Label>
                                    <Input
                                        id="edit-contact-position"
                                        value={editForm.data.cperson_position}
                                        onChange={(e) => editForm.setData('cperson_position', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-contact-number" className="text-right">
                                        Contact Number
                                    </Label>
                                    <Input
                                        id="edit-contact-number"
                                        value={editForm.data.cperson_contactnum}
                                        onChange={(e) => editForm.setData('cperson_contactnum', e.target.value)}
                                        className="col-span-3"
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" disabled={editForm.processing}>
                                    {editForm.processing ? 'Updating...' : 'Update HTE'}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* Deadline Restriction Modal */}
                <Dialog open={isDeadlineModalOpen} onOpenChange={setIsDeadlineModalOpen}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-amber-600">
                                <CalendarIcon className="h-5 w-5" />
                                Action Restriction
                            </DialogTitle>
                            <DialogDescription className="pt-4 space-y-3">
                                <div className="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg">
                                    <p className="text-sm text-amber-900 dark:text-amber-100 font-medium">
                                        {archivingRestrictionMessage || 'HTE management actions are restricted due to an active deadline.'}
                                    </p>
                                </div>
                                {deadlineStatus?.student_assessment && (
                                    <div className="text-sm text-muted-foreground space-y-1">
                                        <p><span className="font-medium">Deadline:</span> {deadlineStatus.student_assessment.title}</p>
                                        <p><span className="font-medium">Ends:</span> {deadlineStatus.student_assessment.formatted_end_date}</p>
                                    </div>
                                )}
                                {deadlineStatus?.internship_placement && (
                                    <div className="text-sm text-muted-foreground space-y-1">
                                        <p><span className="font-medium">Deadline:</span> {deadlineStatus.internship_placement.title}</p>
                                        <p><span className="font-medium">Ends:</span> {deadlineStatus.internship_placement.formatted_end_date}</p>
                                    </div>
                                )}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button 
                                onClick={() => setIsDeadlineModalOpen(false)}
                                className="w-full sm:w-auto"
                            >
                                Okay
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* Archive Confirmation Modal */}
                <Dialog open={isArchiveModalOpen} onOpenChange={setIsArchiveModalOpen}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Archive className="h-5 w-5 text-orange-600" />
                                Archive
                            </DialogTitle>
                            <DialogDescription className="pt-4">
                                <p className="text-base text-foreground">
                                    Are you sure you want to archive this HTE Account?
                                </p>
                                {hteToArchive && (
                                    <div className="mt-4 p-3 bg-muted rounded-lg">
                                        <p className="text-sm">
                                            <span className="font-medium">Company:</span> {hteToArchive.company_name || 'Not provided'}
                                        </p>
                                        <p className="text-sm">
                                            <span className="font-medium">Email:</span> {hteToArchive.email}
                                        </p>
                                    </div>
                                )}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter className="gap-3">
                            <Button 
                                variant="outline"
                                onClick={cancelArchive}
                                className="w-full sm:w-auto"
                            >
                                Cancel
                            </Button>
                            <Button 
                                variant="destructive"
                                onClick={confirmArchive}
                                className="w-full sm:w-auto"
                            >
                                Yes
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* Unarchive Confirmation Modal */}
                <Dialog open={isUnarchiveModalOpen} onOpenChange={setIsUnarchiveModalOpen}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <ArchiveRestore className="h-5 w-5 text-green-600" />
                                Unarchive
                            </DialogTitle>
                            <DialogDescription className="pt-4">
                                <p className="text-base text-foreground">
                                    Are you sure you want to unarchive this HTE Account?
                                </p>
                                {hteToUnarchive && (
                                    <div className="mt-4 p-3 bg-muted rounded-lg">
                                        <p className="text-sm">
                                            <span className="font-medium">Company:</span> {hteToUnarchive.company_name || 'Not provided'}
                                        </p>
                                        <p className="text-sm">
                                            <span className="font-medium">Email:</span> {hteToUnarchive.email}
                                        </p>
                                    </div>
                                )}
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter className="gap-3">
                            <Button 
                                variant="outline"
                                onClick={cancelUnarchive}
                                className="w-full sm:w-auto"
                            >
                                Cancel
                            </Button>
                            <Button 
                                variant="default"
                                onClick={confirmUnarchive}
                                className="w-full sm:w-auto bg-green-600 hover:bg-green-700"
                            >
                                Yes
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
