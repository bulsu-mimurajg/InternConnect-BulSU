import { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { CalendarIcon, PlusIcon, EditIcon, TrashIcon } from 'lucide-react';
import { ClockIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/admin-dashboard',
    },
    {
        title: 'Events',
        href: '/admin/events',
    },
];

interface Deadline {
    id: number;
    title: string;
    category: string;
    category_display: string;
    start_date: string;
    end_date: string;
    status: 'active' | 'expired';
    is_active: boolean;
    is_expired: boolean;
    created_at: string;
    updated_at: string;
}

interface CategoryOption {
    value: string;
    label: string;
}

interface EventsPageProps {
    activeDeadlines: Deadline[];
    expiredDeadlines: Deadline[];
    categoryOptions: CategoryOption[];
}

export default function EventsPage({ activeDeadlines, expiredDeadlines, categoryOptions }: EventsPageProps) {
    const [showForm, setShowForm] = useState(false);
    const [editingDeadline, setEditingDeadline] = useState<Deadline | null>(null);
    const [showArchived, setShowArchived] = useState(false);
    const [showExtendDialog, setShowExtendDialog] = useState(false);
    const [extendingDeadline, setExtendingDeadline] = useState<Deadline | null>(null);
    const { flash } = usePage().props as any;

    const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
        title: '',
        category: '',
        start_date: '',
        end_date: '',
    });

    const { patch, processing: extending, errors: extendErrors, reset: resetExtend } = useForm({});

    // Get current deadlines based on filter
    const currentDeadlines = showArchived ? expiredDeadlines : activeDeadlines;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (editingDeadline) {
            put(`/admin/deadlines/${editingDeadline.id}`, {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                    setEditingDeadline(null);
                },
                onError: (errors) => {
                    console.error('Update errors:', errors);
                },
            });
        } else {
            post('/admin/deadlines', {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                },
                onError: (errors) => {
                    console.error('Creation errors:', errors);
                },
            });
        }
    };

    const handleEdit = (deadline: Deadline) => {
        setEditingDeadline(deadline);
        setData({
            title: deadline.title,
            category: deadline.category,
            start_date: deadline.start_date,
            end_date: deadline.end_date,
        });
        setShowForm(true);
    };

    const handleExtend = (deadline: Deadline) => {
        setExtendingDeadline(deadline);
        setShowExtendDialog(true);
    };

    const handleExtendSubmit = () => {
        if (extendingDeadline) {
            patch(`/admin/deadlines/${extendingDeadline.id}/extend`, {
                onSuccess: () => {
                    resetExtend();
                    setShowExtendDialog(false);
                    setExtendingDeadline(null);
                },
                onError: (errors) => {
                    console.error('Extension errors:', errors);
                },
            });
        }
    };

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this deadline?')) {
            destroy(`/admin/deadlines/${id}`);
        }
    };

    const handleCancel = () => {
        reset();
        setShowForm(false);
        setEditingDeadline(null);
    };

    const handleExtendCancel = () => {
        resetExtend();
        setShowExtendDialog(false);
        setExtendingDeadline(null);
    };

    const getStatusBadge = (deadline: Deadline) => {
        if (deadline.is_expired) {
            return (
                <Badge variant="secondary" className="bg-red-100 text-red-800">
                    Expired
                </Badge>
            );
        } else if (deadline.is_active) {
            return (
                <Badge variant="default" className="bg-green-100 text-green-800">
                    Active
                </Badge>
            );
        } else {
            return (
                <Badge variant="outline" className="bg-gray-100 text-gray-800">
                    Inactive
                </Badge>
            );
        }
    };

    const formatDateTime = (dateTimeString: string) => {
        const date = new Date(dateTimeString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Events Management" />
            <div className="space-y-6">
                {/* Success Message */}
                {flash?.success && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {flash.success}
                    </div>
                )}
                
                {/* Error Message */}
                {flash?.error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {flash.error}
                    </div>
                )}

                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Events Management</h1>
                        <p className="text-muted-foreground">
                            Manage application deadlines and important dates
                        </p>
                    </div>
                    <div className="flex gap-2 flex-wrap">
                        <Button
                            variant="outline"
                            onClick={() => setShowArchived(!showArchived)}
                        >
                            {showArchived ? 'Show Active' : 'Show Expired'}
                        </Button>
                        <Button
                            onClick={() => setShowForm(true)}
                            className="flex items-center gap-2"
                        >
                            <PlusIcon className="h-4 w-4" />
                            Add Deadline
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => {
                                if (confirm('Process SIP endorsements automatically? This will endorse students based on their highest compatibility scores.')) {
                                    post('/admin/deadlines/process-sip');
                                }
                            }}
                            className="flex items-center gap-2"
                        >
                            <ClockIcon className="h-4 w-4" />
                            Process SIP Endorsements
                        </Button>
                        <Button
                            variant="secondary"
                            onClick={() => {
                                if (confirm('Process HTE placements automatically? This will place students based on their ranking and available slots.')) {
                                    post('/admin/deadlines/process-hte');
                                }
                            }}
                            className="flex items-center gap-2"
                        >
                            <ClockIcon className="h-4 w-4" />
                            Process HTE Placements
                        </Button>
                        <Button
                            variant="default"
                            onClick={() => {
                                if (confirm('Process all automatic deadlines? This will run both SIP endorsements and HTE placements.')) {
                                    post('/admin/deadlines/process-all');
                                }
                            }}
                            className="flex items-center gap-2"
                        >
                            <ClockIcon className="h-4 w-4" />
                            Process All Deadlines
                        </Button>
                    </div>
                </div>

                {/* Add/Edit Form */}
                {showForm && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CalendarIcon className="h-5 w-5" />
                                {editingDeadline ? 'Edit Deadline' : 'Add New Deadline'}
                            </CardTitle>
                            <CardDescription>
                                {editingDeadline 
                                    ? 'Update the deadline information below.' 
                                    : 'Enter the deadline information below.'
                                }
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="title">Title</Label>
                                    <Input
                                        id="title"
                                        type="text"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        placeholder="Enter deadline title"
                                        className={errors.title ? 'border-red-500' : ''}
                                    />
                                    {errors.title && (
                                        <p className="text-sm text-red-500">{errors.title}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="category">Category</Label>
                                    <Select value={data.category} onValueChange={(value) => setData('category', value)}>
                                        <SelectTrigger className={errors.category ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Select a category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categoryOptions.map((option) => (
                                                <SelectItem key={option.value} value={option.value}>
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category && (
                                        <p className="text-sm text-red-500">{errors.category}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="start_date">Start Date & Time</Label>
                                        <Input
                                            id="start_date"
                                            type="datetime-local"
                                            value={data.start_date}
                                            onChange={(e) => setData('start_date', e.target.value)}
                                            className={errors.start_date ? 'border-red-500' : ''}
                                        />
                                        {errors.start_date && (
                                            <p className="text-sm text-red-500">{errors.start_date}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="end_date">End Date & Time</Label>
                                        <Input
                                            id="end_date"
                                            type="datetime-local"
                                            value={data.end_date}
                                            onChange={(e) => setData('end_date', e.target.value)}
                                            className={errors.end_date ? 'border-red-500' : ''}
                                        />
                                        {errors.end_date && (
                                            <p className="text-sm text-red-500">{errors.end_date}</p>
                                        )}
                                    </div>
                                </div>

                                {/* Display general errors */}
                                {Object.keys(errors).length > 0 && (
                                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                        <p className="font-medium">Please fix the following errors:</p>
                                        <ul className="list-disc list-inside mt-2">
                                            {Object.entries(errors).map(([field, error]) => (
                                                <li key={field}>{field}: {error}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                                
                                <div className="flex gap-2">
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Saving...' : (editingDeadline ? 'Update' : 'Create')}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={handleCancel}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {/* Deadlines List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <CalendarIcon className="h-5 w-5" />
                            {showArchived ? 'Expired Deadlines' : 'Active Deadlines'}
                            <Badge variant="outline" className="ml-2">
                                {currentDeadlines.length}
                            </Badge>
                        </CardTitle>
                        <CardDescription>
                            {showArchived 
                                ? 'Manage expired application deadlines' 
                                : 'Manage active application deadlines'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {currentDeadlines.length === 0 ? (
                            <div className="text-center py-8">
                                <CalendarIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-medium mb-2">
                                    {showArchived ? 'No expired deadlines found' : 'No deadlines found'}
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    {showArchived 
                                        ? 'No deadlines have expired yet.'
                                        : 'Get started by creating your first deadline.'
                                    }
                                </p>
                                {!showArchived && (
                                    <Button onClick={() => setShowForm(true)}>
                                        <PlusIcon className="h-4 w-4 mr-2" />
                                        Add Deadline
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {currentDeadlines.map((deadline) => (
                                    <div
                                        key={deadline.id}
                                        className="flex items-start justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="font-medium">{deadline.title}</h3>
                                                {getStatusBadge(deadline)}
                                            </div>
                                            <div className="mb-2">
                                                <Badge variant="outline" className="text-xs">
                                                    {deadline.category_display}
                                                </Badge>
                                            </div>
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-muted-foreground">
                                                <div>
                                                    <span className="font-medium">Start:</span> {formatDateTime(deadline.start_date)}
                                                </div>
                                                <div>
                                                    <span className="font-medium">End:</span> {formatDateTime(deadline.end_date)}
                                                </div>
                                            </div>
                                            <div className="text-xs text-muted-foreground mt-2">
                                                Created: {deadline.created_at} | Updated: {deadline.updated_at}
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            {!deadline.is_expired && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleExtend(deadline)}
                                                    className="text-blue-600 hover:text-blue-700"
                                                >
                                                    <ClockIcon className="h-4 w-4" />
                                                </Button>
                                            )}
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleEdit(deadline)}
                                            >
                                                <EditIcon className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => handleDelete(deadline.id)}
                                                className="text-red-600 hover:text-red-700"
                                            >
                                                <TrashIcon className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Extension Dialog */}
                <Dialog open={showExtendDialog} onOpenChange={setShowExtendDialog}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <ClockIcon className="h-5 w-5" />
                                Extend Deadline
                            </DialogTitle>
                            <DialogDescription>
                                Extend the deadline "{extendingDeadline?.title}" by 1 month.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4">
                            <div className="space-y-2">
                                <p className="text-sm text-muted-foreground">
                                    This will extend the deadline by 1 month from the current end date.
                                </p>
                                <p className="text-sm font-medium">
                                    Current end date: {extendingDeadline?.end_date ? new Date(extendingDeadline.end_date).toLocaleString() : 'N/A'}
                                </p>
                            </div>

                            {/* Display general errors */}
                            {Object.keys(extendErrors).length > 0 && (
                                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <p className="font-medium">Please fix the following errors:</p>
                                    <ul className="list-disc list-inside mt-2">
                                        {Object.entries(extendErrors).map(([field, error]) => (
                                            <li key={field}>{field}: {error}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            
                            <div className="flex gap-2">
                                <Button onClick={handleExtendSubmit} disabled={extending}>
                                    {extending ? 'Extending...' : 'Confirm Extension'}
                                </Button>
                                <Button type="button" variant="outline" onClick={handleExtendCancel}>
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}