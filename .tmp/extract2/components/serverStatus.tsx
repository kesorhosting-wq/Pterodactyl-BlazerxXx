import React, { memo, useEffect, useRef, useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCircle, faSpinner } from '@fortawesome/free-solid-svg-icons';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import isEqual from 'react-fast-compare';

// Status type definition for server status badge
type ServerStatusType = 'online' | 'offline' | 'starting' | 'stopping' | 'installing' | 'transferring' | 'suspended';

// Get simplified status from server state
const getSimplifiedStatus = (
    serverStatus: string | null,
    powerState: ServerPowerState | undefined,
    isTransferring: boolean,
    isSuspended: boolean
): ServerStatusType => {
    if (isSuspended || serverStatus === 'suspended') return 'suspended';
    if (isTransferring) return 'transferring';
    if (serverStatus === 'installing') return 'installing';
    if (serverStatus === 'restoring_backup') return 'starting';
    
    if (powerState === 'running') return 'online';
    if (powerState === 'starting') return 'starting';
    if (powerState === 'stopping') return 'stopping';
    
    return 'offline';
};

// Status configuration with colors and labels
const statusConfig = {
    online: {
        label: 'Online',
        color: tw`text-green-400`,
        bgColor: tw`bg-green-500`,
        icon: faCircle,
        animated: false
    },
    offline: {
        label: 'Offline',
        color: tw`text-red-400`,
        bgColor: tw`bg-red-500`,
        icon: faCircle,
        animated: false
    },
    starting: {
        label: 'Starting',
        color: tw`text-yellow-400`,
        bgColor: tw`bg-yellow-500`,
        icon: faSpinner,
        animated: true
    },
    stopping: {
        label: 'Stopping',
        color: tw`text-red-400`,
        bgColor: tw`bg-red-500`,
        icon: faSpinner,
        animated: true
    },
    installing: {
        label: 'Installing',
        color: tw`text-blue-400`,
        bgColor: tw`bg-blue-500`,
        icon: faSpinner,
        animated: true
    },
    transferring: {
        label: 'Transferring',
        color: tw`text-purple-400`,
        bgColor: tw`bg-purple-500`,
        icon: faSpinner,
        animated: true
    },
    suspended: {
        label: 'Suspended',
        color: tw`text-neutral-400`,
        bgColor: tw`bg-neutral-500`,
        icon: faCircle,
        animated: false
    }
};



// Glass status badge component
const StatusBadge = styled.span<{ $powerState: ServerPowerState | undefined }>`
    ${tw`px-2 py-1 rounded text-xs font-medium text-white inline-block ml-1`};
    backdrop-filter: blur(8px);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    
    ${({ $powerState }: { $powerState: ServerPowerState | undefined }) => {
        switch ($powerState) {
            case 'running':
                return `
                    background-color: rgba(16, 185, 129, 0.2);
                    border: 1px solid rgb(34, 197, 94);
                    box-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
                `;
            case 'starting':
                return `
                    background-color: rgba(245, 158, 11, 0.2);
                    border: 1px solid rgb(251, 191, 36);
                    box-shadow: 0 0 10px rgba(251, 191, 36, 0.3);
                `;
            case 'stopping':
                return `
                    background-color: rgba(239, 68, 68, 0.2);
                    border: 1px solid rgb(248, 113, 113);
                    box-shadow: 0 0 10px rgba(248, 113, 113, 0.3);
                `;
            default:
                return `
                    background-color: rgba(107, 114, 128, 0.2);
                    border: 1px solid rgb(156, 163, 175);
                    box-shadow: 0 0 10px rgba(156, 163, 175, 0.3);
                `;
        }
    }};
`;

type Timer = ReturnType<typeof setInterval>;

