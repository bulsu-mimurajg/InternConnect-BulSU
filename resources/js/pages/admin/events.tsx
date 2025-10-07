import { useState } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DateTimePicker } from '@/components/ui/date-time-picker';
import { CalendarIcon, PlusIcon, EditIcon, TrashIcon } from 'lucide-react';
import { ClockIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
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
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const [deletingDeadline, setDeletingDeadline] = useState<Deadline | null>(null);
    const [showEditDialog, setShowEditDialog] = useState(false);
    const { flash } = usePage().props as any;

    const { data, setData, post, put, delete: destroy, processing, errors, reset } = useForm({
        title: '',
        category: '',
        start_date: null as Date | null,
        end_date: null as Date | null,
    });

    const { patch, processing: extending, errors: extendErrors, reset: resetExtend } = useForm({});

    // Get current deadlines based on filter
    const currentDeadlines = showArchived ? expiredDeadlines : activeDeadlines;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Convert Date objects to local date strings for API (avoid timezone issues)
        const formatDateForAPI = (date: Date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        };

        const submitData = {
            ...data,
            start_date: data.start_date ? formatDateForAPI(data.start_date) : '',
            end_date: data.end_date ? formatDateForAPI(data.end_date) : '',
        };

        if (editingDeadline) {
            router.put(`/admin/deadlines/${editingDeadline.id}`, submitData, {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                    setShowEditDialog(false);
                    setEditingDeadline(null);
                },
                onError: (errors: any) => {
                    console.error('Update errors:', errors);
                },
            });
        } else {
            router.post('/admin/deadlines', submitData, {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                },
                onError: (errors: any) => {
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
            start_date: new Date(deadline.start_date),
            end_date: new Date(deadline.end_date),
        });
        setShowEditDialog(true);
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

    const handleDelete = (deadline: Deadline) => {
        setDeletingDeadline(deadline);
        setShowDeleteDialog(true);
    };

    const handleDeleteConfirm = () => {
        if (deletingDeadline) {
            destroy(`/admin/deadlines/${deletingDeadline.id}`, {
                onSuccess: () => {
                    setShowDeleteDialog(false);
                    setDeletingDeadline(null);
                },
                onError: (errors) => {
                    console.error('Delete errors:', errors);
                },
            });
        }
    };

    const handleDeleteCancel = () => {
        setShowDeleteDialog(false);
        setDeletingDeadline(null);
    };

    const handleCancel = () => {
        reset();
        setShowForm(false);
        setEditingDeadline(null);
    };

    const handleEditCancel = () => {
        reset();
        setShowEditDialog(false);
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
            <div className="space-y-6 p-4 md:p-6">
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
                <div className="space-y-4 md:space-y-6">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div className="space-y-2">
                            <h1 className="text-2xl md:text-3xl font-bold tracking-tight text-foreground">
                                Events Management
                            </h1>
                            <p className="text-sm md:text-base text-muted-foreground">
                                Manage application deadlines and important dates
                            </p>
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        {/* Primary Actions */}
                        <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                            <Button
                                onClick={() => setShowForm(true)}
                                className="flex items-center gap-2 h-9"
                            >
                                <PlusIcon className="h-4 w-4" />
                                Add Deadline
                            </Button>
                            <Button
                                variant="outline"
                                onClick={() => setShowArchived(!showArchived)}
                                className="flex items-center gap-2 h-9"
                            >
                                <CalendarIcon className="h-4 w-4" />
                                {showArchived ? 'Show Active' : 'Show Expired'}
                            </Button>
                        </div>
                    </div>
                </div>

                {/* 3-Tier Automatic Placement Info Card */}
                <Card className="border-l-4 border-l-blue-500 bg-blue-50 dark:bg-blue-900/20">
                    <CardContent className="p-4">
                        <div className="flex items-center gap-6">
                            <div className="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0">
                                <ClockIcon className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div className="space-y-3 flex-1">
                                <h3 className="font-semibold text-blue-800 dark:text-blue-200">
                                    3-Tier Automatic Internship Placement System
                                </h3>
                                <div className="text-sm text-blue-700 dark:text-blue-300 space-y-2">
                                    <div className="flex items-start gap-2">
                                        <span className="font-semibold min-w-[140px]">Tier 1 (T-30 min):</span>
                                        <span>Place already-endorsed students according to compatibility rankings and available slots</span>
                                    </div>
                                    <div className="flex items-start gap-2">
                                        <span className="font-semibold min-w-[140px]">Tier 2 (T-20 min):</span>
                                        <span>Auto-endorse and place students from matched page (students with valid matches but not yet endorsed) by compatibility</span>
                                    </div>
                                    <div className="flex items-start gap-2">
                                        <span className="font-semibold min-w-[140px]">Tier 3 (T-10 min):</span>
                                        <span>Emergency placement for students with no remaining fallback options and those rejected by HTE into any available internship slots</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Add Form */}
                {showForm && (
                    <Card className="border-border shadow-sm">
                        <CardHeader className="pb-4 space-y-2">
                            <CardTitle className="flex items-center gap-2 text-xl font-semibold text-foreground">
                                <CalendarIcon className="h-5 w-5" />
                                Add New Deadline
                            </CardTitle>
                            <CardDescription className="text-sm text-muted-foreground">
                                Enter the deadline information below.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 gap-6">
                                    <div className="space-y-3">
                                        <Label htmlFor="title" className="text-sm font-medium text-foreground">
                                            Title
                                        </Label>
                                        <Input
                                            id="title"
                                            type="text"
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder="Enter deadline title"
                                            className={`h-10 ${errors.title ? 'border-destructive focus-visible:ring-destructive' : ''}`}
                                        />
                                        {errors.title && (
                                            <p className="text-sm text-destructive">{errors.title}</p>
                                        )}
                                    </div>

                                    <div className="space-y-3">
                                        <Label htmlFor="category" className="text-sm font-medium text-foreground">
                                            Category
                                        </Label>
                                        <Select value={data.category} onValueChange={(value) => setData('category', value)}>
                                            <SelectTrigger className={`h-10 ${errors.category ? 'border-destructive focus-visible:ring-destructive' : ''}`}>
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
                                            <p className="text-sm text-destructive">{errors.category}</p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                        <div className="space-y-3">
                                            <Label htmlFor="start_date" className="text-sm font-medium text-foreground">
                                                Start Date & Time
                                            </Label>
                                            <DateTimePicker
                                                value={data.start_date}
                                                onChange={(date) => setData('start_date', date)}
                                                placeholder="Select start date and time"
                                                error={!!errors.start_date}
                                            />
                                            {errors.start_date && (
                                                <p className="text-sm text-destructive">{errors.start_date}</p>
                                            )}
                                        </div>

                                        <div className="space-y-3">
                                            <Label htmlFor="end_date" className="text-sm font-medium text-foreground">
                                                End Date & Time
                                            </Label>
                                            <DateTimePicker
                                                value={data.end_date}
                                                onChange={(date) => setData('end_date', date)}
                                                placeholder="Select end date and time"
                                                error={!!errors.end_date}
                                            />
                                            {errors.end_date && (
                                                <p className="text-sm text-destructive">{errors.end_date}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row gap-3 pt-4">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        {processing ? 'Saving...' : 'Create Deadline'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleCancel}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {/* Deadlines List */}
                <Card className="border-border shadow-sm">
                    <CardHeader className="pb-4 space-y-2">
                        <CardTitle className="flex items-center gap-2 text-xl font-semibold text-foreground">
                            <CalendarIcon className="h-5 w-5" />
                            {showArchived ? 'Expired Deadlines' : 'Active Deadlines'}
                            <Badge variant="secondary" className="ml-2 bg-muted text-muted-foreground">
                                {currentDeadlines.length}
                            </Badge>
                        </CardTitle>
                        <CardDescription className="text-sm text-muted-foreground">
                            {showArchived
                                ? 'Manage expired application deadlines'
                                : 'Manage active application deadlines'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {currentDeadlines.length === 0 ? (
                            <div className="text-center py-12 px-4">
                                <div className="flex flex-col items-center space-y-4">
                                    <div className="p-4 rounded-full bg-muted">
                                        <CalendarIcon className="h-8 w-8 text-muted-foreground" />
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-semibold text-foreground">
                                            {showArchived ? 'No expired deadlines found' : 'No deadlines found'}
                                        </h3>
                                        <p className="text-sm text-muted-foreground max-w-sm">
                                            {showArchived
                                                ? 'No deadlines have expired yet.'
                                                : 'Get started by creating your first deadline.'
                                            }
                                        </p>
                                    </div>
                                    {!showArchived && (
                                        <Button
                                            onClick={() => setShowForm(true)}
                                            className="h-9 px-6"
                                        >
                                            <PlusIcon className="h-4 w-4 mr-2" />
                                            Add Deadline
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {currentDeadlines.map((deadline) => (
                                    <Card
                                        key={deadline.id}
                                        className="border-border hover:shadow-md transition-all duration-200"
                                    >
                                        <CardContent className="p-4 md:p-6">
                                            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                                <div className="flex-1 space-y-3">
                                                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                                        <h3 className="font-semibold text-foreground text-base">
                                                            {deadline.title}
                                                        </h3>
                                                        <div className="flex items-center gap-2">
                                                            {getStatusBadge(deadline)}
                                                            <Badge variant="outline" className="text-xs">
                                                                {deadline.category_display}
                                                            </Badge>
                                                        </div>
                                                    </div>

                                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-medium text-muted-foreground">Start:</span>
                                                            <span className="text-foreground">{formatDateTime(deadline.start_date)}</span>
                                                        </div>
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-medium text-muted-foreground">End:</span>
                                                            <span className="text-foreground">{formatDateTime(deadline.end_date)}</span>
                                                        </div>
                                                    </div>

                                                    <div className="text-xs text-muted-foreground pt-2 border-t border-border">
                                                        <span>Created: {deadline.created_at}</span>
                                                        <span className="mx-2">•</span>
                                                        <span>Updated: {deadline.updated_at}</span>
                                                    </div>
                                                </div>

                                                <div className="flex flex-row md:flex-col gap-2 md:gap-3">
                                                    {!deadline.is_expired && (
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleExtend(deadline)}
                                                            className="flex items-center gap-2 h-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                                                        >
                                                            <ClockIcon className="h-4 w-4" />
                                                            <span className="hidden sm:inline">Extend</span>
                                                        </Button>
                                                    )}
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleEdit(deadline)}
                                                        className="flex items-center gap-2 h-8"
                                                    >
                                                        <EditIcon className="h-4 w-4" />
                                                        <span className="hidden sm:inline">Edit</span>
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleDelete(deadline)}
                                                        className="flex items-center gap-2 h-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                                    >
                                                        <TrashIcon className="h-4 w-4" />
                                                        <span className="hidden sm:inline">Delete</span>
                                                    </Button>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Extension Dialog */}
                <Dialog open={showExtendDialog} onOpenChange={setShowExtendDialog}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader className="space-y-3">
                            <DialogTitle className="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <ClockIcon className="h-5 w-5" />
                                Extend Deadline
                            </DialogTitle>
                            <DialogDescription className="text-sm text-muted-foreground">
                                Extend the deadline "{extendingDeadline?.title}" by 1 month.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-6">
                            <div className="space-y-3">
                                <div className="p-4 bg-muted/50 rounded-lg">
                                    <p className="text-sm text-muted-foreground mb-2">
                                        This will extend the deadline by 1 month from the current end date.
                                    </p>
                                    <p className="text-sm font-medium text-foreground">
                                        Current end date: {extendingDeadline?.end_date ? new Date(extendingDeadline.end_date).toLocaleString() : 'N/A'}
                                    </p>
                                </div>
                            </div>

                            <div className="flex flex-col sm:flex-row gap-3">
                                <Button
                                    onClick={handleExtendSubmit}
                                    disabled={extending}
                                    className="flex-1 sm:flex-none h-9"
                                >
                                    {extending ? 'Extending...' : 'Confirm Extension'}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handleExtendCancel}
                                    className="flex-1 sm:flex-none h-9"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>

                {/* Edit Dialog */}
                <Dialog open={showEditDialog} onOpenChange={setShowEditDialog}>
                    <DialogContent className="sm:max-w-2xl">
                        <DialogHeader className="space-y-3">
                            <DialogTitle className="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <EditIcon className="h-5 w-5" />
                                Edit Deadline
                            </DialogTitle>
                            <DialogDescription className="text-sm text-muted-foreground">
                                Update the deadline information below.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-6">
                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 gap-6">
                                    <div className="space-y-3">
                                        <Label htmlFor="edit_title" className="text-sm font-medium text-foreground">
                                            Title
                                        </Label>
                                        <Input
                                            id="edit_title"
                                            type="text"
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            placeholder="Enter deadline title"
                                            className={`h-10 ${errors.title ? 'border-destructive focus-visible:ring-destructive' : ''}`}
                                        />
                                        {errors.title && (
                                            <p className="text-sm text-destructive">{errors.title}</p>
                                        )}
                                    </div>

                                    {/* Category display - read-only */}
                                    <div className="space-y-3">
                                        <Label className="text-sm font-medium text-foreground">
                                            Category
                                        </Label>
                                        <div className="h-10 px-3 py-2 bg-muted text-muted-foreground rounded-md border border-input flex items-center">
                                            {editingDeadline?.category_display || 'Unknown Category'}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            Category cannot be changed when editing a deadline
                                        </p>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                                        <div className="space-y-3">
                                            <Label htmlFor="edit_start_date" className="text-sm font-medium text-foreground">
                                                Start Date & Time
                                            </Label>
                                            <DateTimePicker
                                                value={data.start_date}
                                                onChange={(date) => setData('start_date', date)}
                                                placeholder="Select start date and time"
                                                error={!!errors.start_date}
                                            />
                                            {errors.start_date && (
                                                <p className="text-sm text-destructive">{errors.start_date}</p>
                                            )}
                                        </div>

                                        <div className="space-y-3">
                                            <Label htmlFor="edit_end_date" className="text-sm font-medium text-foreground">
                                                End Date & Time
                                            </Label>
                                            <DateTimePicker
                                                value={data.end_date}
                                                onChange={(date) => setData('end_date', date)}
                                                placeholder="Select end date and time"
                                                error={!!errors.end_date}
                                            />
                                            {errors.end_date && (
                                                <p className="text-sm text-destructive">{errors.end_date}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex flex-col sm:flex-row gap-3">
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        {processing ? 'Updating...' : 'Update Deadline'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleEditCancel}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </DialogContent>
                </Dialog>

                {/* Delete Confirmation Dialog */}
                <Dialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader className="space-y-2">
                            <DialogTitle className="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <TrashIcon className="h-5 w-5 text-destructive" />
                                Delete Deadline
                            </DialogTitle>
                            <DialogDescription className="text-sm text-muted-foreground">
                                Delete "{deletingDeadline?.title}"? This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="flex flex-col sm:flex-row gap-3 pt-4">
                            <Button
                                variant="destructive"
                                onClick={handleDeleteConfirm}
                                disabled={processing}
                                className="flex-1 sm:flex-none h-9"
                            >
                                {processing ? 'Deleting...' : 'Delete'}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleDeleteCancel}
                                className="flex-1 sm:flex-none h-9"
                            >
                                Cancel
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
