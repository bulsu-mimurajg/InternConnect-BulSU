import { BarChart as RechartsBarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

interface BarChartProps {
    data: Array<{
        name: string;
        value: number;
        [key: string]: any;
    }>;
    dataKey: string;
    xAxisKey?: string;
    color?: string;
    height?: number;
    title?: string;
}

export default function BarChart({ 
    data, 
    dataKey, 
    xAxisKey = 'name', 
    color = '#2563eb', 
    height = 300,
    title 
}: BarChartProps) {
    return (
        <div className="w-full">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height={height}>
                <RechartsBarChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis 
                        dataKey={xAxisKey} 
                        tick={{ fontSize: 12 }}
                        angle={-45}
                        textAnchor="end"
                        height={80}
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
                    <Bar dataKey={dataKey} fill={color} radius={[4, 4, 0, 0]} />
                </RechartsBarChart>
            </ResponsiveContainer>
        </div>
    );
}
