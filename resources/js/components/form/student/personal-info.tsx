import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useFormContext } from 'react-hook-form';
import { usePage } from '@inertiajs/react';

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

interface AdditionalInfo {
    id: number;
    info_name: string;
    is_active: boolean;
}

interface PageProps extends Record<string, unknown> {
    additionalInfos?: AdditionalInfo[];
}

export default function PersonalInfo() {
    const { control } = useFormContext();
    const { additionalInfos = [] } = usePage<PageProps>().props;

    // Convert additional info to field format
    const additionalInfoFields = additionalInfos.map((info) => ({
        name: info.info_name.toLowerCase().replace(/[ -]/g, '_'),
        label: info.info_name,
        placeholder: `Enter your ${info.info_name.toLowerCase()}`,
    }));

    // Combine static fields with dynamic additional info fields
    const allFields = [
        ...personalInfoSections[0].fields,
        ...additionalInfoFields,
    ];

    return (
        <>
            <div className="max-h-[40vh] md:max-h-[60vh] overflow-y-auto pr-2">
                <div className="mb-6">
                    <h1 className="mt-4 mb-2 text-lg font-semibold">Personal Information</h1>
                    <div className="grid grid-cols-2 gap-4">
                        {allFields.map((field) => (
                            <FormField
                                key={field.name}
                                control={control}
                                name={field.name}
                                render={({ field: formField }) => (
                                    <FormItem className="p-2">
                                        <FormLabel>{field.label}</FormLabel>
                                        <FormControl>
                                            <Input placeholder={field.placeholder} {...formField} />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
