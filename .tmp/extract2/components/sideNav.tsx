import * as React from 'react';
import { useState, useEffect } from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCogs, faSignOutAlt, faGlobe, faDownload, faWindowMaximize, faHome, faChartLine, faCog, faRocket, faNetworkWired, faHdd, faUsers, faCalendarAlt, faDatabase, faFolder, faTerminal, faUserFriends, faFileAlt, faPlug, faPuzzlePiece, faKey, faUser, faBars, faTimes } from '@fortawesome/free-solid-svg-icons';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import Avatar from '@/components/Avatar';
import tw, { theme } from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import { ServerContext } from '@/state/server';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import crypto from 'crypto';
import axios from 'axios';
import defaultRoutes from '@/routers/routes';
import addonRoutes from '@/blueprint/extends/routers/routes';

interface RouteDefinition {
    path: string;
    name?: string;
    exact?: boolean;
    adminOnly?: boolean;
    identifier?: string;
    permission?: string;
    component?: React.ComponentType<any>;
}

const SideNavigation = styled.div`
    ${tw`flex flex-col fixed top-0 left-0 z-50`};
    background: rgba(44, 51, 58, 0.95);
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    left: 0 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    height: 100vh;
    width: 240px !important;
    padding: 1.5rem 1rem;
    box-sizing: border-box;
    position: fixed !important;
    top: 0 !important;
    margin-top: 0 !important;
    padding: 0.5% !important;
    border-radius: 0rem !important;

    /* Compact mode: Hide text, show icons only */
    @media (max-width: 1540px) and (min-width: 1381px) {
        width: 80px !important;
        padding: 1rem 0.5rem !important;
    }
    
    @media (max-width: 1380px) {
        display: none; /* Hide desktop version on mobile */
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw;
        min-width: 0;
        z-index: 9999;
        border-radius: 0rem !important;
        background-color: rgba(0, 0, 0, 0.8);
        box-shadow: 0 4px 32px 0 rgba(0,0,0,0.55);
        align-items: stretch;
        justify-content: flex-start;
        padding: 2rem 1.5rem;
        transition: transform 0.3s cubic-bezier(.4,0,.2,1), box-shadow 0.3s;
        transform: translateY(100%);
        z-index: 9999;
        top: 0 !important;
        left: 0 !important;
        border: none !important;
        margin-left: 0 !important;
        
        &.mobile-open {
            display: flex; /* Show when open */
            transform: translateY(0);
        }
    }
`;

const MobileMenuButton = styled.button`
    display: none;
    position: fixed;
    bottom: 20px;
    right: 5px; /* Moved further right */
    z-index: 10001; /* Increased z-index */
    width: 60px;
    height: 60px;
    border-radius: var(--br-default) !important;
    background-color: hsla(0, 0%, 0%, 0.8) !important;
    backdrop-filter: blur(10px);
    border: 2px solid var(--primary-color);
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    pointer-events: auto !important; /* Force clickable */
    outline: none; /* Remove focus outline */
    touch-action: manipulation; /* Prevent touch delays */
    user-select: none; /* Prevent text selection */
    -webkit-tap-highlight-color: transparent; /* Remove tap highlight */
    -webkit-touch-callout: none; /* Disable callout */
    -webkit-user-select: none; /* Webkit user select */
    -moz-user-select: none; /* Firefox user select */
    -ms-user-select: none; /* IE user select */
    
    /* Ensure button is above everything */
    isolation: isolate;
    position: fixed !important;
    z-index: 99999 !important;
    
    &:hover {
        background-color: hsla(0, 0%, 0%, 0.8) !important;
        border-color: var(--primary-color);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
        transform: translateY(-2px);
    }
    
    &:active {
        transform: translateY(0px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    
    &.close-button {
        background-color: hsla(0, 0%, 0%, 0.8) !important;
        border-color: var(--primary-color);
        
        &:hover {
            background-color: hsla(0, 0%, 0%, 0.8) !important;
            border-color: var(--primary-color);
        }
        
        &:active {
            transform: translateY(0px);
        }
    }
    
    @media (max-width: 1380px) {
        display: flex;
        align-items: center;
        justify-content: center;
    }
`;

