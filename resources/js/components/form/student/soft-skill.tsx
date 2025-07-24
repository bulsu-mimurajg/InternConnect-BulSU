const softSkillSections = [
    {
        title: 'Communication',
        skills: [
            {
                name: 'explain_complex_ideas',
                label: 'How well can you explain a complex idea or concept clearly to someone who is not familiar with it?',
            },
            {
                name: 'collaboration',
                label: 'How well do you collaborate with others to achieve a common goal?',
            },
        ],
    },
    // Add more sections as needed
];

export default function SoftSkill() {

    return (
        <div className="flex flex-col gap-4">
            {/* Instruction */}
            <div>
                <h1 className="text-lg font-semibold mb-2">
                    Below are questions designed to assess your soft skills and help you evaluate your current abilities,
                    giving you a clearer understanding of your strengths.
                </h1>
                <ul className="text-sm mb-4">
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
