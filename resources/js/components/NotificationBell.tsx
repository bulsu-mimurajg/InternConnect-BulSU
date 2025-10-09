import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Pagination } from '@/components/ui/pagination';
import { usePagination } from '@/hooks/usePagination';
import {
    BellIcon,
    CheckIcon,
    ClockIcon,
    UsersIcon,
    CalendarIcon,
    RotateCcwIcon,
    ListIcon,
    MailIcon,
    FileTextIcon,
    TargetIcon,
    CheckCircleIcon,
    ChevronLeft,
    ChevronRight
} from 'lucide-react';
import { router, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    is_read: boolean;
    created_at: string;
    updated_at: string;
    data?: Record<string, unknown>;
}

interface NotificationBellProps {
    initialCount?: number;
}

interface UserRole {
    name: string;
    id?: number;
}

interface AuthUser {
    id: number;
    name: string;
    email: string;
    roles: UserRole[];
}

interface SharedData {
    auth: {
        user: AuthUser;
        role: string;
    };
    csrf_token?: string;
    [key: string]: unknown;
}

interface FilterButton {
    key: 'all' | 'endorsement' | 'deadline' | 'placement' | 'approval';
    label: string;
}

export default function NotificationBell({ initialCount = 0 }: NotificationBellProps) {
    const { auth, csrf_token } = usePage<SharedData>().props;
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(initialCount);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);
    const [isFiltering, setIsFiltering] = useState(false);
    const [filter, setFilter] = useState<'all' | 'endorsement' | 'deadline' | 'placement' | 'approval'>('all');
    const [showRead, setShowRead] = useState(true);
    const [lastLocalUpdate, setLastLocalUpdate] = useState<{[key: number]: number}>({});
    const [expandedNotifications, setExpandedNotifications] = useState<Set<number>>(new Set());
    const [pagination, setPagination] = useState({
        current_page: 1,
        per_page: 10,
        total: 0,
        last_page: 1,
        from: 0,
        to: 0,
    });


    // Helper function to refresh CSRF token from server
    const refreshCsrfToken = async (): Promise<string> => {
        try {
            const response = await fetch('/csrf-token', {
                method: 'GET',
                credentials: 'same-origin',
            });

            if (response.ok) {
                const data = await response.json();
                return data.token || '';
            }
        } catch (error) {
            console.error('Failed to refresh CSRF token:', error);
        }
        return '';
    };

    // Helper function to get CSRF token from multiple sources
    const getCsrfToken = (): string => {
        // Try Inertia props first (most reliable)
        if (csrf_token) {
            return csrf_token;
        }

        // Try meta tag
        const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (metaToken) {
            return metaToken;
        }

        // Try to get from window object (Laravel sometimes puts it there)
        const windowToken = (window as any).Laravel?.csrfToken;
        if (windowToken) {
            return windowToken;
        }

        // Try to get from cookies
        const cookies = document.cookie.split(';');
        const csrfCookie = cookies.find(cookie => cookie.trim().startsWith('XSRF-TOKEN='));
        if (csrfCookie) {
            return decodeURIComponent(csrfCookie.split('=')[1]);
        }

        return '';
    };

    const fetchNotifications = async (page = pagination.current_page, filterToUse = filter, showReadToUse = showRead) => {
        setIsLoading(true);
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                per_page: pagination.per_page.toString(),
                filter: filterToUse,
                show_read: showReadToUse.toString(),
            });

            const response = await fetch(`/notifications/get?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            // Check if response is OK and is JSON
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const responseText = await response.text();
                console.error('Received non-JSON response:', responseText.substring(0, 200));
                throw new Error('Expected JSON response but received HTML or other format');
            }

            const data = await response.json();
            const serverNotifications = data.notifications || [];

            // Preserve local changes by merging with server data
            setNotifications(prev => {
                const merged = serverNotifications.map((serverNotif: Notification) => {
                    const localTimestamp = lastLocalUpdate[serverNotif.id];
                    const serverTimestamp = new Date(serverNotif.updated_at).getTime();

                    // If we have a local update that's more recent, preserve the local state
                    if (localTimestamp && localTimestamp > serverTimestamp) {
                        const localNotif = prev.find(p => p.id === serverNotif.id);
                        return localNotif || serverNotif;
                    }

                    return serverNotif;
                });

                return merged;
            });

            setUnreadCount(data.unreadCount || 0);

            // Validate pagination data from server
            const serverPagination = data.pagination || pagination;
            const validatedPagination = {
                ...serverPagination,
                current_page: Math.max(1, Math.min(serverPagination.current_page || 1, serverPagination.last_page || 1)),
                last_page: Math.max(1, serverPagination.last_page || 1),
                per_page: Math.max(1, serverPagination.per_page || 10),
                total: Math.max(0, serverPagination.total || 0),
            };

            setPagination(validatedPagination);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);

            // If there's an error fetching notifications, set empty state but don't break the UI
            setNotifications([]);
            setUnreadCount(0);
            setPagination({
                current_page: 1,
                per_page: pagination.per_page,
                total: 0,
                last_page: 1,
                from: 0,
                to: 0,
            });
        } finally {
            setIsLoading(false);
        }
    };

    const markAsRead = async (notificationId: number): Promise<boolean> => {
        try {
            // Get CSRF token using helper function
            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                console.error('CSRF token not found in any source');
                return false;
            }

            const response = await fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error(`HTTP error! status: ${response.status}, response: ${errorText}`);

                // If it's a CSRF token mismatch (419), try to refresh the token and retry
                if (response.status === 419) {
                    const newToken = await refreshCsrfToken();
                    if (newToken) {
                        const retryResponse = await fetch(`/notifications/${notificationId}/mark-read`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': newToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });

                        if (retryResponse.ok) {
                            const retryResult = await retryResponse.json();
                            if (retryResult.success) {
                                // Update local state
                                setNotifications(prev =>
                                    prev.map(notif =>
                                        notif.id === notificationId ? { ...notif, is_read: true } : notif
                                    )
                                );
                                setUnreadCount(prev => Math.max(0, prev - 1));

                                // If showing unread only, remove the notification from the list
                                if (!showRead) {
                                    setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
                                    setPagination(prev => ({
                                        ...prev,
                                        total: Math.max(0, prev.total - 1),
                                        from: Math.max(1, prev.from - 1),
                                        to: Math.max(0, prev.to - 1)
                                    }));
                                }
                                return true;
                            }
                        }
                    }
                }

                return false;
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const responseText = await response.text();
                console.error('Received non-JSON response for markAsRead:', responseText.substring(0, 200));
                return false;
            }

            const result = await response.json();

            if (result.success) {
                // Record local update timestamp
                const now = Date.now();
                setLastLocalUpdate(prev => ({
                    ...prev,
                    [notificationId]: now
                }));

                // Update local state
                setNotifications(prev =>
                    prev.map(notif =>
                        notif.id === notificationId ? { ...notif, is_read: true } : notif
                    )
                );
                setUnreadCount(prev => Math.max(0, prev - 1));

                // If showing unread only, remove the notification from the list
                if (!showRead) {
                    setNotifications(prev => prev.filter(notif => notif.id !== notificationId));
                    setPagination(prev => ({
                        ...prev,
                        total: Math.max(0, prev.total - 1),
                        from: Math.max(1, prev.from - 1),
                        to: Math.max(0, prev.to - 1)
                    }));
                }
                return true;
            } else {
                console.error('Server returned error:', result);
                return false;
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
            // Don't update UI optimistically on network errors to avoid inconsistent state
            return false;
        }
    };

    const markAllAsRead = async () => {
        try {
            // Get CSRF token using helper function
            const csrfToken = getCsrfToken();

            if (!csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                setNotifications(prev =>
                    prev.map(notif => ({ ...notif, is_read: true }))
                );
                setUnreadCount(0);

                // If showing unread only, clear the list since all are now read
                if (!showRead) {
                    setNotifications([]);
                    setPagination(prev => ({ ...prev, total: 0, last_page: 1 }));
                }
            } else {
                console.error('Server returned error:', result);
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
            // Still update UI optimistically
            setNotifications(prev =>
                prev.map(notif => ({ ...notif, is_read: true }))
            );
            setUnreadCount(0);
        }
    };

    const markAsUnread = async (notificationId: number) => {
        try {
            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const response = await fetch(`/notifications/${notificationId}/mark-unread`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                // Record local update timestamp
                const now = Date.now();
                setLastLocalUpdate(prev => ({
                    ...prev,
                    [notificationId]: now
                }));

                // Update local state
                setNotifications(prev =>
                    prev.map(notif =>
                        notif.id === notificationId ? { ...notif, is_read: false } : notif
                    )
                );
                setUnreadCount(prev => prev + 1);

                // If showing unread only, refetch to ensure proper filtering
                if (!showRead) {
                    fetchNotifications(pagination.current_page, filter, false);
                }
            } else {
                console.error('Server returned error:', result);
            }
        } catch (error) {
            console.error('Failed to mark notification as unread:', error);
        }
    };

    // Handle pagination changes
    const handlePageChange = (page: number) => {
        // Validate page bounds
        if (page < 1) {
            page = 1;
        }
        if (page > pagination.last_page) {
            page = pagination.last_page;
        }

        // Only proceed if page is valid and different from current
        if (page === pagination.current_page) {
            return;
        }

        // Update pagination state first
        setPagination(prev => ({ ...prev, current_page: page }));

        fetchNotifications(page, filter, showRead);
    };

    // Handle filter changes with debouncing
    const handleFilterChange = (newFilter: 'all' | 'endorsement' | 'deadline' | 'placement' | 'approval') => {
        // Prevent multiple filter changes
        if (isFiltering || newFilter === filter) {
            return;
        }

        setIsFiltering(true);
        setFilter(newFilter);
        setPagination(prev => ({ ...prev, current_page: 1 }));

        // Clear notifications immediately to prevent showing wrong notifications
        setNotifications([]);
        setIsLoading(true);

        // Debounce the API call to prevent jittering
        setTimeout(() => {
            fetchNotifications(1, newFilter, showRead).finally(() => {
                setIsFiltering(false);
            });
        }, 150);
    };

    // Handle show read toggle with debouncing
    const handleShowReadToggle = () => {
        if (isFiltering) {
            return;
        }

        setIsFiltering(true);
        const newShowRead = !showRead;
        setShowRead(newShowRead);
        setPagination(prev => ({ ...prev, current_page: 1 }));

        // Apply immediate client-side filtering for instant UI feedback
        if (newShowRead) {
            // Show all notifications (no filtering needed)
            setNotifications(prev => prev);
        } else {
            // Show only unread notifications immediately
            setNotifications(prev => prev.filter(notification => !notification.is_read));
        }
        setIsLoading(true);

        // Debounce the API call to prevent jittering
        setTimeout(() => {
            fetchNotifications(1, filter, newShowRead).finally(() => {
                setIsFiltering(false);
            });
        }, 150);
    };

    const getFilteredNotifications = () => {
        // Apply client-side filtering as a fallback for immediate UI updates
        if (!showRead) {
            return notifications.filter(notification => !notification.is_read);
        }
        return notifications;
    };

    const toggleNotificationExpansion = (notificationId: number) => {
        setExpandedNotifications(prev => {
            const newSet = new Set(prev);
            if (newSet.has(notificationId)) {
                newSet.delete(notificationId);
            } else {
                newSet.add(notificationId);
            }
            return newSet;
        });
    };

    const isNotificationExpanded = (notificationId: number) => {
        return expandedNotifications.has(notificationId);
    };

    const shouldShowSeeMore = (message: string) => {
        // Show "See more" if message is longer than approximately 80 characters
        // This is a rough estimate for when text would be truncated to 2 lines
        return message.length > 80;
    };

    const handleNotificationClick = async (notification: Notification) => {
        // Prevent multiple clicks during processing
        if (isProcessing) {
            return;
        }

        setIsProcessing(true);

        try {
            // Helper function to check if user has a specific role
            const hasRole = (roleName: string): boolean => {
                return auth.user?.roles?.some((role: UserRole) => role.name === roleName) || false;
            };

            // Mark as read when clicked (regardless of current status) and wait for completion
            const markReadSuccess = await markAsRead(notification.id);

            // Ensure the API call has completed before proceeding with navigation
            if (!markReadSuccess) {
                // Still update UI optimistically to provide immediate feedback
                setNotifications(prev =>
                    prev.map(notif =>
                        notif.id === notification.id ? { ...notif, is_read: true } : notif
                    )
                );
                setUnreadCount(prev => Math.max(0, prev - 1));
            }

            // Add a delay to ensure the API call has fully completed before navigation
            await new Promise(resolve => setTimeout(resolve, 500));

            // Close the notification dropdown before navigation
            setIsOpen(false);

            // Don't reset filters - keep current filter state to prevent jittering

            // Handle navigation based on notification type using Inertia.js
            try {
                if (notification.type === 'hte_endorsement') {
                // Navigate to HTE endorsement table
                if (notification.data?.student_id) {
                    // If we have student_id, navigate with highlighting
                    const studentId = notification.data.student_id as number;
                    router.get(`/hte/endorsement-table?highlightStudent=${studentId}&highlightDuration=1500`);
                } else {
                    // If no student_id, just navigate to the table
                    router.get('/hte/endorsement-table');
                }
            } else if (notification.type === 'hte_deadline') {
                // Navigate based on deadline category
                if (notification.data?.category === 'student_placements_by_hte') {
                    router.get('/hte/endorsement-table');
                } else {
                    router.get('/form');
                }
            } else if (notification.type === 'student_deadline' || notification.type === 'unified_deadline' || notification.type === 'deadline_released' || notification.type === 'deadline_expired') {
                // Navigate based on deadline category and user role
                if (notification.data?.category === 'student_verification' && hasRole('adviser')) {
                    // Only student verification deadlines redirect advisers to verification page
                    try {
                        router.get('/student-verification');
                    } catch (error) {
                        console.error('Failed to redirect to student verification page:', error);
                        // Fallback to dashboard
                        router.get('/adviser/dashboard');
                    }
                } else if (notification.data?.category === 'student_placements') {
                    // Navigate based on user role
                    if (hasRole('admin')) {
                        router.get('/student/placed');
                    } else {
                        router.get('/student/dashboard');
                    }
                } else if (notification.data?.category === 'student_assessment_form') {
                    // Navigate to assessment for students
                    router.get('/assessment');
                } else if (notification.data?.category === 'hte_assessment_form') {
                    // Navigate to form for HTE users
                    router.get('/form');
                } else if (notification.data?.category === 'internship_placement') {
                    // Navigate based on user role for internship placement
                    if (hasRole('admin')) {
                        router.get('/student/placed');
                    } else if (hasRole('hte')) {
                        router.get('/hte/endorsement-table');
                    } else {
                        router.get('/student/dashboard');
                    }
                } else {
                    // Default fallback for other deadline types
                    if (hasRole('admin')) {
                        router.get('/admin/dashboard');
                    } else if (hasRole('adviser')) {
                        router.get('/adviser/dashboard');
                    } else if (hasRole('hte')) {
                        router.get('/hte/dashboard');
                    } else {
                        router.get('/student/dashboard');
                    }
                }
            } else if (notification.type === 'student_placement' || notification.type === 'student_placement_status') {
                // Navigate based on user role
                if (hasRole('admin')) {
                    router.get('/student/placed');
                } else {
                    router.get('/student/dashboard');
                }
            } else if (notification.type === 'student_approval_request' || notification.type === 'student_status_change' || notification.type === 'student_registration_pending' || notification.type === 'new_student_registration' || notification.type === 'student_verification_pending' || notification.type === 'student_approved' || notification.type === 'student_approval_needed') {
                // Navigate based on user role
                if (hasRole('adviser')) {
                    // Use redirect_url if available, otherwise default to student-verification
                    if (notification.data?.redirect_url && typeof notification.data.redirect_url === 'string') {
                        router.get(notification.data.redirect_url);
                    } else {
                        router.get('/student-verification');
                    }
                } else if (hasRole('admin')) {
                    router.get('/student/list');
                } else {
                    router.get('/student/dashboard');
                }
            } else {
                // Default navigation based on user role
                if (hasRole('adviser')) {
                    router.get('/adviser/dashboard');
                } else if (hasRole('admin')) {
                    router.get('/admin/dashboard');
                } else if (hasRole('hte')) {
                    router.get('/hte/dashboard');
                } else {
                    router.get('/student/dashboard');
                }
            }
            } catch (navigationError) {
                console.error('Error during navigation:', navigationError);
                // Fallback to dashboard if navigation fails
                router.get('/dashboard');
            }
        } catch (error) {
            console.error('Error handling notification click:', error);
        } finally {
            setIsProcessing(false);
        }
    };

    useEffect(() => {
        fetchNotifications(1, filter, showRead);

        // Poll for new notifications every 30 seconds
        const interval = setInterval(() => fetchNotifications(1, filter, showRead), 30000);
        return () => clearInterval(interval);
    }, []);

    // Refresh notifications when the dropdown is opened
    useEffect(() => {
        if (isOpen) {
            fetchNotifications(1, filter, showRead);
        }
    }, [isOpen]);

    // Reset filter to 'all' when component mounts to ensure it's valid for user's role
    useEffect(() => {
        const validFilters = getFilterButtons().map(btn => btn.key);
        if (!validFilters.includes(filter)) {
            setFilter('all');
        }
    }, []);

    // Refetch notifications when filter or showRead changes
    useEffect(() => {
        if (isOpen) {
            fetchNotifications(1, filter, showRead);
        }
    }, [filter, showRead]);

    const formatTimeAgo = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        return `${Math.floor(diffInSeconds / 86400)}d ago`;
    };

    const getNotificationIcon = (type: string) => {
        const iconClass = "h-4 w-4";
        switch (type) {
            case 'student_verification_pending':
            case 'student_approval_needed':
                return <UsersIcon className={iconClass} />;
            case 'student_assessment_pending':
            case 'hte_assessment_pending':
                return <FileTextIcon className={iconClass} />;
            case 'student_match_found':
            case 'student_placement':
            case 'student_placement_status':
                return <TargetIcon className={iconClass} />;
            case 'deadline_released':
            case 'deadline_expired':
                return <CalendarIcon className={iconClass} />;
            case 'hte_endorsement':
                return <CheckCircleIcon className={iconClass} />;
            case 'hte_deadline':
            case 'student_deadline':
                return <ClockIcon className={iconClass} />;
            case 'student_approval_request':
            case 'student_status_change':
            case 'student_registration_pending':
            case 'new_student_registration':
            case 'student_approved':
            case 'student_assessment_completed':
                return <UsersIcon className={iconClass} />;
            default:
                return <BellIcon className={iconClass} />;
        }
    };

    const getFilterButtons = (): FilterButton[] => {
        // Helper function to check if user has a specific role
        const hasRole = (roleName: string): boolean => {
            return auth.user?.roles?.some((role: UserRole) => role.name === roleName) || false;
        };

        if (hasRole('student')) {
            // Students only see: All, Placement, Deadline
            return [
                { key: 'all', label: 'All' },
                { key: 'placement', label: 'Placement' },
                { key: 'deadline', label: 'Deadline' }
            ];
        } else if (hasRole('hte')) {
            // HTEs see: All, Endorsement, Deadline
            return [
                { key: 'all', label: 'All' },
                { key: 'endorsement', label: 'Endorsement' },
                { key: 'deadline', label: 'Deadline' }
            ];
        } else if (hasRole('adviser')) {
            // Advisers see: All, Approval, Deadline
            return [
                { key: 'all', label: 'All' },
                { key: 'approval', label: 'Approval' },
                { key: 'deadline', label: 'Deadline' }
            ];
        } else if (hasRole('admin')) {
            // Admins see: All, Endorsement, Approval, Deadline
            return [
                { key: 'all', label: 'All' },
                { key: 'approval', label: 'Approval' },
                { key: 'deadline', label: 'Deadline' }
            ];
        }

        // Default fallback
        return [
            { key: 'all', label: 'All' }
        ];
    };

    return (
        <div className="relative">
            <Button
                variant="ghost"
                size="sm"
                onClick={() => {
                    setIsOpen(!isOpen);
                    if (!isOpen) {
                        fetchNotifications(1, filter, showRead);
                    }
                }}
                className="relative"
            >
                <BellIcon className="h-5 w-5" />
                {unreadCount > 0 && (
                    <Badge
                        variant="destructive"
                        className="absolute -top-1 -right-1 h-5 w-5 rounded-full p-0 flex items-center justify-center text-xs"
                    >
                        {unreadCount > 99 ? '99+' : unreadCount}
                    </Badge>
                )}
            </Button>

            {isOpen && (
                <Card className="absolute right-0 top-full mt-2 w-80 sm:w-96 shadow-xl z-50 py-0">
                    <CardContent className="p-0">
                        <div className="px-3 sm:px-4 py-3 sm:py-2 border-b">
                            <div className="flex items-center justify-between">
                                <h3 className="font-semibold text-foreground flex items-center gap-2 text-sm sm:text-base">
                                    <BellIcon className="h-4 w-4" />
                                    <span className="hidden sm:inline">Notifications</span>
                                    <span className="sm:hidden">Notifications</span>
                                    {unreadCount > 0 && (
                                        <Badge variant="destructive" className="text-xs">
                                            {unreadCount}
                                        </Badge>
                                    )}
                                </h3>
                                <div className="flex gap-1 sm:gap-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={handleShowReadToggle}
                                        className="h-8 w-8 p-0"
                                        title={showRead ? 'Show unread only' : 'Show all notifications'}
                                    >
                                        {showRead ? <ListIcon className="h-4 w-4" /> : <MailIcon className="h-4 w-4" />}
                                    </Button>
                                    {unreadCount > 0 && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={markAllAsRead}
                                            className="h-8 px-2 text-xs hidden sm:flex"
                                        >
                                            <CheckIcon className="h-4 w-4 mr-1" />
                                            <span className="hidden lg:inline">Mark all read</span>
                                            <span className="lg:hidden">Mark all</span>
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Filter Buttons */}
                        <div className="p-3 sm:p-3 border-b">
                            <div className="flex justify-center gap-1 flex-wrap">
                                {getFilterButtons().map((button) => (
                                    <Button
                                        key={button.key}
                                        variant={filter === button.key ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => handleFilterChange(button.key)}
                                        className={cn(
                                            "h-7 sm:h-8 px-2 sm:px-3 text-xs transition-all duration-200 flex-1 min-w-0",
                                            filter === button.key && "ring-2 ring-primary/20"
                                        )}
                                        disabled={isLoading || isFiltering}
                                    >
                                        <span className="truncate">{button.label}</span>
                                    </Button>
                                ))}
                            </div>
                            {(isLoading || isFiltering) && (
                                <div className="text-center text-xs text-muted-foreground mt-2">
                                    {isFiltering ? 'Switching filters...' : `Loading ${filter} notifications...`}
                                </div>
                            )}
                        </div>

                        <div className="max-h-96 overflow-y-auto">
                            {(isLoading || isFiltering) ? (
                                <div className="p-3 sm:p-4">
                                    {isFiltering ? (
                                        <div className="flex items-center justify-center py-6 sm:py-8">
                                            <div className="flex items-center gap-2 text-muted-foreground">
                                                <div className="animate-spin rounded-full h-4 w-4 border-2 border-primary border-t-transparent"></div>
                                                <span className="text-xs sm:text-sm">Switching to {filter} notifications...</span>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="flex items-start gap-2 sm:gap-3">
                                            <div className="flex-shrink-0 rounded-full p-2 sm:p-2.5 bg-muted animate-pulse">
                                                <BellIcon className="h-4 w-4 text-muted-foreground" />
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <div className="space-y-2">
                                                    <div className="h-3 sm:h-4 bg-muted rounded animate-pulse w-3/4"></div>
                                                    <div className="h-3 bg-muted rounded animate-pulse w-full"></div>
                                                    <div className="h-3 bg-muted rounded animate-pulse w-2/3"></div>
                                                </div>
                                                <div className="flex items-center gap-1 sm:gap-1.5 mt-2 sm:mt-3">
                                                    <div className="h-3 w-3 bg-muted rounded animate-pulse"></div>
                                                    <div className="h-3 bg-muted rounded animate-pulse w-12 sm:w-16"></div>
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ) : getFilteredNotifications().length === 0 ? (
                                <div className="p-4 sm:p-6 text-center text-muted-foreground">
                                    <div className="rounded-full bg-muted p-2 sm:p-3 mb-3 mx-auto w-fit">
                                        <BellIcon className="h-5 w-5 sm:h-6 sm:w-6" />
                                    </div>
                                    <h3 className="font-medium mb-1 text-sm sm:text-base">No notifications</h3>
                                    <p className="text-xs sm:text-sm text-muted-foreground">
                                        You're all caught up
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-1">
                                    {getFilteredNotifications().map((notification) => (
                                        <div
                                            key={notification.id}
                                            className={cn(
                                                "p-3 sm:p-4 hover:bg-accent/50 transition-colors group cursor-pointer border-b border-border/50 last:border-b-0",
                                                !notification.is_read && "bg-primary/5 border-l-4 border-l-primary",
                                                isProcessing && "opacity-50 cursor-not-allowed"
                                            )}
                                            onClick={() => handleNotificationClick(notification)}
                                        >
                                            <div className="flex items-start gap-2 sm:gap-3">
                                                <div className={cn(
                                                    "flex-shrink-0 rounded-full p-2 sm:p-2.5 mt-0.5 shadow-sm",
                                                    !notification.is_read
                                                        ? "bg-primary text-primary-foreground shadow-primary/20"
                                                        : "bg-accent text-accent-foreground"
                                                )}>
                                                    {getNotificationIcon(notification.type)}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-start justify-between gap-2">
                                                        <p className={cn(
                                                            "text-xs sm:text-sm font-semibold leading-tight line-clamp-2",
                                                            !notification.is_read ? "text-foreground" : "text-foreground/80"
                                                        )}>
                                                            {notification.title}
                                                        </p>
                                                        <div className="flex items-center gap-1 sm:gap-2 ml-2">
                                                            {!notification.is_read && (
                                                                <div className="w-2 sm:w-2.5 h-2 sm:h-2.5 bg-primary rounded-full flex-shrink-0 shadow-sm" />
                                                            )}
                                                            <div className="opacity-0 sm:group-hover:opacity-100 transition-opacity">
                                                                {notification.is_read ? (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            markAsUnread(notification.id);
                                                                        }}
                                                                        className="h-5 w-5 sm:h-6 sm:w-6 p-0 hover:bg-accent"
                                                                        title="Mark as unread"
                                                                    >
                                                                        <RotateCcwIcon className="h-3 w-3" />
                                                                    </Button>
                                                                ) : (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            markAsRead(notification.id);
                                                                        }}
                                                                        className="h-5 w-5 sm:h-6 sm:w-6 p-0 hover:bg-accent"
                                                                        title="Mark as read"
                                                                    >
                                                                        <CheckIcon className="h-3 w-3" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div className="mt-1 sm:mt-1.5">
                                                        <p className={cn(
                                                            "text-xs sm:text-sm text-foreground/70 leading-relaxed",
                                                            !isNotificationExpanded(notification.id) && "line-clamp-2"
                                                        )}>
                                                            {notification.message}
                                                        </p>
                                                        {shouldShowSeeMore(notification.message) && (
                                                            <button
                                                                onClick={(e) => {
                                                                    e.stopPropagation();
                                                                    toggleNotificationExpansion(notification.id);
                                                                }}
                                                                className="text-xs text-primary hover:text-primary/80 font-medium mt-1 transition-colors duration-200 hover:underline focus:outline-none focus:ring-2 focus:ring-primary/20 rounded-sm px-1 py-0.5"
                                                            >
                                                                {isNotificationExpanded(notification.id) ? 'See less' : 'See more'}
                                                            </button>
                                                        )}
                                                    </div>
                                                    <p className="text-xs text-muted-foreground mt-1.5 sm:mt-2.5 flex items-center gap-1 sm:gap-1.5 font-medium">
                                                        <ClockIcon className="h-3 w-3" />
                                                        {formatTimeAgo(notification.created_at)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}

                            {/* Pagination */}
                            {pagination.last_page > 1 && (
                                <div className="border-t border-border/50 p-2 sm:p-3">
                                    <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4">
                                        {/* Summary */}
                                        <div className="text-xs text-muted-foreground text-center sm:text-left">
                                            Showing {pagination.from}-{pagination.to} of {pagination.total}
                                        </div>

                                        {/* Page Navigation */}
                                        <div className="flex items-center justify-center gap-1">
                                            {/* Previous Button */}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => {
                                                    const prevPage = Math.max(1, pagination.current_page - 1);
                                                    if (prevPage !== pagination.current_page) {
                                                        handlePageChange(prevPage);
                                                    }
                                                }}
                                                disabled={pagination.current_page <= 1}
                                                className="h-6 w-6 sm:h-7 sm:w-7 p-0 hover:bg-accent"
                                            >
                                                <ChevronLeft className="h-3 w-3" />
                                            </Button>

                                            {/* Page Numbers */}
                                            <div className="flex items-center gap-1">
                                                {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => {
                                                    // Show first page, last page, current page, and pages around current page
                                                    // On mobile, show fewer pages to fit better
                                                    const showPage =
                                                        page === 1 ||
                                                        page === pagination.last_page ||
                                                        Math.abs(page - pagination.current_page) <= (window.innerWidth < 640 ? 0 : 1);

                                                    if (!showPage) {
                                                        // Show ellipsis for gaps
                                                        if (page === 2 && pagination.current_page > (window.innerWidth < 640 ? 2 : 4)) {
                                                            return <span key={`ellipsis-start`} className="px-1 text-xs text-muted-foreground">...</span>;
                                                        }
                                                        if (page === pagination.last_page - 1 && pagination.current_page < pagination.last_page - (window.innerWidth < 640 ? 1 : 3)) {
                                                            return <span key={`ellipsis-end`} className="px-1 text-xs text-muted-foreground">...</span>;
                                                        }
                                                        return null;
                                                    }

                                                    return (
                                                        <Button
                                                            key={page}
                                                            variant={page === pagination.current_page ? "default" : "ghost"}
                                                            size="sm"
                                                            onClick={() => {
                                                                if (page !== pagination.current_page && page >= 1 && page <= pagination.last_page) {
                                                                    handlePageChange(page);
                                                                }
                                                            }}
                                                            className="h-6 w-6 sm:h-7 sm:w-7 p-0 text-xs hover:bg-accent"
                                                        >
                                                            {page}
                                                        </Button>
                                                    );
                                                })}
                                            </div>

                                            {/* Next Button */}
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onClick={() => {
                                                    const nextPage = Math.min(pagination.last_page, pagination.current_page + 1);
                                                    if (nextPage !== pagination.current_page) {
                                                        handlePageChange(nextPage);
                                                    }
                                                }}
                                                disabled={pagination.current_page >= pagination.last_page}
                                                className="h-6 w-6 sm:h-7 sm:w-7 p-0 hover:bg-accent"
                                            >
                                                <ChevronRight className="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