const MobileBackdrop = styled.div`
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    backdrop-filter: blur(2px);
    
    @media (max-width: 1380px) {
        &.mobile-open {
            display: block;
        }
    }
    
    /* Ensure it's always hidden on desktop */
    @media (min-width: 1381px) {
        display: none !important;
    }
`;

const SectionShared = styled.div`
    ${tw`flex flex-col`};
    margin-bottom: 2rem;
    overflow-y: scroll;
    overflow-x: hidden;

    @media (max-width: 1380px) {
       margin-bottom: 1.5rem;
       padding: 1.5%;
    }
`;

const MiddleSection = styled(SectionShared)`
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow-y: scroll;
    overflow-x: hidden;
    height: 50vh;
    
    @media (max-width: 1380px) {
        flex: 1 1 auto;
        overflow-y: scroll;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1.5rem;
        justify-content: flex-start;
        margin: 0;
        padding: 1.5%;
    }
`;

const NavItem = styled.div`
    ${tw`flex items-center cursor-pointer transition-all duration-200 rounded-lg mb-1`};
    padding: 0.75rem 1rem;
    text-decoration: none;
    color: #9ca3af;
    font-weight: 500;
    height: auto;
    width: 100%;
    border-radius: 0.5rem;
    transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    box-sizing: border-box;
    border-left: 3px solid transparent;
    
    &:hover {
        background-color: color-mix(in srgb, var(--primary-color) 15%, transparent);
        color: var(--primary-color);
        border-left: 3px solid var(--primary-color);
        padding-left: calc(1rem - 3px);
        transform: translateX(2px);
    }

    &.active {
        background-color: color-mix(in srgb, var(--primary-color) 15%, transparent);
        color: var(--primary-color);
        border-left: 3px solid var(--primary-color);
        padding-left: calc(1rem - 3px);
    }
    
    /* Compact mode: Center icons, reduce padding */
    @media (max-width: 1540px) and (min-width: 1381px) {
        padding: 0.75rem 0.5rem;
        justify-content: center;
        
        &:hover {
            padding-left: calc(0.5rem - 3px);
            transform: translateX(0px);
        }
        
        &.active {
            padding-left: calc(0.5rem - 3px);
        }
    }
    
    @media (max-width: 1380px) {
        padding: 0.75rem 1rem;
        height: auto;
        width: 100%;
    }
`;

const NavIcon = styled.div`
    ${tw`flex items-center justify-center mr-3`};
    width: 20px;
    height: 20px;
    font-size: 1rem;
    
    /* Compact mode: Remove right margin since no text */
    @media (max-width: 1540px) and (min-width: 1381px) {
        margin-right: 0;
    }
`;

const NavText = styled.span`
    ${tw`text-sm font-medium`};
    
    /* Hide text in compact mode */
    @media (max-width: 1540px) and (min-width: 1381px) {
        display: none;
    }
    
    @media (max-width: 1380px) {
        display: block;
    }
`;

const NavLinkStyled = styled(NavLink)`
    ${tw`w-full flex no-underline`};

    &.active ${NavItem} {
        background-color: color-mix(in srgb, var(--primary-color) 15%, transparent);
        color: var(--primary-color);
        border-left: 3px solid var(--primary-color);
        padding-left: calc(1rem - 3px);
    }
`;

const ButtonStyled = styled.button`
    ${tw`w-full flex no-underline`};
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    background: none !important;
    outline: none !important;
    box-shadow: none !important;
`;

const AppHeader = styled.div`
    ${tw`flex items-center justify-center`};
    padding: 1rem;
    background-color: var(--transparent);
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
    min-height: 64px;
    gap: 0.75rem;
    
    /* Compact mode: Hide text, center logo */
    @media (max-width: 1540px) and (min-width: 1381px) {
        padding: 0.75rem 0.5rem;
        min-height: 48px;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    @media (max-width: 1380px) {
        padding: 1rem;
        min-height: 48px;
        flex-direction: row;
        gap: 0.75rem;
    }
`;

const AppLogo = styled.img`
    height: 2rem;
    width: auto;
    object-fit: contain;
    
    /* Compact mode: Smaller logo */
    @media (max-width: 1540px) and (min-width: 1381px) {
        height: 1.5rem;
    }
    
    @media (max-width: 1380px) {
        height: 1.75rem;
    }
`;

