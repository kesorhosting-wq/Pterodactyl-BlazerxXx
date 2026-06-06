import * as React from 'react';
import { useState, useEffect } from 'react';
import { NavLink, useRouteMatch } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faArchive,
    faBars,
    faCalendarAlt,
    faCogs,
    faDatabase,
    faFolderOpen,
    faHistory,
    faHome,
    faMicrochip,
    faNetworkWired,
    faPlug,
    faPuzzlePiece,
    faRocket,
    faSignOutAlt,
    faStore,
    faTerminal,
    faTimes,
    faUsers,
} from '@fortawesome/free-solid-svg-icons';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import crypto from 'crypto';

const SideNavigation = styled.div`
    ${tw`flex flex-col fixed top-0 left-0 z-50`};
    background:
        linear-gradient(180deg, rgba(13, 18, 29, 0.92), rgba(5, 8, 14, 0.84)),
        color-mix(in srgb, var(--primary-color) 10%, transparent);
    background-color: rgba(5, 8, 14, 0.88) !important;
    backdrop-filter: blur(22px) saturate(1.18);
    border-right: 1px solid rgba(255, 255, 255, 0.12);
    height: 100vh;
    max-height: 100vh;
    width: 260px !important;
    padding: 1rem !important;
    box-sizing: border-box;
    position: fixed !important;
    top: 0 !important;
    margin-top: 0 !important;
    border-radius: 0rem !important;
    box-shadow: 22px 0 70px rgba(0, 0, 0, 0.34);
    animation: euphoria-sidebar-in 360ms cubic-bezier(0.22, 1, 0.36, 1);
    overflow: hidden;
    min-height: 0;

    &::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at 15% 8%, color-mix(in srgb, var(--primary-color) 22%, transparent), transparent 15rem),
            linear-gradient(90deg, rgba(255,255,255,0.06), transparent 42%);
        opacity: 0.8;
    }

    & > * {
        position: relative;
        z-index: 1;
    }
    
    /* Compact mode: Hide text, show icons only */
    @media (max-width: 1540px) and (min-width: 1381px) {
        width: 84px !important;
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
        background-color: rgba(5, 8, 14, 0.94) !important;
        box-shadow: 0 4px 45px rgba(0,0,0,0.65);
        align-items: stretch;
        justify-content: flex-start;
        padding: 2rem 1.5rem;
        transition: transform 0.34s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s;
        transform: translateY(104%);
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
    bottom: 18px;
    right: 18px;
    z-index: 10001;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background:
        linear-gradient(135deg, color-mix(in srgb, var(--primary-color) 72%, #fff 8%), color-mix(in srgb, var(--primary-color) 42%, #000 44%)) !important;
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: white;
    font-size: 22px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.34), 0 0 0 6px rgba(255, 255, 255, 0.04);
    pointer-events: auto !important;
    outline: none;
    touch-action: manipulation;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    isolation: isolate;
    position: fixed !important;
    z-index: 99999 !important;
    
    &:hover {
        box-shadow: 0 22px 55px rgba(0, 0, 0, 0.42), 0 0 0 8px color-mix(in srgb, var(--primary-color) 18%, transparent);
        transform: translateY(-2px) scale(1.02);
    }
    
    &:active {
        transform: translateY(0px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }
    
    &.close-button {
        background: rgba(9, 13, 22, 0.9) !important;
        border-color: rgba(255, 255, 255, 0.22);
        
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
    background: rgba(2, 6, 12, 0.62);
    z-index: 9998;
    backdrop-filter: blur(8px);
    
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
    flex: 0 0 auto;
    min-height: 0;
    margin-bottom: 1rem;
    overflow-y: auto;
    overflow-x: hidden;
    gap: 0.35rem;
    overscroll-behavior: contain;
    scrollbar-width: thin;
    scrollbar-color: color-mix(in srgb, var(--primary-color) 55%, rgba(255,255,255,0.16)) transparent;

    &::-webkit-scrollbar {
        width: 8px;
    }

    &::-webkit-scrollbar-track {
        background: transparent;
    }

    &::-webkit-scrollbar-thumb {
        background: color-mix(in srgb, var(--primary-color) 45%, rgba(255,255,255,0.16));
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    @media (max-width: 1380px) {
       margin-bottom: 1.5rem;
       padding: 1.5%;
    }
`;

