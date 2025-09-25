import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronDownIcon } from 'lucide-react';

interface Section {
    section_id: number;
    section_name: string;
}

interface SectionSwitcherProps {
    sections: Section[];
    currentSectionId: number | null; // Allow null for "All Sections"
    className?: string;
    showAllSections?: boolean; // New prop to enable "All Sections" option
}

export default function SectionSwitcher({ sections, currentSectionId, className = '', showAllSections = false }: SectionSwitcherProps) {
    const [isSwitching, setIsSwitching] = useState(false);

    // Don't render if there's only one section and All Sections is not enabled
    if (sections.length <= 1 && !showAllSections) {
        return null;
    }

    const getCurrentSectionName = () => {
        if (currentSectionId === null) {
            return 'All Sections';
        }
        const currentSection = sections.find(s => s.section_id === currentSectionId);
        return currentSection?.section_name || 'Select Section';
    };

    const handleSectionChange = (sectionId: string) => {
        if (sectionId === currentSectionId?.toString()) return;

        setIsSwitching(true);
        
        // Handle "All Sections" option
        if (sectionId === 'all') {
            router.post(route('adviser.switch-section', 'all'), {}, {
                onFinish: () => {
                    setIsSwitching(false);
                },
                onError: () => {
                    setIsSwitching(false);
                }
            });
        } else {
            router.post(route('adviser.switch-section', parseInt(sectionId)), {}, {
                onFinish: () => {
                    setIsSwitching(false);
                },
                onError: () => {
                    setIsSwitching(false);
                }
            });
        }
    };

    return (
        <div className={`flex items-center gap-2 ${className}`}>
            <span className="text-sm text-muted-foreground">Section:</span>
            <Select
                value={currentSectionId === null ? 'all' : currentSectionId.toString()}
                onValueChange={handleSectionChange}
                disabled={isSwitching}
            >
                <SelectTrigger className="w-auto min-w-[150px] h-8 text-sm">
                    <SelectValue>
                        {isSwitching ? 'Switching...' : getCurrentSectionName()}
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
                    {showAllSections && (
                        <SelectItem value="all">
                            All Sections
                        </SelectItem>
                    )}
                    {sections.map((section) => (
                        <SelectItem key={section.section_id} value={section.section_id.toString()}>
                            {section.section_name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
