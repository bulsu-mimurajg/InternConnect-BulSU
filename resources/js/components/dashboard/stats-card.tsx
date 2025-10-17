import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LucideIcon, ChevronDownIcon, ChevronUpIcon, ExternalLinkIcon } from 'lucide-react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { useState } from 'react';
import { Link } from '@inertiajs/react';

interface InternshipSlot {
    id: number;
    position_title: string;
    slot_count: number;
    is_active: boolean;
    created_at: string;
}

interface StatsCardProps {
    title: string;
    value: string | number | React.ReactNode;
    description: string;
    icon: LucideIcon;
    iconColor?: string;
    internshipSlots?: InternshipSlot[];
}

export function StatsCard({ title, value, description, icon: Icon, iconColor = "text-muted-foreground", internshipSlots }: StatsCardProps) {
    const [isOpen, setIsOpen] = useState(false);
    
    // Check if this is the Total Slots card that should be expandable
    const isTotalSlotsCard = title === "Total Slots" && internshipSlots && internshipSlots.length > 0;
    
    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className={`h-4 w-4 ${iconColor}`} />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                <p className="text-xs text-muted-foreground">
                    {description}
                </p>
                
                {/* Expandable content for Total Slots */}
                {isTotalSlotsCard && (
                    <Collapsible open={isOpen} onOpenChange={setIsOpen}>
                        <CollapsibleTrigger asChild>
                            <button 
                                className="mt-3 flex items-center gap-2 text-xs text-blue-600 hover:text-blue-800 transition-colors"
                                onClick={() => setIsOpen(!isOpen)}
                            >
                                {isOpen ? (
                                    <>
                                        <ChevronUpIcon className="h-3 w-3" />
                                        Hide breakdown
                                    </>
                                ) : (
                                    <>
                                        <ChevronDownIcon className="h-3 w-3" />
                                        Show breakdown
                                    </>
                                )}
                            </button>
                        </CollapsibleTrigger>
                        
                        <CollapsibleContent className="mt-3 space-y-2 animate-in fade-in-0 slide-in-from-top-2 duration-200">
                            <div className="border-t pt-3">
                                <h4 className="text-xs font-medium text-gray-700 mb-2">Individual Internship Slots:</h4>
                                <div className="space-y-2">
                                    {internshipSlots.map((internship) => (
                                        <Link 
                                            key={internship.id} 
                                            href={`/hte/internship/${internship.id}`}
                                            className="block"
                                        >
                                            <div className="flex items-center justify-between text-xs bg-gray-50 p-2 rounded hover:bg-gray-100 transition-colors cursor-pointer group">
                                                <div className="flex-1">
                                                    <div className="font-medium text-gray-900 truncate group-hover:text-blue-600 transition-colors">
                                                        {internship.position_title}
                                                    </div>
                                                    <div className="text-gray-500 text-xs">
                                                        {new Date(internship.created_at).toLocaleDateString()}
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <span className={`px-2 py-1 rounded-full text-xs ${
                                                        internship.is_active 
                                                            ? 'bg-green-100 text-green-800' 
                                                            : 'bg-gray-100 text-gray-800'
                                                    }`}>
                                                        {internship.is_active ? 'Active' : 'Inactive'}
                                                    </span>
                                                    <span className="font-bold text-blue-600">
                                                        {internship.slot_count} slot{internship.slot_count !== 1 ? 's' : ''}
                                                    </span>
                                                    <ExternalLinkIcon className="h-3 w-3 text-gray-400 group-hover:text-blue-600 transition-colors" />
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                                <div className="mt-3 pt-2 border-t border-gray-200">
                                    <div className="flex items-center justify-between text-sm font-semibold text-gray-900">
                                        <span>Total Available Slots:</span>
                                        <span className="text-blue-600">{value}</span>
                                    </div>
                                </div>
                            </div>
                        </CollapsibleContent>
                    </Collapsible>
                )}
            </CardContent>
        </Card>
    );
}
