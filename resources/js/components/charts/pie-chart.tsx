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
    const handleSliceClick = (entry: PieChartData, index: number) => {
        if (onSliceClick) {
            onSliceClick(entry, index);
        }
    };

    const CustomTooltip = ({ active, payload }: { active?: boolean; payload?: Array<{ payload: PieChartData }> }) => {
        if (active && payload && payload.length) {
            const data = payload[0].payload;
            return (
                <div className="bg-card border border-border p-4 rounded-xl shadow-lg backdrop-blur-sm text-center">
                    <p className="font-semibold text-foreground">{data.name}</p>
                    <p className="text-xs text-muted-foreground">
                        {((data.value / totalWeight) * 100).toFixed(1)}% of total
                    </p>
                </div>
            );
        }
        return null;
    };

    return (
        <div className="w-full h-full bg-card/30 rounded-xl p-8 border border-border/50 flex flex-col">
            {title && (
                <div className="text-center mb-8 flex-shrink-0">
                    <h3 className="text-xl font-semibold text-foreground">{title}</h3>
                    <p className="text-sm text-muted-foreground mt-2">Total Weight: {totalWeight}%</p>
                </div>
            )}
            <div className="flex-1 min-h-[350px]">
                <ResponsiveContainer width="100%" height="100%">
                    <RechartsPieChart>
                        <Pie
                            data={data}
                            cx="50%"
                            cy="50%"
                            labelLine={false}
                            label={showLabels ? ({ value }) => `${((value / totalWeight) * 100).toFixed(1)}%` : undefined}
                            outerRadius={120}
                            innerRadius={30}
                            fill="#8884d8"
                            dataKey="value"
                            onClick={handleSliceClick}
                            style={{ cursor: onSliceClick ? 'pointer' : 'default' }}
                            stroke="hsl(var(--background))"
                            strokeWidth={2}
                        >
                            {data.map((entry, index) => (
                                <Cell 
                                    key={`cell-${index}`} 
                                    fill={entry.color || COLORS[index % COLORS.length]} 
                                    stroke="hsl(var(--background))"
                                    strokeWidth={2}
                                />
                            ))}
                        </Pie>
                        {showTooltip && <Tooltip content={<CustomTooltip />} />}
                        {showLegend && (
                            <Legend 
                                wrapperStyle={{ 
                                    paddingTop: '24px',
                                    fontSize: '14px',
                                    textAlign: 'center'
                                }}
                                iconType="circle"
                                layout="horizontal"
                                align="center"
                                verticalAlign="bottom"
                            />
                        )}
                    </RechartsPieChart>
                </ResponsiveContainer>
            </div>
        </div>
    );
}
