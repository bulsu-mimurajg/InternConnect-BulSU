import { useState, useEffect } from 'react';
import { Head, useForm, usePage, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { PlusIcon, EditIcon, ArchiveIcon, RotateCcwIcon, SearchIcon, XIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type Category, type SubCategory } from '@/types';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HTE Criteria',
        href: '/forms/hte-criteria',
    },
];

interface HTEQuestion {
    id: number;
    question: string;
    subcategory_id: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    subcategory: {
        id: number;
        subcategory_name: string;
        category_id: number;
        category: {
            id: number;
            category_name: string;
        };
    };
}

interface HTECriteriaPageProps {
    questions: {
        data: HTEQuestion[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    subcategories: SubCategory[];
    filters: {
        search?: string;
        category_id?: string;
        subcategory_id?: string;
        status?: string;
    };
}

export default function HTECriteriaPage({ questions, categories, subcategories, filters }: HTECriteriaPageProps) {
    const [showForm, setShowForm] = useState(false);
    const [editingQuestion, setEditingQuestion] = useState<HTEQuestion | null>(null);
    const [selectedCategory, setSelectedCategory] = useState<string>('');
    const [availableSubcategories, setAvailableSubcategories] = useState<SubCategory[]>([]);
    const [showArchived, setShowArchived] = useState(filters.status === 'archived');
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [filterCategory, setFilterCategory] = useState(filters.category_id || '');
    const [filterSubcategory, setFilterSubcategory] = useState(filters.subcategory_id || '');
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

    const { data, setData, post, put, patch, processing, errors, reset } = useForm({
        question: '',
        subcategory_id: '',
        is_active: true as boolean,
    });

    // Update subcategories when category changes
    useEffect(() => {
        if (selectedCategory) {
            const filtered = subcategories.filter(
                sub => sub.category_id.toString() === selectedCategory
            );
            setAvailableSubcategories(filtered);
        } else {
            setAvailableSubcategories([]);
        }
    }, [selectedCategory, subcategories]);

    // Update form subcategories when filter category changes
    useEffect(() => {
        if (filterCategory) {
            setFilterSubcategory('');
        }
    }, [filterCategory]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        if (editingQuestion) {
            put(route('admin.hte-criteria.update', editingQuestion.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setShowForm(false);
                    setEditingQuestion(null);
                    reset();
                    setSelectedCategory('');
                },
            });
        } else {
            post(route('admin.hte-criteria.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    setShowForm(false);
                    reset();
                    setSelectedCategory('');
                },
            });
        }
    };

    const handleEdit = (question: HTEQuestion) => {
        setEditingQuestion(question);
        setSelectedCategory(question.subcategory.category_id.toString());
        setData({
            question: question.question,
            subcategory_id: question.subcategory_id.toString(),
            is_active: question.is_active,
        });
        setShowForm(true);
    };

    const handleArchive = (questionId: number) => {
        if (confirm('Are you sure you want to archive this question?')) {
            patch(route('admin.hte-criteria.archive', questionId), {
                preserveScroll: true,
            });
        }
    };

    const handleRestore = (questionId: number) => {
        if (confirm('Are you sure you want to restore this question?')) {
            patch(route('admin.hte-criteria.restore', questionId), {
                preserveScroll: true,
            });
        }
    };