const AppName = styled.div`
    ${tw`text-white text-sm font-medium text-center`};
    line-height: 1.2;
    font-size: 0.850rem;
    font-weight: 700;
    text-align: center;
    line-height: 1;
    
    /* Hide text in compact mode */
    @media (max-width: 1540px) and (min-width: 1381px) {
        display: none;
    }
    
    @media (max-width: 1380px) {
        display: block;
    }
`;

const CustomTooltip = styled(Tooltip)`
    ${tw`text-neutral-200`};
    background-color: hsla(0, 0%, 0%, 0.8) !important;
`;


const getIconForRoute = (routeName: string) => {
    switch (routeName) {
        case 'Activity':
            return faChartLine;
        case 'Settings':
            return faCog;
        case 'Startup':
            return faRocket;
        case 'Network':
            return faNetworkWired;
        case 'Backups':
            return faHdd;
        case 'Users':
            return faUsers;
        case 'Schedules':
            return faCalendarAlt;
        case 'Databases':
            return faDatabase;
        case 'Files':
            return faFolder;
        case 'Console':
            return faTerminal;
        case 'Players':
            return faUserFriends;
        case 'MC Logs':
            return faFileAlt;
        case 'Plugins Installer':
            return faPlug;
        case 'Mods Installer':
            return faPuzzlePiece;
        case 'SSH Keys':
            return faTerminal;
        case 'API Credentials':
            return faKey;
        case 'Account':
            return faUser;
        default:
            if (routeName.includes('domain')) return faGlobe;
            if (routeName.includes('addon') || routeName.includes('plugin')) return faDownload;
            return faWindowMaximize;
    }
};

const md5 = (str: string): string => {
    return crypto.createHash('md5').update(str).digest('hex');
};

const useExtensionEggs = () => {
    const [extensionEggs, setExtensionEggs] = useState<{ [x: string]: string[] }>(
        addonRoutes.server.reduce((prev, current) => ({ ...prev, [current.identifier]: ['-1'] }), {})
    );

    useEffect(() => {
        (async () => {
            const newEggs: { [x: string]: string[] } = {};
            for (const id of Object.keys(extensionEggs)) {
                const resp = await fetch(`/api/client/extensions/blueprint/eggs?${new URLSearchParams({ id })}`);
                newEggs[id] = (await resp.json()) as string[];
            }
            setExtensionEggs(newEggs);
        })();
    }, []);

    return extensionEggs;
};



