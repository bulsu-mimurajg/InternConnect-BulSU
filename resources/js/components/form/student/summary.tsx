// components/forms/steps/summary-display.tsx
import { FC } from 'react';
// import type { FormSchema } from '@/components/form/student/form.tsx';

// interface Props {
//     values: FormSchema;
// }

// const labelMap: Record<string, string> = {
//     firstName: 'First Name',
//     lastName: 'Last Name',
//     middleName: 'Middle Name',
//     suffix: 'Suffix',
//     cplusplus: 'C++',
//     csharp: 'C#',
//     java: 'Java',
//     python: 'Python',
//     explain_complex_ideas: 'Explain Complex Ideas',
//     collaboration: 'Collaboration',
// };

const Summary = () => {
    return (
        <div className="max-h-[300px] overflow-y-auto pr-2 grid grid-cols-1 md:grid-cols-4 gap-4">
            {/*{Object.entries(values).map(([key, value]) => (*/}
            {/*    <div key={key} className="border p-4 rounded-md bg-muted/40">*/}
            {/*        <div className="font-medium">{labelMap[key] ?? key}</div>*/}
            {/*        <div className="text-sm text-muted-foreground">{value || '—'}</div>*/}
            {/*    </div>*/}
            {/*))}*/}
        </div>
    );
};

export default Summary;
