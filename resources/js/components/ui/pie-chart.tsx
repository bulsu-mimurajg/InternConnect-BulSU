import React from 'react';
import {
    PieChart as RechartsPieChart,
    Pie,
    Cell,
    ResponsiveContainer,
    Tooltip,
    Legend,
} from 'recharts';

interface PieChartData {
    name: string;
    value: number;
    color: string;
}

interface PieChartProps {
    data: PieChartData[];
    title?: string;
    onSliceClick?: (data: PieChartData, index: number) => void;
    totalWeight: number;
    showLabels?: boolean;
    showTooltip?: boolean;
    showLegend?: boolean;
}

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884D8', '#82CA9D', '#FFC658', '#FF6B6B'];

export function PieChart({ 
    data, 
    title, 
    onSliceClick, 
    totalWeight, 
    showLabels = true, 
    showTooltip = true, 
    showLegend = true 
}: PieChartProps) {
    const handleSliceClick = (entry: any, index: number) => {
        if (onSliceClick) {
            onSliceClick(entry, index);
        }
    };

    const CustomTooltip = ({ active, payload }: any) => {
        if (active && payload && payload.length) {
            const data = payload[0].payload;
            return (
                <div className="bg-white p-3 border rounded-lg shadow-lg">
                    <p className="font-medium">{data.name}</p>
                    <p className="text-sm text-gray-600">Weight: {data.value}%</p>
                    <p className="text-xs text-gray-500">
                        {((data.value / totalWeight) * 100).toFixed(1)}% of total
                    </p>
                </div>
            );
        }
        return null;
    };

    return (
        <div className="w-full h-80">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height="100%">
                <RechartsPieChart>
                    <Pie
                        data={data}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={showLabels ? ({ name, value }) => `${name}: ${value}%` : undefined}
                        outerRadius={80}
                        fill="#8884d8"
                        dataKey="value"
                        onClick={handleSliceClick}
                        style={{ cursor: onSliceClick ? 'pointer' : 'default' }}
                    >
                        {data.map((entry, index) => (
                            <Cell 
                                key={`cell-${index}`} 
                                fill={entry.color || COLORS[index % COLORS.length]} 
                            />
                        ))}
                    </Pie>
                    {showTooltip && <Tooltip content={<CustomTooltip />} />}
                    {showLegend && <Legend />}
                </RechartsPieChart>
            </ResponsiveContainer>
        </div>
    );
}
