import { useState, useEffect, useCallback } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { PlusIcon, EditIcon, ArchiveIcon, RotateCcwIcon, SearchIcon, FilterIcon, ArrowUpDownIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Additional Info',
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

interface AdditionalInfoPageProps {
    additionalInfos: AdditionalInfo[];
    filters: {
        search: string;
    };
}

export default function AdditionalInfoPage({ additionalInfos, filters }: AdditionalInfoPageProps) {
    const [showForm, setShowForm] = useState(false);
    const [editingInfo, setEditingInfo] = useState<AdditionalInfo | null>(null);
    const [showArchived, setShowArchived] = useState(false);
    const [searchTerm, setSearchTerm] = useState(filters.search);
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

    const { data, setData, post, put, patch, processing, errors, reset } = useForm({
        info_name: '',
    });

    // Filter additional infos based on archive status
    const filteredInfos = additionalInfos.filter(info =>
        showArchived ? !info.is_active : info.is_active
    );

    // Apply filters and search
    const applyFilters = useCallback(() => {
        const params = new URLSearchParams();

        if (searchTerm) params.set('search', searchTerm);

        router.get('/forms/additional-info', Object.fromEntries(params), {
            preserveState: true,
            replace: true,
        });
    }, [searchTerm]);

    // Handle search with debounce
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            applyFilters();
        }, 500);
        return () => clearTimeout(timeoutId);
    }, [searchTerm, applyFilters]);

    // Clear all filters
    const clearFilters = () => {
        setSearchTerm('');
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingInfo) {
            put(`/forms/additional-info/${editingInfo.id}`, {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                    setEditingInfo(null);
                },
                onError: (errors) => {
                    console.error('Update errors:', errors);
                },
            });
        } else {
            post('/forms/additional-info', {
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

    const handleEdit = (info: AdditionalInfo) => {
        setEditingInfo(info);
        setData({
            info_name: info.info_name,
        });
        setShowForm(true);
    };

    const handleArchive = (id: number) => {
        if (confirm('Are you sure you want to archive this additional info?')) {
            patch(`/forms/additional-info/${id}/archive`);
        }
    };

    const handleRestore = (id: number) => {
        if (confirm('Are you sure you want to restore this additional info?')) {
            patch(`/forms/additional-info/${id}/restore`);
        }
    };

    const handleCancel = () => {
        reset();
        setShowForm(false);
        setEditingInfo(null);
    };

    const getStatusBadge = (isActive: boolean) => {
        return isActive ? (
            <Badge variant="default" className="bg-green-100 text-green-800">
                Active
            </Badge>
        ) : (
            <Badge variant="secondary" className="bg-gray-100 text-gray-800">
                Archived
            </Badge>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Additional Info Management" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                        <h1 className="text-3xl font-bold tracking-tight">Additional Info Management</h1>
                        <p className="text-muted-foreground">
                            Manage additional information fields for student forms
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={() => setShowArchived(!showArchived)}
                        >
                            {showArchived ? 'Show Active' : 'Show Archived'}
                        </Button>
                        <Button
                            onClick={() => setShowForm(true)}
                            className="flex items-center gap-2"
                        >
                            <PlusIcon className="h-4 w-4" />
                            Add Field
                        </Button>
                    </div>
                </div>

                {/* Filters and Search */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FilterIcon className="h-5 w-5" />
                            Filters & Search
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Search */}
                            <div className="space-y-2">
                                <Label htmlFor="search">Search Fields</Label>
                                <div className="relative">
                                    <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="search"
                                        placeholder="Search field names..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Clear Filters Button */}
                        <div className="flex justify-end mt-4">
                            <Button
                                variant="outline"
                                onClick={clearFilters}
                                className="flex items-center gap-2"
                            >
                                <ArrowUpDownIcon className="h-4 w-4" />
                                Clear Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Add/Edit Form */}
                {showForm && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <PlusIcon className="h-5 w-5" />
                                {editingInfo ? 'Edit Field' : 'Add New Field'}
                            </CardTitle>
                            <CardDescription>
                                {editingInfo
                                    ? 'Update the field information below.'
                                    : 'Enter the field information below.'
                                }
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="info_name">Field Name</Label>
                                    <Input
                                        id="info_name"
                                        value={data.info_name}
                                        onChange={(e) => setData('info_name', e.target.value)}
                                        className={errors.info_name ? 'border-red-500' : ''}
                                        placeholder="Enter the field name (e.g., LinkedIn Profile, GitHub Repository)"
                                    />
                                    {errors.info_name && (
                                        <p className="text-sm text-red-500">{errors.info_name}</p>
                                    )}
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
                                        {processing ? 'Saving...' : (editingInfo ? 'Update' : 'Create')}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={handleCancel}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {/* Additional Info List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <PlusIcon className="h-5 w-5" />
                            {showArchived ? 'Archived Fields' : 'Active Fields'}
                        </CardTitle>
                        <CardDescription>
                            {showArchived
                                ? 'Manage archived additional info fields'
                                : 'Manage active additional info fields'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {filteredInfos.length === 0 ? (
                            <div className="text-center py-8">
                                <PlusIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-medium mb-2">
                                    {showArchived ? 'No archived fields found' : 'No fields found'}
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    {showArchived
                                        ? 'No fields have been archived yet.'
                                        : 'Get started by creating your first field.'
                                    }
                                </p>
                                {!showArchived && (
                                    <Button onClick={() => setShowForm(true)}>
                                        <PlusIcon className="h-4 w-4 mr-2" />
                                        Add Field
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {filteredInfos.map((info) => (
                                    <div
                                        key={info.id}
                                        className="flex items-start justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                                    >
                                        <div className="flex-1">
                                            <div className="flex items-center gap-3 mb-2">
                                                <h3 className="font-medium">{info.info_name}</h3>
                                                {getStatusBadge(info.is_active)}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                Created: {new Date(info.created_at).toLocaleDateString()}
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            {info.is_active ? (
                                                <>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleEdit(info)}
                                                    >
                                                        <EditIcon className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => handleArchive(info.id)}
                                                        className="text-orange-600 hover:text-orange-700"
                                                    >
                                                        <ArchiveIcon className="h-4 w-4" />
                                                    </Button>
                                                </>
                                            ) : (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handleRestore(info.id)}
                                                    className="text-green-600 hover:text-green-700"
                                                >
                                                    <RotateCcwIcon className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
