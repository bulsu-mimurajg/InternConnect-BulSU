import React, { useState, useEffect, useMemo} from 'react';
import { Head, router, usePage, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { BatchActions, BatchActionPresets } from '@/components/ui/batch-actions';
import { CheckCircle, XCircle, User, GraduationCap, AlertCircle, FilterIcon, Building2 } from 'lucide-react';
import { toast } from 'sonner';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';

interface Student {
    id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    middle_name?: string;
    specialization: string;
    compatibility_score: number;
    rank: number;
    endorsement_date: string;
}

interface Internship {
    id: number;
    position: string;
    department: string;
    company_name: string;
    hte_id: number;
}

interface Endorsement {
    id: number;
    student: Student;
    internship: Internship;
    compatibility_score: number;
    endorsement_date: string;
}

interface Props {
    endorsements?: Endorsement[];
    internships?: Internship[];
    hteId: number;
    showSubmissionPrompt: boolean;
    csrf_token?: string;
}

export default function EndorsementTable({ endorsements = [], internships = [], showSubmissionPrompt, csrf_token }: Props) {
    const [selectedInternship, setSelectedInternship] = useState<string>('all');
    const [loading, setLoading] = useState<Record<number, boolean>>({});
    const [selectedEndorsements, setSelectedEndorsements] = useState<Set<number>>(new Set());
    const [highlightedStudentId, setHighlightedStudentId] = useState<number | null>(null);
    const [batchLoading, setBatchLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const [errorType, setErrorType] = useState<string | null>(null);
    const [showFilters, setShowFilters] = useState(false);
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    // CSRF Token Management
    const getFreshCsrfToken = () => {
        return csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    // Function to refresh CSRF token by making a request to get a new one
    const refreshCsrfToken = async () => {
        try {
            const response = await fetch('/csrf-token', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                const data = await response.json();
                // Update the meta tag with new token
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', data.token);
                }
                return data.token;
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
        return getFreshCsrfToken();
    };

    // Ensure CSRF token is set in meta tag when component mounts
    useEffect(() => {
        if (csrf_token) {
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.setAttribute('content', csrf_token);
            }
        }
    }, [csrf_token]);

    // Handle student highlighting from notification clicks
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const highlightStudent = urlParams.get('highlightStudent');
        const highlightDuration = parseInt(urlParams.get('highlightDuration') || '1500');

        console.log('URL params:', { highlightStudent, highlightDuration });
        console.log('Current URL:', window.location.href);
        console.log('Available endorsements:', endorsements.map(e => ({ id: e.student.id, name: `${e.student.first_name} ${e.student.last_name}` })));

        if (highlightStudent) {
            const studentId = parseInt(highlightStudent);
            console.log('Setting highlighted student ID:', studentId, 'Type:', typeof studentId);
            setHighlightedStudentId(studentId);

            // Remove highlight after specified duration
            const timer = setTimeout(() => {
                console.log('Removing highlight for student ID:', studentId);
                setHighlightedStudentId(null);
                // Clean up URL parameters
                const newUrl = new URL(window.location.href);
                newUrl.searchParams.delete('highlightStudent');
                newUrl.searchParams.delete('highlightDuration');
                window.history.replaceState({}, '', newUrl.toString());
            }, highlightDuration);

            return () => clearTimeout(timer);
        }
    }, [endorsements]);

    // Filter endorsements based on selected internship
    const filteredEndorsements = selectedInternship === 'all'
        ? endorsements
        : endorsements.filter(endorsement => endorsement.internship.id.toString() === selectedInternship);

    // Sort endorsements by compatibility score (highest first)
    const sortedEndorsements = [...filteredEndorsements].sort((a, b) => b.compatibility_score - a.compatibility_score);

    // Create a stable reset trigger for pagination
    const resetTrigger = useMemo(() =>
        selectedInternship,
        [selectedInternship]
    );

    // Pagination hook with auto-reset on filter changes
    const endorsementPagination = usePagination({
        data: sortedEndorsements,
        itemsPerPage: 10,
        resetTrigger: resetTrigger,
    });

    const getScoreLabel = (score: number) => {
        if (score >= 90) return 'Excellent';
        if (score >= 80) return 'Very Good';
        if (score >= 70) return 'Good';
        if (score >= 60) return 'Fair';
        return 'Poor';
    };

    const getScoreColor = (score: number) => {
        if (score >= 90) return 'bg-green-100 text-green-800';
        if (score >= 80) return 'bg-blue-100 text-blue-800';
        if (score >= 70) return 'bg-yellow-100 text-yellow-800';
        if (score >= 60) return 'bg-orange-100 text-orange-800';
        return 'bg-red-100 text-red-800';
    };

    const getGradePoint = (score: number) => {
        if (score >= 96.50) return '1.00';
        if (score >= 93.50) return '1.25';
        if (score >= 90.50) return '1.50';
        if (score >= 87.50) return '1.75';
        if (score >= 84.50) return '2.00';
        if (score >= 81.50) return '2.25';
        if (score >= 78.50) return '2.50';
        if (score >= 75.50) return '2.75';
        if (score >= 75.00) return '3.00';
        return '5.00';
    };

    const handleApprove = async (endorsementId: number) => {
        setLoading(prev => ({ ...prev, [endorsementId]: true }));

        try {
            router.post(`/hte/approve-endorsement/${endorsementId}`, {}, {
                onSuccess: () => {
                    toast.success('Endorsement approved successfully');
                    // Refresh the page to show updated data
                    router.reload();
                },
                onError: (errors) => {
                    console.error('Error approving endorsement:', errors);
                    toast.error('Failed to approve endorsement');
                },
                onFinish: () => {
                    setLoading(prev => ({ ...prev, [endorsementId]: false }));
                }
            });
        } catch (error) {
            console.error('Error:', error);
            toast.error('An error occurred while approving the endorsement');
            setLoading(prev => ({ ...prev, [endorsementId]: false }));
        }
    };

    const handleReject = async (endorsementId: number) => {
        setLoading(prev => ({ ...prev, [endorsementId]: true }));

        try {
            router.post(`/hte/reject-endorsement/${endorsementId}`, {}, {
                onSuccess: () => {
                    toast.success('Endorsement rejected successfully');
                    // Refresh the page to show updated data (including any automatic fallback)
                    router.reload();
                },
                onError: (errors) => {
                    console.error('Error rejecting endorsement:', errors);
                    toast.error('Failed to reject endorsement');
                },
                onFinish: () => {
                    setLoading(prev => ({ ...prev, [endorsementId]: false }));
                }
            });
        } catch (error) {
            console.error('Error:', error);
            toast.error('An error occurred while rejecting the endorsement');
            setLoading(prev => ({ ...prev, [endorsementId]: false }));
        }
    };

    // Batch selection handlers
    const handleSelectEndorsement = (endorsementId: number, checked: boolean) => {
        const newSelected = new Set(selectedEndorsements);
        if (checked) {
            newSelected.add(endorsementId);
        } else {
            newSelected.delete(endorsementId);
        }
        setSelectedEndorsements(newSelected);
    };

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            const allIds = endorsementPagination.paginatedData.map(e => e.id);
            setSelectedEndorsements(new Set(allIds));
        } else {
            setSelectedEndorsements(new Set());
        }
    };

    const handleBatchApprove = async () => {
        if (selectedEndorsements.size === 0) return;

        const csrfToken = getFreshCsrfToken();

        // Check if CSRF token exists (basic auth check)
        if (!csrfToken) {
            setErrorMessage('Authentication error: CSRF token not found. Please refresh the page and try again.');
            setErrorType('error');
            return;
        }

        setBatchLoading(true);
        setErrorMessage(null);
        setErrorType(null);

        try {
            const response = await fetch('/hte/batch-approve-endorsements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    endorsement_ids: Array.from(selectedEndorsements)
                }),
            });

            // Handle CSRF token mismatch
            if (response.status === 419) {
                const newCsrfToken = await refreshCsrfToken();

                // Retry the request with fresh token
                const retryResponse = await fetch('/hte/batch-approve-endorsements', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': newCsrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        endorsement_ids: Array.from(selectedEndorsements)
                    }),
                });

                if (retryResponse.ok) {
                    const result = await retryResponse.json();
                    setErrorMessage(result.message || 'Batch approval completed successfully!');
                    setErrorType('success');
                    setSelectedEndorsements(new Set());
                    setTimeout(() => {
                        router.reload();
                    }, 1500);
                } else {
                    const retryResult = await retryResponse.json();
                    setErrorMessage(retryResult.message || 'An error occurred during batch approval');
                    setErrorType('error');
                }
            } else if (response.ok) {
                const result = await response.json();
                setErrorMessage(result.message || 'Batch approval completed successfully!');
                setErrorType('success');
                setSelectedEndorsements(new Set());
                setTimeout(() => {
                    router.reload();
                }, 1500);
            } else {
                const result = await response.json();
                setErrorMessage(result.message || 'An error occurred during batch approval');
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in batch approval:', error);
            setErrorMessage('An error occurred during batch approval');
            setErrorType('error');
        } finally {
            setBatchLoading(false);
        }
    };

    const handleBatchReject = async () => {
        if (selectedEndorsements.size === 0) return;

        const csrfToken = getFreshCsrfToken();

        // Check if CSRF token exists (basic auth check)
        if (!csrfToken) {
            setErrorMessage('Authentication error: CSRF token not found. Please refresh the page and try again.');
            setErrorType('error');
            return;
        }

        setBatchLoading(true);
        setErrorMessage(null);
        setErrorType(null);

        try {
            const response = await fetch('/hte/batch-reject-endorsements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    endorsement_ids: Array.from(selectedEndorsements)
                }),
            });

            // Handle CSRF token mismatch
            if (response.status === 419) {
                const newCsrfToken = await refreshCsrfToken();

                // Retry the request with fresh token
                const retryResponse = await fetch('/hte/batch-reject-endorsements', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': newCsrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        endorsement_ids: Array.from(selectedEndorsements)
                    }),
                });

                if (retryResponse.ok) {
                    const result = await retryResponse.json();
                    setErrorMessage(result.message || 'Batch rejection completed successfully!');
                    setErrorType('success');
                    setSelectedEndorsements(new Set());
                    setTimeout(() => {
                        router.reload();
                    }, 1500);
                } else {
                    const retryResult = await retryResponse.json();
                    setErrorMessage(retryResult.message || 'An error occurred during batch rejection');
                    setErrorType('error');
                }
            } else if (response.ok) {
                const result = await response.json();
                setErrorMessage(result.message || 'Batch rejection completed successfully!');
                setErrorType('success');
                setSelectedEndorsements(new Set());
                setTimeout(() => {
                    router.reload();
                }, 1500);
            } else {
                const result = await response.json();
                setErrorMessage(result.message || 'An error occurred during batch rejection');
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in batch rejection:', error);
            setErrorMessage('An error occurred during batch rejection');
            setErrorType('error');
        } finally {
            setBatchLoading(false);
        }
    };

    // Show assessment prompt if not submitted
    if (showSubmissionPrompt) {
        return (
            <AppLayout>
                <Head title="Student Endorsements" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <Card>
                        <CardContent className="p-6">
                            <div className="text-center">
                                <AlertCircle className="mx-auto h-12 w-12 text-yellow-500 mb-4" />
                                <h2 className="text-xl font-semibold mb-2">Assessment Not Submitted</h2>
                                <p className="text-muted-foreground mb-4">
                                    You need to complete your assessment form to view student endorsements.
                                </p>
                                <Button asChild>
                                    <Link href="/form">
                                        Take Assessment
                                    </Link>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout>
            <Head title="Student Endorsements" />

            <div className="flex h-full flex-1 flex-col gap-4 md:gap-6 rounded-xl p-4 md:p-6">
                {/* Flash Messages */}
                {flash?.success && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {flash.success}
                    </div>
                )}
                {flash?.error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {flash.error}
                    </div>
                )}

                {/* Error/Success Display */}
                {errorMessage && (
                    <Card className={`border-l-4 ${
                        errorType === 'success' ? 'border-l-green-500 bg-green-50 dark:bg-green-900/20' : 'border-l-red-500 bg-red-50 dark:bg-red-900/20'
                    }`}>
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between">
                                <div className="flex items-start gap-3 flex-1">
                                    {errorType === 'success' ? (
                                        <CheckCircle className="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" />
                                    ) : (
                                        <XCircle className="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" />
                                    )}
                                    <div className="flex-1">
                                        <div className={`font-medium ${
                                            errorType === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'
                                        }`}>
                                            {errorType === 'success' ? 'Success' : 'Error'}
                                        </div>
                                        <div className={`mt-1 text-sm ${
                                            errorType === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'
                                        }`}>
                                            {errorMessage}
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                        setErrorMessage(null);
                                        setErrorType(null);
                                    }}
                                    className="text-muted-foreground hover:text-foreground"
                                >
                                    <XCircle className="h-4 w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-white">Student Endorsements</h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-1">
                            Review and approve students who have been endorsed by the admin for your internships.
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
                    </div>
                </div>

                {/* Filters */}
                {showFilters && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FilterIcon className="h-5 w-5" />
                                Filters
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {/* Internship Filter (Full Width) */}
                                <div className="space-y-2">
                                    <label htmlFor="internship-filter" className="text-sm font-medium">
                                        Internship Position
                                    </label>
                                    <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select internship position" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Internships</SelectItem>
                                            {internships.map((internship) => (
                                                <SelectItem key={internship.id} value={internship.id.toString()}>
                                                    {internship.position}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Batch Actions */}
                {selectedEndorsements.size > 0 && (
                    <BatchActions
                        selectedCount={selectedEndorsements.size}
                        selectedLabel="endorsement"
                        description="You can approve or reject multiple students at once. Approved students will be placed in your internships."
                        actions={[
                            {
                                ...BatchActionPresets.endorse.approve,
                                label: `Approve All (${selectedEndorsements.size})`,
                                onClick: handleBatchApprove,
                                disabled: batchLoading
                            },
                            {
                                ...BatchActionPresets.endorse.reject,
                                label: `Reject All (${selectedEndorsements.size})`,
                                onClick: handleBatchReject,
                                disabled: batchLoading
                            }
                        ]}
                        onClearSelection={() => setSelectedEndorsements(new Set())}
                        isLoading={batchLoading}
                    />
                )}

                {/* Endorsements Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Endorsed Students</CardTitle>
                        <CardDescription>
                            {sortedEndorsements.length} student{sortedEndorsements.length !== 1 ? 's' : ''} endorsed for review
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {sortedEndorsements.length === 0 ? (
                            <div className="text-center py-8">
                                <User className="mx-auto h-12 w-12 text-gray-400" />
                                <h3 className="mt-2 text-sm font-medium text-gray-900">No endorsed students</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    {selectedInternship === 'all'
                                        ? 'No students have been endorsed for your internships yet.'
                                        : 'No students have been endorsed for the selected internship position.'
                                    }
                                </p>
                            </div>
                        ) : (
                            <>
                                {/* Mobile/Tablet Card View */}
                                <div className="block lg:hidden space-y-3 md:space-y-4">

                                    {endorsementPagination.paginatedData.map((endorsement) => (
                                        <Card
                                            key={endorsement.id}
                                            className={`p-4 md:p-6 transition-all duration-200 hover:shadow-md ${
                                                highlightedStudentId === endorsement.student.id
                                                    ? 'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                                    : ''
                                            }`}
                                        >
                                            {/* Header with checkbox, student info, and score */}
                                            <div className="flex items-start justify-between mb-4">
                                                <div className="flex items-start gap-3 flex-1">
                                                    <Checkbox
                                                        checked={selectedEndorsements.has(endorsement.id)}
                                                        onCheckedChange={(checked) => handleSelectEndorsement(endorsement.id, checked as boolean)}
                                                        className="mt-1"
                                                    />
                                                    <div className="flex-1 min-w-0">
                                                        <div className="font-semibold text-base md:text-lg text-foreground mb-1">
                                                            {endorsement.student.first_name} {endorsement.student.middle_name} {endorsement.student.last_name}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground font-mono">
                                                            {endorsement.student.student_number}
                                                        </div>
                                                        {endorsement.student.specialization && (
                                                            <div className="text-xs text-muted-foreground mt-1">
                                                                {endorsement.student.specialization}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="flex flex-col items-end gap-1 ml-2">
                                                    <span className="font-semibold text-sm md:text-base bg-primary/10 text-primary px-2 py-1 rounded-md">
                                                        {Math.round(endorsement.compatibility_score)}%
                                                    </span>
                                                    <span className="text-xs text-muted-foreground font-mono">
                                                        {getGradePoint(endorsement.compatibility_score)}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Internship Details */}
                                            <div className="bg-muted/50 rounded-lg p-3 md:p-4 mb-4">
                                                <div className="space-y-2">
                                                    <div>
                                                        <div className="font-medium text-sm md:text-base text-foreground">
                                                            {endorsement.internship.position}
                                                        </div>
                                                        <div className="text-xs md:text-sm text-muted-foreground">
                                                            {endorsement.internship.department}
                                                        </div>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                                        <span className="text-sm text-foreground">{endorsement.internship.company_name}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Footer with badges and action buttons */}
                                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <Badge className={`text-xs ${getScoreColor(endorsement.compatibility_score)}`}>
                                                        <GraduationCap className="h-3 w-3 mr-1" />
                                                        {getScoreLabel(endorsement.compatibility_score)}
                                                    </Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        {new Date(endorsement.endorsement_date).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <div className="flex gap-2">
                                                    <Button
                                                        size="default"
                                                        variant="default"
                                                        onClick={() => handleApprove(endorsement.id)}
                                                        disabled={loading[endorsement.id] || batchLoading}
                                                        className="bg-green-600 hover:bg-green-700 text-white flex-1 sm:flex-none min-w-[80px]"
                                                    >
                                                        <CheckCircle className="h-4 w-4 mr-2" />
                                                        Approve
                                                    </Button>
                                                    <Button
                                                        size="default"
                                                        variant="destructive"
                                                        onClick={() => handleReject(endorsement.id)}
                                                        disabled={loading[endorsement.id] || batchLoading}
                                                        className="flex-1 sm:flex-none min-w-[80px]"
                                                    >
                                                        <XCircle className="h-4 w-4 mr-2" />
                                                        Reject
                                                    </Button>
                                                </div>
                                            </div>
                                        </Card>
                                    ))}
                                </div>

                                {/* Desktop Table View */}
                                <div className="hidden lg:block overflow-x-auto">
                                    <table className="w-full min-w-[800px]">
                                        <thead>
                                            <tr className="border-b border-gray-200">
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-10">
                                                    <Checkbox
                                                        checked={selectedEndorsements.size === endorsementPagination.paginatedData.length && endorsementPagination.paginatedData.length > 0}
                                                        onCheckedChange={handleSelectAll}
                                                    />
                                                </th>
                                                <th className="text-center py-3 px-2 font-semibold text-sm w-16">#</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-48">Student</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden xl:table-cell w-32">Student Number</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden lg:table-cell w-40">Specialization</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-48">Position</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-24">Score</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm hidden xl:table-cell w-20">Date</th>
                                                <th className="text-left py-3 px-2 font-semibold text-sm w-28">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {endorsementPagination.paginatedData.map((endorsement, index) => (
                                            <tr key={endorsement.id} className="border-b border-gray-100 hover:bg-muted/50 transition-colors">
                                                <td className="py-3 px-2">
                                                    <Checkbox
                                                        checked={selectedEndorsements.has(endorsement.id)}
                                                        onCheckedChange={(checked) => handleSelectEndorsement(endorsement.id, checked as boolean)}
                                                    />
                                                </td>
                                                <td className="text-center py-3 px-2 font-mono text-sm text-muted-foreground">
                                                    {getRowNumber(endorsementPagination.currentPage, 10, index)}
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="min-w-0">
                                                        <div className="font-medium text-sm truncate">
                                                            {endorsement.student.first_name} {endorsement.student.middle_name} {endorsement.student.last_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2 font-mono text-xs hidden xl:table-cell">
                                                    <span className="truncate block">{endorsement.student.student_number}</span>
                                                </td>
                                                <td className="py-3 px-2 hidden lg:table-cell">
                                                    <span className="text-xs truncate block">{endorsement.student.specialization}</span>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="min-w-0">
                                                        <div className="font-medium text-xs truncate block">{endorsement.internship.position}</div>
                                                        <div className="text-xs text-muted-foreground truncate">
                                                            {endorsement.internship.company_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="text-xs font-medium">
                                                        {Math.round(endorsement.compatibility_score)}% | {getGradePoint(endorsement.compatibility_score)}
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2 text-xs text-muted-foreground hidden xl:table-cell">
                                                    {new Date(endorsement.endorsement_date).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="flex items-center gap-1">
                                                        <Button
                                                            size="sm"
                                                            variant="default"
                                                            onClick={() => handleApprove(endorsement.id)}
                                                            disabled={loading[endorsement.id] || batchLoading}
                                                            className="bg-green-600 hover:bg-green-700 text-white text-xs px-1 py-1 h-6 w-6"
                                                            title="Approve"
                                                        >
                                                            <CheckCircle className="h-3 w-3" />
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => handleReject(endorsement.id)}
                                                            disabled={loading[endorsement.id] || batchLoading}
                                                            className="text-xs px-1 py-1 h-6 w-6"
                                                            title="Reject"
                                                        >
                                                            <XCircle className="h-3 w-3" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        )}

                        {/* Pagination */}
                        {sortedEndorsements.length > 0 && (
                            <Pagination
                                currentPage={endorsementPagination.currentPage}
                                totalPages={endorsementPagination.totalPages}
                                onPageChange={endorsementPagination.handlePageChange}
                                showSummary={true}
                                totalItems={sortedEndorsements.length}
                                itemsPerPage={10}
                            />
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
