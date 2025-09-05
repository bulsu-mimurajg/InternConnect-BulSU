import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
import { Plus, MoreHorizontal, Edit, Archive, Eye, ArchiveRestore } from 'lucide-react';
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
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Management',
        href: '/hte',
    },
];

export default function HTEManagement({ htes, showArchived = false }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedHTE, setSelectedHTE] = useState<HTE | null>(null);
    const [showDebug, setShowDebug] = useState(false);
    const [showArchivedHTEs, setShowArchivedHTEs] = useState(showArchived);

    // Debug: Log HTE data received from backend
    console.log('HTE Management - Received HTEs:', htes);
    console.log('HTE Management - Total HTEs:', htes.length);

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
        
        // Debug: Log form data before submission
        console.log('HTE Creation - Form Data:', createForm.data);
        console.log('HTE Creation - CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));
        
        createForm.post(route('admin.hte.store'), {
            onSuccess: () => {
                console.log('HTE Creation - Success!');
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
            onError: (errors) => {
                console.error('HTE Creation - Errors:', errors);
                console.error('HTE Creation - Error Details:', JSON.stringify(errors, null, 2));
                
                // Log specific error types
                if (errors.email) {
                    console.error('Email Error:', errors.email);
                }
                if (errors.username) {
                    console.error('Username Error:', errors.username);
                }
                if (errors.password) {
                    console.error('Password Error:', errors.password);
                }
                if (errors.password_confirmation) {
                    console.error('Password Confirmation Error:', errors.password_confirmation);
                }
                if (errors.error) {
                    console.error('General Error:', errors.error);
                }
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
            
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>HTE Management</CardTitle>
                                <CardDescription>
                                    Manage Host Training Establishment accounts
                                </CardDescription>
                                <div className="mt-2">
                                    <Button 
                                        variant="outline" 
                                        size="sm" 
                                        onClick={() => setShowDebug(!showDebug)}
                                    >
                                        {showDebug ? 'Hide' : 'Show'} Debug Info
                                    </Button>
                                </div>
                            </div>
                            <div className="flex gap-2">
                                <Button 
                                    variant="outline" 
                                    onClick={toggleArchivedView}
                                >
                                    {showArchivedHTEs ? (
                                        <>
                                            <Eye className="mr-2 h-4 w-4" />
                                            Show Active
                                        </>
                                    ) : (
                                        <>
                                            <Archive className="mr-2 h-4 w-4" />
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
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200">
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
                                    {htes.map((hte) => (
                                        <tr key={hte.id} className={`border-b border-gray-100 hover:bg-gray-50 ${hte.status === 'archived' ? 'bg-gray-100 opacity-75' : ''}`}>
                                            <td className="py-3 px-4 font-medium">
                                                {hte.username}
                                            </td>
                                            <td className="py-3 px-4">{hte.email}</td>
                                            <td className="py-3 px-4">{getStatusBadge(hte.status)}</td>
                                            <td className="py-3 px-4">
                                                {hte.company_name || 'Not provided'}
                                            </td>
                                            <td className="py-3 px-4">
                                                {hte.contact_person || 'Not provided'}
                                            </td>
                                            <td className="py-3 px-4">
                                                <Badge variant={hte.is_submit ? "default" : "outline"}>
                                                    {hte.is_submit ? 'Submitted' : 'Not Submitted'}
                                                </Badge>
                                            </td>
                                            <td className="py-3 px-4">{hte.created_at}</td>
                                            <td className="py-3 px-4 text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" className="h-8 w-8 p-0">
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
                                                                className="text-green-600"
                                                            >
                                                                <ArchiveRestore className="mr-2 h-4 w-4" />
                                                                Unarchive
                                                            </DropdownMenuItem>
                                                        ) : (
                                                            <DropdownMenuItem 
                                                                onClick={() => handleArchive(hte)}
                                                                className="text-red-600"
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
                    </CardContent>
                </Card>

                {/* Debug Panel */}
                {showDebug && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Debug Information</CardTitle>
                            <CardDescription>
                                Debug data for HTE management
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div>
                                    <h4 className="font-semibold">HTE Data Summary:</h4>
                                    <p>Total HTEs: {htes.length}</p>
                                    <p>Active HTEs: {htes.filter(h => h.is_active).length}</p>
                                    <p>Submitted Forms: {htes.filter(h => h.is_submit).length}</p>
                                </div>
                                
                                <div>
                                    <h4 className="font-semibold">Recent HTEs:</h4>
                                    <div className="max-h-40 overflow-y-auto">
                                        {htes.slice(0, 5).map((hte) => (
                                            <div key={hte.id} className="text-sm border-b pb-1 mb-1">
                                                <p><strong>ID:</strong> {hte.id} | <strong>Username:</strong> {hte.username} | <strong>Email:</strong> {hte.email}</p>
                                                <p><strong>Status:</strong> {hte.status} | <strong>Active:</strong> {hte.is_active ? 'Yes' : 'No'} | <strong>Submitted:</strong> {hte.is_submit ? 'Yes' : 'No'}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div>
                                    <h4 className="font-semibold">Form Data (Create):</h4>
                                    <pre className="text-xs bg-gray-100 p-2 rounded overflow-x-auto">
                                        {JSON.stringify(createForm.data, null, 2)}
                                    </pre>
                                </div>

                                <div>
                                    <h4 className="font-semibold">Form Errors (Create):</h4>
                                    <pre className="text-xs bg-red-100 p-2 rounded overflow-x-auto">
                                        {JSON.stringify(createForm.errors, null, 2)}
                                    </pre>
                                </div>

                                <div>
                                    <h4 className="font-semibold">Form Data (Edit):</h4>
                                    <pre className="text-xs bg-gray-100 p-2 rounded overflow-x-auto">
                                        {JSON.stringify(editForm.data, null, 2)}
                                    </pre>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

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
