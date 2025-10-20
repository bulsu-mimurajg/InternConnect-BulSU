import React from 'react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Copy, RefreshCw } from 'lucide-react';

interface OTPGeneratorProps {
    otp: string;
    showOTP: boolean;
    onGenerateOTP: () => void;
    onCopyOTP: () => void;
    label?: string;
    description?: string;
    className?: string;
}

export const OTPGenerator: React.FC<OTPGeneratorProps> = ({
    otp,
    showOTP,
    onGenerateOTP,
    onCopyOTP,
    label = "Password",
    description = "This password will be sent to the user's email. They must change it on first login.",
    className = ""
}) => {
    return (
        <TooltipProvider>
            <div className={`space-y-2 ${className}`}>
                {label && <Label className="text-sm font-medium">{label}</Label>}
                <div className="relative">
                    <Input
                        type="text"
                        value={otp}
                        placeholder="Generate OTP"
                        readOnly
                        className={`pr-20 ${showOTP ? 'font-mono' : ''}`}
                    />
                    <div className="absolute right-1 top-1/2 transform -translate-y-1/2 flex items-center gap-1">
                        {showOTP && otp && (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={onCopyOTP}
                                        className="h-6 w-6 p-0"
                                    >
                                        <Copy className="h-3 w-3" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Copy</p>
                                </TooltipContent>
                            </Tooltip>
                        )}
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={onGenerateOTP}
                            className="h-6 px-2 text-xs"
                        >
                            <RefreshCw className="h-3 w-3 mr-1" />
                            Generate
                        </Button>
                    </div>
                </div>
                {showOTP && otp && (
                    <div className="p-2 bg-muted/50 rounded text-xs text-muted-foreground">
                        {description}
                    </div>
                )}
            </div>
        </TooltipProvider>
    );
};
