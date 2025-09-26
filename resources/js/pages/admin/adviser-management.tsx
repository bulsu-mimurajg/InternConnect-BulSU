import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Plus, MoreHorizontal, Edit, Archive, Eye, ArchiveRestore, Filter, ArrowUpDown, Search, GavelIcon } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

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
        status?: string;
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
    const [localFilters, setLocalFilters] = useState({
        search: filters.search || '',
        status: filters.status || 'all',
        section: filters.section || 'all',
    });

    const createForm = useForm({
        email: '',
        username: '',
        password: '',
        password_confirmation: '',
        adviser_fname: '',
        adviser_lname: '',
        section_ids: [] as number[],
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

        createForm.post(route('admin.adviser.store'), {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
            onError: (errors) => {
                // Handle validation errors
                console.error('Adviser creation failed:', errors);
            },
        });
    };

    const handleEditSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (selectedAdviser) {
            editForm.put(route('admin.adviser.update', selectedAdviser.id), {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setSelectedAdviser(null);
                    editForm.reset();
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
                onError: (errors) => {
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
                onError: (errors) => {
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
        const form = formType === 'create' ? createForm : editForm;
        const currentSectionIds = form.data.section_ids;

        if (currentSectionIds.includes(sectionId)) {
            // Remove section
            form.setData('section_ids', currentSectionIds.filter(id => id !== sectionId));
        } else {
            // Add section
            form.setData('section_ids', [...currentSectionIds, sectionId]);
        }
    };

    const handleFilterChange = (filterType: 'search' | 'status' | 'section', value: string) => {
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
        setLocalFilters({ search: '', status: 'all', section: 'all' });
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
        const matchesStatus = localFilters.status === 'all' || adviser.status === localFilters.status;
        const matchesSection = localFilters.section === 'all' || 
                              adviser.sections.some(s => s.section_name === localFilters.section);
        return matchesSearch && matchesStatus && matchesSection;
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
                                                <Button type="submit" disabled={createForm.processing}>
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
                                Filter advisers by status, section, or search by username, email, or name
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
                                            placeholder="Search advisers..."
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

                {/* Results Summary */}
                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted-foreground">
                        Showing {filteredAdvisers.length} of {advisers.length} adviser{advisers.length !== 1 ? 's' : ''}
                    </p>
                </div>

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
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Username</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Name</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Sections</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filteredAdvisers.map((adviser) => (
                                            <tr key={adviser.id} className={`border-b hover:bg-muted/50 transition-colors ${adviser.status === 'archived' ? 'opacity-75' : ''}`}>
                                                <td className="py-3 px-4 font-medium">
                                                    {adviser.username}
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{adviser.email}</td>
                                                <td className="py-3 px-4">{adviser.full_name}</td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{adviser.section_names}</td>
                                                <td className="py-3 px-4">{getStatusBadge(adviser.status)}</td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {new Date(adviser.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem onClick={() => handleEdit(adviser)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </DropdownMenuItem>
                                                            {adviser.status === 'archived' ? (
                                                                <DropdownMenuItem
                                                                    onClick={() => handleUnarchive(adviser)}
                                                                    className="text-green-600 focus:text-green-600"
                                                                >
                                                                    <ArchiveRestore className="mr-2 h-4 w-4" />
                                                                    Unarchive
                                                                </DropdownMenuItem>
                                                            ) : (
                                                                <DropdownMenuItem
                                                                    onClick={() => handleArchive(adviser)}
                                                                    className="text-destructive focus:text-destructive"
                                                                >
                                                                    <Archive className="mr-2 h-4 w-4" />
                                                                    Archive
                                                                </DropdownMenuItem>
                                                            )}
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
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
                                <Button type="submit" disabled={editForm.processing}>
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
