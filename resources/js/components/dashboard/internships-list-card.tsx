import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { BriefcaseIcon, UsersIcon, CalendarIcon, MapPinIcon, ExternalLinkIcon } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Internship {
    id: number;
    position_title: string;
    department: string;
    placement_description: string;
    slot_count: number;
    is_active: boolean;
    created_at: string;
}

interface InternshipsListCardProps {
    internships: Internship[];
}

export function InternshipsListCard({ internships }: InternshipsListCardProps) {
    // Sort internships by creation date (newest first) and then by active status
    const sortedInternships = [...internships].sort((a, b) => {
        // First sort by active status (active first)
        if (a.is_active !== b.is_active) {
            return a.is_active ? -1 : 1;
        }
        // Then sort by creation date (newest first)
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <BriefcaseIcon className="h-5 w-5" />
                    All Internships
                </CardTitle>
                <CardDescription>Complete list of internship opportunities you've created</CardDescription>
            </CardHeader>
            <CardContent>
                {internships.length > 0 ? (
                    <div className="space-y-3">
                        {sortedInternships.map((internship) => (
                            <Link 
                                key={internship.id} 
                                href={`/hte/profile?internship=${internship.id}`}
                                className="block"
                            >
                                <div className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors cursor-pointer group">
                                    <div className="flex-1 space-y-2">
                                        <div className="flex items-center gap-3">
                                            <h4 className="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                                {internship.position_title}
                                            </h4>
                                            <Badge variant={internship.is_active ? "default" : "secondary"}>
                                                {internship.is_active ? "Active" : "Inactive"}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center gap-4 text-sm text-gray-600">
                                            <div className="flex items-center gap-1">
                                                <MapPinIcon className="h-3 w-3" />
                                                <span>{internship.department}</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <UsersIcon className="h-3 w-3" />
                                                <span>{internship.slot_count} slot{internship.slot_count !== 1 ? 's' : ''}</span>
                                            </div>
                                            <div className="flex items-center gap-1">
                                                <CalendarIcon className="h-3 w-3" />
                                                <span>{new Date(internship.created_at).toLocaleDateString()}</span>
                                            </div>
                                        </div>
                                        {internship.placement_description && (
                                            <p className="text-sm text-gray-500 line-clamp-2">
                                                {internship.placement_description}
                                            </p>
                                        )}
                                    </div>
                                    
                                    <div className="flex items-center gap-2 text-gray-400 group-hover:text-blue-600 transition-colors">
                                        <span className="text-sm font-medium">View Details</span>
                                        <ExternalLinkIcon className="h-4 w-4" />
                                    </div>
                                </div>
                            </Link>
                        ))}
                        
                        {/* Summary section */}
                        <div className="mt-4 pt-4 border-t border-gray-200">
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-600">Total Internships:</span>
                                <span className="font-semibold">{internships.length}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-600">Active Internships:</span>
                                <span className="font-semibold text-green-600">
                                    {internships.filter(i => i.is_active).length}
                                </span>
                            </div>
                            <div className="flex items-center justify-between text-sm">
                                <span className="text-gray-600">Total Available Slots:</span>
                                <span className="font-semibold text-blue-600">
                                    {internships.reduce((sum, i) => sum + i.slot_count, 0)}
                                </span>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="text-center py-8 text-muted-foreground">
                        <BriefcaseIcon className="h-16 w-16 mx-auto mb-3 opacity-50" />
                        <p className="text-lg font-medium">No internships created yet</p>
                        <p className="text-sm">Start by creating your first internship opportunity</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
