import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CheckCircle, XCircle, User, GraduationCap, Star, Building2, Briefcase } from 'lucide-react';
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
    endorsements: Endorsement[];
    internships: Internship[];
    hteId: number;
}

export default function EndorsementTable({ endorsements, internships, hteId }: Props) {
    const [selectedInternship, setSelectedInternship] = useState<string>('all');
    const [loading, setLoading] = useState<Record<number, boolean>>({});
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;

    // Filter endorsements by selected internship
    const filteredEndorsements = selectedInternship === 'all' 
        ? endorsements 
        : endorsements.filter(endorsement => endorsement.internship.id === parseInt(selectedInternship));

    // Sort by company name, position, department
    const sortedEndorsements = [...filteredEndorsements].sort((a, b) => {
        // First by company name
        if (a.internship.company_name !== b.internship.company_name) {
            return a.internship.company_name.localeCompare(b.internship.company_name);
        }
        // Then by position
        if (a.internship.position !== b.internship.position) {
            return a.internship.position.localeCompare(b.internship.position);
        }
        // Finally by department
        return a.internship.department.localeCompare(b.internship.department);
    });

    const handleApprove = async (endorsement: Endorsement) => {
        setLoading(prev => ({ ...prev, [endorsement.id]: true }));
        
        try {
            router.post(route('hte.approve-endorsement', endorsement.id), {}, {
                onSuccess: () => {
                    toast.success('Student approved successfully!');
                    // The page will automatically refresh due to the redirect
                },
                onError: (errors) => {
                    console.error('Approval failed:', errors);
                    toast.error('Failed to approve student. Please try again.');
                },
                onFinish: () => {
                    setLoading(prev => ({ ...prev, [endorsement.id]: false }));
                }
            });
        } catch (error) {
            console.error('Approval failed:', error);
            toast.error('Failed to approve student. Please try again.');
            setLoading(prev => ({ ...prev, [endorsement.id]: false }));
        }
    };

    const handleReject = async (endorsement: Endorsement) => {
        setLoading(prev => ({ ...prev, [endorsement.id]: true }));
        
        try {
            router.post(route('hte.reject-endorsement', endorsement.id), {}, {
                onSuccess: () => {
                    toast.success('Student rejected. They will be moved to their next highest compatibility HTE.');
                    // The page will automatically refresh due to the redirect
                },
                onError: (errors) => {
                    console.error('Rejection failed:', errors);
                    toast.error('Failed to reject student. Please try again.');
                },
                onFinish: () => {
                    setLoading(prev => ({ ...prev, [endorsement.id]: false }));
                }
            });
        } catch (error) {
            console.error('Rejection failed:', error);
            toast.error('Failed to reject student. Please try again.');
            setLoading(prev => ({ ...prev, [endorsement.id]: false }));
        }
    };

    const getScoreBadgeVariant = (score: number) => {
        if (score >= 80) return 'default';
        if (score >= 60) return 'secondary';
        return 'destructive';
    };

    const getScoreLabel = (score: number) => {
        if (score >= 90) return 'Excellent';
        if (score >= 80) return 'Very Good';
        if (score >= 70) return 'Good';
        if (score >= 60) return 'Fair';
        return 'Poor';
    };

    return (
        <AppLayout>
            <Head title="Student Endorsements" />

            <div className="space-y-6">
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
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Student Endorsements</h1>
                        <p className="text-muted-foreground">
                            Review and approve students who have been endorsed by the admin for your internships.
                        </p>
                    </div>
                </div>

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <CardTitle>Filter by Internship</CardTitle>
                        <CardDescription>
                            Select a specific internship to view endorsed students for that position.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex items-center gap-4">
                            <Select value={selectedInternship} onValueChange={setSelectedInternship}>
                                <SelectTrigger className="w-[300px]">
                                    <SelectValue placeholder="Select an internship" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Internships</SelectItem>
                                    {internships.map((internship) => (
                                        <SelectItem key={internship.id} value={internship.id.toString()}>
                                            {internship.position} - {internship.department}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Endorsements Table */}
                {sortedEndorsements.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <GraduationCap className="h-12 w-12 text-muted-foreground mb-4" />
                            <h3 className="text-lg font-semibold mb-2">No Endorsements Available</h3>
                            <p className="text-muted-foreground text-center">
                                There are no students endorsed for the selected internship.
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <Card>
                        <CardHeader>
                            <CardTitle>Endorsed Students</CardTitle>
                            <CardDescription>
                                Students endorsed by admin for your internships. Approve to place them or reject to move them to their next highest compatibility HTE.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b border-gray-200">
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Student Number</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Specialization</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Company</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Position</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Department</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Compatibility</th>
                                            <th className="text-left py-3 px-4 font-semibold text-sm">Endorsed Date</th>
                                            <th className="text-right py-3 px-4 font-semibold text-sm">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {sortedEndorsements.map((endorsement) => (
                                            <tr key={endorsement.id} className="border-b border-gray-100">
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <User className="h-4 w-4 text-muted-foreground" />
                                                        <div>
                                                            <div className="font-medium">
                                                                {endorsement.student.first_name} {endorsement.student.middle_name} {endorsement.student.last_name}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4 font-mono text-sm">
                                                    {endorsement.student.student_number}
                                                </td>
                                                <td className="py-3 px-4">
                                                    {endorsement.student.specialization}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <Building2 className="h-4 w-4 text-muted-foreground" />
                                                        <span className="font-medium">{endorsement.internship.company_name}</span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <Briefcase className="h-4 w-4 text-muted-foreground" />
                                                        <span>{endorsement.internship.position}</span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4">
                                                    {endorsement.internship.department}
                                                </td>
                                                <td className="py-3 px-4">
                                                    <div className="flex items-center gap-2">
                                                        <Badge variant={getScoreBadgeVariant(endorsement.compatibility_score)}>
                                                            {endorsement.compatibility_score}%
                                                        </Badge>
                                                        <span className="text-sm text-muted-foreground">
                                                            {getScoreLabel(endorsement.compatibility_score)}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td className="py-3 px-4 text-sm text-muted-foreground">
                                                    {new Date(endorsement.endorsement_date).toLocaleDateString()}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            onClick={() => handleApprove(endorsement)}
                                                            disabled={loading[endorsement.id]}
                                                            className="bg-green-600 hover:bg-green-700"
                                                        >
                                                            <CheckCircle className="h-4 w-4 mr-1" />
                                                            Approve
                                                        </Button>
                                                        <Button
                                                            size="sm"
                                                            variant="destructive"
                                                            onClick={() => handleReject(endorsement)}
                                                            disabled={loading[endorsement.id]}
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
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
