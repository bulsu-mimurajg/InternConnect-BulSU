import { useState, useMemo } from 'react';
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Plus, Edit, Archive, Eye, ArchiveRestore, Filter, ArrowUpDown, Search, GavelIcon } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useOTP } from '@/lib/otp-utils';
import { OTPGenerator } from '@/components/ui/otp-generator';
import { type AdviserFormErrors, type EmailValidationState } from '@/types/form-errors';

interface Adviser {
    id: number;
    user_id: number;
    username: string;
    email: string;
    status: string;
    adviser_fname: string;
    adviser_lname: string;
    full_name: string;
    sections: {
        section_id: number;
        section_name: string;
    }[];
    section_names: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    advisers: Adviser[];
    sections: Section[];
    showArchived?: boolean;
    filters?: {
        search?: string;
        section?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Adviser Management',
        href: '/adviser',
    },
];

export default function AdviserManagement({ advisers, sections, showArchived = false, filters = {} }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedAdviser, setSelectedAdviser] = useState<Adviser | null>(null);
    const [showArchivedAdvisers, setShowArchivedAdvisers] = useState(showArchived);
    const [showFilters, setShowFilters] = useState(false);
    const [emailValidation, setEmailValidation] = useState<EmailValidationState>({
        create: { isValid: true, message: '' },
        edit: { isValid: true, message: '' }
    });
    const { otp, showOTP, generateNewOTP, copyOTP, resetOTP } = useOTP();
    const [localFilters, setLocalFilters] = useState({
        search: filters.search || '',
        section: filters.section || 'all',
    });

    // Handle OTP generation
    const handleGenerateOTP = () => {
        const newOTP = generateNewOTP();
        createForm.setData('password', newOTP);
        createForm.setData('password_confirmation', newOTP);
        return newOTP;
    };

    // Copy OTP to clipboard
    const handleCopyOTP = async () => {
        try {
            await copyOTP();
            // You could add a toast notification here
        } catch (err) {
            console.error('Failed to copy OTP:', err);
        }
    };
    const validateEmail = (email: string): { isValid: boolean; message: string } => {
        if (!email.trim()) {
            return { isValid: false, message: 'Email is required' };
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return { isValid: false, message: 'Please enter a valid email address' };
        }
        
        return { isValid: true, message: '' };
    };

    // Handle email change with validation
    const handleEmailChange = (email: string, formType: 'create' | 'edit') => {
        const validation = validateEmail(email);
        setEmailValidation(prev => ({
            ...prev,
            [formType]: validation
        }));
        
        if (formType === 'create') {
            createForm.setData('email', email);
        } else {
            editForm.setData('email', email);
        }
    };

    const createForm = useForm({
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        adviser_fname: '',
        adviser_lname: '',
        section_ids: [] as number[],
        use_otp: true, // Flag to indicate OTP usage
    });

    const editForm = useForm({
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        adviser_fname: '',
        adviser_lname: '',
        section_ids: [] as number[],
    });

    const handleCreateSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validate email before submission
        const emailValidation = validateEmail(createForm.data.email);
        if (!emailValidation.isValid) {
            setEmailValidation(prev => ({
                ...prev,
                create: emailValidation
            }));
            return;
        }

        // Ensure OTP is generated and set in form data
        const currentOTP = otp || handleGenerateOTP();
        
        // Update form data and submit
        createForm.setData('password', currentOTP);
        createForm.setData('password_confirmation', currentOTP);
        
        createForm.post(route('admin.adviser.store'), {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                createForm.reset();
                setEmailValidation(prev => ({
                    ...prev,
                    create: { isValid: true, message: '' }
                }));
                resetOTP();
            },
            onError: (errors: AdviserFormErrors) => {
                // Handle validation errors
                console.error('Adviser creation failed:', errors);
            },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Validate email before submission
        const emailValidation = validateEmail(editForm.data.email);
        if (!emailValidation.isValid) {
            setEmailValidation(prev => ({
                ...prev,
                edit: emailValidation
            }));
            return;
        }
        
        if (selectedAdviser) {
            editForm.put(route('admin.adviser.update', selectedAdviser.id), {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setSelectedAdviser(null);
                    editForm.reset();
                    setEmailValidation(prev => ({
                        ...prev,
                        edit: { isValid: true, message: '' }
                    }));
                },
            });
        }
    };

    const handleEdit = (adviser: Adviser) => {
        setSelectedAdviser(adviser);
        editForm.setData({
            email: adviser.email,
            username: adviser.username,
            password: '',
            password_confirmation: '',
            adviser_fname: adviser.adviser_fname,
            adviser_lname: adviser.adviser_lname,
            section_ids: adviser.sections.map(s => s.section_id),
        });
        setIsEditDialogOpen(true);
    };

    const handleArchive = (adviser: Adviser) => {
        if (confirm('Are you sure you want to archive this adviser account?')) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.adviser.archive', adviser.id), {}, {
                onSuccess: () => {
                    // Optionally show success message or refresh data
                },
                onError: (errors: Record<string, string>) => {
                    console.error('Archive error:', errors);
                }
            });
        }
    };

    const handleUnarchive = (adviser: Adviser) => {
        if (confirm('Are you sure you want to unarchive this adviser account?')) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.adviser.unarchive', adviser.id), {}, {
                onSuccess: () => {
                    // Optionally show success message or refresh data
                },
                onError: (errors: Record<string, string>) => {
                    console.error('Unarchive error:', errors);
                }
            });
        }
    };

    const toggleArchivedView = () => {
        setShowArchivedAdvisers(!showArchivedAdvisers);
        // Navigate to the appropriate view using Inertia router
        const routeName = showArchivedAdvisers ? 'admin.adviser' : 'admin.adviser.archived';
        router.visit(route(routeName));
    };

    const handleSectionToggle = (sectionId: number, formType: 'create' | 'edit') => {
        const currentSectionIds = formType === 'create' ? createForm.data.section_ids : editForm.data.section_ids;

        if (currentSectionIds.includes(sectionId)) {
            // Remove section
            const newSectionIds = currentSectionIds.filter(id => id !== sectionId);
            if (formType === 'create') {
                createForm.setData('section_ids', newSectionIds);
            } else {
                editForm.setData('section_ids', newSectionIds);
            }
        } else {
            // Add section
            const newSectionIds = [...currentSectionIds, sectionId];
            if (formType === 'create') {
                createForm.setData('section_ids', newSectionIds);
            } else {
                editForm.setData('section_ids', newSectionIds);
            }
        }
    };

    const handleFilterChange = (filterType: 'search' | 'section', value: string) => {
        const newFilters = { ...localFilters, [filterType]: value };
        setLocalFilters(newFilters);

        // Apply filters immediately
        const params = new URLSearchParams();
        if (newFilters.search) {
            params.append('search', newFilters.search);
        }
        if (newFilters.section && newFilters.section !== 'all') {
            params.append('section', newFilters.section);
        }

        const routeName = showArchivedAdvisers ? 'admin.adviser.archived' : 'admin.adviser';
        router.get(route(routeName), params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ search: '', section: 'all' });
        const routeName = showArchivedAdvisers ? 'admin.adviser.archived' : 'admin.adviser';
        router.get(route(routeName), {}, {
            preserveState: true,
            replace: true
        });
    };

    const filteredAdvisers = advisers.filter(adviser => {
        const matchesSearch = adviser.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            adviser.email.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            adviser.full_name.toLowerCase().includes(localFilters.search.toLowerCase());
        const matchesSection = localFilters.section === 'all' || 
                              adviser.sections.some(s => s.section_name === localFilters.section);
        return matchesSearch && matchesSection;
    });

    // Create a stable reset trigger for pagination
    const resetTrigger = useMemo(() => 
        `${localFilters.search}-${localFilters.section}`,
        [localFilters.search, localFilters.section]
    );

    // Pagination hook with auto-reset on filter changes
    const adviserPagination = usePagination({
        data: filteredAdvisers,
        itemsPerPage: 10,
        resetTrigger: resetTrigger,
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Adviser Management" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Header Section */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div className="space-y-1">
                        <h1 className="text-2xl font-bold tracking-tight">Adviser Management</h1>
                        <p className="text-muted-foreground">
                            Manage adviser accounts and their assigned sections
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
                            {showArchivedAdvisers ? (
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
                                <Button>
                                    <Plus className="mr-2 h-4 w-4" />
                                    Add Adviser
                                </Button>
                            </DialogTrigger>
                                    <DialogContent className="sm:max-w-[425px]">
                                        <DialogHeader>
                                            <DialogTitle>Create New Adviser Account</DialogTitle>
                                            <DialogDescription>
                                                Create a new adviser account with login credentials, name, and section.
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
                                                            onChange={(e) => handleEmailChange(e.target.value, 'create')}
                                                            className={`${createForm.errors.email || !emailValidation.create.isValid ? 'border-red-500' : ''}`}
                                                            required
                                                        />
                                                        {(createForm.errors.email || !emailValidation.create.isValid) && (
                                                            <p className="text-red-500 text-xs mt-1">
                                                                {createForm.errors.email || emailValidation.create.message}
                                                            </p>
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
                                                <div className="grid grid-cols-4 items-start gap-4">
                                                    <Label className="text-right pt-2">
                                                        Password
                                                    </Label>
                                                    <div className="col-span-3">
                                                        <OTPGenerator
                                                            otp={otp}
                                                            showOTP={showOTP}
                                                            onGenerateOTP={handleGenerateOTP}
                                                            onCopyOTP={handleCopyOTP}
                                                            label=""
                                                            description="This password will be sent to the adviser's email. They must change it on first login."
                                                        />
                                                        {createForm.errors.password && (
                                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.password}</p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-4 items-center gap-4">
                                                    <Label htmlFor="adviser_fname" className="text-right">
                                                        First Name
                                                    </Label>
                                                    <div className="col-span-3">
                                                        <Input
                                                            id="adviser_fname"
                                                            value={createForm.data.adviser_fname}
                                                            onChange={(e) => createForm.setData('adviser_fname', e.target.value)}
                                                            className={createForm.errors.adviser_fname ? 'border-red-500' : ''}
                                                            required
                                                        />
                                                        {createForm.errors.adviser_fname && (
                                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.adviser_fname}</p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-4 items-center gap-4">
                                                    <Label htmlFor="adviser_lname" className="text-right">
                                                        Last Name
                                                    </Label>
                                                    <div className="col-span-3">
                                                        <Input
                                                            id="adviser_lname"
                                                            value={createForm.data.adviser_lname}
                                                            onChange={(e) => createForm.setData('adviser_lname', e.target.value)}
                                                            className={createForm.errors.adviser_lname ? 'border-red-500' : ''}
                                                            required
                                                        />
                                                        {createForm.errors.adviser_lname && (
                                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.adviser_lname}</p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-4 items-start gap-4">
                                                    <Label className="text-right pt-2">
                                                        Sections
                                                    </Label>
                                                    <div className="col-span-3">
                                                        <div className="space-y-2 max-h-32 overflow-y-auto border rounded-md p-2">
                                                            {sections.map((section) => (
                                                                <div key={section.section_id} className="flex items-center space-x-2">
                                                                    <Checkbox
                                                                        id={`create-section-${section.section_id}`}
                                                                        checked={createForm.data.section_ids.includes(section.section_id)}
                                                                        onCheckedChange={() => handleSectionToggle(section.section_id, 'create')}
                                                                    />
                                                                    <Label
                                                                        htmlFor={`create-section-${section.section_id}`}
                                                                        className="text-sm font-normal cursor-pointer"
                                                                    >
                                                                        {section.section_name}
                                                                    </Label>
                                                                </div>
                                                            ))}
                                                        </div>
                                                        {createForm.errors.section_ids && (
                                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.section_ids}</p>
                                                        )}
                                                        {createForm.data.section_ids.length === 0 && (
                                                            <p className="text-amber-600 text-xs mt-1">Please select at least one section</p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <DialogFooter>
                                                <Button type="submit" disabled={createForm.processing || !emailValidation.create.isValid || !otp}>
                                                    {createForm.processing ? 'Creating...' : 'Create Adviser'}
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
                                Filter advisers by section or search by username, email, or name
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {/* Search Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="search-filter">Search</Label>
                                    <div className="relative">
                                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                                        <Input
                                            id="search-filter"
                                            placeholder="Search advisers..."
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
                                            {sections.map((section) => (
                                                <SelectItem key={section.section_id} value={section.section_name}>
                                                    {section.section_name}
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
                                        <ArrowUpDown className="h-4 w-4 mr-2" />
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Advisers Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <GavelIcon className="h-5 w-5"/>
                            Advisers
                        </CardTitle>
                        <CardDescription>
                            {showArchivedAdvisers 
                                ? 'Archived adviser accounts' 
                                : 'Active adviser accounts and their assigned sections'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredAdvisers.length === 0 ? (
                            <div className="py-12 text-center">
                                <div className="flex flex-col items-center space-y-4">
                                    <Archive className="h-12 w-12 text-muted-foreground" />
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-medium">
                                            {showArchivedAdvisers ? 'No archived advisers found' : 'No advisers found'}
                                        </h3>
                                        <p className="text-muted-foreground">
                                            {showArchivedAdvisers 
                                                ? 'No adviser accounts have been archived yet.' 
                                                : 'Get started by creating your first adviser account.'
                                            }
                                        </p>
                                    </div>
                                    {!showArchivedAdvisers && (
                                        <Button onClick={() => setIsCreateDialogOpen(true)}>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Adviser
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
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Name</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Sections</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {adviserPagination.paginatedData.map((adviser, index) => (
                                            <tr key={adviser.id} className={`border-b hover:bg-muted/50 transition-colors ${adviser.status === 'archived' ? 'opacity-75' : ''}`}>
                                                <td className="text-center py-3 px-4 font-mono text-sm text-muted-foreground">
                                                    {getRowNumber(adviserPagination.currentPage, 10, index)}
                                                </td>
                                                <td className="py-3 px-4 font-medium">
                                                    {adviser.username}
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{adviser.email}</td>
                                                <td className="py-3 px-4">{adviser.full_name}</td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{adviser.section_names}</td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {new Date(adviser.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <div className="flex items-center gap-2 justify-end">
                                                        <Tooltip>
                                                            <TooltipTrigger asChild>
                                                                <Button
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => handleEdit(adviser)}
                                                                    className="h-8 w-8 p-0"
                                                                >
                                                                    <Edit className="h-4 w-4" />
                                                                </Button>
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                <p>Edit Adviser Account</p>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                        {adviser.status === 'archived' ? (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={() => handleUnarchive(adviser)}
                                                                        className="h-8 w-8 p-0 text-green-600 hover:text-green-700 hover:bg-green-100"
                                                                    >
                                                                        <ArchiveRestore className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>Restore Adviser Account</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        ) : (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={() => handleArchive(adviser)}
                                                                        className="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                                                                    >
                                                                        <Archive className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>Archive Adviser Account</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        
                        {/* Pagination */}
                        {filteredAdvisers.length > 0 && (
                            <Pagination
                                currentPage={adviserPagination.currentPage}
                                totalPages={adviserPagination.totalPages}
                                onPageChange={adviserPagination.handlePageChange}
                                showSummary={true}
                                totalItems={filteredAdvisers.length}
                                itemsPerPage={10}
                            />
                        )}
                    </CardContent>
                </Card>


                {/* Edit Dialog */}
                <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                    <DialogContent className="sm:max-w-[600px] max-h-[80vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle>Edit Adviser Account</DialogTitle>
                            <DialogDescription>
                                Update adviser account information.
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
                                        onChange={(e) => handleEmailChange(e.target.value, 'edit')}
                                        className={`col-span-3 ${editForm.errors.email || !emailValidation.edit.isValid ? 'border-red-500' : ''}`}
                                        required
                                    />
                                    {(editForm.errors.email || !emailValidation.edit.isValid) && (
                                        <p className="text-red-500 text-xs mt-1 col-span-3 col-start-2">
                                            {editForm.errors.email || emailValidation.edit.message}
                                        </p>
                                    )}
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
                                    <Label htmlFor="edit-adviser-fname" className="text-right">
                                        First Name
                                    </Label>
                                    <Input
                                        id="edit-adviser-fname"
                                        value={editForm.data.adviser_fname}
                                        onChange={(e) => editForm.setData('adviser_fname', e.target.value)}
                                        className="col-span-3"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-center gap-4">
                                    <Label htmlFor="edit-adviser-lname" className="text-right">
                                        Last Name
                                    </Label>
                                    <Input
                                        id="edit-adviser-lname"
                                        value={editForm.data.adviser_lname}
                                        onChange={(e) => editForm.setData('adviser_lname', e.target.value)}
                                        className="col-span-3"
                                        required
                                    />
                                </div>
                                <div className="grid grid-cols-4 items-start gap-4">
                                    <Label className="text-right pt-2">
                                        Sections
                                    </Label>
                                    <div className="col-span-3">
                                        <div className="space-y-2 max-h-32 overflow-y-auto border rounded-md p-2">
                                            {sections.map((section) => (
                                                <div key={section.section_id} className="flex items-center space-x-2">
                                                    <Checkbox
                                                        id={`edit-section-${section.section_id}`}
                                                        checked={editForm.data.section_ids.includes(section.section_id)}
                                                        onCheckedChange={() => handleSectionToggle(section.section_id, 'edit')}
                                                    />
                                                    <Label
                                                        htmlFor={`edit-section-${section.section_id}`}
                                                        className="text-sm font-normal cursor-pointer"
                                                    >
                                                        {section.section_name}
                                                    </Label>
                                                </div>
                                            ))}
                                        </div>
                                        {editForm.errors.section_ids && (
                                            <p className="text-red-500 text-xs mt-1">{editForm.errors.section_ids}</p>
                                        )}
                                        {editForm.data.section_ids.length === 0 && (
                                            <p className="text-amber-600 text-xs mt-1">Please select at least one section</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" disabled={editForm.processing || !emailValidation.edit.isValid}>
                                    {editForm.processing ? 'Updating...' : 'Update Adviser'}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
