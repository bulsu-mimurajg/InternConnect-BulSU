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
import { Checkbox } from '@/components/ui/checkbox';
import { Plus, MoreHorizontal, Edit, Archive, Eye, ArchiveRestore } from 'lucide-react';
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
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Adviser Management',
        href: '/adviser',
    },
];

export default function AdviserManagement({ advisers, sections, showArchived = false }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedAdviser, setSelectedAdviser] = useState<Adviser | null>(null);
    const [showDebug, setShowDebug] = useState(false);
    const [showArchivedAdvisers, setShowArchivedAdvisers] = useState(showArchived);

    // Debug: Log Adviser data received from backend
    console.log('Adviser Management - Received Advisers:', advisers);
    console.log('Adviser Management - Total Advisers:', advisers.length);
    console.log('Adviser Management - Sections:', sections);

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

        // Debug: Log form data before submission
        console.log('Adviser Creation - Form Data:', createForm.data);
        console.log('Adviser Creation - CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

        createForm.post(route('admin.adviser.store'), {
            onSuccess: () => {
                console.log('Adviser Creation - Success!');
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
            onError: (errors) => {
                console.error('Adviser Creation - Errors:', errors);
                console.error('Adviser Creation - Error Details:', JSON.stringify(errors, null, 2));

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
                if (errors.adviser_fname) {
                    console.error('First Name Error:', errors.adviser_fname);
                }
                if (errors.adviser_lname) {
                    console.error('Last Name Error:', errors.adviser_lname);
                }
                if (errors.section_id) {
                    console.error('Section Error:', errors.section_id);
                }
                if (errors.error) {
                    console.error('General Error:', errors.error);
                }
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

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Adviser Management</CardTitle>
                                <CardDescription>
                                    Manage adviser accounts and their sections
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
                                    {showArchivedAdvisers ? (
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
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b border-gray-200">
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Username</th>
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Email</th>
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Name</th>
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Section</th>
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                        <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                        <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {advisers.map((adviser) => (
                                        <tr key={adviser.id} className={`border-b border-gray-100 hover:bg-gray-50 ${adviser.status === 'archived' ? 'bg-gray-100 opacity-75' : ''}`}>
                                            <td className="py-3 px-4 font-medium">
                                                {adviser.username}
                                            </td>
                                            <td className="py-3 px-4">{adviser.email}</td>
                                            <td className="py-3 px-4">{adviser.full_name}</td>
                                            <td className="py-3 px-4">{adviser.section_names}</td>
                                            <td className="py-3 px-4">{getStatusBadge(adviser.status)}</td>
                                            <td className="py-3 px-4">{adviser.created_at}</td>
                                            <td className="py-3 px-4 text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" className="h-8 w-8 p-0">
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
                                                                className="text-green-600"
                                                            >
                                                                <ArchiveRestore className="mr-2 h-4 w-4" />
                                                                Unarchive
                                                            </DropdownMenuItem>
                                                        ) : (
                                                            <DropdownMenuItem
                                                                onClick={() => handleArchive(adviser)}
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
                                Debug data for adviser management
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                <div>
                                    <h4 className="font-semibold">Adviser Data Summary:</h4>
                                    <p>Total Advisers: {advisers.length}</p>
                                    <p>Active Advisers: {advisers.filter(a => a.is_active).length}</p>
                                    <p>Verified Advisers: {advisers.filter(a => a.status === 'verified').length}</p>
                                </div>

                                <div>
                                    <h4 className="font-semibold">Sections Available:</h4>
                                    <div className="max-h-40 overflow-y-auto">
                                        {sections.map((section) => (
                                            <div key={section.section_id} className="text-sm border-b pb-1 mb-1">
                                                <p><strong>ID:</strong> {section.section_id} | <strong>Name:</strong> {section.section_name}</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div>
                                    <h4 className="font-semibold">Recent Advisers:</h4>
                                    <div className="max-h-40 overflow-y-auto">
                                        {advisers.slice(0, 5).map((adviser) => (
                                            <div key={adviser.id} className="text-sm border-b pb-1 mb-1">
                                                <p><strong>ID:</strong> {adviser.id} | <strong>Username:</strong> {adviser.username} | <strong>Email:</strong> {adviser.email}</p>
                                                <p><strong>Name:</strong> {adviser.full_name} | <strong>Sections:</strong> {adviser.section_names} | <strong>Status:</strong> {adviser.status}</p>
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
