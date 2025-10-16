import { BarChart as RechartsBarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

interface ChartData {
  [key: string]: string | number;
}

interface BarChartProps {
  data: ChartData[];
  dataKey: string;
  xAxisKey: string;
  color?: string;
  height?: number;
  showGrid?: boolean;
  showTooltip?: boolean;
  title?: string;
  description?: string;
}

export function BarChart({ 
  data, 
  dataKey, 
  xAxisKey, 
  color = '#3b82f6', 
  height = 300,
  showGrid = true,
  showTooltip = true,
  title,
  description
}: BarChartProps) {
  const CustomTooltip = ({ active, payload, label }: { active?: boolean; payload?: Array<{ name: string; value: number }>; label?: string }) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-card border border-border p-4 rounded-xl shadow-lg backdrop-blur-sm text-center">
          <p className="font-semibold text-foreground">{label}</p>
          <p className="text-sm text-muted-foreground">
            {payload[0].name}: {payload[0].value}
          </p>
        </div>
      );
    }
    return null;
  };

  return (
    <div className="w-full h-full bg-card/30 rounded-xl p-4 border border-border/50 flex flex-col">
      {(title || description) && (
        <div className="text-center mb-8 flex-shrink-0">
          {title && (
            <h3 className="text-xl font-semibold text-foreground">{title}</h3>
          )}
          {description && (
            <p className="text-sm text-muted-foreground mt-2">{description}</p>
          )}
        </div>
      )}
      <div className="flex-1 min-h-[300px]">
        <ResponsiveContainer width="100%" height={height}>
          <RechartsBarChart data={data} margin={{ top: 20, right: 20, left: 5, bottom: 20 }}>
            {showGrid && <CartesianGrid strokeDasharray="3 3" className="opacity-20" stroke="hsl(var(--border))" />}
            <XAxis 
              dataKey={xAxisKey} 
              className="text-xs fill-muted-foreground"
              tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
              axisLine={{ stroke: 'hsl(var(--border))' }}
              tickLine={{ stroke: 'hsl(var(--border))' }}
            />
            <YAxis 
              className="text-xs fill-muted-foreground"
              tick={{ fontSize: 12, fill: 'hsl(var(--muted-foreground))' }}
              axisLine={{ stroke: 'hsl(var(--border))' }}
              tickLine={{ stroke: 'hsl(var(--border))' }}
            />
            {showTooltip && <Tooltip content={<CustomTooltip />} />}
            <Bar 
              dataKey={dataKey} 
              fill={color}
              radius={[6, 6, 0, 0]}
              stroke="hsl(var(--background))"
              strokeWidth={1}
            />
          </RechartsBarChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
