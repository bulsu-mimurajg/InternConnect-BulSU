import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useFormContext } from 'react-hook-form';

const personalInfoSections = [
    {
        title: 'Basic Student Information',
        fields: [
            {
                name: 'linkedin',
                label: 'Linkedin Profile Link',
                placeholder: 'linkedin.com/jemswoi',
            },
            {
                name: 'facebook',
                label: 'Facebook Profile Link',
                placeholder: 'facebook.com/jemswoi',
            }
        ],
    }
];

export default function PersonalInfo() {
    const { control } = useFormContext();

    return (
        <>
            <div className="max-h-[40vh] md:max-h-[60vh] overflow-y-auto pr-2">
                {personalInfoSections.map((section) => (
                    <div key={section.title} className="mb-6">
                        <h1 className="mt-4 mb-2 text-lg font-semibold">{section.title}</h1>
                        <div className="grid grid-cols-2 gap-4">
                            {section.fields.map((fields) => (
                                <FormField
                                    key={fields.name}
                                    control={control}
                                    name={fields.name}
                                    render={({ field }) => (
                                        <FormItem className="p-2">
                                            <FormLabel>{fields.label}</FormLabel>
                                            <FormControl>
                                                <Input placeholder={fields.placeholder} {...field} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </>
    );
}
