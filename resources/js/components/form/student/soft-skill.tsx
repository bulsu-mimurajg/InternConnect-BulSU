import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFormContext } from 'react-hook-form';
import { useEffect, useState } from 'react';
import { useFormFields } from '@/contexts/FormFieldsContext';

interface Skill {
    name: string;
    label: string;
    code_snippet?: string;
    subcategory_id: number;
    question_id: number;
    question_type: string;
    answers: Array<{
        id: number;
        text: string;
        display_order: number;
    }>;
}

interface Section {
    title: string;
    skills: Skill[];
}

export default function SoftSkill() {
    const { control } = useFormContext();
    const { setSoftSkillFields } = useFormFields();
    const [softSkillSections, setSoftSkillSections] = useState<Section[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchSoftSkills = async () => {
            try {
                setLoading(true);
                const response = await fetch('/assessment/soft-skills', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }
                const data = await response.json();
                setSoftSkillSections(data);

                // Register fields for validation
                const fields = data.flatMap((section: Section) =>
                    section.skills.map((skill: Skill) => skill.name)
                );
                setSoftSkillFields(fields);

                setError(null);
            } catch (err) {
                console.error('Failed to fetch soft skills data:', err);
                setError('Failed to load soft skills data');
            } finally {
                setLoading(false);
            }
        };

        fetchSoftSkills();
    }, [setSoftSkillFields]); // Now safe to include - memoized with useCallback

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
            <div className="overflow-y-auto pr-2">
                {softSkillSections.map((section: Section) => (
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

                                        {/* Code Snippet Display */}
                                        {skill.code_snippet && (
                                            <div className="my-3 p-4 bg-gray-900 dark:bg-gray-800 rounded-lg border border-gray-700">
                                                <pre className="text-sm text-gray-100 overflow-x-auto">
                                                    <code>{skill.code_snippet}</code>
                                                </pre>
                                            </div>
                                        )}

                                        <FormControl>
                                            <RadioGroup
                                                onValueChange={field.onChange}
                                                value={field.value ?? ''}
                                                className="mb-6 flex flex-col gap-2"
                                                data-field={skill.name}
                                            >
                                                {skill.answers && skill.answers.length > 0 ? (
                                                    skill.answers.map((answer) => (
                                                        <FormItem key={answer.id} className="flex items-center gap-3">
                                                            <FormControl>
                                                                <RadioGroupItem value={answer.id.toString()} />
                                                            </FormControl>
                                                            <FormLabel className="font-normal">{answer.text}</FormLabel>
                                                        </FormItem>
                                                    ))
                                                ) : (
                                                    // Fallback to Likert scale if no answers
                                                    ["1", "2", "3", "4", "5"].map((val) => (
                                                        <FormItem key={val} className="flex items-center gap-3">
                                                            <FormControl>
                                                                <RadioGroupItem value={val} />
                                                            </FormControl>
                                                            <FormLabel className="font-normal">{val}</FormLabel>
                                                        </FormItem>
                                                    ))
                                                )}
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
