import { Icon } from '@/components/icon';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type NavGroup, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Collapsible, CollapsibleTrigger, CollapsibleContent } from '@radix-ui/react-collapsible';
import { ChevronDownIcon } from 'lucide-react';
import { useState, useEffect } from 'react';
import { cn } from '@/lib/utils';

export function NavMain({ items = [], groups = [], role }: { items?: NavItem[]; groups?: NavGroup[]; role?: string }) {
    const page = usePage<SharedData>();
    const [openSections, setOpenSections] = useState<Record<string, boolean>>({});
    
    // Get current URL safely
    const currentUrl = page.url || window.location.pathname;
    
    // Get role-based group label
    const getRoleLabel = (role?: string) => {
        switch (role) {
            case 'admin':
                return 'Administrator';
            case 'hte':
                return 'Host Training Establishment';
            case 'adviser':
                return 'Adviser';
            case 'student':
                return 'Student';
            case 'guest':
                return 'Guest';
            default:
                return 'Main Menu';
        }
    };
    
    // Initialize open sections based on current URL
    useEffect(() => {
        const newOpenSections: Record<string, boolean> = {};
        
        // Handle single items array
        items.forEach((item) => {
            if (item.subNav) {
                newOpenSections[item.title] = currentUrl.startsWith(item.href);
            }
        });
        
        // Handle grouped navigation
        groups.forEach((group) => {
            group.items.forEach((item) => {
                if (item.subNav) {
                    newOpenSections[item.title] = currentUrl.startsWith(item.href);
                }
            });
        });
        
        setOpenSections(newOpenSections);
    }, [currentUrl, items, groups]);
    
    // Toggle section open/close
    const toggleSection = (title: string, event: React.MouseEvent) => {
        event.preventDefault();
        event.stopPropagation();
        setOpenSections(prev => ({
            ...prev,
            [title]: !prev[title]
        }));
    };
    
    // Helper function to render navigation items
    const renderNavItems = (navItems: NavItem[]) => {
        return navItems.map((item) => (
            <SidebarMenuItem key={item.title}>
                {item.subNav ? (
                    <Collapsible 
                        open={openSections[item.title]} 
                        onOpenChange={(open) => setOpenSections(prev => ({ ...prev, [item.title]: open }))}
                    >
                        <CollapsibleTrigger asChild>
                            <SidebarMenuButton
                                isActive={currentUrl.startsWith(item.href)}
                                tooltip={{ children: item.title }}
                                onClick={(e) => toggleSection(item.title, e)}
                                className="w-full justify-between"
                            >
                                <div className="flex items-center">
                                    {item.icon && <Icon iconNode={item.icon} className="h-4 w-4 mr-2" />}
                                    <span>{item.title}</span>
                                </div>
                                <ChevronDownIcon className={cn(
                                    "ml-auto transition-transform duration-200",
                                    openSections[item.title] ? "rotate-180" : ""
                                )} />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenu>
                                {item.subNav.map((subItem) => (
                                    <SidebarMenuItem key={subItem.title}>
                                        <SidebarMenuButton asChild isActive={currentUrl === subItem.href}>
                                            <Link href={subItem.href} prefetch>
                                                <span className="ml-4">{subItem.title}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                ))}
                            </SidebarMenu>
                        </CollapsibleContent>
                    </Collapsible>
                ) : (
                    <SidebarMenuButton asChild isActive={currentUrl.startsWith(item.href)} tooltip={{ children: item.title }}>
                        <Link href={item.href} prefetch>
                            {item.icon && <Icon iconNode={item.icon} className="h-4 w-4" />}
                            <span>{item.title}</span>
                        </Link>
                    </SidebarMenuButton>
                )}
            </SidebarMenuItem>
        ));
    };
    
    // If we have groups, render them with separate group labels
    if (groups.length > 0) {
        return (
            <>
                {groups.map((group, groupIndex) => (
                    <SidebarGroup key={groupIndex} className="px-2 py-0">
                        <SidebarGroupLabel>{group.title}</SidebarGroupLabel>
                        <SidebarMenu>
                            {renderNavItems(group.items)}
                        </SidebarMenu>
                    </SidebarGroup>
                ))}
            </>
        );
    }
    
    // Otherwise, render single group with role-based label
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>{getRoleLabel(role)}</SidebarGroupLabel>
            <SidebarMenu>
                {renderNavItems(items)}
            </SidebarMenu>
        </SidebarGroup>
    );
}
