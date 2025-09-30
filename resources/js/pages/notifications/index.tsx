import { Head, router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    BellIcon, 
    CheckIcon, 
    FileTextIcon, 
    TargetIcon, 
    CalendarIcon, 
    UsersIcon, 
    ClockIcon, 
    CheckCircleIcon,
    MoreHorizontalIcon
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { cn } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notifications',
        href: '/notifications',
    },
];

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    is_read: boolean;
    created_at: string;
    data?: any;
}

interface Props {
    notifications: Notification[];
    unreadCount: number;
}

export default function NotificationsIndex({ notifications, unreadCount }: Props) {
    const markAsRead = async (notificationId: number) => {
        try {
            await fetch(`/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            router.reload();
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
            
            router.reload();
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    };

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
            case 'student_assessment_pending':
            case 'hte_assessment_pending':
                return <FileTextIcon className={iconClass} />;
            case 'student_match_found':
                return <TargetIcon className={iconClass} />;
            case 'deadline_released':
                return <CalendarIcon className={iconClass} />;
            case 'student_verification_pending':
                return <UsersIcon className={iconClass} />;
            case 'deadline_expired':
                return <ClockIcon className={iconClass} />;
            case 'student_approval_needed':
                return <CheckCircleIcon className={iconClass} />;
            default:
                return <BellIcon className={iconClass} />;
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notifications" />
            <div className="space-y-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold">Notifications</h1>
                        {unreadCount > 0 && (
                            <p className="text-sm text-muted-foreground">
                                {unreadCount} unread
                            </p>
                        )}
                    </div>
                    {unreadCount > 0 && (
                        <Button onClick={markAllAsRead} variant="ghost" size="sm">
                            <CheckIcon className="h-4 w-4 mr-1" />
                            Mark all read
                        </Button>
                    )}
                </div>

                {/* Notifications List */}
                {notifications.length === 0 ? (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-16">
                            <div className="rounded-full bg-muted p-3 mb-4">
                                <BellIcon className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="font-medium mb-1">No notifications</h3>
                            <p className="text-sm text-muted-foreground text-center">
                                You're all caught up
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-1">
                        {notifications.map((notification) => (
                            <Card 
                                key={notification.id}
                                className={cn(
                                    "transition-all duration-200 hover:shadow-sm",
                                    !notification.is_read && "border-l-4 border-l-primary bg-primary/5"
                                )}
                            >
                                <CardContent className="p-4">
                                    <div className="flex items-start gap-3">
                                        <div className={cn(
                                            "flex-shrink-0 rounded-full p-2",
                                            !notification.is_read 
                                                ? "bg-primary/10 text-primary" 
                                                : "bg-muted text-muted-foreground"
                                        )}>
                                            {getNotificationIcon(notification.type)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-2">
                                                <div className="flex-1 min-w-0">
                                                    <h3 className={cn(
                                                        "font-medium text-sm leading-tight",
                                                        !notification.is_read ? "text-foreground" : "text-muted-foreground"
                                                    )}>
                                                        {notification.title}
                                                    </h3>
                                                    <p className="text-sm text-muted-foreground mt-1 line-clamp-2">
                                                        {notification.message}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground mt-2">
                                                        {formatTimeAgo(notification.created_at)}
                                                    </p>
                                                </div>
                                                {!notification.is_read && (
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => markAsRead(notification.id)}
                                                        className="h-8 w-8 p-0 hover:bg-primary/10"
                                                    >
                                                        <CheckIcon className="h-4 w-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