export default () => {
    const location = useLocation();
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data?.rootAdmin ?? false);
    const [profilePictureUrl, setProfilePictureUrl] = useState<string | null>(null);
    const [email, setEmail] = useState<string | null>(null);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isMobile, setIsMobile] = useState(false);
    const [isCompactMode, setIsCompactMode] = useState(false);
    const [appName, setAppName] = useState<string>('');
    const [logoUrl, setLogoUrl] = useState<string>('');
    const [sideNavCompanyEnabled, setSideNavCompanyEnabled] = useState<boolean>(true);
    const [sideNavEnabled, setSideNavEnabled] = useState<boolean>(true);
    const [txAdminEnabled, setTxAdminEnabled] = useState<boolean>(false);
    const [txAdminEggId, setTxAdminEggId] = useState<string>('');

    // Check if mobile on mount and resize
    useEffect(() => {
        const checkScreenSize = () => {
            const width = window.innerWidth;
            setIsMobile(width <= 1380);
            setIsCompactMode(width > 1380 && width <= 1540);
        };
        
        checkScreenSize();
        window.addEventListener('resize', checkScreenSize);
        
        return () => window.removeEventListener('resize', checkScreenSize);
    }, []);

    // Lock/unlock body scroll when mobile menu opens/closes
    useEffect(() => {
        if (isMobile && isMobileMenuOpen) {
            // Lock body scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
        } else {
            // Unlock body scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
        }

        // Cleanup on unmount
        return () => {
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
        };
    }, [isMobile, isMobileMenuOpen]);

    const toggleMobileMenu = () => {
        setIsMobileMenuOpen(!isMobileMenuOpen);
    };

    const closeMobileMenu = () => {
        setIsMobileMenuOpen(false);
    };

    // TX Admin functionality
    const handleTxAdminClick = () => {
        if (!uuid) return;
        
        // Check for both old and new TX Admin port environment variables
        const TXAdminPortVar = variables.find((variable: any) => variable.envVariable === 'TXADMIN_PORT');
        const TXHostTxaPortVar = variables.find((variable: any) => variable.envVariable === 'TXHOST_TXA_PORT');
        const TXAdminEnabledVar = variables.find((variable: any) => variable.envVariable === 'TXADMIN_ENABLE');
        
        // Use new variable first, fallback to old one for backward compatibility
        const TXAdminPort = TXHostTxaPortVar?.serverValue || TXAdminPortVar?.serverValue;
        const TXAdminEnabled = TXAdminEnabledVar?.serverValue;
        
        if (parseInt(TXAdminEnabled ?? '0') === 1 && TXAdminPort) {
            http.get(`/api/client/servers/${uuid}`).then((data) => {
                const ip = data.data.attributes.relationships.allocations.data[0].attributes.ip;
                window.open(`http://${ip}:${TXAdminPort}`, '_blank');
            });
        }
        closeMobileMenu();
    };

    // Check if TX Admin should be shown
    const shouldShowTxAdmin = () => {
        if (!txAdminEnabled || !isServerRoute || !serverEgg || !txAdminEggId) return false;
        if (serverEgg.toString() !== txAdminEggId) return false;
        
        // Check for both old and new TX Admin port environment variables
        const TXAdminPortVar = variables.find((variable: any) => variable.envVariable === 'TXADMIN_PORT');
        const TXHostTxaPortVar = variables.find((variable: any) => variable.envVariable === 'TXHOST_TXA_PORT');
        const TXAdminEnabledVar = variables.find((variable: any) => variable.envVariable === 'TXADMIN_ENABLE');
        
        const TXAdminEnabled = TXAdminEnabledVar?.serverValue;
        const hasTxAdminPort = TXHostTxaPortVar?.serverValue || TXAdminPortVar?.serverValue;
        
        // TX Admin button shows if it's enabled AND there's a port configured (either old or new variable)
        return parseInt(TXAdminEnabled ?? '0') === 1 && !!hasTxAdminPort;
    };

    const handleMobileMenuClick = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (isMobileMenuOpen) {
            closeMobileMenu();
        } else {
            toggleMobileMenu();
        }
    };

    useEffect(() => {
        const fetchProfileData = async () => {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const { data } = await http.get('/extensions/euphoriatheme/user/profile', {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ?? '',
                    },
                });
                setProfilePictureUrl(data.profile_picture_url);
                setEmail(data.email);
            } catch (error) {
                console.error('Failed to fetch profile data:', error);
            }
        };

        fetchProfileData();
    }, []);

    useEffect(() => {
        // Fetch app name and logo from Pterodactyl Panel settings
        const fetchAppSettings = async () => {
            try {
                // Try to get app name from SiteConfiguration first
                const siteConfig = (window as any).SiteConfiguration;
                if (siteConfig && siteConfig.name) {
                    setAppName(siteConfig.name);
                } else {
                    // Fallback: Try to get app name from meta tags
                    const metaAppName = document.querySelector('meta[name="app-name"]')?.getAttribute('content');
                    if (metaAppName) {
                        setAppName(metaAppName);
                    }
                }

                // Get side nav company enabled setting
                if (siteConfig && typeof siteConfig.side_nav_company_enabled !== 'undefined') {
                    // Convert database value (0/1 or "0"/"1") to boolean
                    setSideNavCompanyEnabled(Boolean(Number(siteConfig.side_nav_company_enabled)));
                }

                // Get side nav enabled setting
                if (siteConfig && typeof siteConfig.side_nav_enabled !== 'undefined') {
                    // Convert database value (0/1 or "0"/"1") to boolean
                    setSideNavEnabled(Boolean(Number(siteConfig.side_nav_enabled)));
                }

                // Get TX Admin settings
                if (siteConfig && typeof siteConfig.tx_admin_enabled !== 'undefined') {
                    setTxAdminEnabled(Boolean(Number(siteConfig.tx_admin_enabled)));
                }
                if (siteConfig && siteConfig.tx_admin_egg_id) {
                    setTxAdminEggId(siteConfig.tx_admin_egg_id);
                }

                // Try to get logo from SiteConfiguration first
                if (siteConfig && siteConfig.logo_url) {
                    setLogoUrl(siteConfig.logo_url);
                } else {
                    // Try to get logo from favicon first (most reliable)
                    const faviconElement = document.querySelector('link[rel="icon"]') as HTMLLinkElement;
                    if (faviconElement && faviconElement.href) {
                        setLogoUrl(faviconElement.href);
                    } else {
                        // Fallback: Try to get logo from common image elements
                        const logoElement = document.querySelector('img[alt*="logo"], img[class*="logo"]') as HTMLImageElement;
                        if (logoElement && logoElement.src) {
                            setLogoUrl(logoElement.src);
                        }
                    }
                }

                // Set fallback values if nothing was found
                if (!appName && !siteConfig?.name) {
                    setAppName('Pterodactyl Panel');
                }
                if (!logoUrl && !siteConfig?.logo_url) {
                    setLogoUrl('/assets/extensions/euphoriatheme/logo.png');
                }
            } catch (error) {
                console.warn('Could not fetch app settings:', error);
                setAppName('Pterodactyl Panel');
                setLogoUrl('/assets/extensions/euphoriatheme/logo.png');
            }
        };

        fetchAppSettings();
    }, []);

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            window.location.href = '/';
        });
    };

    const isServerRoute = location.pathname.startsWith('/server/');
    const isAccountRoute = location.pathname.startsWith('/account');
    const defaultRoutesToRender = isAccountRoute ? defaultRoutes.account : (isServerRoute ? defaultRoutes.server : defaultRoutes.account);
    const blueprintRoutesToRender = isAccountRoute ? addonRoutes.account : (isServerRoute ? addonRoutes.server : addonRoutes.account);

    const id = isServerRoute ? ServerContext.useStoreState((state) => state.server.data?.id ?? '') : '';
    const serverEgg = isServerRoute ? ServerContext.useStoreState((state) => state.server.data?.BlueprintFramework.eggId ?? '') : '';
    const uuid = isServerRoute ? ServerContext.useStoreState((state) => state.server.data?.uuid ?? '') : '';
    const variables = isServerRoute ? ServerContext.useStoreState((state) => state.server.data?.variables ?? []) : [];
    const extensionEggs = useExtensionEggs();

    const filteredBlueprintRoutesToRender = blueprintRoutesToRender
        .filter((route: RouteDefinition) => route.path !== '/account')
        .filter((route: RouteDefinition) => (route.adminOnly ? rootAdmin : true))
        .filter((route: RouteDefinition) =>
            route.identifier && extensionEggs[route.identifier]?.includes('-1')
                ? true
                : route.identifier && extensionEggs[route.identifier]?.find((id: string) => id === serverEgg?.toString())
        );

    const routesToRender = [...defaultRoutesToRender, ...filteredBlueprintRoutesToRender];
    
    // Don't render navigation if side nav is disabled
    if (!sideNavEnabled) {
        return null;
    }
    
    return (
        <div>
            {/* Mobile Menu Button - Changes to close button when menu is open */}
            <MobileMenuButton 
                onClick={handleMobileMenuClick}
                className={isMobileMenuOpen ? 'close-button' : ''}
            >
                <FontAwesomeIcon icon={isMobileMenuOpen ? faTimes : faBars} />
            </MobileMenuButton>

            {/* Mobile Backdrop - Only render on mobile when menu is open */}
            {isMobile && isMobileMenuOpen && (
                <MobileBackdrop 
                    className="mobile-open"
                    onClick={closeMobileMenu}
                />
            )}

            <SideNavigation
                id="SideNavigation"
                className={isMobileMenuOpen ? 'mobile-open' : ''}
                style={{
                    display: 'flex',
                    left: 0,
                }}
                role="navigation"
                aria-label="Main navigation"
                tabIndex={0}
            >
                <SpinnerOverlay visible={isLoggingOut} />
                
                {/* App Header - Show app name and logo only if enabled */}
                {sideNavCompanyEnabled && (
                    <AppHeader>
                        {logoUrl && (
                            <AppLogo 
                                src={logoUrl} 
                                alt="App Logo"
                                onError={(e) => {
                                    // Hide logo if it fails to load
                                    (e.target as HTMLImageElement).style.display = 'none';
                                }}
                            />
                        )}
                        <AppName>{appName}</AppName>
                    </AppHeader>
                )}
                
                <MiddleSection>
                    <CustomTooltip content="Dashboard" placement="right" disabled={!isCompactMode}>
                        <NavLinkStyled to={'/'} exact onClick={closeMobileMenu}>
                            <NavItem>
                                <NavIcon>
                                    <FontAwesomeIcon icon={faHome} />
                                </NavIcon>
                                <NavText>Dashboard</NavText>
                            </NavItem>
                        </NavLinkStyled>
                    </CustomTooltip>
                    {routesToRender.map((route) =>
                        route.name ? (
                            <CustomTooltip key={route.path} content={route.name} placement="right" disabled={!isCompactMode}>
                                <NavLinkStyled to={isServerRoute ? `/server/${id}${route.path}` : `/account${route.path}`} exact={route.exact} activeClassName="active" onClick={closeMobileMenu}>
                                    <NavItem>
                                        <NavIcon>
                                            <FontAwesomeIcon icon={getIconForRoute(route.name)} />
                                        </NavIcon>
                                        <NavText>{route.name}</NavText>
                                    </NavItem>
                                </NavLinkStyled>
                            </CustomTooltip>
                        ) : null
                    )}

                    {/* TX Admin Button - Show only if TX Admin is enabled and current server matches the configured egg ID */}
                    {shouldShowTxAdmin() && (
                        <CustomTooltip content="TX Admin" placement="right" disabled={!isCompactMode}>
                            <ButtonStyled onClick={handleTxAdminClick}>
                                <NavItem>
                                    <NavIcon>
                                        <FontAwesomeIcon icon={faNetworkWired} />
                                    </NavIcon>
                                    <NavText>TX Admin</NavText>
                                    <div style={{ marginLeft: 'auto', opacity: 0.7 }}>
                                        <FontAwesomeIcon icon={faWindowMaximize} style={{ fontSize: '0.8em' }} />
                                    </div>
                                </NavItem>
                            </ButtonStyled>
                        </CustomTooltip>
                    )}
                </MiddleSection>
                
                <SectionShared>
                    {rootAdmin && (
                        <CustomTooltip content="Admin Panel" placement="right" disabled={!isCompactMode}>
                            <a href={'/admin'} rel={'noreferrer'} className={'w-full flex no-underline'} onClick={closeMobileMenu}>
                                <NavItem>
                                    <NavIcon>
                                        <FontAwesomeIcon icon={faCogs} />
                                    </NavIcon>
                                    <NavText>Admin</NavText>
                                </NavItem>
                            </a>
                        </CustomTooltip>
                    )}
                    <CustomTooltip content="Account Settings" placement="right" disabled={!isCompactMode}>
                        <NavLinkStyled to={'/account'} activeClassName="active" onClick={closeMobileMenu}>
                            <NavItem>
                                <NavIcon>
                                    {profilePictureUrl ? (
                                        <img src={profilePictureUrl} style={{ borderRadius: '50%', height: '20px', width: '20px', objectFit: 'cover' }} alt="User" />
                                    ) : (
                                        email && (
                                            <img src={`https://www.gravatar.com/avatar/${md5(email.toLowerCase())}?s=160`} style={{ borderRadius: '50%', height: '20px', width: '20px', objectFit: 'cover' }} alt="User" />
                                        )
                                    )}
                                </NavIcon>
                                <NavText>Account Settings</NavText>
                            </NavItem>
                        </NavLinkStyled>
                    </CustomTooltip>
                    <CustomTooltip content="Sign Out" placement="right" disabled={!isCompactMode}>
                        <ButtonStyled onClick={() => { onTriggerLogout(); closeMobileMenu(); }}>
                            <NavItem>
                                <NavIcon>
                                    <FontAwesomeIcon icon={faSignOutAlt} />
                                </NavIcon>
                                <NavText>Sign Out</NavText>
                            </NavItem>
                        </ButtonStyled>
                    </CustomTooltip>
                </SectionShared>
            </SideNavigation>
        </div>
    );
};