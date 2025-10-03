import { useState, useEffect, useCallback, useMemo } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';
import { FileTextIcon, PlusIcon, EditIcon, ArchiveIcon, RotateCcwIcon, SearchIcon, FilterIcon, ArrowUpDownIcon, ChevronDown, ChevronUp, Archive, Eye } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Question, type Category, type SubCategory } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assessment Form',
        href: '/forms/assessment',
    },
];

interface FormsPageProps {
    questions: Question[];
    categories: Category[];
    subcategories: SubCategory[];
    filters: {
        search: string;
        category_id: string;
        subcategory_id: string;
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
            deadline: any;
            affected_functionality: string[];
        }>;
    };
}

export default function FormsPage({ questions, categories, subcategories, filters, deadlineStatus }: FormsPageProps) {
    const [showForm, setShowForm] = useState(false);
    const [editingQuestion, setEditingQuestion] = useState<Question | null>(null);
    const [selectedCategory, setSelectedCategory] = useState<string>('');
    const [availableSubcategories, setAvailableSubcategories] = useState<SubCategory[]>([]);
    const [showArchived, setShowArchived] = useState(false);
    const [showFilters, setShowFilters] = useState(false);
    const [searchTerm, setSearchTerm] = useState(filters.search);
    const [filterCategory, setFilterCategory] = useState(filters.category_id);
    const [filterSubcategory, setFilterSubcategory] = useState(filters.subcategory_id);
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };



    const { data, setData, post, put, patch, processing, errors, reset } = useForm({
        question: '',
        subcategory_id: '',
    });

    // Filter questions based on archive status
    const filteredQuestions = questions.filter(question =>
        showArchived ? !question.is_active : question.is_active
    );

    // Apply additional filters (search, category, subcategory)
    const applyAdditionalFilters = useCallback(() => {
        return filteredQuestions.filter(question => {
            const matchesSearch = !searchTerm || 
                question.question.toLowerCase().includes(searchTerm.toLowerCase());
            const matchesCategory = !filterCategory || 
                question.subcategory.category.id.toString() === filterCategory;
            const matchesSubcategory = !filterSubcategory || 
                question.subcategory.id.toString() === filterSubcategory;
            
            return matchesSearch && matchesCategory && matchesSubcategory;
        });
    }, [filteredQuestions, searchTerm, filterCategory, filterSubcategory]);

    const finalFilteredQuestions = applyAdditionalFilters();

    // Create a stable reset trigger
    const resetTrigger = useMemo(() => 
        `${searchTerm}-${filterCategory}-${filterSubcategory}-${showArchived}`,
        [searchTerm, filterCategory, filterSubcategory, showArchived]
    );

    // Pagination hook with auto-reset on filter changes
    const questionsPagination = usePagination({
        data: finalFilteredQuestions,
        itemsPerPage: 10,
        resetTrigger: resetTrigger, // Auto-reset when filters change
    });


    // Note: All filtering is now handled client-side for better pagination experience
    // No server-side filtering needed since we're using client-side pagination

    // Reset subcategory when category changes
    useEffect(() => {
        if (!filterCategory) {
            setFilterSubcategory('');
        }
    }, [filterCategory]);

    // Clear all filters
    const clearFilters = () => {
        setSearchTerm('');
        setFilterCategory('');
        setFilterSubcategory('');
    };

    // Get filtered subcategories based on selected category
    const getFilteredSubcategories = () => {
        if (filterCategory) {
            return subcategories.filter(sub => sub.category_id.toString() === filterCategory);
        }
        return subcategories;
    };

    // Handle category selection
    const handleCategoryChange = (categoryId: string) => {
        setSelectedCategory(categoryId);
        const category = categories.find(cat => cat.id.toString() === categoryId);
        setAvailableSubcategories(category?.subCategories || []);
        setData('subcategory_id', ''); // Reset subcategory selection
    };



    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingQuestion) {
            put(`/forms/questions/${editingQuestion.id}`, {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                    setEditingQuestion(null);
                    setSelectedCategory('');
                    setAvailableSubcategories([]);
                },
                onError: (errors) => {
                    console.error('Update errors:', errors);
                },
            });
        } else {
            post('/forms/questions', {
                onSuccess: () => {
                    reset();
                    setShowForm(false);
                    setSelectedCategory('');
                    setAvailableSubcategories([]);
                },
                onError: (errors) => {
                    console.error('Creation errors:', errors);
                },
            });
        }
    };

    const handleEdit = (question: Question) => {
        setEditingQuestion(question);
        setData({
            question: question.question,
            subcategory_id: question.subcategory_id.toString(),
        });

        // Set the category and subcategories
        const categoryId = question.subcategory.category.id.toString();
        setSelectedCategory(categoryId);
        const category = categories.find(cat => cat.id.toString() === categoryId);
        setAvailableSubcategories(category?.subCategories || []);
        setShowForm(true);
    };

    const handleArchive = (id: number) => {
        if (confirm('Are you sure you want to archive this question?')) {
            patch(`/forms/questions/${id}/archive`);
        }
    };

    const handleRestore = (id: number) => {
        if (confirm('Are you sure you want to restore this question?')) {
            patch(`/forms/questions/${id}/restore`);
        }
    };

    const handleCancel = () => {
        reset();
        setShowForm(false);
        setEditingQuestion(null);
        setSelectedCategory('');
        setAvailableSubcategories([]);
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

    // Check if forms management is restricted due to deadlines
    const isFormsManagementRestricted = useMemo(() => {
        return deadlineStatus?.restrictions.some(restriction => 
            restriction.affected_functionality.includes('forms_management')
        ) || false;
    }, [deadlineStatus]);

    // Get restriction message
    const restrictionMessage = useMemo(() => {
        const restriction = deadlineStatus?.restrictions.find(restriction => 
            restriction.affected_functionality.includes('forms_management')
        );
        return restriction?.message || '';
    }, [deadlineStatus]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Forms Management" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Deadline Restriction Alert */}
                {isFormsManagementRestricted && (
                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div className="flex items-start">
                            <div className="flex-shrink-0">
                                <FileTextIcon className="h-5 w-5 text-amber-400" />
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-amber-800">
                                    Forms Management Restricted
                                </h3>
                                <div className="mt-2 text-sm text-amber-700">
                                    <p>{restrictionMessage}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

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
                        <h1 className="text-3xl font-bold tracking-tight">Forms Management</h1>
                        <p className="text-muted-foreground">
                            Manage assessment questions and their categories
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button 
                            variant="outline" 
                            size="default"
                            onClick={() => setShowFilters(!showFilters)}
                        >
                            <FilterIcon className="h-4 w-4" />
                            Filters
                        </Button>
                        <Button
                            variant="outline"
                            onClick={() => setShowArchived(!showArchived)}
                            disabled={isFormsManagementRestricted}
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
                        <Button
                            onClick={() => setShowForm(true)}
                            className="flex items-center gap-2"
                            disabled={isFormsManagementRestricted}
                        >
                            <PlusIcon className="h-4 w-4" />
                            Add Question
                        </Button>
                    </div>
                </div>

                {/* Filters and Search */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2">
                                    <FilterIcon className="h-5 w-5" />
                                    Filters & Search
                                </CardTitle>
                                <Button
                                    variant="outline"
                                    onClick={clearFilters}
                                    className="flex items-center gap-2"
                                >
                                    <ArrowUpDownIcon className="h-4 w-4" />
                                    Clear Filters
                                </Button>
                            </div>
                            <CardDescription>
                                Filter questions by category, subcategory, or search by question text
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {/* Search */}
                                <div className="space-y-2">
                                    <Label htmlFor="search">Search Questions</Label>
                                    <div className="relative">
                                        <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            id="search"
                                            placeholder="Search questions..."
                                            value={searchTerm}
                                            onChange={(e) => setSearchTerm(e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                </div>

                                {/* Category Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="filter-category">Category</Label>
                                    <Select value={filterCategory || "all"} onValueChange={(value) => setFilterCategory(value === "all" ? "" : value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Categories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Categories</SelectItem>
                                            {categories.map((category) => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.category_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Subcategory Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="filter-subcategory">Subcategory</Label>
                                    <Select
                                        value={filterSubcategory || "all"}
                                        onValueChange={(value) => setFilterSubcategory(value === "all" ? "" : value)}
                                        disabled={!filterCategory}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All Subcategories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Subcategories</SelectItem>
                                            {getFilteredSubcategories().map((subcategory) => (
                                                <SelectItem key={subcategory.id} value={subcategory.id.toString()}>
                                                    {subcategory.subcategory_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Add/Edit Form */}
                {showForm && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FileTextIcon className="h-5 w-5" />
                                {editingQuestion ? 'Edit Question' : 'Add New Question'}
                            </CardTitle>
                            <CardDescription>
                                {editingQuestion
                                    ? 'Update the question information below.'
                                    : 'Enter the question information below.'
                                }
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="question">Question</Label>
                                    <Textarea
                                        id="question"
                                        value={data.question}
                                        onChange={(e) => setData('question', e.target.value)}
                                        className={errors.question ? 'border-red-500' : ''}
                                        placeholder="Enter the assessment question..."
                                        rows={3}
                                    />
                                    {errors.question && (
                                        <p className="text-sm text-red-500">{errors.question}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="category">Category</Label>
                                        <Select value={selectedCategory} onValueChange={handleCategoryChange}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((category) => (
                                                    <SelectItem key={category.id} value={category.id.toString()}>
                                                        {category.category_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subcategory">Subcategory</Label>
                                        <Select
                                            value={data.subcategory_id}
                                            onValueChange={(value) => setData('subcategory_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a subcategory" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableSubcategories.map((subcategory) => (
                                                    <SelectItem key={subcategory.id} value={subcategory.id.toString()}>
                                                        {subcategory.subcategory_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.subcategory_id && (
                                            <p className="text-sm text-red-500">{errors.subcategory_id}</p>
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
                                        {processing ? 'Saving...' : (editingQuestion ? 'Update' : 'Create')}
                                    </Button>
                                    <Button type="button" variant="outline" onClick={handleCancel}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {/* Questions List */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileTextIcon className="h-5 w-5" />
                            {showArchived ? 'Archived Questions' : 'Active Questions'}
                        </CardTitle>
                        <CardDescription>
                            {showArchived
                                ? 'Manage archived assessment questions'
                                : 'Manage active assessment questions'
                            }
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {finalFilteredQuestions.length === 0 ? (
                            <div className="text-center py-8">
                                <FileTextIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-medium mb-2">
                                    {showArchived ? 'No archived questions found' : 'No questions found'}
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    {showArchived
                                        ? 'No questions have been archived yet.'
                                        : 'Get started by creating your first question.'
                                    }
                                </p>
                                {!showArchived && (
                                    <Button onClick={() => setShowForm(true)}>
                                        <PlusIcon className="h-4 w-4 mr-2" />
                                        Add Question
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="text-center p-3 font-medium text-muted-foreground w-16">#</th>
                                            <th className="text-left p-3 font-medium text-muted-foreground">Question</th>
                                            <th className="text-left p-3 font-medium text-muted-foreground">Category</th>
                                            <th className="text-left p-3 font-medium text-muted-foreground">Subcategory</th>
                                            <th className="text-left p-3 font-medium text-muted-foreground">Status</th>
                                            <th className="text-right p-3 font-medium text-muted-foreground">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {questionsPagination.paginatedData.map((question, index) => (
                                            <tr key={question.id} className="border-b hover:bg-muted/50 transition-colors">
                                                <td className="text-center p-3 font-mono text-sm text-muted-foreground">
                                                    {getRowNumber(questionsPagination.currentPage, 10, index)}
                                                </td>
                                                <td className="p-3">
                                                    <div>
                                                        <div className="font-medium mb-1">Question #{question.id}</div>
                                                        <p className="text-sm text-muted-foreground">
                                                            {question.question}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-sm">{question.subcategory.category.category_name}</span>
                                                </td>
                                                <td className="p-3">
                                                    <span className="text-sm">{question.subcategory.subcategory_name}</span>
                                                </td>
                                                <td className="p-3">
                                                    {getStatusBadge(question.is_active)}
                                                </td>
                                                <td className="p-3 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        {question.is_active ? (
                                                            <>
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() => handleEdit(question)}
                                                                    disabled={isFormsManagementRestricted}
                                                                >
                                                                    <EditIcon className="h-4 w-4" />
                                                                </Button>
                                                                <Button
                                                                    variant="outline"
                                                                    size="sm"
                                                                    onClick={() => handleArchive(question.id)}
                                                                    className="text-orange-600 hover:text-orange-700"
                                                                    disabled={isFormsManagementRestricted}
                                                                >
                                                                    <ArchiveIcon className="h-4 w-4" />
                                                                </Button>
                                                            </>
                                                        ) : (
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleRestore(question.id)}
                                                                className="text-green-600 hover:text-green-700"
                                                                disabled={isFormsManagementRestricted}
                                                            >
                                                                <RotateCcwIcon className="h-4 w-4" />
                                                            </Button>
                                                        )}
                                                    </div>
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
                {finalFilteredQuestions.length > 0 && (
                    <Pagination
                        currentPage={questionsPagination.currentPage}
                        totalPages={questionsPagination.totalPages}
                        onPageChange={questionsPagination.handlePageChange}
                        showSummary={true}
                        totalItems={finalFilteredQuestions.length}
                        itemsPerPage={10}
                    />
                )}
            </div>
        </AppLayout>
    );
}
