import * as React from 'react';
import { useState, useEffect } from 'react';

interface AuthenticationAwarenessProps {
    className?: string;
}

declare global {
    interface Window {
        SiteConfiguration?: {
            advert_enabled?: string | number | boolean;
            logo_url?: string;
            signup_enabled?: boolean;
            primary_color?: string;
            show_logo_login_enabled?: boolean;
            [key: string]: any;
        };
    }
}

const AuthenticationAwareness: React.FC<AuthenticationAwarenessProps> = ({ className = '' }) => {
    const [isEnabled, setIsEnabled] = useState<boolean>(false);

    useEffect(() => {
        // Get advert enabled setting from SiteConfiguration
        const siteConfig = window.SiteConfiguration;
        if (siteConfig && siteConfig.advert_enabled !== undefined) {
            // Convert database value (0/1 or "0"/"1" or boolean) to boolean
            const advertEnabled = siteConfig.advert_enabled;
            setIsEnabled(
                advertEnabled === true ||
                advertEnabled === 1 ||
                advertEnabled === "1" ||
                advertEnabled === "true"
            );
        }
    }, []);

    // Don't render if not enabled
    if (!isEnabled) {
        return null;
    }

    const handleClick = (e: React.MouseEvent<HTMLDivElement>) => {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            const newWindow = window.open('https://euphoriatheme.uk', '_blank');
            if (!newWindow) {
                // Fallback if popup is blocked
                window.location.href = 'https://euphoriatheme.uk';
            }
        } catch (error) {
            // Fallback for any errors
            window.location.href = 'https://euphoriatheme.uk';
        }
    };

    return (
        <>
            <style>
                {`
                    #app {
                        margin-left: 0% !important;
                    }
                `}
            </style>
            <div 
                id="authentication-awareness" 
                className={`authentication-awareness ${className}`}
                onClick={handleClick}
                style={{
                    position: 'fixed',
                    bottom: '20px',
                    left: '20px',
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    color: 'white',
                    padding: '15px',
                    borderRadius: '0.25rem',
                    zIndex: 1000,
                    textAlign: 'center',
                    display: 'flex',
                    alignItems: 'center',
                    cursor: 'pointer',
                    transition: 'background-color 0.2s ease'
                }}
                onMouseEnter={(e) => {
                    e.currentTarget.style.backgroundColor = 'rgba(0, 0, 0, 0.9)';
                }}
                onMouseLeave={(e) => {
                    e.currentTarget.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
                }}
            >
            <img 
                src="/extensions/euphoriatheme/images/logo.png" 
                alt="Logo" 
                className="awareness-logo"
                style={{
                    height: '20px',
                    marginRight: '10px'
                }}
            /> 
            Powered by Euphoria Theme!
        </div>
        </>
    );
};

export default AuthenticationAwareness;
