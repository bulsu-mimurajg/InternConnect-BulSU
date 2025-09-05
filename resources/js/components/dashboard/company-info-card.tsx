import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Building2Icon, MapPinIcon, MailIcon, PhoneIcon, UserIcon } from 'lucide-react';

interface CompanyInfoCardProps {
    companyName: string;
    address: string;
    email: string;
    phone: string;
    contactPerson: string;
}

export function CompanyInfoCard({ companyName, address, email, phone, contactPerson }: CompanyInfoCardProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Building2Icon className="h-5 w-5" />
                    Company Information
                </CardTitle>
                <CardDescription>Your company details and contact information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                <div className="flex items-center gap-3">
                    <Building2Icon className="h-4 w-4 text-muted-foreground" />
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Company Name</label>
                        <p className="text-lg font-semibold">{companyName}</p>
                    </div>
                </div>
                
                <div className="flex items-center gap-3">
                    <MapPinIcon className="h-4 w-4 text-muted-foreground" />
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Address</label>
                        <p className="text-sm">{address}</p>
                    </div>
                </div>
                
                <div className="flex items-center gap-3">
                    <MailIcon className="h-4 w-4 text-muted-foreground" />
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Email</label>
                        <p className="text-sm">{email}</p>
                    </div>
                </div>
                
                <div className="flex items-center gap-3">
                    <PhoneIcon className="h-4 w-4 text-muted-foreground" />
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Phone</label>
                        <p className="text-sm">{phone}</p>
                    </div>
                </div>
                
                <div className="flex items-center gap-3">
                    <UserIcon className="h-4 w-4 text-muted-foreground" />
                    <div>
                        <label className="text-sm font-medium text-muted-foreground">Contact Person</label>
                        <p className="text-sm">{contactPerson}</p>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
