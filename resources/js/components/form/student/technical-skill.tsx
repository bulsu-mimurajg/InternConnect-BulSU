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

            </div>
        </div>
    );
}
