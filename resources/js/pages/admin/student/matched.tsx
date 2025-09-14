import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AdminLayout from '@/layouts/admin/layout';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import StudentDetailsModal from '@/components/student-details-modal';
import { 
    UserIcon, 
    TargetIcon,
    TrendingUpIcon,
    EyeIcon,
    CheckCircleIcon,
    XCircleIcon,
    SearchIcon,
    FilterIcon
} from 'lucide-react';

// breadcrumbs is unused, so we'll remove it

interface MatchedStudent {
    id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    middle_name?: string;
    section: string;
    specialization?: string;
        best_match: {
            internship: {
                id: number;
                position_title: string;
                department: string;
                hte: {
                    company_name: string;
                };
                slot_count?: number; // Total slots
                available_slots?: number; // Available slots (total - occupied)
                occupied_slots?: number; // Currently occupied slots
            };
            compatibility_score: number;
            status?: string; // 'pending', 'approved', 'rejected'
        };
}

interface SectionOption {
    name: string;
    total_students: number;
    placed_students: number;
    placement_rate: number;
}

interface InternshipOption {
    id: number;
    title?: string;
    position_title?: string;
    company?: string;
    department?: string;
    total_slots?: number;
    occupied_slots?: number;
    available_slots?: number;
    occupancy_rate?: number;
    hte?: {
        company_name?: string;
    };
}

interface Filters {
    sections: SectionOption[];
    internships: InternshipOption[];
    currentSection: string | null;
    currentInternship: string | null;
    currentSearch: string | null;
}

interface Props {
    matchedStudents: MatchedStudent[];
    filters: Filters;
}

