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
import { Plus, MoreHorizontal, Edit, Archive, Eye, ArchiveRestore, Filter, ChevronDown, ChevronUp, ArrowUpDown, Search, BriefcaseBusinessIcon } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

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
    is_active: boolean;
    is_submit: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    htes: HTE[];
    showArchived?: boolean;
    filters?: {
        search?: string;
        status?: string;
        submission?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Management',
        href: '/hte',
    },
];

export default function HTEManagement({ htes, showArchived = false, filters = {} }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedHTE, setSelectedHTE] = useState<HTE | null>(null);
    const [showArchivedHTEs, setShowArchivedHTEs] = useState(showArchived);
    const [showFilters, setShowFilters] = useState(false);
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
            cperson_fname: hte.contact_person?.split(' ')[0] || '',
            cperson_lname: hte.contact_person?.split(' ').slice(1).join(' ') || '',
            cperson_position: hte.contact_position || '',
            cperson_contactnum: hte.contact_number || '',
        });
        setIsEditDialogOpen(true);
    };

    const handleArchive = (hte: HTE) => {
        if (confirm('Are you sure you want to archive this HTE account?')) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.hte.archive', hte.id), {}, {
                onSuccess: () => {
                    // Optionally show success message or refresh data
                },
                onError: (errors) => {
                    console.error('Archive error:', errors);
                }
            });
        }
    };

    const handleUnarchive = (hte: HTE) => {
        if (confirm('Are you sure you want to unarchive this HTE account?')) {
            // Use Inertia's router to make a PATCH request
            router.patch(route('admin.hte.unarchive', hte.id), {}, {
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

    const filteredHTEs = htes.filter(hte => {
        const matchesSearch = hte.username.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            hte.email.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                            (hte.company_name && hte.company_name.toLowerCase().includes(localFilters.search.toLowerCase())) ||
                            (hte.contact_person && hte.contact_person.toLowerCase().includes(localFilters.search.toLowerCase()));
        const matchesStatus = localFilters.status === 'all' || hte.status === localFilters.status;
        const matchesSubmission = localFilters.submission === 'all' || 
                                (localFilters.submission === 'submitted' && hte.is_submit) ||
                                (localFilters.submission === 'not_submitted' && !hte.is_submit);
        return matchesSearch && matchesStatus && matchesSubmission;
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
            <Head title="HTE Management" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
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
                                <Button>
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

                {/* Results Summary */}
                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted-foreground">
                        Showing {filteredHTEs.length} of {htes.length} HTE{htes.length !== 1 ? 's' : ''}
                    </p>
                </div>

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
                                        <Button onClick={() => setIsCreateDialogOpen(true)}>
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
                                        {filteredHTEs.map((hte) => (
                                            <tr key={hte.id} className={`border-b hover:bg-muted/50 transition-colors ${hte.status === 'archived' ? 'opacity-75' : ''}`}>
                                                <td className="py-3 px-4 font-medium">
                                                    {hte.username}
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">{hte.email}</td>
                                                <td className="py-3 px-4">{getStatusBadge(hte.status)}</td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {hte.company_name || 'Not provided'}
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {hte.contact_person || 'Not provided'}
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
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem onClick={() => handleEdit(hte)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </DropdownMenuItem>
                                                            {hte.status === 'archived' ? (
                                                                <DropdownMenuItem 
                                                                    onClick={() => handleUnarchive(hte)}
                                                                    className="text-green-600 focus:text-green-600"
                                                                >
                                                                    <ArchiveRestore className="mr-2 h-4 w-4" />
                                                                    Unarchive
                                                                </DropdownMenuItem>
                                                            ) : (
                                                                <DropdownMenuItem 
                                                                    onClick={() => handleArchive(hte)}
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
            </div>
        </AppLayout>
    );
}
