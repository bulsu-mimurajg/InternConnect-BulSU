import { AreaChart as RechartsAreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

interface AreaChartProps {
    data: Array<{
        [key: string]: any;
    }>;
    dataKey: string;
    xAxisKey: string;
    color?: string;
    height?: number;
    title?: string;
}

export default function AreaChart({ 
    data, 
    dataKey, 
    xAxisKey, 
    color = '#2563eb', 
    height = 300, 
    title 
}: AreaChartProps) {
    return (
        <div className="w-full">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height={height}>
                <RechartsAreaChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis 
                        dataKey={xAxisKey} 
                        tick={{ fontSize: 12 }}
                    />
                    <YAxis tick={{ fontSize: 12 }} />
                    <Tooltip 
                        contentStyle={{
                            backgroundColor: 'white',
                            border: '1px solid #e5e7eb',
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                        }}
                    />
                    <Area
                        type="monotone"
                        dataKey={dataKey}
                        stroke={color}
                        fill={color}
                        fillOpacity={0.3}
                        strokeWidth={2}
                    />
                </RechartsAreaChart>
            </ResponsiveContainer>
        </div>
    );
}
