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
    maxValue?: number;
}

export function RadarChart({ data, title, maxValue }: RadarChartProps) {
    // Calculate the maximum value for the chart domain
    // Use maxValue prop if provided, otherwise use the highest fullMark from data, or default to 5
    const calculatedMaxValue = maxValue || 
        Math.max(...data.map(item => item.fullMark || 0), 5) || 5;
    
    return (
        <div className="w-full h-80">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center text-gray-800">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height="100%">
                <RechartsRadarChart cx="50%" cy="50%" outerRadius="80%" data={data}>
                    <PolarGrid stroke="#e5e7eb" />
                    <PolarAngleAxis 
                        dataKey="subject" 
                        tick={{ fontSize: 12, fill: '#6b7280' }}
                    />
                    <PolarRadiusAxis 
                        angle={30} 
                        domain={[0, calculatedMaxValue]} 
                        tick={{ fontSize: 10, fill: '#9ca3af' }}
                    />
                    <Radar
                        name="Score"
                        dataKey="score"
                        stroke="#3b82f6"
                        fill="#3b82f6"
                        fillOpacity={0.3}
                        strokeWidth={2}
                    />
                    <Tooltip 
                        contentStyle={{
                            backgroundColor: '#ffffff',
                            border: '1px solid #e5e7eb',
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                        }}
                        labelStyle={{ fontWeight: 'bold', color: '#374151' }}
                    />
                </RechartsRadarChart>
            </ResponsiveContainer>
        </div>
    );
} 