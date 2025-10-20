import { useState, useEffect } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { DateTimePicker } from '@/components/ui/date-time-picker';
import { CalendarIcon, PlusIcon, EditIcon, CheckCircleIcon, UsersIcon } from 'lucide-react';
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
    status: 'active' | 'inactive' | 'expired';
    is_active: boolean;
    is_expired: boolean;
    is_inactive: boolean;
    created_at: string;
    updated_at: string;
}

interface CategoryOption {
    value: string;
    label: string;
}

interface EventsPageProps {
    allDeadlines: Deadline[];
    activeDeadlines: Deadline[];
    expiredDeadlines: Deadline[];
    categoryOptions: CategoryOption[];
    nextCategory?: {
        value: string;
        label: string;
    };
    sequenceInfo: {
        [key: string]: {
            can_create: boolean;
            missing_categories: string[];
            order: number;
        };
    };
    activeSeason?: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        status: string;
    } | null;
    seasons: {
        id: number;
        name: string;
        start_date: string;
        end_date: string;
        status: string;
        deadlines_count: number;
        students_count: number;
        active_students_count: number;
    }[];
}

export default function EventsPage({ allDeadlines, activeDeadlines, expiredDeadlines, categoryOptions, nextCategory, sequenceInfo, activeSeason, seasons }: EventsPageProps) {
    const [showAddDialog, setShowAddDialog] = useState(false);
    const [editingDeadline, setEditingDeadline] = useState<Deadline | null>(null);
    const [showArchived] = useState(false);
    const [showExtendDialog, setShowExtendDialog] = useState(false);
    const [extendingDeadline, setExtendingDeadline] = useState<Deadline | null>(null);
    const [showEditDialog, setShowEditDialog] = useState(false);
    const [manualErrors, setManualErrors] = useState<{ [key: string]: string }>({});
    const [currentSequenceInfo, setCurrentSequenceInfo] = useState(sequenceInfo);
    const [currentNextCategory, setCurrentNextCategory] = useState(nextCategory);
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

    const { data, setData, delete: destroy, processing, errors, reset } = useForm({
        title: '',
        category: '',
        start_date: null as Date | null,
        end_date: null as Date | null,
        season_id: seasons.length > 0 ? seasons[0].id : '',
    });

    const { patch, processing: extending, reset: resetExtend } = useForm({});

    // Get current deadlines based on filter - now using allDeadlines
    const currentDeadlines = showArchived ? expiredDeadlines : allDeadlines;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
    
        // Validation checks
        if (data.category && currentSequenceInfo[data.category] && !currentSequenceInfo[data.category].can_create) {
            const missingCategories = currentSequenceInfo[data.category].missing_categories.map(cat => getCategoryDisplayName(cat)).join(', ');
            alert(`Cannot create this deadline yet. Please create deadlines in chronological order. Missing: ${missingCategories}`);
            return;
        }
    
        if (errors.category || manualErrors.category) {
            alert('Please resolve the category error before submitting.');
            return;
        }
    
        // Format dates for Laravel (Asia/Manila timezone)
        const formatDate = (date: Date) => {
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
            start_date: data.start_date ? formatDate(data.start_date) : '',
            end_date: data.end_date ? formatDate(data.end_date) : '',
        };
    
        if (editingDeadline) {
            router.put(`/admin/deadlines/${editingDeadline.id}`, submitData, {
                onSuccess: () => {
                    reset();
                    setShowAddDialog(false);
                    setShowEditDialog(false);
                    setEditingDeadline(null);
                    
                    // Refresh sequence info for the selected season
                    if (data.season_id && typeof data.season_id === 'number') {
                        getSequenceInfoForSeason(data.season_id).then((seasonInfo) => {
                            if (seasonInfo) {
                                setCurrentSequenceInfo(seasonInfo.sequenceInfo);
                                setCurrentNextCategory(seasonInfo.nextCategory);
                            }
                        });
                    }
                },
                onError: (errors: Record<string, string>) => console.error('Update errors:', errors),
            });
        } else {
            router.post('/admin/deadlines', submitData, {
                onSuccess: () => {
                    reset();
                    setManualErrors({});
                    setShowAddDialog(false);
                    
                    // Refresh sequence info for the selected season
                    if (data.season_id && typeof data.season_id === 'number') {
                        getSequenceInfoForSeason(data.season_id).then((seasonInfo) => {
                            if (seasonInfo) {
                                setCurrentSequenceInfo(seasonInfo.sequenceInfo);
                                setCurrentNextCategory(seasonInfo.nextCategory);
                            }
                        });
                    }
                },
                onError: (errors: Record<string, string>) => {
                    console.error('Creation errors:', errors);
                    setManualErrors(errors);
                },
            });
        }
    };
    

    const handleEdit = (deadline: Deadline) => {
        setEditingDeadline(deadline);
    
        // Parse backend string as local time (Asia/Manila)
        const start = new Date(deadline.start_date.replace(' ', 'T'));
        const end = new Date(deadline.end_date.replace(' ', 'T'));
    
        setData({
            title: deadline.title,
            category: deadline.category,
            start_date: start,
            end_date: end,
            season_id: seasons.length > 0 ? seasons[0].id : '',
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


    const handleAddCancel = () => {
        reset();
        setManualErrors({});
        setShowAddDialog(false);
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
        } else if (deadline.is_inactive) {
            return (
                <Badge variant="outline" className="bg-gray-100 text-gray-800">
                    Inactive
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

    const getCategoryDisplayName = (category: string) => {
        const categoryMap: { [key: string]: string } = {
            'hte_assessment_form': 'HTE Assessment',
            'student_verification': 'Student Verification',
            'student_assessment_form': 'Student Assessment',
            'internship_placement': 'Internship Placement',
        };
        return categoryMap[category] || category;
    };


    const getAvailableCategories = () => {
        return categoryOptions.filter(option => {
            const info = currentSequenceInfo[option.value];
            return info && info.can_create;
        });
    };

    // Get sequence info for the selected season
    const getSequenceInfoForSeason = async (seasonId: number) => {
        try {
            const response = await fetch(`/admin/deadlines/sequence-info/${seasonId}`);
            if (response.ok) {
                return await response.json();
            }
        } catch (error) {
            console.error('Failed to fetch sequence info:', error);
        }
        return null;
    };

    // Update sequence info when season changes
    useEffect(() => {
        if (data.season_id && typeof data.season_id === 'number') {
            getSequenceInfoForSeason(data.season_id).then((seasonInfo) => {
                if (seasonInfo) {
                    setCurrentSequenceInfo(seasonInfo.sequenceInfo);
                    setCurrentNextCategory(seasonInfo.nextCategory);
                }
            });
        }
    }, [data.season_id]);

    // Debug: Log errors when they change
    useEffect(() => {
        if (Object.keys(errors).length > 0) {
            console.log('Form errors detected:', errors);
        }
    }, [errors]);

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

                    {/* Active Season Info */}
                    {activeSeason ? (
                        <Card className="border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-950/50">
                            <CardHeader className="pb-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                            <CheckCircleIcon className="h-5 w-5 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-green-900 dark:text-green-100">Current Active Season</h3>
                                            <p className="text-sm text-green-700 dark:text-green-300">{activeSeason.name}</p>
                                        </div>
                                    </div>
                                    <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Active
                                    </Badge>
                                </div>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div className="flex items-start gap-3">
                                        <CalendarIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium text-green-800 dark:text-green-200">Duration</p>
                                            <p className="text-sm text-green-700 dark:text-green-300">
                                                {new Date(activeSeason.start_date).toLocaleDateString()} - {new Date(activeSeason.end_date).toLocaleDateString()}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <ClockIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium text-green-800 dark:text-green-200">Deadlines</p>
                                            <p className="text-sm text-green-700 dark:text-green-300">
                                                {allDeadlines.length} total ({activeDeadlines.length} active, {expiredDeadlines.length} expired)
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <UsersIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium text-green-800 dark:text-green-200">Students</p>
                                            <p className="text-sm text-green-700 dark:text-green-300">
                                                {(() => {
                                                    const currentSeason = seasons.find(s => s.id === activeSeason.id);
                                                    return currentSeason ? `${currentSeason.active_students_count} active (${currentSeason.students_count} total)` : 'No data available';
                                                })()}
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-start gap-3">
                                        <CheckCircleIcon className="h-4 w-4 text-green-600 dark:text-green-400 mt-0.5" />
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium text-green-800 dark:text-green-200">Placements</p>
                                            <p className="text-sm text-green-700 dark:text-green-300">
                                                {(() => {
                                                    const currentSeason = seasons.find(s => s.id === activeSeason.id);
                                                    if (!currentSeason) return 'No data available';
                                                    const placedCount = currentSeason.students_count - currentSeason.active_students_count;
                                                    const placementRate = currentSeason.students_count > 0 ? Math.round((placedCount / currentSeason.students_count) * 100) : 0;
                                                    return `${placedCount} placed (${placementRate}%)`;
                                                })()}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card className="border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/50">
                            <CardHeader className="pb-4">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                                        <ClockIcon className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-amber-900 dark:text-amber-100">No Active Season</h3>
                                        <p className="text-sm text-amber-700 dark:text-amber-300">Create deadlines for any available season</p>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent className="pt-0">
                                <div className="space-y-3">
                                    <p className="text-sm text-amber-700 dark:text-amber-300">
                                        You can still create deadlines for any season by selecting it in the form below.
                                    </p>
                                    <div className="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-lg border border-amber-200 dark:border-amber-800">
                                        <p className="text-sm text-amber-800 dark:text-amber-200">
                                            <strong>Tip:</strong> To activate a season, go to Manage Seasons and ensure all 5 deadline categories are created.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Action Buttons */}
                    <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        {/* Primary Actions */}
                        <div className="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <Button
                                    onClick={() => setShowAddDialog(true)}
                                    className="flex items-center gap-2 h-9"
                                    title={seasons.length === 0 ? "Create an internship season first" : ""}
                                >
                                    <PlusIcon className="h-4 w-4" />
                                    Add Deadline
                                </Button>
                            <Button
                                onClick={() => router.get('/admin/seasons')}
                                className="flex items-center gap-2 h-9"
                            >
                                <CalendarIcon className="h-4 w-4" />
                                Manage Seasons
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
                                        <span>Place already-endorsed students according to corresponding internships with available slots</span>
                                    </div>
                                    <div className="flex items-start gap-2">
                                        <span className="font-semibold min-w-[140px]">Tier 2 (T-20 min):</span>
                                        <span>Auto-endorse and place students from matched page (students with valid matches but not yet endorsed) by compatibility</span>
                                    </div>
                                    <div className="flex items-start gap-2">
                                        <span className="font-semibold min-w-[140px]">Tier 3 (T-10 min):</span>
                                        <span>Emergency placement for remaining students
                                            rejected by HTE and no remaining fallback options into internship with available slots by compatibility</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>


                {/* Deadlines List */}
                <Card className="border-border shadow-sm">
                    <CardHeader className="pb-4 space-y-2">
                        <CardTitle className="flex items-center gap-2 text-xl font-semibold text-foreground">
                            <CalendarIcon className="h-5 w-5" />
                            {showArchived ? 'Expired Deadlines' : 'All Deadlines for Active Season'}
                            <Badge variant="secondary" className="ml-2 bg-muted text-muted-foreground">
                                {currentDeadlines.length}
                            </Badge>
                        </CardTitle>
                        <CardDescription className="text-sm text-muted-foreground">
                            {showArchived
                                ? 'Manage expired application deadlines'
                                : 'Manage all deadlines for the current active season regardless of status'
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
                                                : 'No deadlines exist for the current active season. Get started by creating your first deadline.'
                                            }
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="w-[200px]">Title</TableHead>
                                            <TableHead className="w-[150px]">Category</TableHead>
                                            <TableHead className="w-[120px]">Status</TableHead>
                                            <TableHead className="w-[180px]">Start Date</TableHead>
                                            <TableHead className="w-[180px]">End Date</TableHead>
                                            <TableHead className="w-[200px] text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {currentDeadlines.map((deadline) => (
                                            <TableRow key={deadline.id} className="hover:bg-muted/50">
                                                <TableCell className="font-medium">
                                                    <div className="space-y-1">
                                                        <div className="font-semibold text-foreground">
                                                            {deadline.title}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            Created: {new Date(deadline.created_at).toLocaleDateString()}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant="outline" className="text-xs">
                                                        {deadline.category_display}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {getStatusBadge(deadline)}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {formatDateTime(deadline.start_date)}
                                                </TableCell>
                                                <TableCell className="text-sm">
                                                    {formatDateTime(deadline.end_date)}
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {!deadline.is_expired && (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="outline"
                                                                        size="sm"
                                                                        onClick={() => handleExtend(deadline)}
                                                                        className="h-8 w-8 p-0 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                                                                    >
                                                                        <ClockIcon className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>Extend deadline by 1 month</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                        <Tooltip>
                                                            <TooltipTrigger asChild>
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() => handleEdit(deadline)}
                                                                    className="h-8 w-8 p-0"
                                                                >
                                                                    <EditIcon className="h-4 w-4" />
                                                                </Button>
                                                            </TooltipTrigger>
                                                            <TooltipContent>
                                                                <p>Edit deadline details</p>
                                                            </TooltipContent>
                                                        </Tooltip>
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
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

                {/* Add Deadline Dialog */}
                <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
                    <DialogContent className="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader className="space-y-3">
                            <DialogTitle className="flex items-center gap-2 text-lg font-semibold text-foreground">
                                <PlusIcon className="h-5 w-5" />
                                Add New Deadline
                            </DialogTitle>
                            <DialogDescription className="text-sm text-muted-foreground">
                                Enter the deadline information below.
                            </DialogDescription>
                        </DialogHeader>
                        
                        {/* Error Message Display */}
                        {(errors.category || manualErrors.category) && (
                            <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div className="flex items-start gap-3">
                                    <div className="w-5 h-5 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span className="text-red-600 dark:text-red-400 text-xs font-bold">!</span>
                                    </div>
                                    <div className="space-y-1">
                                        <h4 className="font-semibold text-red-800 dark:text-red-200 text-sm">
                                            Cannot Create Deadline
                                        </h4>
                                        <p className="text-sm text-red-700 dark:text-red-300">
                                            {errors.category || manualErrors.category}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}
                        
                        {/* Flash Error Message Display */}
                        {flash?.error && (
                            <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div className="flex items-start gap-3">
                                    <div className="w-5 h-5 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span className="text-red-600 dark:text-red-400 text-xs font-bold">!</span>
                                    </div>
                                    <div className="space-y-1">
                                        <h4 className="font-semibold text-red-800 dark:text-red-200 text-sm">
                                            Cannot Create Deadline
                                        </h4>
                                        <p className="text-sm text-red-700 dark:text-red-300">
                                            {flash.error}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        )}
                        
                        
                        {/* Info Card */}
                        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                            <div className="flex items-start gap-3">
                                <div className="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <CalendarIcon className="h-3 w-3 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div className="space-y-1">
                                    <h4 className="font-semibold text-blue-800 dark:text-blue-200 text-sm">
                                        Deadline Creation Order
                                    </h4>
                                    <p className="text-sm text-blue-700 dark:text-blue-300">
                                        Create deadlines chronologically:
                                    </p>
                                    <div className="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">1.</span>
                                            <span>HTE Assessment</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">2.</span>
                                            <span>Student Verification</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">3.</span>
                                            <span>Student Assessment</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">4.</span>
                                            <span>Internship Placement</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium">5.</span>
                                            <span>Archive Students</span>
                                        </div>
                                    </div>
                                    {currentNextCategory && (
                                        <div className="mt-3 p-2 bg-green-100 dark:bg-green-900/30 rounded border border-green-200 dark:border-green-800">
                                            <p className="text-sm font-medium text-green-800 dark:text-green-200">
                                                Next: {currentNextCategory.label}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                        <div className="space-y-4">
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="grid grid-cols-1 gap-4">
                                    <div className="space-y-3">
                                        <Label htmlFor="add_title" className="text-sm font-medium text-foreground">
                                            Title
                                        </Label>
                                        <Input
                                            id="add_title"
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
                                        <Label htmlFor="add_season" className="text-sm font-medium text-foreground">
                                            Internship Season
                                        </Label>
                                        <Select value={data.season_id.toString()} onValueChange={(value) => {
                                            setData('season_id', parseInt(value));
                                        }}>
                                            <SelectTrigger className="h-10">
                                                <SelectValue placeholder="Select a season" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {seasons.map((season) => (
                                                    <SelectItem key={season.id} value={season.id.toString()}>
                                                        <div className="flex items-center gap-2">
                                                            <span>{season.name}</span>
                                                            <span className="text-xs text-gray-500">
                                                                ({season.deadlines_count}/5 deadlines)
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.season_id && (
                                            <p className="text-sm text-destructive">{errors.season_id}</p>
                                        )}
                                    </div>

                                    <div className="space-y-3">
                                        <Label htmlFor="add_category" className="text-sm font-medium text-foreground">
                                            Category
                                        </Label>
                                        <Select value={data.category} onValueChange={(value) => {
                                            setData('category', value);
                                            // Clear manual errors when category changes
                                            if (manualErrors.category) {
                                                setManualErrors({});
                                            }
                                        }}>
                                            <SelectTrigger className={`h-10 ${errors.category ? 'border-destructive focus-visible:ring-destructive' : ''}`}>
                                                <SelectValue placeholder="Select a category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {getAvailableCategories().map((option) => {
                                                    const isRecommended = currentNextCategory?.value === option.value;
                                                    return (
                                                        <SelectItem key={option.value} value={option.value}>
                                                            <div className="flex items-center gap-2">
                                                                <span>{option.label}</span>
                                                                {isRecommended && (
                                                                    <Badge variant="secondary" className="text-xs bg-green-100 text-green-800">
                                                                        Recommended
                                                                    </Badge>
                                                                )}
                                                            </div>
                                                        </SelectItem>
                                                    );
                                                })}
                                            </SelectContent>
                                        </Select>
                                        {errors.category && (
                                            <p className="text-sm text-destructive">{errors.category}</p>
                                        )}
                                        {data.category && currentSequenceInfo[data.category] && !currentSequenceInfo[data.category].can_create && (
                                            <div className="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                                <p className="text-sm text-yellow-800 dark:text-yellow-200">
                                                    <strong>Cannot create this deadline yet.</strong> Please create deadlines in chronological order. Missing: {currentSequenceInfo[data.category].missing_categories.map(cat => getCategoryDisplayName(cat)).join(', ')}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                        <div className="space-y-3">
                                            <Label htmlFor="add_start_date" className="text-sm font-medium text-foreground">
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
                                            <Label htmlFor="add_end_date" className="text-sm font-medium text-foreground">
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

                                <div className="flex flex-col sm:flex-row gap-2 justify-end">
                                    <Button
                                        type="submit"
                                        disabled={processing || !!errors.category || !!manualErrors.category || (!!data.category && currentSequenceInfo[data.category] && !currentSequenceInfo[data.category].can_create)}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        {processing ? 'Saving...' : 'Create Deadline'}
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleAddCancel}
                                        className="flex-1 sm:flex-none h-9"
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
