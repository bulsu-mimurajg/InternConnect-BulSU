import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    BellIcon,
    CheckIcon,
    ClockIcon,
    UsersIcon,
    AlertCircleIcon,
    BriefcaseIcon,
    CalendarIcon,
    RotateCcwIcon,
    ListIcon,
    MailIcon,
    FileTextIcon,
    TargetIcon,
    CheckCircleIcon
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
    data?: Record<string, unknown>;
}

interface NotificationBellProps {
    initialCount?: number;
}

export default function NotificationBell({ initialCount = 0 }: NotificationBellProps) {
    const { auth } = usePage<{ auth: { user: { roles: string[] } } }>().props;
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(initialCount);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [filter, setFilter] = useState<'all' | 'endorsement' | 'deadline' | 'placement' | 'approval'>('all');
    const [showRead, setShowRead] = useState(true);
    const [lastLocalUpdate, setLastLocalUpdate] = useState<{[key: number]: number}>({});

    const fetchNotifications = async () => {
        setIsLoading(true);
        try {
            const response = await fetch('/notifications/get');
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

                // Sort by created_at date (newest first)
                return merged.sort((a, b) =>
                    new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
                );
            });

            setUnreadCount(data.unreadCount || 0);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const markAsRead = async (notificationId: number) => {
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
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
                        notif.id === notificationId ? { ...notif, is_read: true } : notif
                    )
                );
                setUnreadCount(prev => Math.max(0, prev - 1));
            } else {
                console.error('Server returned error:', result);
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
            // Still update UI optimistically
            setNotifications(prev =>
                prev.map(notif =>
                    notif.id === notificationId ? { ...notif, is_read: true } : notif
                )
            );
            setUnreadCount(prev => Math.max(0, prev - 1));
        }
    };

    const markAllAsRead = async () => {
        try {
            const response = await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
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
            await fetch(`/notifications/${notificationId}/mark-unread`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

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
        } catch (error) {
            console.error('Failed to mark notification as unread:', error);
        }
    };

    const getFilteredNotifications = () => {
        let filtered = notifications;

        // Filter by type
        switch (filter) {
            case 'endorsement':
                filtered = filtered.filter(n => n.type === 'hte_endorsement');
                break;
            case 'deadline':
                filtered = filtered.filter(n =>
                    n.type === 'hte_deadline' ||
                    n.type === 'student_deadline' ||
                    n.type === 'deadline_released' ||
                    n.type === 'deadline_expired'
                );
                break;
            case 'placement':
                filtered = filtered.filter(n =>
                    n.type === 'student_placement' ||
                    n.type === 'student_placement_status'
                );
                break;
            case 'approval':
                filtered = filtered.filter(n =>
                    n.type === 'student_approval_request' ||
                    n.type === 'student_status_change'
                );
                break;
            default:
                // Keep all notifications
                break;
        }

        // Filter by read/unread status
        if (!showRead) {
            filtered = filtered.filter(n => !n.is_read);
        }
        // If showRead is true, show all notifications (both read and unread)

        return filtered;
    };

    const handleNotificationClick = (notification: Notification) => {
        // Helper function to check if user has a specific role
        const hasRole = (roleName: string) => {
            return auth.user?.roles?.some((role: any) => role.name === roleName) || false;
        };

        // Debug logging
        console.log('=== NOTIFICATION DEBUG ===');
        console.log('Notification type:', notification.type);
        console.log('User roles:', auth.user?.roles);
        console.log('Roles is array:', Array.isArray(auth.user?.roles));
        console.log('Roles length:', auth.user?.roles?.length);
        console.log('First role:', auth.user?.roles?.[0]);
        console.log('Has adviser role (old):', auth.user?.roles?.includes('adviser'));
        console.log('Has adviser role (new):', hasRole('adviser'));
        console.log('==========================');

        // Mark as read when clicked (regardless of current status)
        markAsRead(notification.id);

        // Reset filters to show all notifications
        setFilter('all');
        setShowRead(true);

        // Handle navigation based on notification type
        if (notification.type === 'hte_endorsement') {
            // Navigate to HTE endorsement table
            if (notification.data?.student_id) {
                // If we have student_id, navigate with highlighting
                const studentId = notification.data.student_id as number;
                router.visit(`/hte/endorsement-table?highlightStudent=${studentId}&highlightDuration=1500`);
            } else {
                // If no student_id, just navigate to the table
                router.visit('/hte/endorsement-table');
            }
        } else if (notification.type === 'hte_deadline') {
            // Navigate based on deadline category
            if (notification.data?.category === 'student_placements_by_hte') {
                router.visit('/hte/endorsement-table');
            } else {
                router.visit('/form');
            }
        } else if (notification.type === 'student_deadline') {
            // Navigate to student assessment or placement based on category and user role
            if (notification.data?.category === 'student_placements') {
                // Navigate based on user role
                if (hasRole('admin')) {
                    router.visit('/student/placed');
                } else {
                    router.visit('/student/dashboard');
                }
            } else {
                // Navigate to assessment (available for students)
                router.visit('/assessment');
            }
        } else if (notification.type === 'student_placement' || notification.type === 'student_placement_status') {
            // Navigate based on user role
            if (hasRole('admin')) {
                router.visit('/student/placed');
            } else {
                router.visit('/student/dashboard');
            }
        } else if (notification.type === 'student_approval_request' || notification.type === 'student_status_change') {
            // Navigate based on user role
            if (hasRole('adviser')) {
                router.visit('/student-verification');
            } else if (hasRole('admin')) {
                router.visit('/student/list');
            } else {
                router.visit('/student/dashboard');
            }
        } else {
            // Default fallback - log unknown notification type
            console.warn('Unknown notification type:', notification.type);
            console.log('Available user roles:', auth.user?.roles);
            console.log('Notification data:', notification.data);

            // Default navigation based on user role
            if (hasRole('adviser')) {
                router.visit('/adviser/dashboard');
            } else if (hasRole('admin')) {
                router.visit('/admin-dashboard');
            } else if (hasRole('hte')) {
                router.visit('/hte/dashboard');
            } else {
                router.visit('/student/dashboard');
            }
        }
    };

    useEffect(() => {
        fetchNotifications();

        // Poll for new notifications every 30 seconds
        const interval = setInterval(fetchNotifications, 30000);
        return () => clearInterval(interval);
    }, []);

    // Reset filter to 'all' when component mounts to ensure it's valid for user's role
    useEffect(() => {
        const validFilters = getFilterButtons().map(btn => btn.key);
        if (!validFilters.includes(filter)) {
            setFilter('all');
        }
    }, []);

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
                return <UsersIcon className={iconClass} />;
            default:
                return <BellIcon className={iconClass} />;
        }
    };

    const getFilterButtons = () => {
        // Helper function to check if user has a specific role
        const hasRole = (roleName: string) => {
            return auth.user?.roles?.some((role: any) => role.name === roleName) || false;
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
            // Admins see: All, Endorsement, Deadline
            return [
                { key: 'all', label: 'All' },
                { key: 'endorsement', label: 'Endorsement' },
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
                        fetchNotifications();
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
                <Card className="absolute right-0 top-full mt-2 w-96 shadow-xl z-50 py-0">
                    <CardContent className="p-0">
                        <div className="px-4 py-2 border-b">
                            <div className="flex items-center justify-between">
                                <h3 className="font-semibold text-foreground flex items-center gap-2">
                                    <BellIcon className="h-4 w-4" />
                                    Notifications
                                    {unreadCount > 0 && (
                                        <Badge variant="destructive" className="text-xs">
                                            {unreadCount}
                                        </Badge>
                                    )}
                                </h3>
                                <div className="flex gap-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => setShowRead(!showRead)}
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
                                            className="h-8 px-2 text-xs"
                                        >
                                            <CheckIcon className="h-4 w-4 mr-1" />
                                            Mark all read
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Filter Buttons */}
                        <div className="p-3 border-b">
                            <div className="flex justify-center gap-1">
                                {getFilterButtons().map((button) => (
                                    <Button
                                        key={button.key}
                                        variant={filter === button.key ? 'default' : 'ghost'}
                                        size="sm"
                                        onClick={() => setFilter(button.key as any)}
                                        className="h-8 px-3 text-xs"
                                    >
                                        {button.label}
                                    </Button>
                                ))}
                            </div>
                        </div>

                        <div className="max-h-96 overflow-y-auto">
                            {isLoading ? (
                                <div className="p-6 text-center text-muted-foreground">
                                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto mb-2"></div>
                                    Loading notifications...
                                </div>
                            ) : getFilteredNotifications().length === 0 ? (
                                <div className="p-6 text-center text-muted-foreground">
                                    <div className="rounded-full bg-muted p-3 mb-3 mx-auto w-fit">
                                        <BellIcon className="h-6 w-6" />
                                    </div>
                                    <h3 className="font-medium mb-1">No notifications</h3>
                                    <p className="text-sm text-muted-foreground">
                                        You're all caught up
                                    </p>
                                </div>
                            ) : (
                                <div className="space-y-1">
                                    {getFilteredNotifications().map((notification) => (
                                        <div
                                            key={notification.id}
                                            className={cn(
                                                "p-4 hover:bg-accent/50 transition-colors group cursor-pointer border-b border-border/50 last:border-b-0",
                                                !notification.is_read && "bg-primary/5 border-l-4 border-l-primary"
                                            )}
                                            onClick={() => handleNotificationClick(notification)}
                                        >
                                            <div className="flex items-start gap-3">
                                                <div className={cn(
                                                    "flex-shrink-0 rounded-full p-2.5 mt-0.5 shadow-sm",
                                                    !notification.is_read
                                                        ? "bg-primary text-primary-foreground shadow-primary/20"
                                                        : "bg-accent text-accent-foreground"
                                                )}>
                                                    {getNotificationIcon(notification.type)}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-start justify-between gap-2">
                                                        <p className={cn(
                                                            "text-sm font-semibold leading-tight",
                                                            !notification.is_read ? "text-foreground" : "text-foreground/80"
                                                        )}>
                                                            {notification.title}
                                                        </p>
                                                        <div className="flex items-center gap-2">
                                                            {!notification.is_read && (
                                                                <div className="w-2.5 h-2.5 bg-primary rounded-full flex-shrink-0 shadow-sm" />
                                                            )}
                                                            <div className="opacity-0 group-hover:opacity-100 transition-opacity">
                                                                {notification.is_read ? (
                                                                    <Button
                                                                        variant="ghost"
                                                                        size="sm"
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            markAsUnread(notification.id);
                                                                        }}
                                                                        className="h-6 w-6 p-0 hover:bg-accent"
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
                                                                        className="h-6 w-6 p-0 hover:bg-accent"
                                                                        title="Mark as read"
                                                                    >
                                                                        <CheckIcon className="h-3 w-3" />
                                                                    </Button>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p className="text-sm text-foreground/70 mt-1.5 line-clamp-2 leading-relaxed">
                                                        {notification.message}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground mt-2.5 flex items-center gap-1.5 font-medium">
                                                        <ClockIcon className="h-3 w-3" />
                                                        {formatTimeAgo(notification.created_at)}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
}
