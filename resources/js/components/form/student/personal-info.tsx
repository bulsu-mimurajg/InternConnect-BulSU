import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useFormContext } from 'react-hook-form';
import { usePage } from '@inertiajs/react';

const personalInfoSections = [
    {
        title: 'Basic Student Information',
        fields: [
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
                    <h1 className="mt-4 mb-2 text-lg font-semibold">Additional Information</h1>
                    
                    {additionalInfos.length === 0 ? (
                        <div className="flex flex-col items-center justify-center py-8 text-center">
                            <div className="rounded-lg border border-border bg-muted/50 p-6 max-w-md">
                                <div className="text-muted-foreground">
                                    <svg className="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 className="text-lg font-medium mb-2">No Additional Questions</h3>
                                    <p className="text-sm">
                                        Proceed to the next step.
                                    </p>
                                </div>
                            </div>
                        </div>
                    ) : (
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
                                                <Input placeholder={field.placeholder} {...formField} data-field={field.name} />
                                            </FormControl>
                                            <FormMessage />
                                        </FormItem>
                                    )}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </>
    );
}
