import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ChevronDownIcon, ChevronRightIcon, ActivityIcon, UserIcon, ClockIcon } from 'lucide-react';

interface Activity {
    id: number;
    description: string;
    causer_name: string;
    causer_email: string | null;
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
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Audit Logs',
        href: '/admin/logs',
    },
];

export default function Logs({ activities }: LogsProps) {
    const [expandedRows, setExpandedRows] = useState<Set<number>>(new Set());

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
        if (description.includes('created')) return { variant: 'default' as const, text: 'Created' };
        if (description.includes('updated')) return { variant: 'secondary' as const, text: 'Updated' };
        if (description.includes('archived')) return { variant: 'outline' as const, text: 'Archived' };
        if (description.includes('unarchived')) return { variant: 'outline' as const, text: 'Restored' };
        if (description.includes('deleted')) return { variant: 'destructive' as const, text: 'Deleted' };
        return { variant: 'secondary' as const, text: 'Action' };
    };

    const formatPropertyValue = (value: any): string => {
        if (value === null || value === undefined) return 'N/A';
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        if (Array.isArray(value)) {
            if (value.length === 0) return 'None';
            return value.join(', ');
        }
        if (typeof value === 'object') return JSON.stringify(value, null, 2);
        return String(value);
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
                    <Badge variant="secondary" className="text-sm">
                        {activities.total} total
                    </Badge>
                </div>

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
                                            Subject
                                        </th>
                                        <th className="h-12 px-4 text-left align-middle font-medium text-muted-foreground">
                                            Time
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {activities.data.map((activity) => {
                                        const badge = getActivityBadge(activity.description);
                                        const hasDetails = Object.keys(activity.properties).length > 0;
                                        const isExpanded = expandedRows.has(activity.id);
                                        
                                        return (
                                            <>
                                                <tr 
                                                    key={activity.id} 
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
                                                        {activity.subject_type && activity.subject_id ? (
                                                            <div className="text-sm">
                                                                <div className="font-medium">
                                                                    {activity.subject_type.replace('App\\Models\\', '')}
                                                                </div>
                                                                <div className="text-xs text-muted-foreground">ID: {activity.subject_id}</div>
                                                            </div>
                                                        ) : (
                                                            <span className="text-muted-foreground">-</span>
                                                        )}
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
                                            </>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {activities.last_page > 1 && (
                    <div className="flex justify-center items-center gap-2">
                        {activities.current_page > 1 && (
                            <Button variant="outline" size="sm" asChild>
                                <Link href={`/admin/logs?page=${activities.current_page - 1}`}>
                                    Previous
                                </Link>
                            </Button>
                        )}
                        <span className="text-sm text-muted-foreground px-3">
                            Page {activities.current_page} of {activities.last_page}
                        </span>
                        {activities.current_page < activities.last_page && (
                            <Button variant="outline" size="sm" asChild>
                                <Link href={`/admin/logs?page=${activities.current_page + 1}`}>
                                    Next
                                </Link>
                            </Button>
                        )}
                    </div>
                )}

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
