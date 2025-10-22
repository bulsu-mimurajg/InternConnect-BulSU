import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { CheckCircle, XCircle, Target, Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';

interface BatchAction {
  label: string;
  icon: React.ComponentType<{ className?: string }>;
  onClick: () => void;
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
  className?: string;
  disabled?: boolean;
}

interface BatchActionsProps {
  selectedCount: number;
  selectedLabel: string; // e.g., "student", "endorsement", "application"
  actions: BatchAction[];
  onClearSelection: () => void;
  isLoading?: boolean;
  description?: string;
  className?: string;
  isVerificationDisabled?: boolean; // New prop to indicate if verification is disabled
  title?: string; // Custom title override
}

export function BatchActions({
  selectedCount,
  selectedLabel,
  actions,
  onClearSelection,
  isLoading = false,
  description,
  className,
  isVerificationDisabled = false,
  title
}: BatchActionsProps) {
  if (selectedCount === 0) return null;

  return (
    <Card className={cn(
      "border-l-4 border-l-primary bg-primary/5 dark:bg-primary/10",
      "transition-all duration-200 ease-in-out",
      "shadow-sm hover:shadow-md",
      className
    )}>
      <CardContent className="p-4 md:p-6">
        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          {/* Info Section */}
          <div className="flex items-start gap-3 flex-1">
            <div className="flex-shrink-0 mt-0.5">
              <Target className="h-5 w-5 text-primary" />
            </div>
            <div className="flex-1 min-w-0">
              <div className="flex items-center gap-2 mb-1">
                <h3 className="font-semibold text-foreground">
                  {title || 'Batch Actions'}
                </h3>
                <Badge variant="secondary" className="text-xs">
                  {selectedCount} {selectedLabel}{selectedCount !== 1 ? 's' : ''} selected
                </Badge>
              </div>
              {description && (
                <p className="text-sm text-muted-foreground leading-relaxed">
                  {description}
                </p>
              )}
            </div>
          </div>

          {/* Actions Section */}
          {isVerificationDisabled ? (
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex flex-col sm:flex-row gap-2 lg:ml-4">
                  {actions.map((action, index) => {
                    const IconComponent = action.icon;
                    return (
                      <Button
                        key={index}
                        variant={action.variant || 'default'}
                        onClick={action.onClick}
                        disabled={action.disabled || isLoading}
                        className={cn(
                          "text-sm font-medium transition-all duration-200",
                          "hover:scale-105 active:scale-95",
                          action.className
                        )}
                      >
                        {isLoading ? (
                          <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        ) : (
                          <IconComponent className="h-4 w-4 mr-2" />
                        )}
                        {action.label}
                      </Button>
                    );
                  })}
                  
                  <Button
                    variant="outline"
                    onClick={onClearSelection}
                    disabled={isLoading}
                    className="text-sm hover:bg-muted"
                  >
                    Clear Selection
                  </Button>
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <p>No ongoing Student Verification. Contact admin for errors.</p>
              </TooltipContent>
            </Tooltip>
          ) : (
            <div className="flex flex-col sm:flex-row gap-2 lg:ml-4">
              {actions.map((action, index) => {
                const IconComponent = action.icon;
                return (
                  <Button
                    key={index}
                    variant={action.variant || 'default'}
                    onClick={action.onClick}
                    disabled={action.disabled || isLoading}
                    className={cn(
                      "text-sm font-medium transition-all duration-200",
                      "hover:scale-105 active:scale-95",
                      action.className
                    )}
                  >
                    {isLoading ? (
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    ) : (
                      <IconComponent className="h-4 w-4 mr-2" />
                    )}
                    {action.label}
                  </Button>
                );
              })}
              
              <Button
                variant="outline"
                onClick={onClearSelection}
                disabled={isLoading}
                className="text-sm hover:bg-muted"
              >
                Clear Selection
              </Button>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}

// Preset action configurations for common use cases
export const BatchActionPresets = {
  endorse: {
    approve: {
      label: 'Approve All',
      icon: CheckCircle,
      variant: 'default' as const,
      className: 'bg-green-600 hover:bg-green-700 text-white'
    },
    reject: {
      label: 'Reject All',
      icon: XCircle,
      variant: 'destructive' as const
    }
  },
  verify: {
    approve: {
      label: 'Verify All',
      icon: CheckCircle,
      variant: 'default' as const,
      className: 'bg-blue-600 hover:bg-blue-700 text-white'
    },
    reject: {
      label: 'Reject All',
      icon: XCircle,
      variant: 'destructive' as const
    }
  }
};
