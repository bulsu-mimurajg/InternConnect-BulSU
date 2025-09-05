import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { BriefcaseIcon, UsersIcon, TrendingUpIcon } from 'lucide-react';

interface QuickAction {
    title: string;
    description: string;
    icon: React.ComponentType<{ className?: string }>;
    iconColor: string;
    onClick?: () => void;
}

interface QuickActionsCardProps {
    actions?: QuickAction[];
}

const defaultActions: QuickAction[] = [
    {
        title: "View Profile",
        description: "Check your company profile",
        icon: BriefcaseIcon,
        iconColor: "text-blue-600"
    },
    {
        title: "Manage Internships",
        description: "Update internship details",
        icon: UsersIcon,
        iconColor: "text-green-600"
    },
    {
        title: "View Reports",
        description: "Check matching results",
        icon: TrendingUpIcon,
        iconColor: "text-purple-600"
    }
];

export function QuickActionsCard({ actions = defaultActions }: QuickActionsCardProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
                <CardDescription>Common tasks and shortcuts</CardDescription>
            </CardHeader>
            <CardContent>
                <div className="grid gap-4 md:grid-cols-3">
                    {actions.map((action, index) => {
                        const IconComponent = action.icon;
                        return (
                            <button 
                                key={index}
                                className="p-4 border rounded-lg hover:bg-gray-50 transition-colors text-left"
                                onClick={action.onClick}
                            >
                                <div className="flex items-center gap-3">
                                    <IconComponent className={`h-5 w-5 ${action.iconColor}`} />
                                    <div>
                                        <p className="font-medium">{action.title}</p>
                                        <p className="text-sm text-muted-foreground">{action.description}</p>
                                    </div>
                                </div>
                            </button>
                        );
                    })}
                </div>
            </CardContent>
        </Card>
    );
}
