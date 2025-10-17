import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { BatchActions, BatchActionPresets } from '@/components/ui/batch-actions';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import SectionSwitcher from '@/components/SectionSwitcher';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { CheckIcon, UsersIcon, UserCheckIcon, RotateCcwIcon, UserXIcon, ClockIcon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student Verification',
        href: '/student-verification',
    },
];

interface Student {
    id: number;
    username: string;
    email: string;
    academe_accounts: Array<{
        section: {
            section_id: number;
            section_name: string;
        };
    }>;
    student?: {
        id: number;
        student_number: string;
        first_name: string;
        last_name: string;
        is_submit: boolean;
    };
    registration_data?: {
        first_name: string;
        last_name: string;
        middle_name?: string;
    };
}

interface DeadlineInfo {
    id: number;
    title: string;
    category: string;
    end_date: string;
}

interface Section {
    section_id: number;
    section_name: string;
}

interface Props {
    pendingStudents: Student[];
    verifiedStudents: Student[];
    rejectedStudents: Student[];
    adviserSection: string | null;
    adviserSections: Section[];
    currentSectionId: number | null;
    hasArchivedSections?: boolean;
    archivedSectionNames?: string[];
    deadlineActive: boolean;
    deadlineInfo: DeadlineInfo | null;
}