    const handleSearch = () => {
        router.get(
            route('admin.hte-criteria'),
            {
                search: searchTerm,
                category_id: filterCategory,
                subcategory_id: filterSubcategory,
                status: showArchived ? 'archived' : 'active',
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const handleClearFilters = () => {
        setSearchTerm('');
        setFilterCategory('');
        setFilterSubcategory('');
        setShowArchived(false);
        router.get(route('admin.hte-criteria'), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleCancel = () => {
        setShowForm(false);
        setEditingQuestion(null);
        reset();
        setSelectedCategory('');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HTE Criteria Management" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">HTE Criteria Management</h1>
                        <p className="text-muted-foreground">
                            Manage questions for HTE assessment criteria (Likert scale 1-5)
                        </p>
                    </div>
                    <Button onClick={() => setShowForm(true)} disabled={showForm}>
                        <PlusIcon className="mr-2 h-4 w-4" />
                        Add Question
                    </Button>
                </div>

                {/* Flash Messages */}
                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-4">
                        <p className="text-sm font-medium text-green-800">{flash.success}</p>
                    </div>
                )}
                {flash?.error && (
                    <div className="rounded-md bg-red-50 p-4">
                        <p className="text-sm font-medium text-red-800">{flash.error}</p>
                    </div>
                )}

                {/* Add/Edit Form */}
                {showForm && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{editingQuestion ? 'Edit Question' : 'Add New Question'}</CardTitle>
                            <CardDescription>
                                {editingQuestion
                                    ? 'Update the HTE criteria question details'
                                    : 'Create a new HTE criteria question for the assessment'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <Label htmlFor="category">Category *</Label>
                                        <Select
                                            value={selectedCategory || undefined}
                                            onValueChange={setSelectedCategory}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map(category => (
                                                    <SelectItem key={category.id} value={category.id.toString()}>
                                                        {category.category_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.subcategory_id && (
                                            <p className="text-sm text-red-500">{errors.subcategory_id}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="subcategory_id">Subcategory *</Label>
                                        <Select
                                            value={data.subcategory_id || undefined}
                                            onValueChange={value => setData('subcategory_id', value)}
                                            disabled={!selectedCategory}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select subcategory" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {availableSubcategories.map(subcategory => (
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

                                <div className="space-y-2">
                                    <Label htmlFor="question">Question Text *</Label>
                                    <Textarea
                                        id="question"
                                        value={data.question}
                                        onChange={e => setData('question', e.target.value)}
                                        placeholder="Enter the HTE criteria question..."
                                        rows={4}
                                        className="resize-none"
                                    />
                                    {errors.question && (
                                        <p className="text-sm text-red-500">{errors.question}</p>
                                    )}
                                </div>

                                <div className="flex justify-end gap-2">
                                    <Button type="button" variant="outline" onClick={handleCancel}>
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Saving...' : editingQuestion ? 'Update' : 'Create'}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <CardTitle>Filters</CardTitle>
                            <Button variant="ghost" size="sm" onClick={handleClearFilters}>
                                <XIcon className="mr-2 h-4 w-4" />
                                Clear All
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-4">
                            <div className="space-y-2">
                                <Label htmlFor="search">Search</Label>
                                <Input
                                    id="search"
                                    placeholder="Search questions..."
                                    value={searchTerm}
                                    onChange={e => setSearchTerm(e.target.value)}
                                    onKeyDown={e => e.key === 'Enter' && handleSearch()}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="filter-category">Category</Label>
                                <div className="flex gap-2">
                                    <Select value={filterCategory || undefined} onValueChange={setFilterCategory}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All categories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map(category => (
                                                <SelectItem key={category.id} value={category.id.toString()}>
                                                    {category.category_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {filterCategory && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => setFilterCategory('')}
                                        >
                                            <XIcon className="h-4 w-4" />
                                        </Button>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="filter-subcategory">Subcategory</Label>
                                <div className="flex gap-2">
                                    <Select
                                        value={filterSubcategory || undefined}
                                        onValueChange={setFilterSubcategory}
                                        disabled={!filterCategory}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="All subcategories" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {subcategories
                                                .filter(sub =>
                                                    !filterCategory || sub.category_id.toString() === filterCategory
                                                )
                                                .map(subcategory => (
                                                    <SelectItem key={subcategory.id} value={subcategory.id.toString()}>
                                                        {subcategory.subcategory_name}
                                                    </SelectItem>
                                                ))}
                                        </SelectContent>
                                    </Select>
                                    {filterSubcategory && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => setFilterSubcategory('')}
                                        >
                                            <XIcon className="h-4 w-4" />
                                        </Button>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select
                                    value={showArchived ? 'archived' : 'active'}
                                    onValueChange={value => setShowArchived(value === 'archived')}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="archived">Archived</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="mt-4 flex justify-end">
                            <Button onClick={handleSearch}>
                                <SearchIcon className="mr-2 h-4 w-4" />
                                Apply Filters
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                {/* Questions Table */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Questions</CardTitle>
                                <CardDescription>
                                    Total: {questions.total} questions
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-[50px]">#</TableHead>
                                        <TableHead>Question</TableHead>
                                        <TableHead>Category</TableHead>
                                        <TableHead>Subcategory</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {questions.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="text-center text-muted-foreground">
                                                No questions found
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        questions.data.map((question, index) => (
                                            <TableRow key={question.id}>
                                                <TableCell>
                                                    {(questions.current_page - 1) * questions.per_page + index + 1}
                                                </TableCell>
                                                <TableCell className="max-w-md">
                                                    <div className="line-clamp-2">{question.question}</div>
                                                </TableCell>
                                                <TableCell>{question.subcategory.category.category_name}</TableCell>
                                                <TableCell>{question.subcategory.subcategory_name}</TableCell>
                                                <TableCell>
                                                    <Badge variant={question.is_active ? 'default' : 'secondary'}>
                                                        {question.is_active ? 'Active' : 'Archived'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-2">
                                                        {question.is_active ? (
                                                            <>
                                                                <Tooltip>
                                                                    <TooltipTrigger asChild>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            onClick={() => handleEdit(question)}
                                                                        >
                                                                            <EditIcon className="h-4 w-4" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>Edit</TooltipContent>
                                                                </Tooltip>
                                                                <Tooltip>
                                                                    <TooltipTrigger asChild>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="icon"
                                                                            onClick={() => handleArchive(question.id)}
                                                                        >
                                                                            <ArchiveIcon className="h-4 w-4" />
                                                                        </Button>
                                                                    </TooltipTrigger>
                                                                    <TooltipContent>Archive</TooltipContent>
                                                                </Tooltip>
                                                            </>
                                                        ) : (
                                                            <Tooltip>
                                                                <TooltipTrigger asChild>
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="icon"
                                                                        onClick={() => handleRestore(question.id)}
                                                                    >
                                                                        <RotateCcwIcon className="h-4 w-4" />
                                                                    </Button>
                                                                </TooltipTrigger>
                                                                <TooltipContent>Restore</TooltipContent>
                                                            </Tooltip>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {questions.last_page > 1 && (
                            <div className="mt-4 flex items-center justify-between">
                                <div className="text-sm text-muted-foreground">
                                    Showing {(questions.current_page - 1) * questions.per_page + 1} to{' '}
                                    {Math.min(questions.current_page * questions.per_page, questions.total)} of{' '}
                                    {questions.total} results
                                </div>
                                <div className="flex gap-2">
                                    {questions.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get(route('admin.hte-criteria', {
                                                ...filters,
                                                page: questions.current_page - 1,
                                            }))}
                                        >
                                            Previous
                                        </Button>
                                    )}
                                    {questions.current_page < questions.last_page && (
                                        <Button
                                            variant="outline"
                                            onClick={() => router.get(route('admin.hte-criteria', {
                                                ...filters,
                                                page: questions.current_page + 1,
                                            }))}
                                        >
                                            Next
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

