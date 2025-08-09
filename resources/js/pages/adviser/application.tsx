import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { CheckIcon, XIcon, UsersIcon, UserCheckIcon, RotateCcwIcon, UserXIcon } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Application',
        href: '/application',
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
}

interface Props {
    pendingStudents: Student[];
    verifiedStudents: Student[];
    adviserSection: {
        section_id: number;
        section_name: string;
    } | null;
}

export default function Application({ pendingStudents, verifiedStudents, adviserSection }: Props) {
    const [selectedStudents, setSelectedStudents] = useState<number[]>([]);
    const [selectedVerifiedStudents, setSelectedVerifiedStudents] = useState<number[]>([]);
    const [isProcessing, setIsProcessing] = useState(false);
    const [showUndoDialog, setShowUndoDialog] = useState(false);
    const [lastAction, setLastAction] = useState<{
        type: 'approve' | 'reject' | 'remove';
        studentIds: number[];
        count: number;
    } | null>(null);
    const [showSuccessMessage, setShowSuccessMessage] = useState(false);

    // Auto-hide success message after 5 seconds
    useEffect(() => {
        if (showSuccessMessage) {
            const timer = setTimeout(() => {
                setShowSuccessMessage(false);
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [showSuccessMessage]);

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
        if (checked) {
            setSelectedVerifiedStudents(prev => [...prev, studentId]);
        } else {
            setSelectedVerifiedStudents(prev => prev.filter(id => id !== studentId));
        }
    };

    const handleSelectAllVerified = (checked: boolean) => {
        if (checked) {
            setSelectedVerifiedStudents(verifiedStudents.map(student => student.id));
        } else {
            setSelectedVerifiedStudents([]);
        }
    };

    const handleApprove = () => {
        if (selectedStudents.length === 0) return;
        
        setIsProcessing(true);
        router.post(route('application.approve'), {
            studentIds: selectedStudents
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setLastAction({
                    type: 'approve',
                    studentIds: selectedStudents,
                    count: selectedStudents.length
                });
                setSelectedStudents([]);
                setShowUndoDialog(true);
            }
        });
    };

    const handleReject = () => {
        if (selectedStudents.length === 0) return;
        
        setIsProcessing(true);
        router.post(route('application.reject'), {
            studentIds: selectedStudents
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setLastAction({
                    type: 'reject',
                    studentIds: selectedStudents,
                    count: selectedStudents.length
                });
                setSelectedStudents([]);
                setShowUndoDialog(true);
            }
        });
    };

    const handleRemoveAccess = () => {
        if (selectedVerifiedStudents.length === 0) return;
        
        setIsProcessing(true);
        router.post(route('application.remove-access'), {
            studentIds: selectedVerifiedStudents
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setLastAction({
                    type: 'remove',
                    studentIds: selectedVerifiedStudents,
                    count: selectedVerifiedStudents.length
                });
                setSelectedVerifiedStudents([]);
                setShowUndoDialog(true);
            }
        });
    };

    const handleUndo = () => {
        if (!lastAction) return;
        
        setIsProcessing(true);
        router.post(route('application.undo'), {
            action: lastAction.type,
            studentIds: lastAction.studentIds
        }, {
            onFinish: () => {
                setIsProcessing(false);
                setShowUndoDialog(false);
                setLastAction(null);
                setShowSuccessMessage(true);
            }
        });
    };

    const closeUndoDialog = () => {
        setShowUndoDialog(false);
        setLastAction(null);
    };

    if (!adviserSection) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Application" />
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Application" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Success Message */}
                {showSuccessMessage && (
                    <div className="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        Action undone successfully!
                    </div>
                )}

                {/* Section Info */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <UsersIcon className="h-5 w-5" />
                            Section: {adviserSection.section_name}
                        </CardTitle>
                        <CardDescription>
                            Manage student applications for your section
                        </CardDescription>
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
                                                <p className="text-sm font-medium truncate">
                                                    {student.username}
                                                </p>
                                                <p className="text-xs text-muted-foreground truncate">
                                                    {student.email}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                
                                {selectedStudents.length > 0 && (
                                    <div className="flex items-center gap-2 pt-4 border-t">
                                        <Button
                                            onClick={handleApprove}
                                            disabled={isProcessing}
                                            className="flex items-center gap-2"
                                        >
                                            <CheckIcon className="h-4 w-4" />
                                            Approve Selected ({selectedStudents.length})
                                        </Button>
                                        <Button
                                            onClick={handleReject}
                                            disabled={isProcessing}
                                            variant="destructive"
                                            className="flex items-center gap-2"
                                        >
                                            <XIcon className="h-4 w-4" />
                                            Reject Selected ({selectedStudents.length})
                                        </Button>
                                    </div>
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
                                        checked={selectedVerifiedStudents.length === verifiedStudents.length}
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
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    {verifiedStudents.map((student) => (
                                        <div
                                            key={student.id}
                                            className="flex items-center space-x-3 p-3 border rounded-lg"
                                        >
                                            <Checkbox
                                                checked={selectedVerifiedStudents.includes(student.id)}
                                                onCheckedChange={(checked) => 
                                                    handleSelectVerifiedStudent(student.id, checked as boolean)
                                                }
                                            />
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center justify-between mb-2">
                                                    <p className="text-sm font-medium truncate">
                                                        {student.username}
                                                    </p>
                                                    <Badge variant="secondary" className="text-xs">
                                                        Verified
                                                    </Badge>
                                                </div>
                                                <p className="text-xs text-muted-foreground truncate mb-2">
                                                    {student.email}
                                                </p>
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
                                    ))}
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
            </div>

            {/* Undo Dialog */}
            <Dialog open={showUndoDialog} onOpenChange={setShowUndoDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle className="flex items-center gap-2">
                            <RotateCcwIcon className="h-5 w-5" />
                            Undo Action
                        </DialogTitle>
                        <DialogDescription>
                            {lastAction && (
                                <>
                                    You just {lastAction.type === 'approve' ? 'approved' : 
                                               lastAction.type === 'reject' ? 'rejected' : 
                                               'removed access for'} {lastAction.count} student{lastAction.count > 1 ? 's' : ''}.
                                    Would you like to undo this action?
                                </>
                            )}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={closeUndoDialog}
                            disabled={isProcessing}
                        >
                            Cancel
                        </Button>
                        <Button
                            onClick={handleUndo}
                            disabled={isProcessing}
                            className="flex items-center gap-2"
                        >
                            <RotateCcwIcon className="h-4 w-4" />
                            Undo Action
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
