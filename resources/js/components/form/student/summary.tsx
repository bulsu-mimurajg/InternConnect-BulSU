import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';

interface Skill {
    name: string;
    label: string;
    subcategory_id: number;
    question_id: number;
    question_type?: string;
    answers?: Array<{
        id: number;
        text: string;
        display_order: number;
    }>;
}

interface Section {
    title: string;
    skills: Skill[];
}

interface AdditionalInfo {
    id: number;
    info_name: string;
    is_active: boolean;
}

interface PageProps extends Record<string, unknown> {
    additionalInfos?: AdditionalInfo[];
}

interface PersonalField {
    name: string;
    label: string;
}

interface PersonalSection {
    title: string;
    type: 'personal';
    fields: PersonalField[];
}

interface SkillSection {
    title: string;
    type: 'technical' | 'soft';
    sections: Section[];
}

type SummarySection = PersonalSection | SkillSection;

export default function Summary() {
    const { watch } = useFormContext();
    const formData = watch();
    const { additionalInfos = [] } = usePage<PageProps>().props;
    const [sections, setSections] = useState<SummarySection[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchSections = async () => {
            try {
                setLoading(true);

                // Fetch all sections data
                const fetchOptions: RequestInit = {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                };

                const [technicalResponse, softResponse] = await Promise.all([
                    fetch('/assessment/technical-skills', fetchOptions),
                    fetch('/assessment/soft-skills', fetchOptions)
                ]);

                const technicalData = await technicalResponse.json();
                const softData = await softResponse.json();

                // Create additional info fields
                const additionalInfoFields = additionalInfos.map((info) => ({
                    name: info.info_name.toLowerCase().replace(/[ -]/g, '_'),
                    label: info.info_name,
                }));

                const allSections: SummarySection[] = [
                    {
                        title: 'Additional Information',
                        type: 'personal' as const,
                        fields: additionalInfoFields
                    },
                    {
                        title: 'Technical Skills',
                        type: 'technical' as const,
                        sections: technicalData as Section[]
                    },
                    {
                        title: 'Soft Skills',
                        type: 'soft' as const,
                        sections: softData as Section[]
                    }
                ];

                setSections(allSections);
            } catch (error) {
                console.error('Error fetching sections:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchSections();
    }, [additionalInfos]);

    const renderPersonalInfo = (section: PersonalSection) => (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            {section.fields.map((field: PersonalField) => (
                <div key={field.name} className="flex justify-between items-center">
                    <span className="font-medium text-gray-700">{field.label}:</span>
                    <span className="text-gray-900">
                        {formData[field.name] ? formData[field.name] : 'Not answered'}
                    </span>
                </div>
            ))}
        </div>
    );

    const renderSkillSections = (section: SkillSection) => (
        <div className="space-y-4">
            {section.sections.map((subSection: Section) => (
                <div key={subSection.title} className="border-t pt-3">
                    <h4 className="font-semibold text-gray-800 mb-2">{subSection.title}</h4>
                    <div className="space-y-2">
                        {subSection.skills.map((skill: Skill) => {
                            const selectedValue = formData[skill.name];
                            let displayValue = 'Not answered';

                            if (selectedValue) {
                                // If skill has answers, find the matching answer text
                                if (skill.answers && skill.answers.length > 0) {
                                    const selectedAnswer = skill.answers.find(
                                        answer => answer.id.toString() === selectedValue.toString()
                                    );
                                    displayValue = selectedAnswer ? selectedAnswer.text : selectedValue;
                                } else {
                                    // Direct numeric value (old Likert scale)
                                    displayValue = selectedValue;
                                }
                            }

                            return (
                                <div key={skill.name} className="flex justify-between items-center text-sm">
                                    <span className="text-gray-600 truncate pr-2">{skill.label}</span>
                                    <span className="text-gray-900 font-medium">
                                        {displayValue}
                                    </span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            ))}
        </div>
    );

    if (loading) {
        return (
            <div className="flex flex-col gap-4">
                <div className="animate-pulse">
                    <div className="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div className="space-y-2">
                        <div className="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div className="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div className="h-4 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-h-[400px] overflow-y-auto pr-2 space-y-6">
            {sections.map((section) => (
                <div key={section.title} className="border rounded-lg p-4">
                    <h3 className="text-lg font-semibold mb-3 text-blue-600">{section.title}</h3>
                    {section.type === 'personal' ? renderPersonalInfo(section) : renderSkillSections(section)}
                </div>
            ))}
        </div>
    );
}