export default function StudentMatched({ matchedStudents, filters }: Props) {
    const { csrf_token } = usePage().props as { csrf_token?: string };
    const [selectedStudent, setSelectedStudent] = useState<MatchedStudent | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const [errorType, setErrorType] = useState<string | null>(null);
    const [localFilters, setLocalFilters] = useState({
        section: filters.currentSection || 'all',
        internship: filters.currentInternship || 'all',
        search: filters.currentSearch || '',
    });

    // Function to get fresh CSRF token
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

    const [selectedStudents, setSelectedStudents] = useState<Set<number>>(new Set());
    const [showConflictDialog, setShowConflictDialog] = useState(false);
    const [conflictData, setConflictData] = useState<{
        has_conflicts: boolean;
        total_approved: number;
        total_conflicts: number;
        approved_students?: Array<{
            student_name: string;
            internship_title: string;
            company_name: string;
            match_rank: number;
            compatibility_score: number;
        }>;
        conflicts?: Array<{
            student_name: string;
            best_match: {
                position_title: string;
                company_name: string;
                compatibility_score: number;
            };
            fallback_match: {
                position_title: string;
                company_name: string;
                compatibility_score: number;
                available_slots: number;
                match_rank: number;
            };
        }>;
    } | null>(null);

    // Debug logging to see what data is received
    useEffect(() => {
        console.log('Received filters:', filters);
        console.log('Received matchedStudents:', matchedStudents);
        console.log('Filters sections:', filters.sections);
        console.log('Filters internships:', filters.internships);
        
        // Debug slot information for each student
        matchedStudents.forEach((student, index) => {
            if (student.best_match?.internship) {
                console.log(`Student ${index + 1} (${student.first_name} ${student.last_name}):`, {
                    position: student.best_match.internship.position_title,
                    total_slots: student.best_match.internship.slot_count,
                    available_slots: student.best_match.internship.available_slots,
                    occupied_slots: student.best_match.internship.occupied_slots
                });
            }
        });
    }, [filters, matchedStudents]);

    // Update local filters when props change
    useEffect(() => {
        setLocalFilters({
            section: filters.currentSection || 'all',
            internship: filters.currentInternship || 'all',
            search: filters.currentSearch || ''
        });
    }, [filters]);

    const getScoreColor = (score: number) => {
        if (score >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (score >= 60) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        return 'bg-red-100 text-red-800 dark:bg-green-900 dark:text-red-200';
    };

    const getScoreLabel = (score: number) => {
        if (score >= 80) return 'Excellent';
        if (score >= 60) return 'Good';
        return 'Fair';
    };

    const getStatusBadge = (status?: string) => {
        if (!status || status === 'pending') return null;
        
        if (status === 'approved') {
            return (
                <Badge className="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Approved
                </Badge>
            );
        }
        
        if (status === 'rejected') {
            return (
                <Badge className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    Rejected
                </Badge>
            );
        }
        
        return null;
    };

    const handleFilterChange = (filterType: 'section' | 'internship' | 'search', value: string) => {
        const newFilters = { ...localFilters, [filterType]: value };
        setLocalFilters(newFilters);

        // Apply filters immediately
        const params = new URLSearchParams();
        if (newFilters.section && newFilters.section !== 'all') {
            params.append('section', newFilters.section);
        }
        if (newFilters.internship && newFilters.internship !== 'all') {
            params.append('internship', newFilters.internship);
        }
        if (newFilters.search) {
            params.append('search', newFilters.search);
        }

        router.get('/student/matched', params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setLocalFilters({ section: 'all', internship: 'all', search: '' });
        router.get('/student/matched', {}, {
            preserveState: true,
            replace: true
        });
    };

    const handleViewDetails = async (student: MatchedStudent) => {
        try {
            setIsLoading(true);
            const response = await fetch(`/student/${student.id}/details`);
            if (response.ok) {
                const data = await response.json();
                setSelectedStudent(data);
                setIsModalOpen(true);
            }
        } catch (error) {
            console.error('Error fetching student details:', error);
        } finally {
            setIsLoading(false);
        }
    };

    // handleApprovePlacement function removed as it's unused

    // handleRejectPlacement function removed as it's unused

    const handleSingleApprove = async (student: MatchedStudent) => {
        if (!student || !student.best_match?.internship) {
            setErrorMessage('Invalid student data or missing internship information');
            setErrorType('error');
            return;
        }

        let csrfToken = getFreshCsrfToken();
        
        try {
            setIsLoading(true);
            setErrorMessage(null);
            setErrorType(null);
            
            console.log('Approving student:', student.id, 'for internship:', student.best_match.internship.id);
            
            const response = await fetch(`/student/${student.id}/approve-placement`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json', // Explicitly request JSON response
                },
                body: JSON.stringify({
                    internship_id: student.best_match.internship.id,
                    compatibility_score: student.best_match.compatibility_score || 0,
                }),
            });
            
            console.log('Response status:', response.status);
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
                console.log('CSRF token mismatch detected, refreshing token...');
                csrfToken = await refreshCsrfToken();
                
                // Retry the request with fresh token
                const retryResponse = await fetch(`/student/${student.id}/approve-placement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        internship_id: student.best_match.internship.id,
                        compatibility_score: student.best_match.compatibility_score || 0,
                    }),
                });
                
                if (retryResponse.ok) {
                    await retryResponse.json();
                    setErrorMessage('Student placement approved successfully!');
                    setErrorType('success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                    return;
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Response is not JSON, likely HTML (login page or error page)
                const responseText = await response.text();
                console.error('Non-JSON response received:', responseText.substring(0, 200));
                
                if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
                    setErrorMessage('Authentication error: You may have been logged out or do not have permission to perform this action. Please refresh the page and try again.');
                    setErrorType('error');
                } else {
                    setErrorMessage('Server returned an unexpected response format. Please try again or contact support.');
                    setErrorType('error');
                }
                return;
            }
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (response.ok) {
                setErrorMessage('Student placement approved successfully!');
                setErrorType('success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Handle errors
                setErrorMessage(`Error: ${result.message}`);
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in single approval:', error);
            
            // Check if it's a JSON parsing error
            if (error instanceof SyntaxError && error.message.includes('Unexpected token')) {
                setErrorMessage('Server returned an invalid response format. This usually indicates an authentication or permission issue. Please refresh the page and try again.');
                setErrorType('error');
            } else {
                setErrorMessage('Error during approval: ' + (error instanceof Error ? error.message : String(error)));
                setErrorType('error');
            }
        } finally {
            setIsLoading(false);
        }
    };

    const handleSingleReject = async (student: MatchedStudent) => {
        if (!student || !student.best_match?.internship) {
            setErrorMessage('Invalid student data or missing internship information');
            setErrorType('error');
            return;
        }

        let csrfToken = getFreshCsrfToken();
        
        try {
            setIsLoading(true);
            setErrorMessage(null);
            setErrorType(null);
            
            console.log('Rejecting student:', student.id, 'for internship:', student.best_match.internship.id);
            
            const response = await fetch(`/student/${student.id}/reject-placement`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json', // Explicitly request JSON response
                },
                body: JSON.stringify({
                    internship_id: student.best_match.internship.id,
                    compatibility_score: student.best_match.compatibility_score || 0,
                }),
            });
            
            console.log('Response status:', response.status);
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
                console.log('CSRF token mismatch detected, refreshing token...');
                csrfToken = await refreshCsrfToken();
                
                // Retry the request with fresh token
                const retryResponse = await fetch(`/student/${student.id}/reject-placement`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        internship_id: student.best_match.internship.id,
                        compatibility_score: student.best_match.compatibility_score || 0,
                    }),
                });
                
                if (retryResponse.ok) {
                    await retryResponse.json();
                    setErrorMessage('Student placement rejected successfully!');
                    setErrorType('success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                    return;
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Response is not JSON, likely HTML (login page or error page)
                const responseText = await response.text();
                console.error('Non-JSON response received:', responseText.substring(0, 200));
                
                if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
                    setErrorMessage('Authentication error: You may have been logged out or do not have permission to perform this action. Please refresh the page and try again.');
                    setErrorType('error');
                } else {
                    setErrorMessage('Server returned an unexpected response format. Please try again or contact support.');
                    setErrorType('error');
                }
                return;
            }
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (response.ok) {
                setErrorMessage('Student placement rejected successfully!');
                setErrorType('success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Handle errors
                setErrorMessage(`Error: ${result.message}`);
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in single rejection:', error);
            
            // Check if it's a JSON parsing error
            if (error instanceof SyntaxError && error.message.includes('Unexpected token')) {
                setErrorMessage('Server returned an invalid response format. This usually indicates an authentication or permission issue. Please refresh the page and try again.');
                setErrorType('error');
            } else {
                setErrorMessage('Error during rejection: ' + (error instanceof Error ? error.message : String(error)));
                setErrorType('error');
            }
        } finally {
            setIsLoading(false);
        }
    };

    // Batch selection handlers
    const handleSelectStudent = (studentId: number, checked: boolean) => {
        const newSelected = new Set(selectedStudents);
        if (checked) {
            newSelected.add(studentId);
        } else {
            newSelected.delete(studentId);
        }
        setSelectedStudents(newSelected);
    };

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            const allIds = matchedStudents.map(s => s.id);
            setSelectedStudents(new Set(allIds));
        } else {
            setSelectedStudents(new Set());
        }
    };

    const handleBatchApprove = async () => {
        if (selectedStudents.size === 0) return;

        const csrfToken = getFreshCsrfToken();
        
        // Check if CSRF token exists (basic auth check)
        if (!csrfToken) {
            setErrorMessage('Authentication error: CSRF token not found. Please refresh the page and try again.');
            setErrorType('error');
            return;
        }
        
        try {
            setIsLoading(true);
            setErrorMessage(null);
            setErrorType(null);
            
            console.log('Checking for slot conflicts before batch approval for students:', Array.from(selectedStudents));
            
            // First, check for slot conflicts
            const conflictResponse = await fetch('/student/check-batch-conflicts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    student_ids: Array.from(selectedStudents),
                    internship_filter: localFilters.internship !== 'all' ? localFilters.internship : null
                }),
            });
            
            if (conflictResponse.ok) {
                const conflictData = await conflictResponse.json();
                
                if (conflictData.has_conflicts) {
                    // Show confirmation dialog
                    setConflictData(conflictData);
                    setShowConflictDialog(true);
                    setIsLoading(false);
                    return;
                }
            }
            
            // No conflicts, proceed with approval
            await proceedWithBatchApproval(csrfToken);
            
        } catch (error) {
            console.error('Error during batch approval:', error);
            setErrorMessage('An error occurred during batch approval. Please try again.');
            setErrorType('error');
            setIsLoading(false);
        }
    };

    const proceedWithBatchApproval = async (csrfToken: string) => {
        try {
            console.log('Proceeding with batch approval for students:', Array.from(selectedStudents));
            
            // Use the new batch approval endpoint
            const response = await fetch('/student/batch-approve-placements', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json', // Explicitly request JSON response
                },
                body: JSON.stringify({
                    student_ids: Array.from(selectedStudents),
                    internship_filter: localFilters.internship !== 'all' ? localFilters.internship : null
                }),
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', Object.fromEntries(response.headers.entries()));
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
                console.log('CSRF token mismatch detected, refreshing token...');
                csrfToken = await refreshCsrfToken();
                
                // Retry the request with fresh token
                const retryResponse = await fetch('/student/batch-approve-placements', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        student_ids: Array.from(selectedStudents),
                        internship_filter: localFilters.internship !== 'all' ? localFilters.internship : null
                    }),
                });
                
                if (retryResponse.ok) {
                    const result = await retryResponse.json();
                    if (result.total_approved > 0) {
                        setErrorMessage(`Successfully approved ${result.total_approved} placement(s)!`);
                        setErrorType('success');
                        setSelectedStudents(new Set());
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        setErrorMessage('No placements were approved. Please check the selection and try again.');
                        setErrorType('error');
                    }
                    return;
                }
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Response is not JSON, likely HTML (login page or error page)
                const responseText = await response.text();
                console.error('Non-JSON response received:', responseText.substring(0, 200));
                
                if (responseText.includes('<!DOCTYPE') || responseText.includes('<html')) {
                    setErrorMessage('Authentication error: You may have been logged out or do not have permission to perform this action. Please refresh the page and try again.');
                    setErrorType('error');
                } else {
                    setErrorMessage('Server returned an unexpected response format. Please try again or contact support.');
                    setErrorType('error');
                }
                return;
            }
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (response.ok) {
                // Success
                if (result.total_approved > 0) {
                    setErrorMessage(`Successfully approved ${result.total_approved} placement(s)!`);
                    setErrorType('success');
                    setSelectedStudents(new Set());
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    setErrorMessage('No placements were approved. Please check the selection and try again.');
                    setErrorType('error');
                }
            } else {
                // Handle errors
                setErrorMessage(`Error: ${result.message}`);
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in batch approval:', error);
            
            // Check if it's a JSON parsing error
            if (error instanceof SyntaxError && error.message.includes('Unexpected token')) {
                setErrorMessage('Server returned an invalid response format. This usually indicates an authentication or permission issue. Please refresh the page and try again.');
                setErrorType('error');
            } else {
                setErrorMessage('Error during batch approval: ' + (error instanceof Error ? error.message : String(error)));
                setErrorType('error');
            }
        } finally {
            setIsLoading(false);
        }
    };

    const handleBatchReject = async () => {
        if (selectedStudents.size === 0) return;

        let csrfToken = getFreshCsrfToken();
        
        try {
            setIsLoading(true);
            setErrorMessage(null);
            setErrorType(null);
            
            const promises = Array.from(selectedStudents).map(async (studentId) => {
                const student = matchedStudents.find(s => s.id === studentId);
                if (!student) return { success: false, error: 'Student not found' };
                
                if (!student.best_match?.internship) {
                    return { success: false, error: 'Student missing internship information' };
                }
                
                try {
                    let response = await fetch(`/student/${student.id}/reject-placement`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            internship_id: student.best_match.internship.id,
                            compatibility_score: student.best_match.compatibility_score || 0,
                        }),
                    });
                    
                    // Handle CSRF token mismatch
                    if (response.status === 419) {
                        console.log('CSRF token mismatch detected, refreshing token...');
                        csrfToken = await refreshCsrfToken();
                        
                        // Retry the request with fresh token
                        response = await fetch(`/student/${student.id}/reject-placement`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                internship_id: student.best_match.internship.id,
                                compatibility_score: student.best_match.compatibility_score || 0,
                            }),
                        });
                    }
                    
                    if (response.ok) {
                        return { success: true };
                    } else {
                        const errorData = await response.json();
                        return { success: false, error: errorData.message || 'Unknown error' };
                    }
                } catch (error) {
                    return { success: false, error: error instanceof Error ? error.message : String(error) };
                }
            });

            const results = await Promise.all(promises);
            const successful = results.filter(r => r.success);
            const failed = results.filter(r => !r.success);
            
            if (failed.length === 0) {
                setErrorMessage(`Successfully rejected ${successful.length} placement(s)!`);
                setErrorType('success');
                setSelectedStudents(new Set());
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                const errorDetails = failed.map((result, index) => {
                    const student = matchedStudents.find(s => s.id === Array.from(selectedStudents)[index]);
                    return `Student ${student?.first_name} ${student?.last_name}: ${result.error}`;
                }).join('\n');
                
                setErrorMessage(`Rejected ${successful.length} out of ${selectedStudents.size} placements.\n\nFailed placements:\n${errorDetails}`);
                setErrorType('error');
            }
        } catch (error) {
            console.error('Error in batch rejection:', error);
            setErrorMessage('Error during batch rejection: ' + (error instanceof Error ? error.message : String(error)));
            setErrorType('error');
        } finally {
            setIsLoading(false);
        }
    };

    // Get filter description
    const getFilterDescription = () => {
        if (localFilters.internship !== 'all') {
            const selectedInternship = filters.internships.find(i => i.id.toString() === localFilters.internship);
            const title = selectedInternship?.title || selectedInternship?.position_title || 'Unknown Internship';
            const company = selectedInternship?.company || selectedInternship?.hte?.company_name || 'Unknown Company';
            return `Showing students ranked by compatibility with "${title}" at ${company}. Rejected matches are shown with status labels.`;
        } else if (localFilters.section !== 'all') {
            return `Showing students from ${localFilters.section} section with their best internship matches (excluding rejected matches)`;
        } else {
            return 'Showing all students with their best internship matches (excluding rejected matches)';
        }
    };

    return (
        <>
            <Head title="Student Matches" />

            <AdminLayout>
                <div className="space-y-6">
                    <div className="flex justify-between items-center">
                        <Heading 
                            title="Student Matches" 
                            description="View and manage student-internship matches based on compatibility scores."
                        />
                    </div>





                    {/* Filters Section */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <FilterIcon className="h-5 w-5" />
                                Filters & Search
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {/* Section Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="section-filter">Section</Label>
                                    <Select 
                                        value={localFilters.section} 
                                        onValueChange={(value) => handleFilterChange('section', value)}
                                    >
                                        <SelectTrigger id="section-filter">
                                            <SelectValue placeholder="Select section" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Sections</SelectItem>
                                            {filters.sections && Array.isArray(filters.sections) && filters.sections.map((section) => {
                                                // Handle both string and object formats
                                                if (typeof section === 'string') {
                                                    return (
                                                        <SelectItem key={section} value={section}>
                                                            {section}
                                                        </SelectItem>
                                                    );
                                                }
                                                
                                                // Handle object format
                                                return (
                                                    <SelectItem key={section.name} value={section.name}>
                                                        <div className="flex flex-col">
                                                            <span className="font-medium">{section.name}</span>
                                                            <span className="text-xs text-muted-foreground">
                                                                {section.placed_students}/{section.total_students} placed ({section.placement_rate}%)
                                                            </span>
                                                        </div>
                                                    </SelectItem>
                                                );
                                            })}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Internship Filter */}
                                <div className="space-y-2">
                                    <Label htmlFor="internship-filter">Internship</Label>
                                    <Select 
                                        value={localFilters.internship} 
                                        onValueChange={(value) => handleFilterChange('internship', value)}
                                    >
                                        <SelectTrigger id="internship-filter">
                                            <SelectValue placeholder="Select internship" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Internships</SelectItem>
                                            {filters.internships && Array.isArray(filters.internships) && filters.internships.map((internship) => {
                                                // Handle both object and basic formats
                                                if (typeof internship === 'object' && internship !== null) {
                                                    return (
                                                        <SelectItem key={internship.id} value={internship.id.toString()}>
                                                            <div className="flex flex-col">
                                                                <span className="font-medium">{internship.title || internship.position_title || 'Unknown Title'}</span>
                                                                <span className="text-xs text-muted-foreground">
                                                                    {internship.company || internship.hte?.company_name || 'Unknown Company'} • {internship.department || 'Unknown Department'}
                                                                </span>
                                                                {internship.total_slots !== undefined && (
                                                                    <span className="text-xs text-muted-foreground">
                                                                        {internship.occupied_slots || 0}/{internship.total_slots} slots occupied ({internship.occupancy_rate || 0}%)
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </SelectItem>
                                                    );
                                                }
                                                
                                                return null;
                                            })}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Search */}
                                <div className="space-y-2">
                                    <Label htmlFor="search">Search</Label>
                                    <div className="relative">
                                        <SearchIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                        <Input
                                            id="search"
                                            placeholder="Search students..."
                                            value={localFilters.search}
                                            onChange={(e) => handleFilterChange('search', e.target.value)}
                                            className="pl-10"
                                        />
                                    </div>
                                </div>

                                {/* Clear Filters */}
                                <div className="space-y-2">
                                    <Label>&nbsp;</Label>
                                    <Button 
                                        variant="outline" 
                                        onClick={clearFilters}
                                        className="w-full"
                                    >
                                        Clear Filters
                                    </Button>
                                </div>
                            </div>

                            {/* Filter Description */}
                            <div className="mt-4 p-3 bg-muted rounded-lg">
                                <p className="text-sm text-muted-foreground">
                                    {getFilterDescription()}
                                </p>
                                <p className="text-xs text-blue-600 mt-2">
                                    💡 The system now allows student placement approvals until all internship slots are filled (0 slots remaining).
                                </p>
                                <p className="text-xs text-green-600 mt-1">
                                    ✅ When viewing "All Internships", rejected matches are filtered out. When viewing a specific internship, rejected matches show with status labels.
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Error Display */}
                    {errorMessage && (
                        <Card className={`border-l-4 ${
                            errorType === 'success' ? 'border-l-green-500 bg-green-50' : 'border-l-red-500 bg-red-50'
                        }`}>
                            <CardContent className="p-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <div className={`font-medium ${
                                            errorType === 'success' ? 'text-green-800' : 'text-red-800'
                                        }`}>
                                            {errorType === 'success' ? 'Success' : 'Error'}
                                        </div>
                                        <div className={`mt-1 text-sm ${
                                            errorType === 'success' ? 'text-green-700' : 'text-red-700'
                                        }`}>
                                            {errorMessage}
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => {
                                            setErrorMessage(null);
                                            setErrorType(null);
                                        }}
                                        className="ml-4 text-gray-400 hover:text-gray-600"
                                    >
                                        ×
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Summary Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Matches</CardTitle>
                                <TargetIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{matchedStudents.length}</div>
                                <p className="text-xs text-muted-foreground">
                                    Students with completed assessments
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Average Score</CardTitle>
                                <TrendingUpIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {matchedStudents.length > 0 
                                        ? Math.round(matchedStudents.reduce((sum, student) => sum + (student.best_match?.compatibility_score || 0), 0) / matchedStudents.length)
                                        : 0
                                    }%
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Overall compatibility
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Top Performers</CardTitle>
                                <UserIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {matchedStudents.filter(s => (s.best_match?.compatibility_score || 0) >= 80).length}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Students with 80%+ scores
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Batch Actions */}
                    {selectedStudents.size > 0 && (
                        <Card className="border-l-4 border-l-blue-500 bg-blue-50">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex-1">
                                        <div className="font-medium text-blue-800">
                                            Batch Actions ({selectedStudents.size} student{selectedStudents.size !== 1 ? 's' : ''} selected)
                                        </div>
                                        <div className="mt-1 text-sm text-blue-700">
                                            You can approve or reject multiple students at once. The system will allow approvals until all slots are filled (0 slots remaining).
                                        </div>
                                    </div>
                                    <div className="flex space-x-2">
                                        <Button
                                            variant="default"
                                            onClick={handleBatchApprove}
                                            disabled={isLoading}
                                            className="bg-green-600 hover:bg-green-700"
                                        >
                                            <CheckCircleIcon className="h-4 w-4 mr-2" />
                                            Approve All ({selectedStudents.size})
                                        </Button>
                                        <Button
                                            variant="destructive"
                                            onClick={handleBatchReject}
                                            disabled={isLoading}
                                        >
                                            <XCircleIcon className="h-4 w-4 mr-2" />
                                            Reject All ({selectedStudents.size})
                                        </Button>
                                        <Button
                                            variant="outline"
                                            onClick={() => setSelectedStudents(new Set())}
                                        >
                                            Clear Selection
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Matches Table */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Student-Internship Matches</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {matchedStudents.length === 0 ? (
                                <div className="text-center py-12">
                                    <div className="text-gray-400 mb-4">
                                        <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">No Matches Found</h3>
                                    <p className="text-gray-500">
                                        {localFilters.section !== 'all' || localFilters.internship !== 'all' || localFilters.search
                                            ? 'Try adjusting your filters or search criteria.'
                                            : 'Students need to complete their assessments to generate matches.'
                                        }
                                    </p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b border-gray-200">
                                                <th className="text-left py-3 px-4 font-semibold text-sm">
                                                    <div className="flex items-center gap-2">
                                                        <Checkbox
                                                            checked={selectedStudents.size === matchedStudents.length && matchedStudents.length > 0}
                                                            onCheckedChange={handleSelectAll}
                                                        />
                                                        Select All
                                                    </div>
                                                </th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Student</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Section</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Best Match</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Compatibility</th>
                                                <th className="text-left py-3 px-4 font-semibold text-sm">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {matchedStudents.map((student) => (
                                                <tr key={student.id} className="border-b border-gray-100 hover:bg-gray-50">
                                                    <td className="py-3 px-4">
                                                        <Checkbox
                                                            checked={selectedStudents.has(student.id)}
                                                            onCheckedChange={(checked) => handleSelectStudent(student.id, checked as boolean)}
                                                        />
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <div className="font-medium text-gray-900">
                                                                {student.last_name}, {student.first_name}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {student.student_number}
                                                            </div>
                                                            {student.middle_name && (
                                                                <div className="text-xs text-gray-400">
                                                                    {student.middle_name}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <Badge variant="outline">{student.section}</Badge>
                                                            {student.specialization && (
                                                                <div className="text-xs text-gray-500 mt-1">
                                                                    {student.specialization}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div>
                                                            <div className="font-medium text-gray-900">
                                                                {student.best_match?.internship?.position_title || 'Unknown Position'}
                                                            </div>
                                                            <div className="text-sm text-gray-500">
                                                                {student.best_match?.internship?.hte?.company_name || 'Unknown Company'}
                                                            </div>
                                                            <div className="text-xs text-gray-400">
                                                                {student.best_match?.internship?.department || 'Unknown Department'}
                                                            </div>
                                                            {student.best_match?.internship?.available_slots !== undefined && (
                                                                <div className="text-xs text-blue-600 font-medium mt-1">
                                                                    {student.best_match.internship.available_slots} slot{student.best_match.internship.available_slots !== 1 ? 's' : ''} available
                                                                </div>
                                                            )}
                                                            {student.best_match?.internship?.slot_count !== undefined && (
                                                                <div className="text-xs text-gray-500 mt-1">
                                                                    Total: {student.best_match.internship.slot_count} slot{student.best_match.internship.slot_count !== 1 ? 's' : ''}
                                                                </div>
                                                            )}
                                                            {/* Show status badge when viewing specific internship */}
                                                            {localFilters.internship !== 'all' && student.best_match?.status && (
                                                                <div className="mt-2">
                                                                    {getStatusBadge(student.best_match.status)}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center space-x-2">
                                                            <Badge 
                                                                className={getScoreColor(student.best_match?.compatibility_score || 0)}
                                                            >
                                                                {student.best_match?.compatibility_score || 0}%
                                                            </Badge>
                                                            <span className="text-xs text-gray-500">
                                                                {getScoreLabel(student.best_match?.compatibility_score || 0)}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex space-x-2">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleViewDetails(student)}
                                                                disabled={isLoading}
                                                                className="flex items-center gap-2"
                                                            >
                                                                <EyeIcon className="h-4 w-4" />
                                                                View Details
                                                            </Button>
                                                            
                                                            <Button
                                                                variant="default"
                                                                size="sm"
                                                                onClick={() => handleSingleApprove(student)}
                                                                disabled={isLoading}
                                                                className="flex items-center gap-2 bg-green-600 hover:bg-green-700"
                                                            >
                                                                <CheckCircleIcon className="h-4 w-4" />
                                                                Approve
                                                            </Button>
                                                            
                                                            <Button
                                                                variant="destructive"
                                                                size="sm"
                                                                onClick={() => handleSingleReject(student)}
                                                                disabled={isLoading}
                                                                className="flex items-center gap-2"
                                                            >
                                                                <XCircleIcon className="h-4 w-4" />
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
                </div>
            </AdminLayout>

            {/* Student Details Modal */}
            <StudentDetailsModal
                isOpen={isModalOpen}
                onClose={() => {
                    setIsModalOpen(false);
                    setSelectedStudent(null);
                }}
                student={selectedStudent}
            />

            {/* Slot Conflict Confirmation Dialog */}
            {showConflictDialog && conflictData && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg className="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900">Batch Approval Summary</h3>
                                <p className="text-sm text-gray-600">Review the placement assignments for selected students</p>
                            </div>
                        </div>
                        
                        <div className="mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <CheckCircleIcon className="w-5 h-5 text-green-600" />
                                        <h4 className="font-semibold text-green-800">Students Getting Approved</h4>
                                    </div>
                                    <p className="text-sm text-green-700">
                                        <strong>{conflictData.total_approved || 0}</strong> students will be placed in their best match
                                    </p>
                                </div>
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h4 className="font-semibold text-yellow-800">Students with Fallback</h4>
                                    </div>
                                    <p className="text-sm text-yellow-700">
                                        <strong>{conflictData.total_conflicts}</strong> students will be placed in fallback matches
                                    </p>
                                </div>
                            </div>
                            
                            {/* Students getting approved */}
                            {conflictData.approved_students && conflictData.approved_students.length > 0 && (
                                <div className="mb-6">
                                    <h4 className="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <CheckCircleIcon className="w-4 h-4 text-green-600" />
                                        Students Getting Their Best Match
                                    </h4>
                                    <div className="space-y-2 max-h-40 overflow-y-auto">
                                        {conflictData.approved_students.map((student, index: number) => (
                                            <div key={index} className="border border-green-200 rounded-lg p-3 bg-green-50">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <span className="font-medium text-gray-900">{student.student_name}</span>
                                                        <span className="text-sm text-gray-600 ml-2">→ {student.internship_title}</span>
                                                        <span className="text-xs text-gray-500 ml-2">({student.company_name})</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <Badge variant="secondary" className="text-xs">
                                                            {student.match_rank}
                                                        </Badge>
                                                        <Badge variant="outline" className="text-xs">
                                                            {student.compatibility_score}%
                                                        </Badge>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                            
                            {/* Students with fallback matches */}
                            {conflictData.conflicts && conflictData.conflicts.length > 0 && (
                                <div>
                                    <h4 className="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                        <svg className="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Students with Fallback Placements
                                    </h4>
                                    <div className="space-y-3 max-h-60 overflow-y-auto">
                                        {conflictData.conflicts.map((conflict, index: number) => (
                                            <div key={index} className="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                                                <div className="font-medium text-gray-900 mb-2">{conflict.student_name}</div>
                                                <div className="space-y-2 text-sm">
                                                    <div className="flex items-center gap-2">
                                                        <span className="text-red-600">❌</span>
                                                        <span className="text-gray-600">Best match unavailable:</span>
                                                        <span className="font-medium">{conflict.best_match.position_title}</span>
                                                        <span className="text-gray-500">({conflict.best_match.company_name})</span>

                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="text-green-600">✅</span>
                                                        <span className="text-gray-600">Will be placed in:</span>
                                                        <span className="font-medium">{conflict.fallback_match.position_title}</span>
                                                        <span className="text-gray-500">({conflict.fallback_match.company_name})</span>
                                                        <Badge variant="secondary" className="text-xs">
                                                            {conflict.fallback_match.available_slots} slots
                                                        </Badge>
                                                        <Badge variant="outline" className="text-xs">
                                                            {conflict.fallback_match.match_rank}
                                                        </Badge>
                                                    </div>
                                                    <div className="text-xs text-gray-500 ml-6">
                                                        Compatibility: {conflict.best_match.compatibility_score}% → {conflict.fallback_match.compatibility_score}%
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                        
                        <div className="flex justify-end gap-3">
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setShowConflictDialog(false);
                                    setConflictData(null);
                                }}
                            >
                                Cancel
                            </Button>
                            <Button
                                onClick={async () => {
                                    setShowConflictDialog(false);
                                    setConflictData(null);
                                    const csrfToken = getFreshCsrfToken();
                                    if (csrfToken) {
                                        await proceedWithBatchApproval(csrfToken);
                                    }
                                }}
                                className="bg-blue-600 hover:bg-blue-700"
                            >
                                Proceed with Placements
                            </Button>
                        </div>
                    </div>
                </div>
            )}


        </>
    );
}