const MiddleSection = styled(SectionShared)`
    flex: 1 1 auto;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 0.2rem;

    &.server-nav-scroll {
        max-height: calc(100vh - 9.5rem);
        overflow-y: scroll !important;
        overflow-x: hidden !important;
        padding-right: 0.35rem;
    }
    
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
    position: relative;
    padding: 0.78rem 0.9rem;
    text-decoration: none;
    color: rgba(226, 232, 240, 0.72);
    font-weight: 500;
    height: auto;
    width: 100%;
    border-radius: 14px;
    transition: all 240ms cubic-bezier(0.22, 1, 0.36, 1);
    user-select: none;
    box-sizing: border-box;
    border: 1px solid transparent;
    overflow: hidden;

    &::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: 0;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary-color) 18%, transparent), rgba(255, 255, 255, 0.04));
        transition: opacity 220ms ease;
    }

    & > * {
        position: relative;
        z-index: 1;
    }
    
    &:hover {
        color: #fff;
        border-color: color-mix(in srgb, var(--primary-color) 38%, rgba(255,255,255,0.12));
        transform: translateX(3px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.22);
    }

    &:hover::before {
        opacity: 1;
    }

    &.active {
        background: color-mix(in srgb, var(--primary-color) 18%, rgba(255,255,255,0.06));
        color: #fff;
        border-color: color-mix(in srgb, var(--primary-color) 45%, rgba(255,255,255,0.18));
        box-shadow: inset 3px 0 0 var(--primary-color), 0 12px 28px rgba(0, 0, 0, 0.2);
    }
    
    /* Compact mode: Center icons, reduce padding */
    @media (max-width: 1540px) and (min-width: 1381px) {
        padding: 0.8rem 0.5rem;
        justify-content: center;
        
        &:hover {
            transform: translateX(0px);
        }
        
        &.active {
            box-shadow: inset 0 -3px 0 var(--primary-color), 0 12px 28px rgba(0, 0, 0, 0.2);
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
    width: 22px;
    height: 22px;
    font-size: 1rem;
    color: inherit;
    
    /* Compact mode: Remove right margin since no text */
    @media (max-width: 1540px) and (min-width: 1381px) {
        margin-right: 0;
    }
`;

const NavText = styled.span`
    ${tw`text-sm font-medium`};
    color: inherit;
    letter-spacing: 0;
    
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
        background: color-mix(in srgb, var(--primary-color) 18%, rgba(255,255,255,0.06));
        color: #fff;
        border-color: color-mix(in srgb, var(--primary-color) 45%, rgba(255,255,255,0.18));
        box-shadow: inset 3px 0 0 var(--primary-color), 0 12px 28px rgba(0, 0, 0, 0.2);
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
    ${tw`flex items-center justify-start`};
    padding: 0.85rem;
    background: rgba(255, 255, 255, 0.055);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 16px;
    margin-bottom: 1rem;
    min-height: 66px;
    gap: 0.75rem;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.08), 0 16px 35px rgba(0,0,0,0.22);
    
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
    height: 2.15rem;
    width: 2.15rem;
    object-fit: contain;
    border-radius: 10px;
    
    /* Compact mode: Smaller logo */
    @media (max-width: 1540px) and (min-width: 1381px) {
        height: 1.5rem;
    }
    
    @media (max-width: 1380px) {
        height: 1.75rem;
    }
`;

const AppName = styled.div`
    ${tw`text-white text-sm font-medium`};
    font-size: 0.92rem;
    font-weight: 700;
    text-align: left;
    line-height: 1.15;
    
    /* Hide text in compact mode */
    @media (max-width: 1540px) and (min-width: 1381px) {
        display: none;
    }
    
    @media (max-width: 1380px) {
        display: block;
    }
`;

const md5 = (str: string): string => {
    return crypto.createHash('md5').update(str).digest('hex');
};

const serverNavigationRoutes = [
    { path: '/', name: 'Console', exact: true },
    { path: '/files', name: 'Files' },
    { path: '/modpack', name: 'Modpack' },
    { path: '/mcplugins', name: 'Plugins' },
    { path: '/databases', name: 'Databases' },
    { path: '/schedules', name: 'Schedules' },
    { path: '/users', name: 'Users' },
    { path: '/backups', name: 'Backups' },
    { path: '/network', name: 'Network' },
    { path: '/startup', name: 'Startup' },
    { path: '/settings', name: 'Settings' },
    { path: '/activity', name: 'Activity' },
    { path: '/bedrock-version', name: 'Bedrock Version' },
    { path: '/minecraft/versions', name: 'Versions' },
    { path: '/splitter', name: 'Splitter' },
    { path: '/minecraft/players', name: 'Players' },
    { path: '/subdomains', name: 'Subdomain' },
    { path: '/mcmods', name: 'Mods Installer' },
];

