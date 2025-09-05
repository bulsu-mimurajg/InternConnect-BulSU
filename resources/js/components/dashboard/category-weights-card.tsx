import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TrendingUpIcon } from 'lucide-react';
import { useState, useEffect } from 'react';

interface Internship {
    id: number;
    position_title: string;
    department: string;
    subcategory_weights: Array<{
        id: number;
        weight: number;
        subcategory: {
            id: number;
            subcategory_name: string;
        };
    }>;
}

interface CategoryWeightsCardProps {
    internships: Internship[];
}

export function CategoryWeightsCard({ internships }: CategoryWeightsCardProps) {
    const [selectedInternshipId, setSelectedInternshipId] = useState<string>('');
    const [selectedInternship, setSelectedInternship] = useState<Internship | null>(null);

    useEffect(() => {
        if (internships.length > 0 && !selectedInternshipId) {
            setSelectedInternshipId(internships[0].id.toString());
            setSelectedInternship(internships[0]);
        }
    }, [internships, selectedInternshipId]);

    useEffect(() => {
        if (selectedInternshipId) {
            const internship = internships.find(i => i.id.toString() === selectedInternshipId);
            setSelectedInternship(internship || null);
        }
    }, [selectedInternshipId, internships]);

    if (internships.length === 0) {
        return null;
    }

    const handleInternshipChange = (value: string) => {
        setSelectedInternshipId(value);
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <TrendingUpIcon className="h-5 w-5" />
                    Assessment Criteria Overview
                </CardTitle>
                <CardDescription>Weight distribution across different assessment categories for the selected internship</CardDescription>
                <div className="flex items-center gap-2">
                    <label htmlFor="internship-select" className="text-sm font-medium">
                        Select Internship:
                    </label>
                    <Select value={selectedInternshipId} onValueChange={handleInternshipChange}>
                        <SelectTrigger className="w-64">
                            <SelectValue placeholder="Choose an internship" />
                        </SelectTrigger>
                        <SelectContent>
                            {internships.map((internship) => (
                                <SelectItem key={internship.id} value={internship.id.toString()}>
                                    {internship.position_title} - {internship.department}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </CardHeader>
            <CardContent>
                {selectedInternship ? (
                    <div className="space-y-4">
                        <div className="p-3 bg-muted rounded-lg">
                            <h4 className="font-medium text-sm mb-1">Selected Internship:</h4>
                            <p className="text-sm text-muted-foreground">
                                {selectedInternship.position_title} - {selectedInternship.department}
                            </p>
                        </div>
                        
                        {selectedInternship.subcategory_weights.length > 0 ? (
                            <div className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    {selectedInternship.subcategory_weights.map((weight) => (
                                        <div key={weight.id} className="p-4 border rounded-lg">
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="font-medium text-sm">{weight.subcategory.subcategory_name}</span>
                                                <Badge variant="outline">{weight.weight}%</Badge>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                <div 
                                                    className="bg-blue-600 h-2 rounded-full" 
                                                    style={{ width: `${Math.min(weight.weight, 100)}%` }}
                                                ></div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <div className="text-center py-8 text-muted-foreground">
                                <p>No assessment criteria set for this internship.</p>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="text-center py-8 text-muted-foreground">
                        <p>Please select an internship to view criteria.</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
