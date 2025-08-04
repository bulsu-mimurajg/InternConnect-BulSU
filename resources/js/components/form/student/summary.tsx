import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';

interface Skill {
    name: string;
    label: string;
    subcategory_id: number;
    question_id: number;
}

interface Section {
    title: string;
    skills: Skill[];
}

export default function Summary() {
    const { watch } = useFormContext();
    const formData = watch();
    const [sections, setSections] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchSections = async () => {
            try {
                setLoading(true);
                
                // Fetch all sections data
                const [languageResponse, technicalResponse, softResponse] = await Promise.all([
                    fetch('/assessment/language-proficiency'),
                    fetch('/assessment/technical-skills'),
                    fetch('/assessment/soft-skills')
                ]);

                const languageData = await languageResponse.json();
                const technicalData = await technicalResponse.json();
                const softData = await softResponse.json();

                const allSections = [
                    {
                        title: 'Personal Information',
                        type: 'personal',
                        fields: [
                            { name: 'firstName', label: 'First Name' },
                            { name: 'lastName', label: 'Last Name' },
                            { name: 'middleName', label: 'Middle Name' },
                            { name: 'suffix', label: 'Suffix' },
                            { name: 'province', label: 'Province' },
                            { name: 'city', label: 'City' },
                            { name: 'zip', label: 'Zip Code' },
                        ]
                    },
                    {
                        title: 'Language Proficiency',
                        type: 'language',
                        sections: languageData
                    },
                    {
                        title: 'Technical Skills',
                        type: 'technical',
                        sections: technicalData
                    },
                    {
                        title: 'Soft Skills',
                        type: 'soft',
                        sections: softData
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
    }, []);

    const renderPersonalInfo = (section: any) => (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            {section.fields.map((field: any) => (
                <div key={field.name} className="flex justify-between items-center">
                    <span className="font-medium text-gray-700">{field.label}:</span>
                    <span className="text-gray-900">
                        {formData[field.name] ? formData[field.name] : 'Not answered'}
                    </span>
                </div>
            ))}
        </div>
    );

    const renderSkillSections = (section: any) => (
        <div className="space-y-4">
            {section.sections.map((subSection: Section) => (
                <div key={subSection.title} className="border-t pt-3">
                    <h4 className="font-semibold text-gray-800 mb-2">{subSection.title}</h4>
                    <div className="space-y-2">
                        {subSection.skills.map((skill: Skill) => (
                            <div key={skill.name} className="flex justify-between items-center text-sm">
                                <span className="text-gray-600 truncate pr-2">{skill.label}</span>
                                <span className="text-gray-900 font-medium">
                                    {formData[skill.name] ? formData[skill.name] : 'Not answered'}
                                </span>
                            </div>
                        ))}
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

