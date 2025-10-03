import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import AdminLayout from '@/layouts/admin/layout';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import { getRowNumber } from '@/lib/pagination-utils';
import StudentDetailsModal from '@/components/student-details-modal';
import { 
    TargetIcon,
    EyeIcon,
    CheckCircleIcon,
    XCircleIcon,
    SearchIcon,
    FilterIcon,
    UsersIcon,
    AlertTriangleIcon,
    InfoIcon,
    BriefcaseBusinessIcon,
    CaptionsIcon,
} from 'lucide-react';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Student Matches',
        href: '/student/matched',
    },
];

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

interface UnplacedStudent {
    id: number;
    student_number: string;
    first_name: string;
    last_name: string;
    middle_name?: string;
    section: string;
    specialization?: string;
    reason: string;
    has_available_matches: boolean;
    requires_manual_intervention: boolean;
    total_matches: number;
    rejected_matches: number;
}

interface Props {
    matchedStudents: MatchedStudent[];
    unplacedStudents: UnplacedStudent[];
    filters: Filters;
    statistics?: {
        total_students_with_assessments: number;
        students_with_matches: number;
        students_without_matches: number;
        unplaced_students: number;
    };
}

export default function StudentMatched({ matchedStudents, unplacedStudents = [], filters, statistics }: Props) {
    const { csrf_token } = usePage().props as { csrf_token?: string };
    const [selectedStudent, setSelectedStudent] = useState<MatchedStudent | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const [errorType, setErrorType] = useState<string | null>(null);
    const [showFilters, setShowFilters] = useState(false);
    const [activeTab, setActiveTab] = useState<'matched' | 'unplaced'>('matched');
    const [localFilters, setLocalFilters] = useState({
        section: filters.currentSection || 'all',
        internship: filters.currentInternship || 'all',
        search: filters.currentSearch || '',
    });

    // Determine if the "Students Without Matches" card should be shown
    const showStudentsWithoutMatchesCard = (statistics?.students_without_matches || 0) > 0;

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


    // Update local filters when props change
    useEffect(() => {
        setLocalFilters({
            section: filters.currentSection || 'all',
            internship: filters.currentInternship || 'all',
            search: filters.currentSearch || ''
        });
    }, [filters]);

    // Filter matched students based on local filters
    const filteredMatchedStudents = matchedStudents.filter(student => {
        const matchesSection = localFilters.section === 'all' || student.section === localFilters.section;
        const matchesInternship = localFilters.internship === 'all' || 
                                 student.best_match?.internship?.id.toString() === localFilters.internship;
        const matchesSearch = localFilters.search === '' || 
                             student.first_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             student.last_name.toLowerCase().includes(localFilters.search.toLowerCase()) ||
                             student.student_number.toLowerCase().includes(localFilters.search.toLowerCase());
        
        return matchesSection && matchesInternship && matchesSearch;
    });

    // Pagination hook
    const matchedPagination = usePagination({
        data: filteredMatchedStudents,
        itemsPerPage: 10,
        resetTrigger: localFilters, // Auto-reset when filters change
    });

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
                    Endorsed
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
            
            
            const response = await fetch(`/student/${student.id}/endorse`, {
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
            
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
                csrfToken = await refreshCsrfToken();
                
                // Retry the request with fresh token
                const retryResponse = await fetch(`/student/${student.id}/endorse`, {
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
                        router.reload({ only: ['matchedStudents'] });
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
            
            if (response.ok) {
                setErrorMessage('Student placement approved successfully!');
                setErrorType('success');
                setTimeout(() => {
                    router.reload({ only: ['matchedStudents'] });
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
            
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
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
                    const result = await retryResponse.json();
                    if (result.fallback) {
                        setErrorMessage(`Student rejected and moved to next match: ${result.new_internship.position_title} at ${result.new_internship.company_name} (${result.new_internship.compatibility_score}% compatibility)`);
                    } else {
                        setErrorMessage('Student placement rejected successfully!');
                    }
                    setErrorType('success');
                    setTimeout(() => {
                        router.reload({ only: ['matchedStudents'] });
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
            
            if (response.ok) {
                if (result.fallback) {
                    setErrorMessage(`Student rejected and moved to next match: ${result.new_internship.position_title} at ${result.new_internship.company_name} (${result.new_internship.compatibility_score}% compatibility)`);
                } else {
                    setErrorMessage('Student placement rejected successfully!');
                }
                setErrorType('success');
                setTimeout(() => {
                    router.reload({ only: ['matchedStudents'] });
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
            // Select only the students visible on the current page
            const currentPageIds = matchedPagination.paginatedData.map(s => s.id);
            setSelectedStudents(new Set(currentPageIds));
        } else {
            // Deselect only the students visible on the current page
            const currentPageIds = matchedPagination.paginatedData.map(s => s.id);
            const newSelected = new Set(selectedStudents);
            currentPageIds.forEach(id => newSelected.delete(id));
            setSelectedStudents(newSelected);
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
            
            // Use the new batch approval endpoint
            const response = await fetch('/student/batch-endorse', {
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
            
            
            // Handle CSRF token mismatch
            if (response.status === 419) {
                csrfToken = await refreshCsrfToken();
                
                // Retry the request with fresh token
                const retryResponse = await fetch('/student/batch-endorse', {
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
                    if (result.total_endorsed > 0) {
                        setErrorMessage(`Successfully endorsed ${result.total_endorsed} student(s)!`);
                        setErrorType('success');
                        setSelectedStudents(new Set());
                        setTimeout(() => {
                            router.reload({ only: ['matchedStudents'] });
                        }, 1500);
                    } else {
                        if (result.errors && result.errors.length > 0) {
                            setErrorMessage(`No students were endorsed. Errors: ${result.errors.join(', ')}`);
                        } else {
                            setErrorMessage('No students were endorsed. Please check the selection and try again.');
                        }
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
            
            if (response.ok) {
                // Success
                if (result.total_endorsed > 0) {
                    setErrorMessage(`Successfully endorsed ${result.total_endorsed} student(s)!`);
                    setErrorType('success');
                    setSelectedStudents(new Set());
                    setTimeout(() => {
                        router.reload({ only: ['matchedStudents'] });
                    }, 1500);
                } else {
                    if (result.errors && result.errors.length > 0) {
                        setErrorMessage(`No students were endorsed. Errors: ${result.errors.join(', ')}`);
                    } else {
                        setErrorMessage('No students were endorsed. Please check the selection and try again.');
                    }
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
                    router.reload({ only: ['matchedStudents'] });
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

            <AdminLayout breadcrumbs={breadcrumbs}>
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    {/* Header */}
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Student Matches</h1>
                            <p className="text-muted-foreground">
                                View and manage student-internship matches based on compatibility scores
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

                    {/* Tab Navigation */}
                    <div className="flex space-x-1 rounded-lg bg-muted p-1">
                        <Button
                            variant={activeTab === 'matched' ? 'default' : 'ghost'}
                            onClick={() => setActiveTab('matched')}
                            className="flex-1"
                        >
                            <UsersIcon className="h-4 w-4 mr-2" />
                            Students with Matches ({matchedStudents.length})
                        </Button>
                        <Button
                            variant={activeTab === 'unplaced' ? 'default' : 'ghost'}
                            onClick={() => setActiveTab('unplaced')}
                            className="flex-1"
                        >
                            <AlertTriangleIcon className="h-4 w-4 mr-2" />
                            Students Without Matches ({Array.isArray(unplacedStudents) ? unplacedStudents.length : 0})
                        </Button>
                    </div>





                    {/* Filters Section */}
                    {showFilters && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <FilterIcon className="h-5 w-5" />
                                    Filters & Search
                                </CardTitle>
                                <CardDescription>
                                    Filter students by section, internship, or search by name or student number
                                </CardDescription>
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
                            <div className="mt-4 p-4 bg-muted rounded-lg border">
                                <div className="flex items-start gap-3">
                                    <InfoIcon className="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <div className="space-y-2">
                                        <p className="text-sm text-muted-foreground">
                                            {getFilterDescription()}
                                        </p>
                                        <div className="space-y-1">
                                            <div className="flex items-center gap-2 text-xs text-blue-600">
                                                <InfoIcon className="h-3 w-3" />
                                                The system allows student placement approvals until all internship slots are filled (0 slots remaining).
                                            </div>
                                            <div className="flex items-center gap-2 text-xs text-green-600">
                                                <CheckCircleIcon className="h-3 w-3" />
                                                When viewing "All Internships", rejected matches are filtered out. When viewing a specific internship, rejected matches show with status labels.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    )}

                    {/* Content based on active tab */}
                    {activeTab === 'matched' ? (
                        <>
                            {/* Summary Cards */}
                            <div className={`grid grid-cols-1 md:grid-cols-${showStudentsWithoutMatchesCard ? 4 : 3} gap-4`}>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Students with Matches</CardTitle>
                                <UsersIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{filteredMatchedStudents.length}</div>
                                <p className="text-xs text-muted-foreground">
                                    Students with available matches
                                </p>
                            </CardContent>
                        </Card>

                        {showStudentsWithoutMatchesCard && (
                            <Card className="border-orange-200 bg-orange-50 dark:bg-orange-900/20">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium text-orange-800 dark:text-orange-200">No Matches Available</CardTitle>
                                    <AlertTriangleIcon className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-orange-800 dark:text-orange-200">{statistics?.students_without_matches || 0}</div>
                                    <p className="text-xs text-orange-700 dark:text-orange-300">
                                        Students with no available slots
                                    </p>
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total HTE</CardTitle>
                                <BriefcaseBusinessIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {new Set(filteredMatchedStudents.map(s => s.best_match?.internship?.hte?.company_name).filter(Boolean)).size}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Host training establishments
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Internships</CardTitle>
                                <CaptionsIcon className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {new Set(filteredMatchedStudents.map(s => s.best_match?.internship?.id).filter(Boolean)).size}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Available intenrship positions
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Students Without Matches Info */}
                    {statistics && statistics.students_without_matches > 0 && (
                        <Card className="border-l-4 border-l-orange-500 bg-orange-50 dark:bg-orange-900/20">
                            <CardContent className="p-4">
                                <div className="flex items-start gap-3">
                                    <AlertTriangleIcon className="h-5 w-5 text-orange-600 mt-0.5 flex-shrink-0" />
                                    <div className="flex-1">
                                        <div className="font-medium text-orange-800 dark:text-orange-200">
                                            Students Without Available Matches
                                        </div>
                                        <div className="mt-1 text-sm text-orange-700 dark:text-orange-300">
                                            {statistics.students_without_matches} student{statistics.students_without_matches !== 1 ? 's' : ''} have exhausted all their internship matches and are not shown in the list below. These students will be automatically placed during the deadline automatic placement process if any slots become available.
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Error Display */}
                    {errorMessage && (
                        <Card className={`border-l-4 ${
                            errorType === 'success' ? 'border-l-green-500 bg-green-50 dark:bg-green-900/20' : 'border-l-red-500 bg-red-50 dark:bg-red-900/20'
                        }`}>
                            <CardContent className="p-4">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-start gap-3 flex-1">
                                        {errorType === 'success' ? (
                                            <CheckCircleIcon className="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" />
                                        ) : (
                                            <XCircleIcon className="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" />
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
                                        onClick={() => {
                                            setErrorMessage(null);
                                            setErrorType(null);
                                        }}
                                        className="text-muted-foreground hover:text-foreground h-10 w-10 p-0 min-h-[44px] min-w-[44px]"
                                    >
                                        <XCircleIcon className="h-6 w-6" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Batch Actions */}
                    {selectedStudents.size > 0 && (
                        <Card className="border-l-4 border-l-blue-500 bg-blue-50 dark:bg-blue-900/20">
                            <CardContent className="p-4">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-start gap-3 flex-1">
                                        <TargetIcon className="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" />
                                        <div className="flex-1">
                                            <div className="font-medium text-blue-800 dark:text-blue-200">
                                                Batch Actions ({selectedStudents.size} student{selectedStudents.size !== 1 ? 's' : ''} selected)
                                            </div>
                                            <div className="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                                You can endorse or reject multiple students at once. The system will allow endorsements until all slots are filled (0 slots remaining).
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex gap-2">
                                        <Button
                                            variant="default"
                                            onClick={handleBatchApprove}
                                            disabled={isLoading}
                                            className="bg-green-600 hover:bg-green-700"
                                        >
                                            <CheckCircleIcon className="h-4 w-4 mr-2" />
                                            Endorse All ({selectedStudents.size})
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
                            <CardTitle className="flex items-center gap-2">
                                <TargetIcon className="h-5 w-5" />
                                Student-Internship Matches
                            </CardTitle>
                            <CardDescription>
                                Review and manage student placements based on compatibility scores
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {filteredMatchedStudents.length === 0 ? (
                                <div className="text-center py-12">
                                    <TargetIcon className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                    <h3 className="text-lg font-medium mb-2">No Matches Found</h3>
                                    <p className="text-muted-foreground">
                                        {localFilters.section !== 'all' || localFilters.internship !== 'all' || localFilters.search
                                            ? 'Try adjusting your filters or search criteria.'
                                            : 'Students need to complete their assessments to generate matches.'
                                        }
                                    </p>
                                </div>
                            ) : (
                                <>
                                    {/* Mobile Card Layout */}
                                    <div className="block md:hidden space-y-3">
                                        {matchedPagination.paginatedData.map((student, index) => (
                                            <Card key={student.id} className="p-4">
                                                <div className="space-y-3">
                                                    {/* Header with selection and row number */}
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex items-center gap-3">
                                                            <Checkbox
                                                                checked={selectedStudents.has(student.id)}
                                                                onCheckedChange={(checked) => handleSelectStudent(student.id, checked as boolean)}
                                                            />
                                                            <span className="text-xs text-muted-foreground font-mono">
                                                                #{getRowNumber(matchedPagination.currentPage, 10, index)}
                                                            </span>
                                                        </div>
                                                        <Badge 
                                                            className={getScoreColor(student.best_match?.compatibility_score || 0)}
                                                        >
                                                            {Math.round(student.best_match?.compatibility_score || 0)}%
                                                        </Badge>
                                                    </div>

                                                    {/* Student Info */}
                                                    <div>
                                                        <div className="font-medium text-base">
                                                            {student.last_name}, {student.first_name}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {student.student_number}
                                                        </div>
                                                        <div className="flex items-center gap-2 mt-1">
                                                            <Badge variant="outline" className="text-xs">{student.section}</Badge>
                                                            {student.specialization && (
                                                                <span className="text-xs text-muted-foreground">
                                                                    {student.specialization}
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>

                                                    {/* Internship Match */}
                                                    <div className="border-l-2 border-primary/20 pl-3">
                                                        <div className="font-medium text-sm">
                                                            {student.best_match?.internship?.position_title || 'Unknown Position'}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            {student.best_match?.internship?.hte?.company_name || 'Unknown Company'}
                                                        </div>
                                                        <div className="text-xs text-muted-foreground">
                                                            {student.best_match?.internship?.department || 'Unknown Department'}
                                                        </div>
                                                        {student.best_match?.internship?.available_slots !== undefined && (
                                                            <div className="text-xs text-blue-600 font-medium mt-1">
                                                                {student.best_match.internship.available_slots} slot{student.best_match.internship.available_slots !== 1 ? 's' : ''} available
                                                            </div>
                                                        )}
                                                        {localFilters.internship !== 'all' && student.best_match?.status && (
                                                            <div className="mt-2">
                                                                {getStatusBadge(student.best_match.status)}
                                                            </div>
                                                        )}
                                                    </div>

                                                    {/* Actions */}
                                                    <div className="flex gap-2 pt-2">
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() => handleViewDetails(student)}
                                                            disabled={isLoading}
                                                            className="flex-1"
                                                        >
                                                            <EyeIcon className="h-4 w-4 mr-1" />
                                                            View
                                                        </Button>
                                                        <Button
                                                            variant="default"
                                                            size="sm"
                                                            onClick={() => handleSingleApprove(student)}
                                                            disabled={isLoading}
                                                            className="bg-green-600 hover:bg-green-700 flex-1"
                                                        >
                                                            <CheckCircleIcon className="h-4 w-4 mr-1" />
                                                            Endorse
                                                        </Button>
                                                        <Button
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() => handleSingleReject(student)}
                                                            disabled={isLoading}
                                                            className="flex-1"
                                                        >
                                                            <XCircleIcon className="h-4 w-4 mr-1" />
                                                            Reject
                                                        </Button>
                                                    </div>
                                                </div>
                                            </Card>
                                        ))}
                                    </div>

                                    {/* Desktop Table Layout */}
                                    <div className="hidden md:block">
                                        <div className="mb-4 flex items-center gap-2">
                                                        <Checkbox
                                                            checked={matchedPagination.paginatedData.length > 0 && matchedPagination.paginatedData.every(student => selectedStudents.has(student.id))}
                                                            onCheckedChange={handleSelectAll}
                                                        />
                                            <span className="text-sm text-muted-foreground">
                                                        Select All ({matchedPagination.paginatedData.length})
                                            </span>
                                                    </div>
                                        
                                        <div className="overflow-x-auto">
                                            <table className="w-full text-sm">
                                                <thead>
                                                    <tr className="border-b">
                                                        <th className="text-center p-2 font-medium text-muted-foreground w-12">#</th>
                                                        <th className="text-left p-2 font-medium text-muted-foreground w-8"></th>
                                                        <th className="text-left p-2 font-medium text-muted-foreground">Student</th>
                                                        <th className="text-left p-2 font-medium text-muted-foreground w-20">Section</th>
                                                        <th className="text-left p-2 font-medium text-muted-foreground">Best Match</th>
                                                        <th className="text-center p-2 font-medium text-muted-foreground w-24">Score</th>
                                                        <th className="text-right p-2 font-medium text-muted-foreground w-48">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {matchedPagination.paginatedData.map((student, index) => (
                                                <tr key={student.id} className="border-b hover:bg-muted/50 transition-colors">
                                                            <td className="text-center p-2 font-mono text-xs text-muted-foreground">
                                                        {getRowNumber(matchedPagination.currentPage, 10, index)}
                                                    </td>
                                                            <td className="p-2">
                                                        <Checkbox
                                                            checked={selectedStudents.has(student.id)}
                                                            onCheckedChange={(checked) => handleSelectStudent(student.id, checked as boolean)}
                                                        />
                                                    </td>
                                                            <td className="p-2">
                                                        <div>
                                                                    <div className="font-medium text-sm">
                                                                {student.last_name}, {student.first_name}
                                                            </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                {student.student_number}
                                                            </div>
                                                        </div>
                                                    </td>
                                                            <td className="p-2">
                                                                <Badge variant="outline" className="text-xs">{student.section}</Badge>
                                                    </td>
                                                            <td className="p-2">
                                                        <div>
                                                                    <div className="font-medium text-sm">
                                                                {student.best_match?.internship?.position_title || 'Unknown Position'}
                                                            </div>
                                                                    <div className="text-xs text-muted-foreground">
                                                                {student.best_match?.internship?.hte?.company_name || 'Unknown Company'}
                                                            </div>
                                                            {student.best_match?.internship?.available_slots !== undefined && (
                                                                        <div className="text-xs text-blue-600 font-medium">
                                                                            {student.best_match.internship.available_slots} slot{student.best_match.internship.available_slots !== 1 ? 's' : ''} left
                                                                </div>
                                                            )}
                                                            {localFilters.internship !== 'all' && student.best_match?.status && (
                                                                        <div className="mt-1">
                                                                    {getStatusBadge(student.best_match.status)}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                            <td className="p-2 text-center">
                                                            <Badge 
                                                                className={getScoreColor(student.best_match?.compatibility_score || 0)}
                                                            >
                                                                {Math.round(student.best_match?.compatibility_score || 0)}%
                                                            </Badge>
                                                    </td>
                                                            <td className="p-2 text-right">
                                                                <div className="flex items-center justify-end gap-1">
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                onClick={() => handleViewDetails(student)}
                                                                disabled={isLoading}
                                                                        className="h-8 px-2"
                                                            >
                                                                        <EyeIcon className="h-3 w-3" />
                                                            </Button>
                                                            
                                                            <Button
                                                                variant="default"
                                                                size="sm"
                                                                onClick={() => handleSingleApprove(student)}
                                                                disabled={isLoading}
                                                                        className="bg-green-600 hover:bg-green-700 h-8 px-2"
                                                            >
                                                                        <CheckCircleIcon className="h-3 w-3" />
                                                            </Button>
                                                            
                                                            <Button
                                                                variant="destructive"
                                                                size="sm"
                                                                onClick={() => handleSingleReject(student)}
                                                                disabled={isLoading}
                                                                        className="h-8 px-2"
                                                            >
                                                                        <XCircleIcon className="h-3 w-3" />
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>

                            {/* Pagination */}
                            <Pagination
                                currentPage={matchedPagination.currentPage}
                                totalPages={matchedPagination.totalPages}
                                onPageChange={matchedPagination.handlePageChange}
                                showSummary={true}
                                totalItems={filteredMatchedStudents.length}
                                itemsPerPage={10}
                            />
                        </>
                    ) : (
                        <>
                            {/* Unplaced Students Section */}
                            <div className="space-y-6">
                                {/* Unplaced Students Summary */}
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <Card className="border-red-200 bg-red-50 dark:bg-red-900/20">
                                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                            <CardTitle className="text-sm font-medium text-red-800 dark:text-red-200">Unplaced Students</CardTitle>
                                            <AlertTriangleIcon className="h-4 w-4 text-red-600 dark:text-red-400" />
                                        </CardHeader>
                                        <CardContent>
                                            <div className="text-2xl font-bold text-red-800 dark:text-red-200">{Array.isArray(unplacedStudents) ? unplacedStudents.length : 0}</div>
                                            <p className="text-xs text-red-700 dark:text-red-300">
                                                Students who couldn't be placed
                                            </p>
                                        </CardContent>
                                    </Card>


                                    <Card>
                                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                            <CardTitle className="text-sm font-medium">No Available Slots</CardTitle>
                                            <UsersIcon className="h-4 w-4 text-muted-foreground" />
                                        </CardHeader>
                                        <CardContent>
                                            <div className="text-2xl font-bold">
                                                {Array.isArray(unplacedStudents) ? unplacedStudents.filter(s => s.reason === 'All matches have no available slots').length : 0}
                                            </div>
                                            <p className="text-xs text-muted-foreground">
                                                Students with exhausted matches
                                            </p>
                                        </CardContent>
                                    </Card>

                                    <Card className="border-orange-200 bg-orange-50 dark:bg-orange-900/20">
                                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                            <CardTitle className="text-sm font-medium text-orange-800 dark:text-orange-200">Manual Intervention</CardTitle>
                                            <AlertTriangleIcon className="h-4 w-4 text-orange-600 dark:text-orange-400" />
                                        </CardHeader>
                                        <CardContent>
                                            <div className="text-2xl font-bold text-orange-800 dark:text-orange-200">
                                                {Array.isArray(unplacedStudents) ? unplacedStudents.filter(s => s.requires_manual_intervention).length : 0}
                                            </div>
                                            <p className="text-xs text-orange-700 dark:text-orange-300">
                                                Students requiring admin action
                                            </p>
                                        </CardContent>
                                    </Card>
                                </div>

                                {/* Unplaced Students Table */}
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <AlertTriangleIcon className="h-5 w-5" />
                                            Students Unable to be Placed
                                        </CardTitle>
                                        <CardDescription>
                                            These students will be handled by the automatic placement system during deadline processing
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent>
                                        {!Array.isArray(unplacedStudents) || unplacedStudents.length === 0 ? (
                                            <div className="text-center py-12">
                                                <CheckCircleIcon className="h-12 w-12 text-green-400 mx-auto mb-4" />
                                                <h3 className="text-lg font-medium mb-2">All Students Have Matches</h3>
                                                <p className="text-muted-foreground">
                                                    Great! All students with assessments have available internship matches.
                                                </p>
                                            </div>
                                        ) : (
                                            <>
                                                {/* Mobile Card Layout for Unplaced Students */}
                                                <div className="block md:hidden space-y-3">
                                                    {Array.isArray(unplacedStudents) && unplacedStudents.map((student) => (
                                                        <Card key={student.id} className="p-4">
                                                            <div className="space-y-3">
                                                                {/* Student Info */}
                                                                <div>
                                                                    <div className="font-medium text-base">
                                                                        {student.last_name}, {student.first_name}
                                                                    </div>
                                                                    <div className="text-sm text-muted-foreground">
                                                                        {student.student_number}
                                                                    </div>
                                                                    <div className="flex items-center gap-2 mt-1">
                                                                        <Badge variant="outline" className="text-xs">{student.section}</Badge>
                                                                        {student.specialization && (
                                                                            <span className="text-xs text-muted-foreground">
                                                                                {student.specialization}
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                </div>

                                                                {/* Status and Reason */}
                                                                <div className="space-y-2">
                                                                    <div className="flex items-center gap-2">
                                                                        {student.requires_manual_intervention ? (
                                                                            <Badge variant="destructive" className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs">
                                                                                Auto-Placement Failed
                                                                            </Badge>
                                                                        ) : (
                                                                            <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 text-xs">
                                                                                Awaiting Auto-Placement
                                                                            </Badge>
                                                                        )}
                                                                    </div>
                                                                    <div className="text-sm text-muted-foreground">
                                                                        <strong>Reason:</strong> {student.reason}
                                                                    </div>
                                                                </div>

                                                                {/* Match Statistics */}
                                                                <div className="bg-muted/50 rounded-lg p-3">
                                                                    <div className="text-sm font-medium mb-1">Match Statistics</div>
                                                                    <div className="grid grid-cols-2 gap-2 text-xs">
                                                                        <div>
                                                                            <span className="text-muted-foreground">Total Matches:</span>
                                                                            <span className="ml-1 font-medium">{student.total_matches}</span>
                                                                        </div>
                                                                        <div>
                                                                            <span className="text-muted-foreground">Rejected:</span>
                                                                            <span className="ml-1 font-medium">{student.rejected_matches}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {/* Action Required */}
                                                                <div className="border-l-2 border-orange-200 pl-3">
                                                                    {student.requires_manual_intervention ? (
                                                                        <div>
                                                                            <Badge variant="destructive" className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs mb-1">
                                                                                Manual Intervention Required
                                                                            </Badge>
                                                                        </div>
                                                                    ) : (
                                                                        <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-xs">
                                                                            Await deadline for Auto-Placement
                                                                        </Badge>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </Card>
                                                    ))}
                                                </div>

                                                {/* Desktop Table Layout for Unplaced Students */}
                                                <div className="hidden md:block">
                                            <div className="overflow-x-auto">
                                                        <table className="w-full text-sm">
                                                    <thead>
                                                        <tr className="border-b">
                                                                    <th className="text-left p-2 font-medium text-muted-foreground">Student</th>
                                                                    <th className="text-left p-2 font-medium text-muted-foreground w-20">Section</th>
                                                                    <th className="text-left p-2 font-medium text-muted-foreground">Reason</th>
                                                                    <th className="text-center p-2 font-medium text-muted-foreground w-24">Matches</th>
                                                                    <th className="text-center p-2 font-medium text-muted-foreground w-32">Status</th>
                                                                    <th className="text-left p-2 font-medium text-muted-foreground">Action Required</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {Array.isArray(unplacedStudents) && unplacedStudents.map((student) => (
                                                            <tr key={student.id} className="border-b hover:bg-muted/50 transition-colors">
                                                                        <td className="p-2">
                                                                    <div>
                                                                                <div className="font-medium text-sm">
                                                                            {student.last_name}, {student.first_name}
                                                                        </div>
                                                                                <div className="text-xs text-muted-foreground">
                                                                            {student.student_number}
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                        <td className="p-2">
                                                                            <Badge variant="outline" className="text-xs">{student.section}</Badge>
                                                                </td>
                                                                        <td className="p-2">
                                                                            <div className="text-sm max-w-xs truncate" title={student.reason}>
                                                                        {student.reason}
                                                                    </div>
                                                                </td>
                                                                        <td className="p-2 text-center">
                                                                    <div className="text-sm">
                                                                                <div className="font-medium">{student.total_matches}</div>
                                                                                <div className="text-xs text-muted-foreground">
                                                                                    {student.rejected_matches} rejected
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                        <td className="p-2 text-center">
                                                                    {student.requires_manual_intervention ? (
                                                                                <Badge variant="destructive" className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs">
                                                                            Auto-Placement Failed
                                                                        </Badge>
                                                                    ) : (
                                                                                <Badge className="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 text-xs">
                                                                            Awaiting Auto-Placement
                                                                        </Badge>
                                                                    )}
                                                                </td>
                                                                        <td className="p-2">
                                                                    {student.requires_manual_intervention ? (
                                                                                <div className="space-y-1">
                                                                                    <Badge variant="destructive" className="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs">
                                                                                Manual Intervention Required
                                                                            </Badge>
                                                                        </div>
                                                                    ) : (
                                                                                <Badge className="bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-xs">
                                                                                    Await deadline for Auto-Placement
                                                                        </Badge>
                                                                    )}
                                                                </td>
                                                            </tr>
                                                        ))}
                                                    </tbody>
                                                </table>
                                            </div>
                                                </div>
                                            </>
                                        )}
                                    </CardContent>
                                </Card>
                            </div>
                        </>
                    )}
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
                    <div className="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                <AlertTriangleIcon className="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">Batch Approval Summary</h3>
                                <p className="text-sm text-gray-600 dark:text-gray-400">Review the placement assignments for selected students</p>
                            </div>
                        </div>
                        
                        <div className="mb-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div className="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <CheckCircleIcon className="w-5 h-5 text-green-600 dark:text-green-400" />
                                        <h4 className="font-semibold text-green-800 dark:text-green-200">Students Getting Endorsed</h4>
                                    </div>
                                    <p className="text-sm text-green-700 dark:text-green-300">
                                        <strong>{conflictData.total_approved || 0}</strong> students will be placed in their best match
                                    </p>
                                </div>
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <div className="flex items-center gap-2 mb-2">
                                        <AlertTriangleIcon className="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                        <h4 className="font-semibold text-yellow-800 dark:text-yellow-200">Students with Fallback</h4>
                                    </div>
                                    <p className="text-sm text-yellow-700 dark:text-yellow-300">
                                        <strong>{conflictData.total_conflicts}</strong> students will be placed in fallback matches
                                    </p>
                                </div>
                            </div>
                            
                            {/* Students getting approved */}
                            {conflictData.approved_students && conflictData.approved_students.length > 0 && (
                                <div className="mb-6">
                                    <h4 className="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <CheckCircleIcon className="w-4 h-4 text-green-600 dark:text-green-400" />
                                        Students Getting Their Best Match
                                    </h4>
                                    <div className="space-y-2 max-h-40 overflow-y-auto">
                                        {conflictData.approved_students.map((student, index: number) => (
                                            <div key={index} className="border border-green-200 dark:border-green-800 rounded-lg p-3 bg-green-50 dark:bg-green-900/20">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <span className="font-medium text-gray-900 dark:text-white">{student.student_name}</span>
                                                        <span className="text-sm text-gray-600 dark:text-gray-400 ml-2">→ {student.internship_title}</span>
                                                        <span className="text-xs text-gray-500 dark:text-gray-500 ml-2">({student.company_name})</span>
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
                                    <h4 className="font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                                        <AlertTriangleIcon className="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                                        Students with Fallback Placements
                                    </h4>
                                    <div className="space-y-3 max-h-60 overflow-y-auto">
                                        {conflictData.conflicts.map((conflict, index: number) => (
                                            <div key={index} className="border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 bg-yellow-50 dark:bg-yellow-900/20">
                                                <div className="font-medium text-gray-900 dark:text-white mb-2">{conflict.student_name}</div>
                                                <div className="space-y-2 text-sm">
                                                    <div className="flex items-center gap-2">
                                                        <XCircleIcon className="w-4 h-4 text-red-600 dark:text-red-400" />
                                                        <span className="text-gray-600 dark:text-gray-400">Best match unavailable:</span>
                                                        <span className="font-medium">{conflict.best_match.position_title}</span>
                                                        <span className="text-gray-500 dark:text-gray-500">({conflict.best_match.company_name})</span>
                                                    </div>
                                                    <div className="flex items-center gap-2">
                                                        <CheckCircleIcon className="w-4 h-4 text-green-600 dark:text-green-400" />
                                                        <span className="text-gray-600 dark:text-gray-400">Will be placed in:</span>
                                                        <span className="font-medium">{conflict.fallback_match.position_title}</span>
                                                        <span className="text-gray-500 dark:text-gray-500">({conflict.fallback_match.company_name})</span>
                                                        <Badge variant="secondary" className="text-xs">
                                                            {conflict.fallback_match.available_slots} slots
                                                        </Badge>
                                                        <Badge variant="outline" className="text-xs">
                                                            {conflict.fallback_match.match_rank}
                                                        </Badge>
                                                    </div>
                                                    <div className="text-xs text-gray-500 dark:text-gray-500 ml-6">
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
