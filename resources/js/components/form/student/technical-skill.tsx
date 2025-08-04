import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { useFormFields } from '@/contexts/FormFieldsContext';

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

export default function TechnicalSkill() {
    const { control } = useFormContext();
    const { setTechnicalSkillFields } = useFormFields();
    const [technicalSkillSections, setTechnicalSkillSections] = useState<Section[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchTechnicalSkills = async () => {
            try {
                setLoading(true);
                const response = await fetch('/assessment/technical-skills');
                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }
                const data = await response.json();
                setTechnicalSkillSections(data);
                
                // Register fields for validation
                const fields = data.flatMap((section: Section) => 
                    section.skills.map((skill: Skill) => skill.name)
                );
                setTechnicalSkillFields(fields);
                
                setError(null);
            } catch (err) {
                console.error('Failed to fetch technical skills data:', err);
                setError('Failed to load technical skills data');
            } finally {
                setLoading(false);
            }
        };

        fetchTechnicalSkills();
    }, []);

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

    if (error) {
        return (
            <div className="flex flex-col gap-4">
                <div className="text-red-600">
                    <p>Error: {error}</p>
                    <p>Please refresh the page to try again.</p>
                </div>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-4">
            <div>
                <h1 className="text-lg font-semibold">
                    For each technical skill below, evaluate your confidence level using the following scale:
                </h1>
                <ul className="text-sm">
                    <li>5 - Advanced</li>
                    <li>4 - Expert</li>
                    <li>3 - Intermediate</li>
                    <li>2 - Beginner</li>
                    <li>1 - Novice</li>
                </ul>
            </div>

            <div className="max-h-[300px] overflow-y-auto pr-2">
                {technicalSkillSections.map((section: Section) => (
                    <div key={section.title} className="mb-6">
                        <h2 className="text-md font-semibold mb-2">{section.title}</h2>
                        {section.skills.map((skill: Skill) => (
                            <FormField
                                key={skill.name}
                                control={control}
                                name={skill.name}
                                render={({ field }) => (
                                    <FormItem className="my-4 space-y-3">
                                        <FormLabel>
                                            {skill.label} <span className="text-red-500">*</span>
                                        </FormLabel>
                                        <FormControl>
                                            <RadioGroup
                                                onValueChange={field.onChange}
                                                value={field.value ?? ''}
                                                className="mb-6 flex"
                                            >
                                                {["1", "2", "3", "4", "5"].map((val) => (
                                                    <FormItem key={val} className="flex items-center gap-3">
                                                        <FormControl>
                                                            <RadioGroupItem value={val} />
                                                        </FormControl>
                                                        <FormLabel className="font-normal">{val}</FormLabel>
                                                    </FormItem>
                                                ))}
                                            </RadioGroup>
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        ))}
                    </div>
                ))}
            </div>
        </div>
    );
}
