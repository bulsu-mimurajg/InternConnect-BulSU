import React from 'react';
import {
    Radar,
    RadarChart as RechartsRadarChart,
    PolarGrid,
    PolarAngleAxis,
    PolarRadiusAxis,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

interface RadarChartProps {
    data: Array<{
        subject: string;
        score: number;
        fullMark?: number;
    }>;
    title?: string;
}

export function RadarChart({ data, title }: RadarChartProps) {
    return (
        <div className="w-full h-80">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height="100%">
                <RechartsRadarChart cx="50%" cy="50%" outerRadius="80%" data={data}>
                    <PolarGrid />
                    <PolarAngleAxis dataKey="subject" />
                    <PolarRadiusAxis angle={30} domain={[0, 5]} />
                    <Radar
                        name="Score"
                        dataKey="score"
                        stroke="#8884d8"
                        fill="#8884d8"
                        fillOpacity={0.6}
                    />
                    <Tooltip />
                </RechartsRadarChart>
            </ResponsiveContainer>
        </div>
    );
} 