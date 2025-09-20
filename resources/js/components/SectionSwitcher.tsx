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
    currentSectionId: number;
    className?: string;
}

export default function SectionSwitcher({ sections, currentSectionId, className = '' }: SectionSwitcherProps) {
    const [isSwitching, setIsSwitching] = useState(false);

    // Don't render if there's only one section or no sections
    if (sections.length <= 1) {
        return null;
    }

    const currentSection = sections.find(s => s.section_id === currentSectionId);
    const currentSectionName = currentSection?.section_name || 'Select Section';

    const handleSectionChange = (sectionId: string) => {
        if (sectionId === currentSectionId.toString()) return;

        setIsSwitching(true);
        router.post(route('adviser.switch-section', parseInt(sectionId)), {}, {
            onFinish: () => {
                setIsSwitching(false);
            },
            onError: () => {
                setIsSwitching(false);
            }
        });
    };

    return (
        <div className={`flex items-center gap-2 ${className}`}>
            <span className="text-sm text-muted-foreground">Section:</span>
            <Select
                value={currentSectionId.toString()}
                onValueChange={handleSectionChange}
                disabled={isSwitching}
            >
                <SelectTrigger className="w-auto min-w-[150px] h-8 text-sm">
                    <SelectValue>
                        {isSwitching ? 'Switching...' : currentSectionName}
                    </SelectValue>
                </SelectTrigger>
                <SelectContent>
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
