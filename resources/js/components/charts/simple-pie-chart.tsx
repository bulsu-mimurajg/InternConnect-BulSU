import React from 'react';

interface PieChartData {
    name: string;
    value: number;
    color: string;
}

interface SimplePieChartProps {
    data: PieChartData[];
    title?: string;
    totalWeight: number;
    showLabels?: boolean;
    showLegend?: boolean;
    onSliceClick?: (data: PieChartData, index: number) => void;
}

export function SimplePieChart({ 
    data, 
    title, 
    totalWeight, 
    showLabels = true, 
    showLegend = true,
    onSliceClick 
}: SimplePieChartProps) {
    if (!data || data.length === 0) {
        return (
            <div className="flex items-center justify-center h-64 w-64 bg-muted rounded-lg border-2 border-dashed border-border">
                <div className="text-center">
                    <div className="text-muted-foreground text-sm">No data available</div>
                </div>
            </div>
        );
    }

    // Calculate cumulative percentage for creating pie slices
    let cumulativePercentage = 0;
    const slices = data.map((item, index) => {
        const percentage = (item.value / totalWeight) * 100;
        const startAngle = (cumulativePercentage / 100) * 360;
        const endAngle = ((cumulativePercentage + percentage) / 100) * 360;
        
        const slice = {
            ...item,
            percentage,
            startAngle,
            endAngle,
            index
        };
        
        cumulativePercentage += percentage;
        return slice;
    });

    // Create SVG path for each slice
    const createSlicePath = (startAngle: number, endAngle: number, radius: number) => {
        const centerX = 100;
        const centerY = 100;
        
        const startAngleRad = (startAngle * Math.PI) / 180;
        const endAngleRad = (endAngle * Math.PI) / 180;
        
        const x1 = centerX + radius * Math.cos(startAngleRad);
        const y1 = centerY + radius * Math.sin(startAngleRad);
        const x2 = centerX + radius * Math.cos(endAngleRad);
        const y2 = centerY + radius * Math.sin(endAngleRad);
        
        const largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";
        
        return `M ${centerX} ${centerY} L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArcFlag} 1 ${x2} ${y2} Z`;
    };

    const radius = 60;

    return (
        <div className="w-full h-full flex flex-col">
            {title && (
                <div className="text-center mb-4 flex-shrink-0">
                    <h3 className="text-lg font-semibold text-foreground">{title}</h3>
                    <p className="text-sm text-muted-foreground mt-1">Total Weight: {totalWeight}%</p>
                </div>
            )}
            
            <div className="flex-1 flex items-center justify-center">
                <div className="flex items-center gap-6">
                    {/* Pie Chart SVG */}
                    <div className="flex-shrink-0 w-full h-full">
                        <svg width="100%" height="100%" viewBox="0 0 200 200" className="transform -rotate-90">
                            {slices.map((slice, index) => (
                                <path
                                    key={index}
                                    d={createSlicePath(slice.startAngle, slice.endAngle, radius)}
                                    fill={slice.color}
                                    stroke="hsl(var(--background))"
                                    strokeWidth="2"
                                    className="cursor-pointer hover:opacity-80 transition-opacity"
                                    onClick={() => onSliceClick?.(slice, index)}
                                />
                            ))}
                            {showLabels && slices.map((slice, index) => {
                                const midAngle = (slice.startAngle + slice.endAngle) / 2;
                                const midAngleRad = (midAngle * Math.PI) / 180;
                                const labelRadius = radius * 0.7;
                                const labelX = 100 + labelRadius * Math.cos(midAngleRad);
                                const labelY = 100 + labelRadius * Math.sin(midAngleRad);
                                
                                if (slice.percentage > 5) { // Only show labels for slices > 5%
                                    return (
                                        <text
                                            key={`label-${index}`}
                                            x={labelX}
                                            y={labelY}
                                            textAnchor="middle"
                                            dominantBaseline="middle"
                                            className="text-xs font-medium fill-foreground"
                                            transform={`rotate(${midAngle + 90} ${labelX} ${labelY})`}
                                        >
                                            {slice.percentage.toFixed(1)}%
                                        </text>
                                    );
                                }
                                return null;
                            })}
                        </svg>
                    </div>

                    {/* Legend */}
                    {showLegend && (
                        <div className="flex-shrink-0">
                            <div className="space-y-2">
                                {slices.map((slice, index) => (
                                    <div key={index} className="flex items-center gap-2">
                                        <div 
                                            className="w-3 h-3 rounded-full"
                                            style={{ backgroundColor: slice.color }}
                                        />
                                        <span className="text-sm text-foreground">{slice.name}</span>
                                        <span className="text-xs text-muted-foreground">
                                            ({slice.value}%)
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