export default () => {
    const serverMatch = useRouteMatch<{ id: string }>('/server/:id');
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data?.rootAdmin);
    const [profilePictureUrl, setProfilePictureUrl] = useState<string | null>(null);
    const [email, setEmail] = useState<string | null>(null);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const [isMobile, setIsMobile] = useState(false);
    const [appName, setAppName] = useState<string>('');
    const [logoUrl, setLogoUrl] = useState<string>('');
    const [sideNavCompanyEnabled, setSideNavCompanyEnabled] = useState<boolean>(true);
    const [sideNavEnabled, setSideNavEnabled] = useState<boolean>(true);
    const [customButtonsInSideNav, setCustomButtonsInSideNav] = useState<boolean>(false);
    const [customButtons, setCustomButtons] = useState<Array<{ title: string; url: string; icon?: string }>>([]);

    const serverBasePath = serverMatch ? `/server/${serverMatch.params.id}` : '';
    const toServerPath = (path: string) =>
        path === '/' ? serverBasePath : `${serverBasePath}/${path.replace(/^\/+/, '')}`;
    const serverIconFor = (name?: string) => {
        switch (name) {
            case 'Console':
                return faTerminal;
            case 'Files':
                return faFolderOpen;
            case 'Databases':
                return faDatabase;
            case 'Schedules':
                return faCalendarAlt;
            case 'Users':
                return faUsers;
            case 'Backups':
                return faArchive;
            case 'Network':
                return faNetworkWired;
            case 'Startup':
                return faRocket;
            case 'Settings':
                return faCogs;
            case 'Activity':
                return faHistory;
            case 'Plugins':
            case 'Mods Installer':
                return faPuzzlePiece;
            case 'Versions':
            case 'Bedrock Version':
                return faMicrochip;
            default:
                return faPlug;
        }
    };

    // Validate URLs used in navigation (allow absolute http(s) and root-relative paths)
    const isValidUrl = (u: any): boolean => {
        if (!u || typeof u !== 'string') return false;
        const trimmed = u.trim();
        return trimmed.length > 3 && (/^https?:\/\//i.test(trimmed) || trimmed.startsWith('/'));
    };

    // Sanitize hrefs for anchors
    const safeHref = (u: string) => {
        try {
            return encodeURI(u.trim());
        } catch (e) {
            return '#';
        }
    };

    // Safe renderer for custom buttons to avoid throwing in render()
    const renderCustomButtons = () => {
        try {
            if (!customButtons || customButtons.length === 0) return null;
            return customButtons.map((btn: { title: string; url: string; icon?: string }, idx: number) => {
                if (!btn || typeof btn.title !== 'string' || !isValidUrl(btn.url)) return null;
                return (
                    <a key={`custom-btn-${idx}`} href={safeHref(btn.url)} target="_blank" rel="noreferrer" className={'w-full flex no-underline'} onClick={() => setIsMobileMenuOpen(false)}>
                        <NavItem>
                            <NavIcon>
                                {btn.icon ? (
                                    <i className={btn.icon} aria-hidden="true" />
                                ) : (
                                    <FontAwesomeIcon icon={faBars} />
                                )}
                            </NavIcon>
                            <NavText>{btn.title}</NavText>
                        </NavItem>
                    </a>
                );
            });
        } catch (e) {
            console.warn('Error rendering custom buttons', e);
            return null;
        }
    };

    // Check if mobile on mount and resize
    useEffect(() => {
        const checkMobile = () => {
            setIsMobile(window.innerWidth <= 1380);
        };
        
        checkMobile();
        window.addEventListener('resize', checkMobile);
        
        return () => window.removeEventListener('resize', checkMobile);
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
                // Helper to validate URLs (allow absolute http(s) and root-relative paths)
                const isValidUrl = (u: any): boolean => {
                    if (!u || typeof u !== 'string') return false;
                    const trimmed = u.trim();
                    return trimmed.length > 3 && (/^https?:\/\//i.test(trimmed) || trimmed.startsWith('/'));
                };

                // Read custom buttons configuration if present
                if (siteConfig) {
                    try {
                        const inSideNav = typeof siteConfig.custom_buttons_in_side_nav !== 'undefined'
                            ? Boolean(Number(siteConfig.custom_buttons_in_side_nav))
                            : false;

                        let buttons: Array<{ title: string; url: string; icon?: string }> = [];
                        if (siteConfig.custom_buttons) {
                            if (typeof siteConfig.custom_buttons === 'string') {
                                try {
                                    buttons = JSON.parse(siteConfig.custom_buttons);
                                } catch (e) {
                                    // ignore parse errors
                                    buttons = [];
                                }
                            } else if (Array.isArray(siteConfig.custom_buttons)) {
                                buttons = siteConfig.custom_buttons;
                            }
                        }

                        // Filter out any malformed buttons (must have a valid url and title)
                        buttons = (buttons || []).filter((b: any) => {
                            try {
                                return b && typeof b.title === 'string' && b.title.trim().length > 0 && isValidUrl(b.url);
                            } catch (e) {
                                return false;
                            }
                        });

                        // Don't rely on a separate "enabled" flag here; rendering is determined by whether
                        // content exists (store/status/discord/customButtons) AND the placement toggle.
                        setCustomButtonsInSideNav(inSideNav);
                        setCustomButtons(buttons || []);
                    } catch (e) {
                        console.warn('Failed to parse custom buttons from SiteConfiguration', e);
                    }
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
                                onError={(e: React.SyntheticEvent<HTMLImageElement>) => {
                                    // Hide logo if it fails to load
                                    (e.target as HTMLImageElement).style.display = 'none';
                                }}
                            />
                        )}
                        <AppName>{appName}</AppName>
                    </AppHeader>
                )}
                
                <MiddleSection className={serverMatch ? 'server-nav-scroll' : undefined}>
                    <NavLinkStyled to={'/'} exact onClick={closeMobileMenu}>
                        <NavItem>
                            <NavIcon>
                                <FontAwesomeIcon icon={faHome} />
                            </NavIcon>
                            <NavText>{serverMatch ? 'All Servers' : 'Dashboard'}</NavText>
                        </NavItem>
                    </NavLinkStyled>

                    {serverMatch ? (
                        <>
                            {serverNavigationRoutes.map((route) => (
                                <NavLinkStyled
                                    key={`${route.path}:${route.name}`}
                                    to={toServerPath(route.path)}
                                    exact={route.exact}
                                    onClick={closeMobileMenu}
                                >
                                    <NavItem>
                                        <NavIcon>
                                            <FontAwesomeIcon icon={serverIconFor(route.name)} />
                                        </NavIcon>
                                        <NavText>{route.name}</NavText>
                                    </NavItem>
                                </NavLinkStyled>
                            ))}
                        </>
                    ) : (
                        <>
                            {/* Built-in navigation URLs - show if enabled and URL is valid */}
                            {customButtonsInSideNav && isValidUrl((window as any).SiteConfiguration?.store_url) && (
                                <a href={(window as any).SiteConfiguration.store_url} target="_blank" rel="noreferrer" className={'w-full flex no-underline'} onClick={closeMobileMenu}>
                                    <NavItem>
                                        <NavIcon>
                                            <FontAwesomeIcon icon={faStore} />
                                        </NavIcon>
                                        <NavText>Store</NavText>
                                    </NavItem>
                                </a>
                            )}

                            {customButtonsInSideNav && isValidUrl((window as any).SiteConfiguration?.status_url) && (
                                <a href={(window as any).SiteConfiguration.status_url} target="_blank" rel="noreferrer" className={'w-full flex no-underline'} onClick={closeMobileMenu}>
                                    <NavItem>
                                        <NavIcon>
                                            <i className={'fa-solid fa-signal'}></i> 
                                        </NavIcon>
                                        <NavText>Status</NavText>
                                    </NavItem>
                                </a>
                            )}

                            {customButtonsInSideNav && isValidUrl((window as any).SiteConfiguration?.discord_url) && (
                                <a href={(window as any).SiteConfiguration.discord_url} target="_blank" rel="noreferrer" className={'w-full flex no-underline'} onClick={closeMobileMenu}>
                                    <NavItem>
                                        <NavIcon>
                                            <i className={'fa-brands fa-discord'}></i>
                                        </NavIcon>
                                        <NavText>Discord</NavText>
                                    </NavItem>
                                </a>
                            )}

                            {/* Additional custom buttons created in the Theme Customiser */}
                            {customButtonsInSideNav && renderCustomButtons()}
                        </>
                    )}
                </MiddleSection>
                
                <SectionShared>
                    {rootAdmin && (
                        <a href={'/admin'} rel={'noreferrer'} className={'w-full flex no-underline'} onClick={closeMobileMenu}>
                            <NavItem>
                                <NavIcon>
                                    <FontAwesomeIcon icon={faCogs} />
                                </NavIcon>
                                <NavText>Admin</NavText>
                            </NavItem>
                        </a>
                    )}
                    
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

                    <ButtonStyled onClick={() => { onTriggerLogout(); closeMobileMenu(); }}>
                        <NavItem>
                            <NavIcon>
                                <FontAwesomeIcon icon={faSignOutAlt} />
                            </NavIcon>
                            <NavText>Sign Out</NavText>
                        </NavItem>
                    </ButtonStyled>
                </SectionShared>
            </SideNavigation>
        </div>
    );
};
