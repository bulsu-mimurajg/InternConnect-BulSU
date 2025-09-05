import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useFormContext } from 'react-hook-form';

const internshipSections = [
    {
        title: 'Position Details',
        fields: [
            {
                name: 'position',
                label: 'Position',
                placeholder: 'Enter position title',
                type: 'text',
            },
            {
                name: 'department',
                label: 'Department',
                placeholder: 'Enter department',
                type: 'text',
            },
        ],
    },
    {
        title: 'Internship Details',
        fields: [
            {
                name: 'numberOfInterns',
                label: 'Number of Interns',
                placeholder: 'Enter number of interns needed',
                type: 'number',
            },
            {
                name: 'duration',
                label: 'Duration',
                placeholder: 'e.g., 3 months, 6 months',
                type: 'text',
            },
            {
                name: 'startDate',
                label: 'Start Date',
                placeholder: '',
                type: 'date',
            },
            {
                name: 'endDate',
                label: 'End Date',
                placeholder: '',
                type: 'date',
            },
        ],
    },
];

export default function InternshipOffered() {
    const { control } = useFormContext();

    return (
        <>
            <div className="max-h-[40vh] md:max-h-[60vh] overflow-y-auto pr-2">
                {internshipSections.map((section) => (
                    <div key={section.title} className="mb-6">
                        <h1 className="mt-4 mb-2 text-lg font-semibold">{section.title}</h1>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {section.fields.map((field) => (
                                <FormField
                                    key={field.name}
                                    control={control}
                                    name={field.name}
                                    render={({ field: formField }) => (
                                        <FormItem className="p-2">
                                            <FormLabel>{field.label}</FormLabel>
                                            <FormControl>
                                                <Input 
                                                    type={field.type}
                                                    placeholder={field.placeholder} 
                                                    {...formField} 
                                                />
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
