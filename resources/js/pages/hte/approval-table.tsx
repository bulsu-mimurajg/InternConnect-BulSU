import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CheckCircle, XCircle, User, GraduationCap, Star } from 'lucide-react';
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
    match_id: number;
}

interface Internship {
    id: number;
    position_title: string;
    department: string;
    slot_count: number;
}

interface Props {
    internships: Internship[];
    selectedInternshipId: number | null;
    studentsForApproval: Student[];
}

export default function ApprovalTable({ internships, selectedInternshipId, studentsForApproval }: Props) {
    const [selectedInternship, setSelectedInternship] = useState<number | null>(selectedInternshipId);
    const [loading, setLoading] = useState<Record<number, boolean>>({});

    const handleInternshipChange = (internshipId: string) => {
        const id = parseInt(internshipId);
        setSelectedInternship(id);
        router.get(route('hte.approval-table'), { internship_id: id }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleApprove = async (student: Student) => {
        if (!selectedInternship) return;

        setLoading(prev => ({ ...prev, [student.id]: true }));

        try {
            const response = await fetch(route('hte.approve-student', student.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    internship_id: selectedInternship,
                    compatibility_score: student.compatibility_score,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                toast.success('Student approved successfully!');
                // Refresh the page to update the list
                router.reload();
            } else {
                toast.error(data.error || 'Failed to approve student');
            }
        } catch (error) {
            toast.error('An error occurred while approving the student');
        } finally {
            setLoading(prev => ({ ...prev, [student.id]: false }));
        }
    };

    const handleReject = async (student: Student) => {
        if (!selectedInternship) return;

        setLoading(prev => ({ ...prev, [student.id]: true }));

        try {
            const response = await fetch(route('hte.reject-student', student.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    internship_id: selectedInternship,
                }),
            });

            const data = await response.json();

            if (response.ok) {
                toast.success('Student rejected successfully!');
                // Refresh the page to update the list
                router.reload();
            } else {
                toast.error(data.error || 'Failed to reject student');
            }
        } catch (error) {
            toast.error('An error occurred while rejecting the student');
        } finally {
            setLoading(prev => ({ ...prev, [student.id]: false }));
        }
    };

    const getScoreBadgeVariant = (score: number) => {
        if (score >= 80) return 'default';
        if (score >= 70) return 'secondary';
        if (score >= 60) return 'outline';
        return 'destructive';
    };

    const getScoreLabel = (score: number) => {
        if (score >= 80) return 'Excellent';
        if (score >= 70) return 'Good';
        if (score >= 60) return 'Fair';
        return 'Poor';
    };

    const selectedInternshipData = internships.find(i => i.id === selectedInternship);

    return (
        <AppLayout>
            <Head title="Student Approval" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Student Approval</h1>
                        <p className="text-muted-foreground">
                            Review and approve students for your internship positions
                        </p>
                    </div>
                    <Link href={route('hte.dashboard')}>
                        <Button variant="outline">
                            Back to Dashboard
                        </Button>
                    </Link>
                </div>

                {/* Internship Selection */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <GraduationCap className="h-5 w-5" />
                            Select Internship
                        </CardTitle>
                        <CardDescription>
                            Choose which internship position to review students for
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Select value={selectedInternship?.toString() || ''} onValueChange={handleInternshipChange}>
                            <SelectTrigger className="w-full">
                                <SelectValue placeholder="Select an internship" />
                            </SelectTrigger>
                            <SelectContent>
                                {internships.map((internship) => (
                                    <SelectItem key={internship.id} value={internship.id.toString()}>
                                        {internship.position_title} - {internship.department}
                                        <span className="ml-2 text-sm text-muted-foreground">
                                            ({internship.slot_count} slots)
                                        </span>
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                {/* Students Table */}
                {selectedInternship && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <User className="h-5 w-5" />
                                Students for Approval
                                {selectedInternshipData && (
                                    <span className="text-sm font-normal text-muted-foreground">
                                        - {selectedInternshipData.position_title}
                                    </span>
                                )}
                            </CardTitle>
                            <CardDescription>
                                Students whose highest compatibility score matches with this internship
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {studentsForApproval.length === 0 ? (
                                <div className="text-center py-8">
                                    <User className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                                    <h3 className="text-lg font-semibold mb-2">No Students Available</h3>
                                    <p className="text-muted-foreground">
                                        There are no students whose highest compatibility score matches with this internship.
                                    </p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b border-gray-200">
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Student Number</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Specialization</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Compatibility Score</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Rank</th>
                                                <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {studentsForApproval.map((student) => (
                                                <tr key={student.id} className="border-b border-gray-100">
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center gap-2">
                                                            <User className="h-4 w-4 text-muted-foreground" />
                                                            <div>
                                                                <div className="font-medium">
                                                                    {student.first_name} {student.middle_name} {student.last_name}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4 font-mono text-sm">
                                                        {student.student_number}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {student.specialization}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center gap-2">
                                                            <Badge variant={getScoreBadgeVariant(student.compatibility_score)}>
                                                                {student.compatibility_score}%
                                                            </Badge>
                                                            <span className="text-sm text-muted-foreground">
                                                                {getScoreLabel(student.compatibility_score)}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center gap-1">
                                                            <Star className="h-4 w-4 text-yellow-500" />
                                                            <span className="font-medium">#{student.rank}</span>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4 text-right">
                                                        <div className="flex items-center justify-end gap-2">
                                                            <Button
                                                                size="sm"
                                                                onClick={() => handleApprove(student)}
                                                                disabled={loading[student.id]}
                                                                className="bg-green-600 hover:bg-green-700"
                                                            >
                                                                <CheckCircle className="h-4 w-4 mr-1" />
                                                                Approve
                                                            </Button>
                                                            <Button
                                                                size="sm"
                                                                variant="destructive"
                                                                onClick={() => handleReject(student)}
                                                                disabled={loading[student.id]}
                                                            >
                                                                <XCircle className="h-4 w-4 mr-1" />
                                                                Reject
                                                            </Button>
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
                )}
            </div>
        </AppLayout>
    );
}
