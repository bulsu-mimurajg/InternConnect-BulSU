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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { Plus, MoreHorizontal, Edit, Archive, ArchiveRestore, SquareLibraryIcon, Eye } from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Section Management',
        href: '/admin/section',
    },
];

interface Section {
    section_id: number;
    section_name: string;
    status: string;
    student_count?: number;
    adviser_count?: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    sections: Section[];
    showArchived?: boolean;
}

export default function SectionManagement({ sections, showArchived = false }: Props) {
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [selectedSection, setSelectedSection] = useState<Section | null>(null);
    const [showArchivedSections, setShowArchivedSections] = useState(showArchived);

    const createForm = useForm({
        section_name: '',
    });

    const editForm = useForm({
        section_name: '',
    });

    const handleCreateSection = useCallback(() => {
        createForm.post('/admin/section', {
            onSuccess: () => {
                setIsCreateDialogOpen(false);
                createForm.reset();
            },
        });
    }, [createForm]);

    const handleEditSection = useCallback((section: Section) => {
        setSelectedSection(section);
        editForm.setData('section_name', section.section_name);
        setIsEditDialogOpen(true);
    }, [editForm]);

    const handleUpdateSection = useCallback(() => {
        if (selectedSection) {
            editForm.put(`/admin/section/${selectedSection.section_id}`, {
                onSuccess: () => {
                    setIsEditDialogOpen(false);
                    setSelectedSection(null);
                    editForm.reset();
                },
            });
        }
    }, [editForm, selectedSection]);

    const handleArchiveSection = useCallback((sectionId: number) => {
        if (confirm('Are you sure you want to archive this section?')) {
            router.patch(`/admin/section/${sectionId}/archive`);
        }
    }, []);

    const handleRestoreSection = useCallback((sectionId: number) => {
        router.patch(`/admin/section/${sectionId}/restore`);
    }, []);

    const activeSections = sections.filter(section => section.status === 'active');
    const archivedSections = sections.filter(section => section.status === 'archived');

    // Get current sections based on showArchivedSections state
    const currentSections = showArchivedSections ? archivedSections : activeSections;

    // Use pagination hook
    const {
        currentPage,
        totalPages,
        paginatedData: paginatedSections,
        handlePageChange,
        resetToFirstPage,
    } = usePagination({
        data: currentSections,
        itemsPerPage: 10,
    });

    // Reset to page 1 when switching between active/archived sections
    const handleToggleArchived = useCallback(() => {
        setShowArchivedSections(!showArchivedSections);
        resetToFirstPage();
    }, [showArchivedSections, resetToFirstPage]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Section Management" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Section Management</h1>
                        <p className="text-muted-foreground">
                            Manage student sections and their assignments
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            onClick={handleToggleArchived}
                        >
                            {showArchivedSections ? (
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
                                <Button className="flex items-center gap-2">
                                    <Plus className="h-4 w-4" />
                                    Add Section
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Create New Section</DialogTitle>
                                    <DialogDescription>
                                        Add a new section to organize students.
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-4">
                                    <div>
                                        <Label htmlFor="section_name">Section Name</Label>
                                        <Input
                                            id="section_name"
                                            value={createForm.data.section_name}
                                            onChange={(e) => createForm.setData('section_name', e.target.value)}
                                            className={createForm.errors.section_name ? 'border-red-500' : ''}
                                            placeholder="e.g., CS-3A, IT-2B"
                                            required
                                        />
                                        {createForm.errors.section_name && (
                                            <p className="text-red-500 text-xs mt-1">{createForm.errors.section_name}</p>
                                        )}
                                    </div>
                                </div>
                                <DialogFooter>
                                    <Button variant="outline" onClick={() => setIsCreateDialogOpen(false)}>
                                        Cancel
                                    </Button>
                                    <Button onClick={handleCreateSection} disabled={createForm.processing}>
                                        {createForm.processing ? 'Creating...' : 'Create Section'}
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                {/* Sections Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <SquareLibraryIcon className="h-5 w-5"/>
                            Sections
                        </CardTitle>
                        <CardDescription>
                            {showArchivedSections 
                                ? 'Archived sections' 
                                : 'Active sections and their assignments'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {currentSections.length === 0 ? (
                            <div className="py-12 text-center">
                                <div className="flex flex-col items-center space-y-4">
                                    <Archive className="h-12 w-12 text-muted-foreground" />
                                    <div className="space-y-2">
                                        <h3 className="text-lg font-medium">
                                            {showArchivedSections ? 'No archived sections found' : 'No active sections found'}
                                        </h3>
                                        <p className="text-muted-foreground">
                                            {showArchivedSections 
                                                ? 'No sections have been archived yet.' 
                                                : 'Get started by creating your first section.'
                                            }
                                        </p>
                                    </div>
                                    {!showArchivedSections && (
                                        <Button onClick={() => setIsCreateDialogOpen(true)}>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Add Section
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Section Name</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Status</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Students</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Advisers</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Created</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {paginatedSections.map((section) => (
                                            <tr key={section.section_id} className={`border-b hover:bg-muted/50 transition-colors ${section.status === 'archived' ? 'opacity-75' : ''}`}>
                                                <td className="py-3 px-4 font-medium">
                                                    {section.section_name}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <Badge variant={section.status === 'active' ? 'default' : 'secondary'}>
                                                        {section.status === 'active' ? 'Active' : 'Archived'}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    <Badge variant="secondary">{section.student_count || 0}</Badge>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    <Badge variant="secondary">{section.adviser_count || 0}</Badge>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {new Date(section.created_at).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem onClick={() => handleEditSection(section)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </DropdownMenuItem>
                                                            {section.status === 'archived' ? (
                                                                <DropdownMenuItem 
                                                                    onClick={() => handleRestoreSection(section.section_id)}
                                                                    className="text-green-600 focus:text-green-600"
                                                                >
                                                                    <ArchiveRestore className="mr-2 h-4 w-4" />
                                                                    Restore
                                                                </DropdownMenuItem>
                                                            ) : (
                                                                <DropdownMenuItem 
                                                                    onClick={() => handleArchiveSection(section.section_id)}
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

                {/* Pagination */}
                <Pagination
                    currentPage={currentPage}
                    totalPages={totalPages}
                    onPageChange={handlePageChange}
                    showSummary={true}
                    totalItems={currentSections.length}
                    itemsPerPage={10}
                />

                {/* Edit Dialog */}
                <Dialog open={isEditDialogOpen} onOpenChange={setIsEditDialogOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Edit Section</DialogTitle>
                            <DialogDescription>
                                Update the section information.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4">
                            <div>
                                <Label htmlFor="edit_section_name">Section Name</Label>
                                <Input
                                    id="edit_section_name"
                                    value={editForm.data.section_name}
                                    onChange={(e) => editForm.setData('section_name', e.target.value)}
                                    className={editForm.errors.section_name ? 'border-red-500' : ''}
                                    placeholder="e.g., CS-3A, IT-2B"
                                    required
                                />
                                {editForm.errors.section_name && (
                                    <p className="text-red-500 text-xs mt-1">{editForm.errors.section_name}</p>
                                )}
                            </div>
                </div>
                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsEditDialogOpen(false)}>
                                Cancel
                            </Button>
                            <Button onClick={handleUpdateSection} disabled={editForm.processing}>
                                {editForm.processing ? 'Updating...' : 'Update Section'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
