import { LineChart as RechartsLineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';

interface LineChartProps {
    data: Array<{
        [key: string]: string | number;
    }>;
    dataKeys: Array<{
        key: string;
        color: string;
        name: string;
    }>;
    xAxisKey: string;
    height?: number;
    title?: string;
}

export default function LineChart({
    data,
    dataKeys,
    xAxisKey,
    height = 300,
    title
}: LineChartProps) {
    return (
        <div className="w-full">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height={height}>
                <RechartsLineChart data={data} margin={{ top: 20, right: 30, left: 20, bottom: 5 }}>
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
                    <Legend />
                    {dataKeys.map((dataKey) => (
                        <Line
                            key={dataKey.key}
                            type="monotone"
                            dataKey={dataKey.key}
                            stroke={dataKey.color}
                            strokeWidth={2}
                            name={dataKey.name}
                            dot={{ r: 4 }}
                        />
                    ))}
                </RechartsLineChart>
            </ResponsiveContainer>
        </div>
    );
}
