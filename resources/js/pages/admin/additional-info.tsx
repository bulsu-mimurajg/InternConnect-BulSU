import { useState, useCallback, useMemo } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Pagination } from '@/components/ui/pagination';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { usePagination } from '@/hooks/usePagination';
import { Plus, Archive, ArchiveRestore, Info, Eye, Edit } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Additional Info Management',
        href: '/forms/additional-info',
    },
];

interface AdditionalInfo {
    id: number;
    info_name: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    additionalInfos: AdditionalInfo[];
    filters: {
        search: string;
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
            deadline: unknown;
            affected_functionality: string[];
        }>;
    };
}

export default function AdditionalInfoManagement({ additionalInfos, deadlineStatus }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedAdditionalInfo, setSelectedAdditionalInfo] = useState<AdditionalInfo | null>(null);
    const [showArchived, setShowArchived] = useState(false);

    const createForm = useForm({
        info_name: '',
    });

    const editForm = useForm({
        info_name: '',
    });

    const handleCreateAdditionalInfo = useCallback(() => {
        createForm.post('/forms/additional-info', {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
        });
    }, [createForm]);

    const handleEditAdditionalInfo = useCallback((additionalInfo: AdditionalInfo) => {
        setSelectedAdditionalInfo(additionalInfo);
        editForm.setData('info_name', additionalInfo.info_name);
        setIsEditDialogOpen(true);
    }, [editForm]);

    const handleUpdateAdditionalInfo = useCallback(() => {
        if (selectedAdditionalInfo) {
            editForm.put(`/forms/additional-info/${selectedAdditionalInfo.id}`, {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setSelectedAdditionalInfo(null);
                    editForm.reset();
                },
            });
        }
    }, [editForm, selectedAdditionalInfo]);

    const handleArchiveAdditionalInfo = useCallback((id: number) => {
        if (confirm('Are you sure you want to archive this additional info?')) {
            router.patch(`/forms/additional-info/${id}/archive`);
        }
    }, []);

    const handleRestoreAdditionalInfo = useCallback((id: number) => {
        router.patch(`/forms/additional-info/${id}/restore`);
    }, []);

    const activeAdditionalInfos = additionalInfos.filter(info => info.is_active);
    const archivedAdditionalInfos = additionalInfos.filter(info => !info.is_active);

    // Get current additional infos based on showArchived state
    const currentAdditionalInfos = showArchived ? archivedAdditionalInfos : activeAdditionalInfos;

    // Use pagination hook
    const {
        currentPage,
        totalPages,
        paginatedData: paginatedAdditionalInfos,
        handlePageChange,
        resetToFirstPage,
    } = usePagination({
        data: currentAdditionalInfos,
        itemsPerPage: 10,
    });

    // Reset to page 1 when switching between active/archived additional infos
    const handleToggleArchived = useCallback(() => {
        setShowArchived(!showArchived);
        resetToFirstPage();
    }, [showArchived, resetToFirstPage]);

    // Check if additional info management is restricted due to deadlines
    const isAdditionalInfoManagementRestricted = useMemo(() => {
        return deadlineStatus?.restrictions.some(restriction => 
            restriction.affected_functionality.includes('additional_info_management')
        ) || false;
    }, [deadlineStatus]);

    // Get restriction message
    const restrictionMessage = useMemo(() => {
        const restriction = deadlineStatus?.restrictions.find(restriction => 
            restriction.affected_functionality.includes('additional_info_management')
        );
        return restriction?.message || '';
    }, [deadlineStatus]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Additional Info Management" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Deadline Restriction Alert */}
                {isAdditionalInfoManagementRestricted && (
                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <Info className="h-5 w-5 text-amber-400" />
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-amber-800">
                                    Additional Info Management Restricted
                                </h3>
                                <div className="mt-2 text-sm text-amber-700">
                                    <p>{restrictionMessage}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Header */}
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Additional Info Management</h1>
                        <p className="text-muted-foreground">
                            Manage additional information fields for student forms
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            onClick={handleToggleArchived}
                            disabled={isAdditionalInfoManagementRestricted}
                        >
                            {showArchived ? (
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
                                    className="flex items-center gap-2"
                                    disabled={isAdditionalInfoManagementRestricted}
                                >
                                    <Plus className="h-4 w-4" />
                                    Add Additional Info
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Create New Additional Info</DialogTitle>
                                    <DialogDescription>
                                        Add a new additional information field.
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="info_name">Info Name</Label>
                                        <Input
                                            id="info_name"
                                            value={createForm.data.info_name}
                                            onChange={(e) => createForm.setData('info_name', e.target.value)}
                                            className={createForm.errors.info_name ? 'border-red-500' : ''}
                                            placeholder="e.g., Previous Work Experience, Skills"
                                            required
                                        />
                                        {createForm.errors.info_name && (
                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.info_name}</p>
                                        )}
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button variant="outline" onClick={() => setIsCreateDialogOpen(false)}>
                                        Cancel
                                    </Button>
                                    <Button onClick={handleCreateAdditionalInfo} disabled={createForm.processing}>
                                        {createForm.processing ? 'Creating...' : 'Create Additional Info'}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Additional Infos Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Info className="h-5 w-5"/>
                            Additional Information Fields
                        </CardTitle>
                        <CardDescription>
                            {showArchived 
                                ? 'Archived additional information fields' 
                                : 'Active additional information fields'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {currentAdditionalInfos.length === 0 ? (
                            <div className="py-12 text-center">
                                <div className="flex flex-col items-center space-y-4">
                                    <Info className="h-12 w-12 text-muted-foreground" />
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-medium">
                                            {showArchived ? 'No archived additional info found' : 'No active additional info found'}
                                        </h3>
                                        <p className="text-muted-foreground">
                                            {showArchived 
                                                ? 'No additional info fields have been archived yet.' 
                                                : 'Get started by creating your first additional info field.'
                                            }
                                        </p>
                                    </div>
                                    {!showArchived && (
                                        <Button 
                                            onClick={() => setIsCreateDialogOpen(true)}
                                            disabled={isAdditionalInfoManagementRestricted}
                                        >
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Additional Info
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Info Name</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {paginatedAdditionalInfos.map((additionalInfo) => (
                                            <tr key={additionalInfo.id} className={`border-b hover:bg-muted/50 transition-colors ${!additionalInfo.is_active ? 'opacity-75' : ''}`}>
                                                <td className="py-3 px-4 font-medium">
                                                    {additionalInfo.info_name}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge variant={additionalInfo.is_active ? 'default' : 'secondary'}>
                                                        {additionalInfo.is_active ? 'Active' : 'Archived'}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {new Date(additionalInfo.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <TooltipProvider>
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={() => handleEditAdditionalInfo(additionalInfo)}
                                                                        disabled={isAdditionalInfoManagementRestricted}
                                                                        className="h-8"
                                                                    >
                                                                        <Edit className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>
                                                                    <p>Edit</p>
                                                                </TooltipContent>
                                                            </Tooltip>
                                                            {!additionalInfo.is_active ? (
                                                                <Tooltip>
                                                                    <TooltipTrigger asChild>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            onClick={() => handleRestoreAdditionalInfo(additionalInfo.id)}
                                                                            disabled={isAdditionalInfoManagementRestricted}
                                                                            className="h-8 text-green-600 hover:text-green-700"
                                                                        >
                                                                            <ArchiveRestore className="h-4 w-4" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <p>Restore</p>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            ) : (
                                                                <Tooltip>
                                                                    <TooltipTrigger asChild>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            onClick={() => handleArchiveAdditionalInfo(additionalInfo.id)}
                                                                            disabled={isAdditionalInfoManagementRestricted}
                                                                            className="h-8 text-destructive hover:text-destructive"
                                                                        >
                                                                            <Archive className="h-4 w-4" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>
                                                                        <p>Archive</p>
                                                                    </TooltipContent>
                                                                </Tooltip>
                                                            )}
                                                        </div>
                                                    </TooltipProvider>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                <Pagination
                    currentPage={currentPage}
                    totalPages={totalPages}
                    onPageChange={handlePageChange}
                    showSummary={true}
                    totalItems={currentAdditionalInfos.length}
                    itemsPerPage={10}
                />

                {/* Edit Dialog */}
                <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Additional Info</DialogTitle>
                            <DialogDescription>
                                Update the additional information field.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4">
                            <div>
                                <Label htmlFor="edit_info_name">Info Name</Label>
                                <Input
                                    id="edit_info_name"
                                    value={editForm.data.info_name}
                                    onChange={(e) => editForm.setData('info_name', e.target.value)}
                                    className={editForm.errors.info_name ? 'border-red-500' : ''}
                                    placeholder="e.g., Previous Work Experience, Skills"
                                    required
                                />
                                {editForm.errors.info_name && (
                                    <p className="text-red-500 text-xs mt-1">{editForm.errors.info_name}</p>
                                )}
                            </div>
                        </div>
                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
                                Cancel
                            </Button>
                            <Button onClick={handleUpdateAdditionalInfo} disabled={editForm.processing}>
                                {editForm.processing ? 'Updating...' : 'Update Additional Info'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}