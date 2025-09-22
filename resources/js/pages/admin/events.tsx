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
                        
                        {/* Automation Section */}
                        <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <Button
                                    variant="secondary"
                                    onClick={() => {
                                        if (confirm('Process SIP endorsements automatically? This will endorse students based on their highest compatibility scores.')) {
                                            post('/admin/deadlines/process-sip');
                                        }
                                    }}
                                    className="flex items-center gap-2 h-9 text-sm"
                                >
                                    <ClockIcon className="h-4 w-4" />
                                    <span className="hidden sm:inline">Process SIP Endorsements</span>
                                    <span className="sm:hidden">Process SIP</span>
                                </Button>
                                <Button
                                    variant="secondary"
                                    onClick={() => {
                                        if (confirm('Process HTE placements automatically? This will place students based on their ranking and available slots.')) {
                                            post('/admin/deadlines/process-hte');
                                        }
                                    }}
                                    className="flex items-center gap-2 h-9 text-sm"
                                >
                                    <ClockIcon className="h-4 w-4" />
                                    <span className="hidden sm:inline">Process HTE Placements</span>
                                    <span className="sm:hidden">Process HTE</span>
                                </Button>
                                <Button
                                    variant="default"
                                    onClick={() => {
                                        if (confirm('Process all automatic deadlines? This will run both SIP endorsements and HTE placements.')) {
                                            post('/admin/deadlines/process-all');
                                        }
                                    }}
                                    className="flex items-center gap-2 h-9 text-sm"
                                >
                                    <ClockIcon className="h-4 w-4" />
                                    <span className="hidden sm:inline">Process All Deadlines</span>
                                    <span className="sm:hidden">Process All</span>
                                </Button>
                        </div>
                    </div>
                </div>

                {/* Add/Edit Form */}
                {showForm && (
                    <Card className="border-border shadow-sm">
                        <CardHeader className="pb-4 space-y-2">
                            <CardTitle className="flex items-center gap-2 text-xl font-semibold text-foreground">
                                <CalendarIcon className="h-5 w-5" />
                                {editingDeadline ? 'Edit Deadline' : 'Add New Deadline'}
                            </CardTitle>
                            <CardDescription className="text-sm text-muted-foreground">
                                {editingDeadline 
                                    ? 'Update the deadline information below.' 
                                    : 'Enter the deadline information below.'
                                }
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
                                            <Input
                                                id="start_date"
                                                type="datetime-local"
                                                value={data.start_date}
                                                onChange={(e) => setData('start_date', e.target.value)}
                                                className={`h-10 ${errors.start_date ? 'border-destructive focus-visible:ring-destructive' : ''}`}
                                            />
                                            {errors.start_date && (
                                                <p className="text-sm text-destructive">{errors.start_date}</p>
                                            )}
                                        </div>

                                        <div className="space-y-3">
                                            <Label htmlFor="end_date" className="text-sm font-medium text-foreground">
                                                End Date & Time
                                            </Label>
                                            <Input
                                                id="end_date"
                                                type="datetime-local"
                                                value={data.end_date}
                                                onChange={(e) => setData('end_date', e.target.value)}
                                                className={`h-10 ${errors.end_date ? 'border-destructive focus-visible:ring-destructive' : ''}`}
                                            />
                                            {errors.end_date && (
                                                <p className="text-sm text-destructive">{errors.end_date}</p>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Display general errors */}
                                {Object.keys(errors).length > 0 && (
                                    <div className="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg">
                                        <p className="font-medium text-sm">Please fix the following errors:</p>
                                        <ul className="list-disc list-inside mt-2 text-sm space-y-1">
                                            {Object.entries(errors).map(([field, error]) => (
                                                <li key={field}>{field}: {String(error)}</li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                                
                                <div className="flex flex-col sm:flex-row gap-3 pt-4">
                                    <Button 
                                        type="submit" 
                                        disabled={processing}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        {processing ? 'Saving...' : (editingDeadline ? 'Update Deadline' : 'Create Deadline')}
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
                                                        onClick={() => handleDelete(deadline.id)}
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

                            {/* Display general errors */}
                            {Object.keys(extendErrors).length > 0 && (
                                <div className="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-lg">
                                    <p className="font-medium text-sm">Please fix the following errors:</p>
                                    <ul className="list-disc list-inside mt-2 text-sm space-y-1">
                                        {Object.entries(extendErrors).map(([field, error]) => (
                                            <li key={field}>{field}: {String(error)}</li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            
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
            </div>
        </AppLayout>
    );
}