export default function Application({ pendingStudents, verifiedStudents, rejectedStudents, adviserSection, adviserSections, currentSectionId, hasArchivedSections = false, archivedSectionNames = [], deadlineActive }: Props) {
    const [selectedStudents, setSelectedStudents] = useState<number[]>([]);
    const [selectedVerifiedStudents, setSelectedVerifiedStudents] = useState<number[]>([]);
    const [selectedRejectedStudents, setSelectedRejectedStudents] = useState<number[]>([]);
    const [isProcessing, setIsProcessing] = useState(false);
    const [showUndoDialog, setShowUndoDialog] = useState(false);
    const [lastAction, setLastAction] = useState<{
        type: 'approve' | 'reject' | 'remove' | 'restore';
        studentIds: number[];
        count: number;
    } | null>(null);

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedStudents(pendingStudents.map(student => student.id));
        } else {
            setSelectedStudents([]);
        }
    };

    const handleSelectStudent = (studentId: number, checked: boolean) => {
        if (checked) {
            setSelectedStudents(prev => [...prev, studentId]);
        } else {
            setSelectedStudents(prev => prev.filter(id => id !== studentId));
        }
    };

    const handleSelectVerifiedStudent = (studentId: number, checked: boolean) => {
        // Don't allow selection of students who have submitted their assessment
        const student = verifiedStudents.find(s => s.id === studentId);
        if (student?.student?.is_submit) {
            return;
        }
        
        if (checked) {
            setSelectedVerifiedStudents(prev => [...prev, studentId]);
        } else {
            setSelectedVerifiedStudents(prev => prev.filter(id => id !== studentId));
        }
    };

    const handleSelectAllVerified = (checked: boolean) => {
        if (checked) {
            // Only select students who haven't submitted their assessment
            const unsubmittedStudents = verifiedStudents.filter(student => !student.student?.is_submit);
            setSelectedVerifiedStudents(unsubmittedStudents.map(student => student.id));
        } else {
            setSelectedVerifiedStudents([]);
        }
    };

    const handleSelectRejectedStudent = (studentId: number, checked: boolean) => {
        if (checked) {
            setSelectedRejectedStudents(prev => [...prev, studentId]);
        } else {
            setSelectedRejectedStudents(prev => prev.filter(id => id !== studentId));
        }
    };

    const handleSelectAllRejected = (checked: boolean) => {
        if (checked) {
            setSelectedRejectedStudents(rejectedStudents.map(student => student.id));
        } else {
            setSelectedRejectedStudents([]);
        }
    };

    const handleApprove = () => {
        if (selectedStudents.length === 0) return;

        setLastAction({
            type: 'approve',
            studentIds: selectedStudents,
            count: selectedStudents.length
        });
        setShowUndoDialog(true);
    };

    const handleReject = () => {
        if (selectedStudents.length === 0) return;

        setLastAction({
            type: 'reject',
            studentIds: selectedStudents,
            count: selectedStudents.length
        });
        setShowUndoDialog(true);
    };

    const handleRemoveAccess = () => {
        if (selectedVerifiedStudents.length === 0) return;

        setLastAction({
            type: 'remove',
            studentIds: selectedVerifiedStudents,
            count: selectedVerifiedStudents.length
        });
        setShowUndoDialog(true);
    };

    const handleRestoreStudents = () => {
        if (selectedRejectedStudents.length === 0) return;

        setLastAction({
            type: 'restore',
            studentIds: selectedRejectedStudents,
            count: selectedRejectedStudents.length
        });
        setShowUndoDialog(true);
    };

    const handleContinue = () => {
        if (!lastAction) return;

        setIsProcessing(true);
        
        // Execute the original action based on the type
        let routeName = '';
        switch (lastAction.type) {
            case 'approve':
                routeName = route('application.approve');
                break;
            case 'reject':
                routeName = route('application.reject');
                break;
            case 'remove':
                routeName = route('application.remove-access');
                break;
            case 'restore':
                routeName = route('application.restore');
                break;
        }

        router.post(routeName, {
            studentIds: lastAction.studentIds
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setShowUndoDialog(false);
                setLastAction(null);
                // Clear the selected students
                setSelectedStudents([]);
                setSelectedVerifiedStudents([]);
                setSelectedRejectedStudents([]);
            }
        });
    };

    const handleCancel = () => {
        setShowUndoDialog(false);
        setLastAction(null);
    };


    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Applications" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    {hasArchivedSections ? (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-amber-600">
                                    <ClockIcon className="h-5 w-5" />
                                    Sections Archived
                                </CardTitle>
                                <CardDescription>
                                    Your assigned sections have been archived and are no longer accessible.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <p className="text-sm text-muted-foreground">
                                        Archived sections: <span className="font-medium">{archivedSectionNames.join(', ')}</span>
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        Please contact an administrator to restore access or get assigned to new sections.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    ) : (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <UsersIcon className="h-5 w-5" />
                                    No Section Assigned
                                </CardTitle>
                                <CardDescription>
                                    You are not assigned to any section. Please contact the administrator.
                                </CardDescription>
                            </CardHeader>
                        </Card>
                    )}
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Application" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Section Info */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <UsersIcon className="h-5 w-5" />
                                    Section: {adviserSection}
                                </CardTitle>
                                <CardDescription>
                                    Manage student applications for your section
                                </CardDescription>
                            </div>
                            {adviserSections.length > 1 && (
                                <SectionSwitcher 
                                    sections={adviserSections}
                                    currentSectionId={currentSectionId}
                                    showAllSections={true}
                                />
                            )}
                        </div>
                    </CardHeader>
                </Card>

                {/* Pending Students */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <UserCheckIcon className="h-5 w-5" />
                                    Pending Students ({pendingStudents.length})
                                </CardTitle>
                                <CardDescription>
                                    Students waiting for approval
                                </CardDescription>
                            </div>
                            {pendingStudents.length > 0 && (
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        checked={selectedStudents.length === pendingStudents.length}
                                        onCheckedChange={handleSelectAll}
                                    />
                                    <span className="text-sm text-muted-foreground">Select All</span>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {pendingStudents.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                No pending students found
                            </div>
                        ) : (
                            <div className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {pendingStudents.map((student) => (
                                    <div
                                        key={student.id}
                                        className="flex items-center space-x-3 p-3 border rounded-lg"
                                    >
                                        <Checkbox
                                            checked={selectedStudents.includes(student.id)}
                                            onCheckedChange={(checked) =>
                                                handleSelectStudent(student.id, checked as boolean)
                                            }
                                        />
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between mb-2">
                                                <p className="text-sm font-medium truncate">
                                                    {student.username} {(() => {
                                                        // Use student data if available, otherwise use registration data
                                                        const firstName = student.student?.first_name || student.registration_data?.first_name;
                                                        const lastName = student.student?.last_name || student.registration_data?.last_name;
                                                        
                                                        if (firstName && lastName) {
                                                            return `| ${firstName} ${lastName}`;
                                                        }
                                                        return '| New Student';
                                                    })()}
                                                </p>
                                            </div>
                                            <p className="text-xs text-muted-foreground truncate mb-2">
                                                {student.email}
                                            </p>
                                            {currentSectionId === null && student.academe_accounts && student.academe_accounts.length > 0 && (
                                                <p className="text-xs text-blue-600 truncate mb-2">
                                                    {student.academe_accounts[0].section.section_name}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>

                                {selectedStudents.length > 0 && (
                                    <BatchActions
                                        selectedCount={selectedStudents.length}
                                        selectedLabel="student"
                                        description={deadlineActive 
                                            ? "You can approve or reject multiple students at once. Approved students will be verified for internship placement."
                                            : "Student verification deadline has expired. You cannot approve students at this time."
                                        }
                                        actions={[
                                            {
                                                ...BatchActionPresets.verify.approve,
                                                label: `Approve Selected (${selectedStudents.length})`,
                                                onClick: handleApprove,
                                                disabled: isProcessing || !deadlineActive
                                            },
                                            {
                                                ...BatchActionPresets.verify.reject,
                                                label: `Reject Selected (${selectedStudents.length})`,
                                                onClick: handleReject,
                                                disabled: isProcessing
                                            }
                                        ]}
                                        onClearSelection={() => setSelectedStudents([])}
                                        isLoading={isProcessing}
                                    />
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Verified Students */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <UserCheckIcon className="h-5 w-5" />
                                    Verified Students ({verifiedStudents.length})
                                </CardTitle>
                                <CardDescription>
                                    Students who have been approved and can access the system
                                </CardDescription>
                            </div>
                            {verifiedStudents.length > 0 && (
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        checked={selectedVerifiedStudents.length === verifiedStudents.filter(student => !student.student?.is_submit).length && verifiedStudents.filter(student => !student.student?.is_submit).length > 0}
                                        onCheckedChange={handleSelectAllVerified}
                                    />
                                    <span className="text-sm text-muted-foreground">Select All</span>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {verifiedStudents.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                No verified students found
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {verifiedStudents.some(student => student.student?.is_submit) && (
                                    <div className="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                        <p className="text-sm text-amber-800">
                                            <strong>Note:</strong> Students who have completed their assessment cannot be unverified.
                                        </p>
                                    </div>
                                )}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {verifiedStudents.map((student) => {
                                    const hasSubmitted = student.student?.is_submit;
                                    return (
                                        <div
                                            key={student.id}
                                            className={`flex items-center space-x-3 p-3 border rounded-lg ${hasSubmitted ? 'opacity-60' : ''}`}
                                        >
                                            <Checkbox
                                                checked={selectedVerifiedStudents.includes(student.id)}
                                                onCheckedChange={(checked) =>
                                                    handleSelectVerifiedStudent(student.id, checked as boolean)
                                                }
                                                disabled={hasSubmitted}
                                            />
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between mb-2">
                                                    <p className="text-sm font-medium truncate">
                                                        {student.username}
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Badge variant="secondary" className="text-xs">
                                                            Verified
                                                        </Badge>
                                                    </div>
                                                </div>
                                                <p className="text-xs text-muted-foreground truncate mb-2">
                                                    {student.email}
                                                </p>
                                                {currentSectionId === null && student.academe_accounts && student.academe_accounts.length > 0 && (
                                                    <p className="text-xs text-blue-600 truncate mb-2">
                                                        {student.academe_accounts[0].section.section_name}
                                                    </p>
                                                )}
                                                {student.student && (
                                                    <div className="space-y-1">
                                                        <p className="text-xs">
                                                            <span className="font-medium">Name:</span> {student.student.first_name} {student.student.last_name}
                                                        </p>
                                                        <p className="text-xs">
                                                            <span className="font-medium">Assessment:</span> {student.student.is_submit ? 'Completed' : 'Pending'}
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                                {selectedVerifiedStudents.length > 0 && (
                                    <div className="flex items-center gap-2 pt-4 border-t">
                                        <Button
                                            onClick={handleRemoveAccess}
                                            disabled={isProcessing}
                                            variant="destructive"
                                            className="flex items-center gap-2"
                                        >
                                            <UserXIcon className="h-4 w-4" />
                                            Remove Access ({selectedVerifiedStudents.length})
                                        </Button>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Rejected Students */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <UserXIcon className="h-5 w-5" />
                                    Rejected Students ({rejectedStudents.length})
                                </CardTitle>
                                <CardDescription>
                                    Students who have been rejected and can be restored to pending status
                                </CardDescription>
                            </div>
                            {rejectedStudents.length > 0 && (
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        checked={selectedRejectedStudents.length === rejectedStudents.length}
                                        onCheckedChange={handleSelectAllRejected}
                                    />
                                    <span className="text-sm text-muted-foreground">Select All</span>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    <CardContent>
                        {rejectedStudents.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                No rejected students found
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {rejectedStudents.map((student) => (
                                        <div
                                            key={student.id}
                                            className="flex items-center space-x-3 p-3 border rounded-lg bg-red-50"
                                        >
                                            <Checkbox
                                                checked={selectedRejectedStudents.includes(student.id)}
                                                onCheckedChange={(checked) =>
                                                    handleSelectRejectedStudent(student.id, checked as boolean)
                                                }
                                            />
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between mb-2">
                                                    <p className="text-sm font-medium truncate">
                                                        {student.username} {(() => {
                                                            const firstName = student.registration_data?.first_name;
                                                            const lastName = student.registration_data?.last_name;
                                                            
                                                            if (firstName && lastName) {
                                                                return `| ${firstName} ${lastName}`;
                                                            }
                                                            return '| Rejected Student';
                                                        })()}
                                                    </p>
                                                    <div className="flex items-center gap-2">
                                                        <Badge variant="destructive" className="text-xs">
                                                            Rejected
                                                        </Badge>
                                                    </div>
                                                </div>
                                                <p className="text-xs text-muted-foreground truncate mb-2">
                                                    {student.email}
                                                </p>
                                                {currentSectionId === null && student.academe_accounts && student.academe_accounts.length > 0 && (
                                                    <p className="text-xs text-blue-600 truncate mb-2">
                                                        {student.academe_accounts[0].section.section_name}
                                                    </p>
                                                )}
                                                <p className="text-xs text-red-600">
                                                    Rejected: {(student as Student & { rejected_at?: string }).rejected_at || 'Unknown'}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {selectedRejectedStudents.length > 0 && (
                                    <div className="flex items-center gap-2 pt-4 border-t">
                                        <Button
                                            onClick={handleRestoreStudents}
                                            disabled={isProcessing}
                                            className="flex items-center gap-2"
                                        >
                                            <RotateCcwIcon className="h-4 w-4" />
                                            Restore to Pending ({selectedRejectedStudents.length})
                                        </Button>
                                    </div>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Confirmation Dialog */}
            <Dialog open={showUndoDialog} onOpenChange={() => {}}>
                <DialogContent className="sm:max-w-[425px] [&>button]:hidden">
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <CheckIcon className="h-5 w-5" />
                            Confirm Action
                        </DialogTitle>
                        <DialogDescription>
                            {lastAction && (
                                <>
                                    Are you sure you want to {lastAction.type === 'approve' ? 'approve' :
                                               lastAction.type === 'reject' ? 'reject' :
                                               lastAction.type === 'remove' ? 'remove access for' :
                                               'restore'} {lastAction.count} student{lastAction.count > 1 ? 's' : ''}?
                                    {lastAction.type === 'approve' && !deadlineActive && (
                                        <div className="mt-2 p-2 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                                            <strong>Warning:</strong> Student verification deadline has expired. You cannot approve students at this time.
                                        </div>
                                    )}
                                </>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={handleCancel}
                            disabled={isProcessing}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleContinue}
                            disabled={isProcessing || (lastAction?.type === 'approve' && !deadlineActive)}
                            className="flex items-center gap-2"
                        >
                            <CheckIcon className="h-4 w-4" />
                            Continue
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
