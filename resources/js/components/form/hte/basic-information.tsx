import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { useFormContext } from 'react-hook-form';

const basicInfoSections = [
    {
        title: 'Company Information',
        fields: [
            {
                name: 'companyName',
                label: 'Company Name',
                placeholder: 'Enter company name',
                type: 'text',
            },
            {
                name: 'contactPerson',
                label: 'Contact Person',
                placeholder: 'Enter contact person name',
                type: 'text',
            },
            {
                name: 'email',
                label: 'Email',
                placeholder: 'Enter email address',
                type: 'email',
            },
            {
                name: 'phone',
                label: 'Phone',
                placeholder: 'Enter phone number',
                type: 'text',
            },
        ],
    },
    {
        title: 'Address',
        fields: [
            {
                name: 'address',
                label: 'Company Address',
                placeholder: 'Enter company address',
                type: 'textarea',
            },
        ],
    },
];

export default function BasicInformation() {
    const { control } = useFormContext();

    return (
        <>
            <div className="max-h-[40vh] md:max-h-[60vh] overflow-y-auto pr-2">
                {basicInfoSections.map((section) => (
                    <div key={section.title} className="mb-6">
                        <h1 className="mt-4 mb-2 text-lg font-semibold">{section.title}</h1>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {section.fields.map((field) => (
                                <FormField
                                    key={field.name}
                                    control={control}
                                    name={field.name}
                                    render={({ field: formField }) => (
                                        <FormItem className={`p-2 ${field.type === 'textarea' ? 'md:col-span-2' : ''}`}>
                                            <FormLabel>{field.label}</FormLabel>
                                            <FormControl>
                                                {field.type === 'textarea' ? (
                                                    <textarea 
                                                        placeholder={field.placeholder} 
                                                        {...formField}
                                                        rows={3}
                                                        className="border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive"
                                                    />
                                                ) : (
                                                    <Input 
                                                        type={field.type}
                                                        placeholder={field.placeholder} 
                                                        {...formField} 
                                                    />
                                                )}
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
