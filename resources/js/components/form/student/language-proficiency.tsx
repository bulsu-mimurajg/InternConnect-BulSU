export default function LanguageProficiency() {
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
