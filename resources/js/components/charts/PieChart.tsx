import { PieChart as RechartsPieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';

interface PieChartProps {
    data: Array<{
        name: string;
        value: number;
        color?: string;
    }>;
    height?: number;
    title?: string;
    showLegend?: boolean;
}

const COLORS = ['#2563eb', '#dc2626', '#059669', '#d97706', '#7c3aed', '#db2777', '#0891b2', '#65a30d'];

export default function PieChart({ 
    data, 
    height = 300, 
    title, 
    showLegend = true 
}: PieChartProps) {
    const dataWithColors = data.map((item, index) => ({
        ...item,
        color: item.color || COLORS[index % COLORS.length]
    }));

    return (
        <div className="w-full">
            {title && (
                <h3 className="text-lg font-semibold mb-4 text-center">{title}</h3>
            )}
            <ResponsiveContainer width="100%" height={height}>
                <RechartsPieChart>
                    <Pie
                        data={dataWithColors}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                        outerRadius={80}
                        fill="#8884d8"
                        dataKey="value"
                    >
                        {dataWithColors.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={entry.color} />
                        ))}
                    </Pie>
                    <Tooltip 
                        contentStyle={{
                            backgroundColor: 'white',
                            border: '1px solid #e5e7eb',
                            borderRadius: '8px',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)'
                        }}
                    />
                    {showLegend && <Legend />}
                </RechartsPieChart>
            </ResponsiveContainer>
        </div>
    );
}