export default (props: any) => {
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    
    // Check if server tooltips are enabled from the site configuration  
    const siteConfig = (window as any).SiteConfiguration || {};
    const serverTooltipsEnabled = siteConfig.server_tooltips_enabled;
    
    // If server tooltips are disabled, don't render anything
    if (!serverTooltipsEnabled) {
        return null;
    }

    // Since no props are passed, try to get server data from DOM context
    const [serverData, setServerData] = useState<any>(null);
    
    // Use a ref to track this component instance
    const componentRef = useRef<HTMLSpanElement>(null);
    
    React.useEffect(() => {
        // Find the server data for this specific component instance
        const findServerData = () => {
            if (!componentRef.current) return;
            
            try {
                // Find the parent server row by traversing up the DOM
                let currentElement = componentRef.current.parentElement;
                let serverLink = null;
                
                // Look for the server link within the same row (traverse up to find it)
                while (currentElement && !serverLink) {
                    // Check if current element is a server link
                    if (currentElement.tagName === 'A' && currentElement.getAttribute('href')?.includes('/server/')) {
                        serverLink = currentElement as HTMLAnchorElement;
                        break;
                    }
                    
                    // Check if current element contains a server link
                    const linkInElement = currentElement.querySelector('a[href*="/server/"]') as HTMLAnchorElement;
                    if (linkInElement) {
                        serverLink = linkInElement;
                        break;
                    }
                    
                    currentElement = currentElement.parentElement;
                }
                
                if (serverLink) {
                    const href = serverLink.href;
                    const serverUuid = href.split('/server/')[1]?.split('/')[0];
                    
                    if (serverUuid) {
                        const basicServer = {
                            uuid: serverUuid,
                            status: null,
                            isTransferring: false,
                            name: serverLink.querySelector('[class*="text-lg"]')?.textContent || 'Server'
                        };
                        
                        setServerData(basicServer);
                    }
                }
            } catch (error) {
                console.error('Error finding server data:', error);
            }
        };
        
        // Small delay to ensure DOM is ready
        setTimeout(findServerData, 50);
    }, []);
    
    // If we found server data, use it to fetch real status
    const [stats, setStats] = useState<ServerStats | null>(null);
    
    React.useEffect(() => {
        if (serverData?.uuid) {
            getServerResourceUsage(serverData.uuid)
                .then((data: ServerStats) => setStats(data))
                .catch((error: any) => console.error('Error fetching server stats:', error));
        }
    }, [serverData]);
    
    // Determine status
    let status: ServerPowerState | undefined = stats?.status || 'offline';
    let label = 'Loading...';
    
    if (stats) {
        if (stats.status === 'running') {
            label = 'Online';
        } else if (stats.status === 'starting') {
            label = 'Starting/Running';
        } else if (stats.status === 'stopping') {
            label = 'Stopping';
        } else {
            label = 'Offline';
        }
    } else if (serverData) {
        label = 'Loading...';
    } else {
        label = 'Unknown';
        status = 'offline';
    }
    
    return React.createElement('span', {
        ref: componentRef,
        style: {
            backgroundColor: status === 'running' ? 'rgba(16, 185, 129, 0.2)' : 
                           status === 'starting' ? 'rgba(245, 158, 11, 0.2)' :
                           status === 'stopping' ? 'rgba(239, 68, 68, 0.2)' :
                           'rgba(107, 114, 128, 0.2)',
            border: status === 'running' ? '1px solid rgb(34, 197, 94)' :
                   status === 'starting' ? '1px solid rgb(251, 191, 36)' :
                   status === 'stopping' ? '1px solid rgb(248, 113, 113)' :
                   '1px solid rgb(156, 163, 175)',
            boxShadow: status === 'running' ? '0 0 10px rgba(34, 197, 94, 0.3)' :
                      status === 'starting' ? '0 0 10px rgba(251, 191, 36, 0.3)' :
                      status === 'stopping' ? '0 0 10px rgba(248, 113, 113, 0.3)' :
                      '0 0 10px rgba(156, 163, 175, 0.3)',
            color: 'white',
            padding: '4px 8px',
            borderRadius: '6px',
            fontSize: '11px',
            fontWeight: '500',
            display: 'inline-block',
            marginLeft: '4px',
            textShadow: '0 1px 2px rgba(0, 0, 0, 0.5)',
            backdropFilter: 'blur(8px)'
        },
        title: `Server Status: ${label}`
    }, label);
    
    /* 
    // Original server logic - will restore once we figure out how to get server data
    const server = props.server || props.data;
    
    // Rest of the server logic commented out until we debug props
    */
};