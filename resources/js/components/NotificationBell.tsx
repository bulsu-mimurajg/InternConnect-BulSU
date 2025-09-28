import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { BellIcon, CheckIcon, ClockIcon, UsersIcon, AlertCircleIcon, BriefcaseIcon, CalendarIcon, RotateCcwIcon, ListIcon, MailIcon } from 'lucide-react';
import { router } from '@inertiajs/react';

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
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [unreadCount, setUnreadCount] = useState(initialCount);
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [filter, setFilter] = useState<'all' | 'endorsement' | 'deadline'>('all');
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
            await fetch(`/notifications/${notificationId}/mark-read`, {
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
                    notif.id === notificationId ? { ...notif, is_read: true } : notif
                )
            );
            setUnreadCount(prev => Math.max(0, prev - 1));
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            setNotifications(prev => 
                prev.map(notif => ({ ...notif, is_read: true }))
            );
            setUnreadCount(0);
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
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
                filtered = filtered.filter(n => n.type === 'hte_deadline');
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
            console.log('HTE Deadline notification clicked:', {
                type: notification.type,
                data: notification.data,
                category: notification.data?.category
            });
            
            if (notification.data?.category === 'student_placements_by_hte') {
                // For student placements deadlines, navigate to endorsement table
                console.log('Navigating to endorsement table for student placements deadline');
                router.visit('/hte/endorsement-table');
            } else {
                // For other HTE deadlines (like assessment forms), navigate to form page
                console.log('Navigating to form page for other HTE deadline');
                router.visit('/form');
            }
        }
    };

    useEffect(() => {
        fetchNotifications();
        
        // Poll for new notifications every 30 seconds
        const interval = setInterval(fetchNotifications, 30000);
        return () => clearInterval(interval);
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
        switch (type) {
            case 'student_verification_pending':
            case 'student_approval_needed':
                return <UsersIcon className="h-4 w-4 text-amber-600" />;
            case 'student_assessment_pending':
            case 'hte_assessment_pending':
                return <AlertCircleIcon className="h-4 w-4 text-yellow-600" />;
            case 'student_match_found':
            case 'student_placement':
                return <BriefcaseIcon className="h-4 w-4 text-green-600" />;
            case 'deadline_released':
            case 'deadline_expired':
                return <CalendarIcon className="h-4 w-4 text-blue-600" />;
            case 'hte_endorsement':
                return <CheckIcon className="h-4 w-4 text-purple-600" />;
            case 'hte_deadline':
                return <ClockIcon className="h-4 w-4 text-red-600" />;
            default:
                return <BellIcon className="h-4 w-4 text-gray-600" />;
        }
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
                <div className="absolute right-0 top-full mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-96 overflow-hidden">
                    <div className="p-4 border-b border-gray-200 bg-gray-50">
                        <div className="flex items-center justify-between">
                            <h3 className="font-semibold text-gray-900 flex items-center gap-2">
                                <BellIcon className="h-4 w-4" />
                                Notifications
                                {unreadCount > 0 && (
                                    <Badge variant="destructive" className="text-xs">
                                        {unreadCount} new
                                    </Badge>
                                )}
                            </h3>
                            <div className="flex gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => setShowRead(!showRead)}
                                    className={`h-7 w-7 p-0 ${
                                        showRead 
                                            ? 'text-gray-600 hover:border hover:border-gray-500 hover:bg-white hover:text-gray-900' 
                                            : 'bg-blue-600 text-white hover:bg-blue-700'
                                    }`}
                                    title={showRead ? 'Show unread notifications' : 'Show all notifications'}
                                >
                                    {showRead ? <ListIcon className="h-3 w-3" /> : <MailIcon className="h-3 w-3" />}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={markAllAsRead}
                                    className={`text-xs h-7 px-2 ${
                                        unreadCount === 0 
                                            ? 'border border-gray-500 bg-white text-gray-500 hover:bg-gray-50' 
                                            : 'text-gray-600 hover:text-white hover:bg-blue-600'
                                    }`}
                                >
                                    <CheckIcon className="h-3 w-3 mr-1" />
                                    Mark all read
                                </Button>
                            </div>
                        </div>
                    </div>
                    
                    {/* Filter Buttons */}
                    <div className="p-3 border-b border-gray-200 bg-gray-50">
                        <div className="flex justify-center gap-1">
                            <Button
                                variant={filter === 'all' ? 'default' : 'ghost'}
                                size="sm"
                                onClick={() => setFilter('all')}
                                className={`h-8 px-3 text-xs ${
                                    filter === 'all' 
                                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                                }`}
                            >
                                All
                            </Button>
                            <Button
                                variant={filter === 'endorsement' ? 'default' : 'ghost'}
                                size="sm"
                                onClick={() => setFilter('endorsement')}
                                className={`h-8 px-3 text-xs ${
                                    filter === 'endorsement' 
                                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                                }`}
                            >
                                Endorsement
                            </Button>
                            <Button
                                variant={filter === 'deadline' ? 'default' : 'ghost'}
                                size="sm"
                                onClick={() => setFilter('deadline')}
                                className={`h-8 px-3 text-xs ${
                                    filter === 'deadline' 
                                        ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                                }`}
                            >
                                Deadline
                            </Button>
                        </div>
                    </div>
                    
                    <div className="max-h-80 overflow-y-auto">
                        {isLoading ? (
                            <div className="p-6 text-center text-gray-500">
                                <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900 mx-auto mb-2"></div>
                                Loading notifications...
                            </div>
                        ) : getFilteredNotifications().length === 0 ? (
                            <div className="p-6 text-center text-gray-500">
                                <BellIcon className="h-8 w-8 mx-auto mb-2 text-gray-300" />
                                <p className="text-sm">
                                    No {showRead ? '' : 'unread'} {filter === 'all' ? '' : filter} notifications
                                </p>
                                <p className="text-xs text-gray-400 mt-1">You're all caught up!</p>
                            </div>
                        ) : (
                            getFilteredNotifications().map((notification) => (
                                <div
                                    key={notification.id}
                                    className={`p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors group cursor-pointer ${
                                        !notification.is_read ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
                                    }`}
                                    onClick={() => handleNotificationClick(notification)}
                                >
                                    <div className="flex items-start gap-3">
                                        <div className="flex-shrink-0 mt-0.5">
                                            {getNotificationIcon(notification.type)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between">
                                            <p className={`text-sm font-medium ${
                                                !notification.is_read ? 'text-gray-900' : 'text-gray-700'
                                            }`}>
                                                {notification.title}
                                            </p>
                                                <div className="flex items-center gap-2">
                                                    {!notification.is_read && (
                                                        <div className="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0" />
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
                                                                className="h-6 w-6 p-0 text-gray-400 hover:text-gray-600"
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
                                                                className="h-6 w-6 p-0 text-gray-400 hover:text-gray-600"
                                                                title="Mark as read"
                                                            >
                                                                <CheckIcon className="h-3 w-3" />
                                                            </Button>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <p className="text-xs text-gray-600 mt-1 line-clamp-2">
                                                {notification.message}
                                            </p>
                                            <p className="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                                <ClockIcon className="h-3 w-3" />
                                                {formatTimeAgo(notification.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                    
                    {notifications.length > 0 && (
                        <div className="p-3 border-t border-gray-200 bg-gray-50">
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() => router.visit('/notifications')}
                                className="w-full text-xs"
                            >
                                View all notifications
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
