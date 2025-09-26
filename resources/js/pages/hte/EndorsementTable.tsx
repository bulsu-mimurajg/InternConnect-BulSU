import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { CheckCircle, XCircle, User, GraduationCap, Star, Building2, Briefcase, Target, AlertTriangle, Info } from 'lucide-react';
import { toast } from 'sonner';

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
}

export default function EndorsementTable({ endorsements = [], internships = [], hteId }: Props) {
    const [selectedInternship, setSelectedInternship] = useState<string>('all');
    const [loading, setLoading] = useState<Record<number, boolean>>({});
    const [selectedEndorsements, setSelectedEndorsements] = useState<Set<number>>(new Set());
    const [batchLoading, setBatchLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const [errorType, setErrorType] = useState<string | null>(null);
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    // Filter endorsements based on selected internship
    const filteredEndorsements = selectedInternship === 'all' 
        ? endorsements 
        : endorsements.filter(endorsement => endorsement.internship.id.toString() === selectedInternship);

    // Sort endorsements by compatibility score (highest first)
    const sortedEndorsements = [...filteredEndorsements].sort((a, b) => b.compatibility_score - a.compatibility_score);

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

    const handleApprove = async (endorsementId: number) => {
        setLoading(prev => ({ ...prev, [endorsementId]: true }));
        
        try {
            router.post(`/hte/approve-endorsement/${endorsementId}`, {}, {
                onSuccess: () => {
                    toast.success('Endorsement approved successfully');
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
            const allIds = sortedEndorsements.map(e => e.id);
            setSelectedEndorsements(new Set(allIds));
        } else {
            setSelectedEndorsements(new Set());
        }
    };

    const handleBatchApprove = async () => {
        if (selectedEndorsements.size === 0) return;

        setBatchLoading(true);
        setErrorMessage(null);
        setErrorType(null);

        try {
            const response = await fetch('/hte/batch-approve-endorsements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    endorsement_ids: Array.from(selectedEndorsements)
                }),
            });

            const result = await response.json();

            if (response.ok) {
                setErrorMessage(result.message);
                setErrorType('success');
                setSelectedEndorsements(new Set());
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
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

        setBatchLoading(true);
        setErrorMessage(null);
        setErrorType(null);

        try {
            const response = await fetch('/hte/batch-reject-endorsements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    endorsement_ids: Array.from(selectedEndorsements)
                }),
            });

            const result = await response.json();

            if (response.ok) {
                setErrorMessage(result.message);
                setErrorType('success');
                setSelectedEndorsements(new Set());
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
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

    return (
        <AppLayout>
            <Head title="Student Endorsements" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-6">
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

                <div className="flex flex-col gap-4">
                    <div>
                        <h1 className="text-2xl md:text-3xl font-bold tracking-tight">Student Endorsements</h1>
                        <p className="text-muted-foreground text-sm md:text-base">
                            Review and approve students who have been endorsed by the admin for your internships.
                        </p>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>Filter endorsements by internship position</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div className="flex flex-col sm:flex-row sm:items-center gap-2">
                                <label htmlFor="internship-filter" className="text-sm font-medium whitespace-nowrap">
                                    Internship Position:
                                </label>
                                <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                                    <SelectTrigger className="w-full sm:w-[250px]">
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

                {/* Batch Actions */}
                {selectedEndorsements.size > 0 && (
                    <Card className="border-l-4 border-l-blue-500 bg-blue-50 dark:bg-blue-900/20">
                        <CardContent className="p-4">
                            <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div className="flex items-start gap-3 flex-1">
                                    <Target className="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <div className="flex-1">
                                        <div className="font-medium text-blue-800 dark:text-blue-200">
                                            Batch Actions ({selectedEndorsements.size} endorsement{selectedEndorsements.size !== 1 ? 's' : ''} selected)
                                        </div>
                                        <div className="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                            You can approve or reject multiple students at once. Approved students will be placed in your internships.
                                        </div>
                                    </div>
                                </div>
                                <div className="flex flex-col sm:flex-row gap-2">
                                    <Button
                                        variant="default"
                                        onClick={handleBatchApprove}
                                        disabled={batchLoading}
                                        className="bg-green-600 hover:bg-green-700 text-sm"
                                    >
                                        <CheckCircle className="h-4 w-4 mr-2" />
                                        Approve All ({selectedEndorsements.size})
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        onClick={handleBatchReject}
                                        disabled={batchLoading}
                                        className="text-sm"
                                    >
                                        <XCircle className="h-4 w-4 mr-2" />
                                        Reject All ({selectedEndorsements.size})
                                    </Button>
                                    <Button
                                        variant="outline"
                                        onClick={() => setSelectedEndorsements(new Set())}
                                        className="text-sm"
                                    >
                                        Clear Selection
                                    </Button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
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
                                <div className="block lg:hidden space-y-4">
                                    {sortedEndorsements.map((endorsement) => (
                                        <Card key={endorsement.id} className="p-4">
                                            <div className="flex items-start justify-between mb-3">
                                                <div className="flex items-center gap-3">
                                                    <Checkbox
                                                        checked={selectedEndorsements.has(endorsement.id)}
                                                        onCheckedChange={(checked) => handleSelectEndorsement(endorsement.id, checked as boolean)}
                                                    />
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-5 w-5 text-muted-foreground" />
                                                        <div>
                                                            <div className="font-medium text-base">
                                                                {endorsement.student.first_name} {endorsement.student.middle_name} {endorsement.student.last_name}
                                                            </div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {endorsement.student.student_number}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Star className="h-4 w-4 text-yellow-500 fill-current" />
                                                    <span className="font-medium text-lg">{endorsement.compatibility_score}%</span>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-1 gap-3 mb-4">
                                                <div className="flex items-center gap-2">
                                                    <Briefcase className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                                    <div>
                                                        <div className="font-medium text-sm">{endorsement.internship.position}</div>
                                                        <div className="text-xs text-muted-foreground">{endorsement.internship.department}</div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Building2 className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                                    <span className="text-sm">{endorsement.internship.company_name}</span>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <GraduationCap className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                                    <span className="text-sm">{endorsement.student.specialization}</span>
                                                </div>
                                            </div>

                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-2">
                                                    <Badge className={`text-xs ${getScoreColor(endorsement.compatibility_score)}`}>
                                                        {getScoreLabel(endorsement.compatibility_score)}
                                                    </Badge>
                                                    <span className="text-xs text-muted-foreground">
                                                        {new Date(endorsement.endorsement_date).toLocaleDateString()}
                                                    </span>
                                                </div>
                                                <div className="flex gap-2">
                                                    <Button
                                                        size="sm"
                                                        variant="default"
                                                        onClick={() => handleApprove(endorsement.id)}
                                                        disabled={loading[endorsement.id] || batchLoading}
                                                        className="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 h-8"
                                                    >
                                                        <CheckCircle className="h-3 w-3 mr-1" />
                                                        Approve
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="destructive"
                                                        onClick={() => handleReject(endorsement.id)}
                                                        disabled={loading[endorsement.id] || batchLoading}
                                                        className="text-xs px-3 py-1 h-8"
                                                    >
                                                        <XCircle className="h-3 w-3 mr-1" />
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
                                                        checked={selectedEndorsements.size === sortedEndorsements.length && sortedEndorsements.length > 0}
                                                        onCheckedChange={handleSelectAll}
                                                    />
                                                </th>
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
                                            {sortedEndorsements.map((endorsement) => (
                                            <tr key={endorsement.id} className="border-b border-gray-100 hover:bg-muted/50 transition-colors">
                                                <td className="py-3 px-2">
                                                    <Checkbox
                                                        checked={selectedEndorsements.has(endorsement.id)}
                                                        onCheckedChange={(checked) => handleSelectEndorsement(endorsement.id, checked as boolean)}
                                                    />
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                                        <div className="min-w-0">
                                                            <div className="font-medium text-sm truncate">
                                                                {endorsement.student.first_name} {endorsement.student.middle_name} {endorsement.student.last_name}
                                                            </div>
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
                                                        <div className="flex items-center gap-1">
                                                            <Briefcase className="h-3 w-3 text-muted-foreground flex-shrink-0" />
                                                            <span className="font-medium text-xs truncate block">{endorsement.internship.position}</span>
                                                        </div>
                                                        <div className="text-xs text-muted-foreground truncate">
                                                            {endorsement.internship.company_name}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-2">
                                                    <div className="flex flex-col gap-1">
                                                        <div className="flex items-center gap-1">
                                                            <Star className="h-3 w-3 text-yellow-500 fill-current flex-shrink-0" />
                                                            <span className="font-medium text-xs">{endorsement.compatibility_score}%</span>
                                                        </div>
                                                        <Badge className={`text-xs ${getScoreColor(endorsement.compatibility_score)} hidden xl:inline-flex w-fit`}>
                                                            {getScoreLabel(endorsement.compatibility_score)}
                                                        </Badge>
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
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}