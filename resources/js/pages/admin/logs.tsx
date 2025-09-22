import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

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

    const getActivityIcon = (description: string) => {
        if (description.includes('created')) return 'CREATE';
        if (description.includes('updated')) return 'UPDATE';
        if (description.includes('archived')) return 'ARCHIVE';
        if (description.includes('unarchived')) return 'RESTORE';
        if (description.includes('deleted')) return 'DELETE';
        return 'ACTION';
    };

    const getActivityColor = (description: string) => {
        if (description.includes('created')) return 'text-green-600';
        if (description.includes('updated')) return 'text-blue-600';
        if (description.includes('archived')) return 'text-orange-600';
        if (description.includes('unarchived')) return 'text-purple-600';
        if (description.includes('deleted')) return 'text-red-600';
        return 'text-gray-600';
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

        // Check if this is an update with changes
        const hasChanges = properties.changes && Object.keys(properties.changes).length > 0;

        return (
            <div className="p-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <h4 className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {hasChanges ? 'Field Changes' : 'Action Details'}
                </h4>
                
                {hasChanges ? (
                    <div className="space-y-3">
                        {Object.entries(properties.changes).map(([field, change]: [string, any]) => (
                            <div key={field} className="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div className="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </div>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <div className="text-xs font-medium text-red-600 dark:text-red-400 mb-1">Before</div>
                                        <div className="text-sm text-gray-800 dark:text-gray-200 break-words p-2 bg-red-50 dark:bg-red-900/20 rounded border">
                                            {formatPropertyValue(change.old)}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-xs font-medium text-green-600 dark:text-green-400 mb-1">After</div>
                                        <div className="text-sm text-gray-800 dark:text-gray-200 break-words p-2 bg-green-50 dark:bg-green-900/20 rounded border">
                                            {formatPropertyValue(change.new)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                        {properties.password_changed && (
                            <div className="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                                <div className="text-sm text-blue-700 dark:text-blue-300">
                                    Password was also changed
                                </div>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {entries.map(([key, value]) => (
                            <div key={key} className="flex flex-col gap-1">
                                <span className="text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                                <span className="text-sm text-gray-800 dark:text-gray-200 break-words">
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
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold">Audit Logs</h1>
                    <div className="text-sm text-gray-500">
                        Total: {activities.total} activities
                    </div>
                </div>

                <div className="flex-1 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead className="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subject
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Time
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                {activities.data.map((activity) => (
                                    <>
                                        <tr 
                                            key={activity.id} 
                                            className={`hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors ${
                                                Object.keys(activity.properties).length > 0 
                                                    ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700' 
                                                    : ''
                                            }`}
                                            onClick={() => Object.keys(activity.properties).length > 0 && toggleRow(activity.id)}
                                        >
                                            <td className="px-4 py-4">
                                                <div className="flex items-center gap-3">
                                                    {Object.keys(activity.properties).length > 0 && (
                                                        <svg
                                                            className={`w-4 h-4 text-gray-400 transition-transform duration-200 ${
                                                                expandedRows.has(activity.id) ? 'rotate-90' : ''
                                                            }`}
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    )}
                                                    <span className={`px-2 py-1 text-xs font-semibold rounded ${getActivityColor(activity.description)} bg-gray-100 dark:bg-gray-800`}>
                                                        {getActivityIcon(activity.description)}
                                                    </span>
                                                    <span className={`font-medium ${getActivityColor(activity.description)}`}>
                                                        {activity.description}
                                                    </span>
                                                </div>
                                                {Object.keys(activity.properties).length > 0 && !expandedRows.has(activity.id) && (
                                                    <div className="mt-2 text-xs text-gray-500">
                                                        <span className="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                                                            {Object.keys(activity.properties).length} detail{Object.keys(activity.properties).length !== 1 ? 's' : ''} - Click to expand
                                                        </span>
                                                    </div>
                                                )}
                                            </td>
                                            <td className="px-4 py-4">
                                                <div className="text-sm">
                                                    <div className="font-medium">{activity.causer_name}</div>
                                                    {activity.causer_email && (
                                                        <div className="text-gray-500">{activity.causer_email}</div>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="px-4 py-4">
                                                {activity.subject_type && activity.subject_id ? (
                                                    <div className="text-sm">
                                                        <div className="font-medium">
                                                            {activity.subject_type.replace('App\\Models\\', '')}
                                                        </div>
                                                        <div className="text-gray-500">ID: {activity.subject_id}</div>
                                                    </div>
                                                ) : (
                                                    <span className="text-gray-400">-</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-4">
                                                <div className="text-sm">
                                                    <div className="font-medium">{activity.created_at}</div>
                                                    <div className="text-gray-500">{activity.created_at_human}</div>
                                                </div>
                                            </td>
                                        </tr>
                                        {expandedRows.has(activity.id) && Object.keys(activity.properties).length > 0 && (
                                            <tr>
                                                <td colSpan={4} className="px-0 py-0">
                                                    {renderPropertyDetails(activity.properties)}
                                                </td>
                                            </tr>
                                        )}
                                    </>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {activities.last_page > 1 && (
                    <div className="flex justify-center gap-2">
                        {activities.current_page > 1 && (
                            <Link
                                href={`/admin/logs?page=${activities.current_page - 1}`}
                                className="px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                        )}
                        <span className="px-3 py-2 text-sm">
                            Page {activities.current_page} of {activities.last_page}
                        </span>
                        {activities.current_page < activities.last_page && (
                            <Link
                                href={`/admin/logs?page=${activities.current_page + 1}`}
                                className="px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        )}
                    </div>
                )}

                {activities.data.length === 0 && (
                    <div className="flex-1 flex items-center justify-center">
                        <div className="text-center">
                            <div className="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                <span className="text-2xl text-gray-400 font-bold">LOG</span>
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                No activities yet
                            </h3>
                            <p className="text-gray-500">
                                Activity logs will appear here as users perform actions in the system.
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
