import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import React, { useState, useCallback } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/ui/pagination';
import { ChevronDownIcon, ChevronRightIcon, ActivityIcon, UserIcon, ClockIcon, LogInIcon, LogOutIcon, FilterIcon, XIcon, CalendarIcon } from 'lucide-react';

interface Activity {
    id: number;
    description: string;
    causer_name: string;
    causer_email: string | null;
    causer_role: string;
    subject_type: string | null;
    subject_id: number | null;
    properties: Record<string, any>;
    created_at: string;
    created_at_human: string;
}

interface ActivitiesData {
    data: Activity[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

interface LogsProps {
    activities: ActivitiesData;
    filters: {
        type?: string;
        user?: string;
        role?: string;
        date_from?: string;
        date_to?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Audit Logs',
        href: '/admin/logs',
    },
];

export default function Logs({ activities, filters }: LogsProps) {
    const [expandedRows, setExpandedRows] = useState<Set<number>>(new Set());
    const [showFilters, setShowFilters] = useState<boolean>(false);
    const [filterValues, setFilterValues] = useState({
        type: filters.type || 'all',
        user: filters.user || '',
        role: filters.role || 'all',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
    });

    const toggleRow = (activityId: number) => {
        const newExpandedRows = new Set(expandedRows);
        if (newExpandedRows.has(activityId)) {
            newExpandedRows.delete(activityId);
        } else {
            newExpandedRows.add(activityId);
        }
        setExpandedRows(newExpandedRows);
    };

    const getActivityBadge = (description: string) => {
        if (description.includes('logged in')) return { variant: 'default' as const, text: 'Login' };
        if (description.includes('logged out')) return { variant: 'outline' as const, text: 'Logout' };
        if (description.includes('created deadline')) return { variant: 'default' as const, text: 'Deadline Created' };
        if (description.includes('updated deadline')) return { variant: 'secondary' as const, text: 'Deadline Updated' };
        if (description.includes('extended deadline')) return { variant: 'secondary' as const, text: 'Deadline Extended' };
        if (description.includes('deleted deadline')) return { variant: 'destructive' as const, text: 'Deadline Deleted' };
        if (description.includes('created')) return { variant: 'default' as const, text: 'Created' };
        if (description.includes('updated')) return { variant: 'secondary' as const, text: 'Updated' };
        if (description.includes('archived')) return { variant: 'outline' as const, text: 'Archived' };
        if (description.includes('unarchived')) return { variant: 'outline' as const, text: 'Restored' };
        if (description.includes('deleted')) return { variant: 'destructive' as const, text: 'Deleted' };
        return { variant: 'secondary' as const, text: 'Action' };
    };

    const getActivityIcon = (description: string) => {
        if (description.includes('logged in')) return LogInIcon;
        if (description.includes('logged out')) return LogOutIcon;
        if (description.includes('deadline')) return CalendarIcon;
        return ActivityIcon;
    };

    const handleFilterChange = (key: string, value: string) => {
        const newFilters = { ...filterValues, [key]: value };
        setFilterValues(newFilters);

        // Apply filters immediately
        const params = new URLSearchParams();
        Object.entries(newFilters).forEach(([filterKey, filterValue]) => {
            if (filterValue && filterValue !== 'all') {
                params.append(filterKey, filterValue);
            }
        });

        router.get('/admin/logs', params.toString() ? Object.fromEntries(params) : {}, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setFilterValues({
            type: 'all',
            user: '',
            role: 'all',
            date_from: '',
            date_to: '',
        });
        router.get('/admin/logs', {}, {
            preserveState: true,
            replace: true
        });
    };

    const handlePageChange = useCallback((page: number) => {
        const params = new URLSearchParams();
        params.append('page', page.toString());
        
        // Add current filters to maintain them when changing pages
        Object.entries(filterValues).forEach(([filterKey, filterValue]) => {
            if (filterValue && filterValue !== 'all') {
                params.append(filterKey, filterValue);
            }
        });

        router.get('/admin/logs', Object.fromEntries(params), {
            preserveState: true,
            replace: true
        });
    }, [filterValues]);

    const hasActiveFilters = Object.entries(filterValues).some(([key, value]) => {
        if (key === 'type' || key === 'role') {
            return value !== '' && value !== 'all';
        }
        return value !== '';
    });

    const formatPropertyValue = (value: any): string => {
        if (value === null || value === undefined) return 'N/A';
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        if (Array.isArray(value)) {
            if (value.length === 0) return 'None';
            return value.join(', ');
        }
        if (typeof value === 'object') return JSON.stringify(value, null, 2);
        
        // Check if the value is a date string (ISO format or datetime-local format)
        const stringValue = String(value);
        if (stringValue.match(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/) || stringValue.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/)) {
            try {
                const date = new Date(stringValue);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                }
            } catch (e) {
                // If date parsing fails, return original value
            }
        }
        
        return stringValue;
    };

    const renderPropertyDetails = (properties: Record<string, any>) => {
        const entries = Object.entries(properties);
        if (entries.length === 0) return null;

        const hasChanges = properties.changes && Object.keys(properties.changes).length > 0;

        return (
            <div className="p-4 bg-muted/30 border-t">
                <h4 className="text-sm font-medium mb-3">
                    {hasChanges ? 'Field Changes' : 'Details'}
                </h4>
                
                {hasChanges ? (
                    <div className="space-y-3">
                        {Object.entries(properties.changes).map(([field, change]: [string, any]) => (
                            <Card key={field} className="p-3">
                                <div className="text-sm font-medium mb-2">
                                    {field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <div className="text-xs font-medium text-destructive mb-1">Before</div>
                                        <div className="text-sm break-words p-2 bg-destructive/10 rounded border">
                                            {formatPropertyValue(change.old)}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-medium text-green-600 mb-1">After</div>
                                        <div className="text-sm break-words p-2 bg-green-50 dark:bg-green-900/20 rounded border">
                                            {formatPropertyValue(change.new)}
                                        </div>
                                    </div>
                                </div>
                            </Card>
                        ))}
                        {properties.password_changed && (
                            <Card className="p-3 bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700">
                                <div className="text-sm text-blue-700 dark:text-blue-300">
                                    Password was also changed
                                </div>
                            </Card>
                        )}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {entries.map(([key, value]) => (
                            <div key={key} className="flex flex-col gap-1">
                                <span className="text-xs font-medium text-muted-foreground">
                                    {key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                                <span className="text-sm break-words">
                                    {formatPropertyValue(value)}
                                </span>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Logs" />
            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Audit Logs</h1>
                        <p className="text-muted-foreground">
                            Track and monitor all system activities and user actions
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        <Button
                            variant="outline"
                            size="default"
                            onClick={() => setShowFilters(!showFilters)}
                            className="flex items-center gap-2"
                        >
                            <FilterIcon className="h-4 w-4" />
                            Filters
                        </Button>
                    </div>
                </div>

                {showFilters && (
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2">
                                    <FilterIcon className="h-5 w-5" />
                                    Filters & Search
                                </CardTitle>
                                {hasActiveFilters && (
                                    <Button variant="outline" onClick={clearFilters} size="sm" className="flex items-center gap-2">
                                        <XIcon className="h-4 w-4" />
                                        Clear All
                                    </Button>
                                )}
                            </div>
                            <CardDescription>
                                Filter logs by action type, user, role, or date range
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Action</label>
                                    <Select value={filterValues.type || undefined} onValueChange={(value) => handleFilterChange('type', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All actions" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All actions</SelectItem>
                                            <SelectItem value="login">Login</SelectItem>
                                            <SelectItem value="logout">Logout</SelectItem>
                                            <SelectItem value="created">Created</SelectItem>
                                            <SelectItem value="updated">Updated</SelectItem>
                                            <SelectItem value="archived">Archived</SelectItem>
                                            <SelectItem value="deleted">Deleted</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">User</label>
                                    <Input
                                        placeholder="Search by username or email"
                                        value={filterValues.user}
                                        onChange={(e) => handleFilterChange('user', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">Role</label>
                                    <Select value={filterValues.role || undefined} onValueChange={(value) => handleFilterChange('role', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="All roles" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All roles</SelectItem>
                                            <SelectItem value="admin">Admin</SelectItem>
                                            <SelectItem value="student">Student</SelectItem>
                                            <SelectItem value="hte">HTE</SelectItem>
                                            <SelectItem value="adviser">Adviser</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">From Date</label>
                                    <Input
                                        type="date"
                                        value={filterValues.date_from}
                                        onChange={(e) => handleFilterChange('date_from', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label className="text-sm font-medium">To Date</label>
                                    <Input
                                        type="date"
                                        value={filterValues.date_to}
                                        onChange={(e) => handleFilterChange('date_to', e.target.value)}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b bg-muted/50">
                                    <tr>
                                        <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Action
                                        </th>
                                        <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            User
                                        </th>
                                        <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Role
                                        </th>
                                        <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {activities.data.map((activity) => {
                                        const badge = getActivityBadge(activity.description);
                                        const ActivityIcon = getActivityIcon(activity.description);
                                        const hasDetails = Object.keys(activity.properties).length > 0;
                                        const isExpanded = expandedRows.has(activity.id);
                                        
                                        return (
                                            <React.Fragment key={activity.id}>
                                                <tr 
                                                    className={`transition-colors ${
                                                        hasDetails ? 'cursor-pointer hover:bg-muted/50' : ''
                                                    }`}
                                                    onClick={() => hasDetails && toggleRow(activity.id)}
                                                >
                                                    <td className="p-4">
                                                        <div className="flex items-center gap-3">
                                                            {hasDetails && (
                                                                isExpanded ? (
                                                                    <ChevronDownIcon className="h-4 w-4 text-muted-foreground" />
                                                                ) : (
                                                                    <ChevronRightIcon className="h-4 w-4 text-muted-foreground" />
                                                                )
                                                            )}
                                                            <ActivityIcon className="h-4 w-4 text-muted-foreground" />
                                                            <Badge variant={badge.variant} className="text-xs">
                                                                {badge.text}
                                                            </Badge>
                                                            <span className="text-sm font-medium">
                                                                {activity.description}
                                                            </span>
                                                        </div>
                                                        {hasDetails && !isExpanded && (
                                                            <div className="mt-2">
                                                                <Badge variant="outline" className="text-xs">
                                                                    {Object.keys(activity.properties).length} details
                                                                </Badge>
                                                            </div>
                                                        )}
                                                    </td>
                                                    <td className="p-4">
                                                        <div className="flex items-center gap-2">
                                                            <UserIcon className="h-4 w-4 text-muted-foreground" />
                                                            <div>
                                                                <div className="text-sm font-medium">{activity.causer_name}</div>
                                                                {activity.causer_email && (
                                                                    <div className="text-xs text-muted-foreground">{activity.causer_email}</div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="p-4">
                                                        <div className="text-sm">
                                                            <div className="font-medium">
                                                                {activity.causer_role}
                                                            </div>
                                                            {activity.subject_type && activity.subject_id && (
                                                                <div className="text-xs text-muted-foreground">
                                                                    {activity.subject_type.replace('App\\Models\\', '')} ID: {activity.subject_id}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="p-4">
                                                        <div className="flex items-center gap-2">
                                                            <ClockIcon className="h-4 w-4 text-muted-foreground" />
                                                            <div className="text-sm">
                                                                <div className="font-medium">{activity.created_at}</div>
                                                                <div className="text-xs text-muted-foreground">{activity.created_at_human}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                {isExpanded && hasDetails && (
                                                    <tr>
                                                        <td colSpan={4} className="p-0">
                                                            {renderPropertyDetails(activity.properties)}
                                                        </td>
                                                    </tr>
                                                )}
                                            </React.Fragment>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <Pagination
                    currentPage={activities.current_page}
                    totalPages={activities.last_page}
                    onPageChange={handlePageChange}
                    showSummary={true}
                    totalItems={activities.total}
                    itemsPerPage={activities.per_page}
                />

                {activities.data.length === 0 && (
                    <Card>
                        <CardContent className="flex flex-col items-center justify-center py-12">
                            <div className="w-12 h-12 rounded-full bg-muted flex items-center justify-center mb-4">
                                <ActivityIcon className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <CardTitle className="text-lg mb-2">No activities yet</CardTitle>
                            <p className="text-muted-foreground text-center max-w-sm">
                                Activity logs will appear here as users perform actions in the system.
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
