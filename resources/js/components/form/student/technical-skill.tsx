import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { useFormContext } from 'react-hook-form';

const technicalSkillSections = [
    {
        title: 'Programming and Coding Skills',
        skills: [
            { name: 'cplusplus', label: 'c++' },
            { name: 'csharp', label: 'c#' },
            { name: 'java', label: 'java' },
            { name: 'python', label: 'python' },
        ],
    },
    {
        title: 'Database',
        skills: [
            { name: 'mysql', label: 'mysql' },
        ],
    },
];


export default function TechnicalSkill() {
    const { control } = useFormContext();

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
                {technicalSkillSections.map((section) => (
                    <div key={section.title} className="mb-6">
                        <h2 className="text-md font-semibold mb-2">{section.title}</h2>
                        {section.skills.map((skill) => (
                            <FormField
                                key={skill.name}
                                control={control}
                                name={skill.name}
                                render={({ field }) => (
                                    <FormItem className="my-4 space-y-3">
                                        <FormLabel className="capitalize">{skill.label}</FormLabel>
                                        <FormControl>
                                            <RadioGroup
                                                onValueChange={field.onChange}
                                                value={field.value}
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
