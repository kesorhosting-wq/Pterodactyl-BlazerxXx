import React, { useState, useEffect } from 'react';
import { ServerContext } from '@/state/server';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { httpErrorToHuman } from '@/api/http';
import useFlash from '@/plugins/useFlash';
import stripAnsi from 'strip-ansi';
import { SocketEvent } from '@/components/server/events';

// Define the power state type based on Pterodactyl Panel
type ServerPowerState = 'offline' | 'starting' | 'running' | 'stopping' | 'installing';

// Toast notification component
interface ToastProps {
    message: string;
    isVisible: boolean;
    onHide: () => void;
}

// Confirmation modal component
interface ConfirmationModalProps {
    isOpen: boolean;
    title: string;
    message: string;
    confirmText: string;
    cancelText: string;
    onConfirm: () => void;
    onCancel: () => void;
    isDangerous?: boolean;
}

const ConfirmationModal: React.FC<ConfirmationModalProps> = ({
    isOpen,
    title,
    message,
    confirmText,
    cancelText,
    onConfirm,
    onCancel,
    isDangerous = false
}) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* Backdrop */}
            <div 
                className="absolute inset-0 backdrop-blur-sm"
                style={{ backgroundColor: 'hsla(0, 0%, 0%, 0.6)' }}
                onClick={onCancel}
            ></div>
            
            {/* Modal */}
            <div 
                className="relative rounded-lg p-6 mx-4 w-full max-w-md shadow-xl border border-gray-600"
                style={{ backgroundColor: 'hsla(0, 0%, 0%, 0.6)' }}
            >
                <div className="flex items-center space-x-3 mb-4">
                    <i className={`fa-solid ${isDangerous ? 'fa-triangle-exclamation text-red-400' : 'fa-question-circle text-blue-400'} text-xl`}></i>
                    <h3 className="text-lg font-semibold text-white">{title}</h3>
                </div>
                
                <p className="text-gray-300 mb-6">{message}</p>
                
                <div className="flex space-x-3 justify-end">
                    <button
                        className="px-4 py-2 rounded font-medium text-sm bg-gray-600/30 hover:bg-gray-600/50 text-gray-300 hover:text-white border border-gray-600/40 hover:border-gray-500/50 transition-all duration-200"
                        onClick={onCancel}
                    >
                        {cancelText}
                    </button>
                    <button
                        className={`px-4 py-2 rounded font-medium text-sm transition-all duration-200 border ${
                            isDangerous 
                                ? 'bg-red-600/20 hover:bg-red-600/40 text-white border-red-500/30 hover:border-red-400/50' 
                                : 'bg-blue-600/20 hover:bg-blue-600/40 text-white border-blue-500/30 hover:border-blue-400/50'
                        }`}
                        onClick={onConfirm}
                    >
                        {confirmText}
                    </button>
                </div>
            </div>
        </div>
    );
};

const Toast: React.FC<ToastProps> = ({ message, isVisible, onHide }) => {
    useEffect(() => {
        if (isVisible) {
            const timer = setTimeout(() => {
                onHide();
            }, 3000); // Hide after 3 seconds
            
            return () => clearTimeout(timer);
        }
        
        // Return undefined when isVisible is false
        return undefined;
    }, [isVisible, onHide]);

    if (!isVisible) return null;

    return (
        <div className="fixed bottom-4 right-4 z-50 transform transition-all duration-300 ease-in-out">
            <div className="bg-green-600/90 backdrop-blur-sm text-white px-4 py-3 rounded-lg shadow-lg border border-green-500/30 flex items-center space-x-2">
                <i className="fa-solid fa-check-circle text-green-300"></i>
                <span className="font-medium">{message}</span>
            </div>
        </div>
    );
};

const serverControl: React.FC = () => {
    const { uuid, name, description, status, internalId } = ServerContext.useStoreState((state) => state.server.data!);
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data?.rootAdmin ?? false);
    const [isLoading, setIsLoading] = useState(false);
    const [logs, setLogs] = useState<string[]>([]);
    const [isTooltipVisible, setTooltipVisible] = useState(false);
    const [toastMessage, setToastMessage] = useState('');
    const [showToast, setShowToast] = useState(false);
    const [showKillConfirmation, setShowKillConfirmation] = useState(false);
    const { addError, clearFlashes } = useFlash();
    const setServerStatus = ServerContext.useStoreActions((actions) => actions.status.setServerStatus);
    
    // Use the actual WebSocket status for power state (like the official PowerButtons component)
    const powerState = ServerContext.useStoreState((state) => state.status.value) as ServerPowerState;
    
    const serverId = uuid;

    // Copy to clipboard function with toast notification
    const copyToClipboard = async (text: string, successMessage: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setToastMessage(successMessage);
            setShowToast(true);
        } catch (err) {
            console.error('Failed to copy: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            setToastMessage(successMessage);
            setShowToast(true);
        }
    };

    const hideToast = () => {
        setShowToast(false);
    };

    // Open server in admin area
    const openAdminArea = () => {
        if (internalId) {
            window.open(`/admin/servers/view/${internalId}`, '_blank');
        }
    };

    // Console log functionality
    const addLog = (data: string) => {
        setLogs((prevLogs) => [...prevLogs, data.startsWith('>') ? data.substring(1) : data]);
    };

    // UseEffect to manage log listening
    useEffect(() => {
        if (!connected || !instance) return;

        // Listen for console output logs
        instance.addListener(SocketEvent.CONSOLE_OUTPUT, addLog);

        return () => {
            // Cleanup listener on unmount
            instance.removeListener(SocketEvent.CONSOLE_OUTPUT, addLog);
        };
    }, [connected, instance]);

    // Prepare the logs for copying
    const logData = stripAnsi(logs.join('\n'));

    const sendPowerCommand = async (command: string) => {
        setIsLoading(true);
        clearFlashes('server:power');
        
        try {
            await fetch(`/api/client/servers/${uuid}/power`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ signal: command }),
            });
            
            // Show success toast based on command
            let toastMessage = '';
            if (command === 'start') {
                setServerStatus('starting' as any);
                toastMessage = 'Server Starting!';
            } else if (command === 'stop') {
                setServerStatus('stopping' as any);
                toastMessage = 'Server Stopping!';
            } else if (command === 'restart') {
                setServerStatus('stopping' as any);
                toastMessage = 'Server Restarting!';
            } else if (command === 'kill') {
                setServerStatus('stopping' as any);
                toastMessage = 'Server Killed!';
            }
            
            // Show the toast notification
            setToastMessage(toastMessage);
            setShowToast(true);
            
        } catch (error) {
            addError({ key: 'server:power', message: httpErrorToHuman(error) });
        } finally {
            setIsLoading(false);
        }
    };

    const handleStart = () => sendPowerCommand('start');
    const handleRestart = () => sendPowerCommand('restart');
    const handleStop = () => sendPowerCommand('stop');
    const handleKill = () => setShowKillConfirmation(true);
    
    const confirmKill = () => {
        setShowKillConfirmation(false);
        sendPowerCommand('kill');
    };
    
    const cancelKill = () => {
        setShowKillConfirmation(false);
    };

    const isStartDisabled = isLoading || powerState !== 'offline';
    const isRestartDisabled = isLoading || !powerState || powerState === 'offline' || powerState === 'installing';
    const isStopDisabled = isLoading || powerState === 'offline' || powerState === 'installing';
    const isKillDisabled = isLoading; // Kill should always be available except when already loading

    const isServerStarting = powerState === 'starting';
    const isServerStopping = powerState === 'stopping';

    return (
        <>
            <Toast 
                message={toastMessage} 
                isVisible={showToast} 
                onHide={hideToast}
            />
            <ConfirmationModal
                isOpen={showKillConfirmation}
                title="Force Kill Server"
                message="Are you sure you want to forcefully kill the server? This will immediately terminate all server processes and may cause data loss or corruption."
                confirmText="Kill Server"
                cancelText="Cancel"
                onConfirm={confirmKill}
                onCancel={cancelKill}
                isDangerous={true}
            />
            <div
                className="style-module_2Vp6MaXq bg-gray-600 relative p-4 mb-4 rounded serverid"
                id="server-id-container"
            >
            <div className="flex flex-col lg:flex-row lg:justify-between lg:items-center w-full">
                {/* Server Name and ID Section - START OF CONTAINER */}
                <div className="flex-shrink-0 mb-4 lg:mb-0">
                    <h1 className="font-header text-2xl text-gray-50 leading-relaxed line-clamp-1">
                        {name || 'Euphoria Craft'}
                    </h1>
                    <div className="flex items-center space-x-2 mt-1">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 512 512"
                            className="Icon___StyledSvg-sc-omsq29-0 ejRaBu text-gray-400 w-3 h-3"
                        >
                            <path d="M64 32C28.7 32 0 60.7 0 96l0 64c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-64c0-35.3-28.7-64-64-64L64 32zm280 72a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm48 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM64 288c-35.3 0-64 28.7-64 64l0 64c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-64c0-35.3-28.7-64-64-64L64 288zm280 72a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm56 24a24 24 0 1 1 48 0 24 24 0 1 1 -48 0z"/>
                        </svg>
                        <span 
                            className="text-xs text-gray-400 cursor-pointer transition-colors font-mono"
                            style={{
                                color: '#9CA3AF'
                            }}
                            onMouseEnter={(e) => {
                                e.currentTarget.style.color = 'var(--primary-color)';
                            }}
                            onMouseLeave={(e) => {
                                e.currentTarget.style.color = '#9CA3AF';
                            }}
                            onClick={() => copyToClipboard(serverId, 'Server ID copied!')}
                        >
                            {serverId ? serverId : 'Not available.'}
                        </span>
                    </div>
                </div>

                {/* Spacer to push buttons to the end */}
                <div className="flex-grow lg:block hidden"></div>

                {/* Power Buttons Section - END OF CONTAINER */}
                <div className="flex flex-row space-x-2 items-center justify-center lg:justify-end flex-shrink-0">
                    <button 
                        className={`w-auto px-3 py-2 rounded font-medium transition-all duration-200 text-sm backdrop-blur-sm border flex items-center justify-center h-9 ${
                            isStartDisabled 
                                ? 'bg-gray-600/30 text-gray-400 cursor-not-allowed border-gray-600/40' 
                                : 'bg-green-600/20 hover:bg-green-600/40 text-white border-green-500/30 hover:border-green-400/50 shadow-lg'
                        }`}
                        disabled={isStartDisabled}
                        onClick={handleStart}
                    >
                        {isServerStarting ? (
                            <i className="fa-solid fa-spinner fa-spin" style={{ fontSize: '14px' }}></i>
                        ) : (
                            <>
                                <i className="fa-solid fa-play" style={{ fontSize: '14px' }}></i>
                                <span className="ml-2 hidden lg:inline">Start</span>
                            </>
                        )}
                    </button>
                    <button 
                        className={`w-auto px-3 py-2 rounded font-medium transition-all duration-200 text-sm backdrop-blur-sm border flex items-center justify-center h-9 ${
                            isRestartDisabled 
                                ? 'bg-gray-600/30 text-gray-400 cursor-not-allowed border-gray-600/40' 
                                : 'bg-orange-600/20 hover:bg-orange-600/40 text-white border-orange-500/30 hover:border-orange-400/50 shadow-lg'
                        }`}
                        disabled={isRestartDisabled}
                        onClick={handleRestart}
                    >
                        {isLoading && isServerStopping ? (
                            <i className="fa-solid fa-spinner fa-spin" style={{ fontSize: '14px' }}></i>
                        ) : (
                            <>
                                <i className="fa-solid fa-rotate-right" style={{ fontSize: '14px' }}></i>
                                <span className="ml-2 hidden lg:inline">Restart</span>
                            </>
                        )}
                    </button>
                    <button 
                        className={`w-auto px-3 py-2 rounded font-medium transition-all duration-200 text-sm backdrop-blur-sm border flex items-center justify-center h-9 ${
                            isStopDisabled 
                                ? 'bg-gray-600/30 text-gray-400 cursor-not-allowed border-gray-600/40' 
                                : 'bg-red-600/20 hover:bg-red-600/40 text-white border-red-500/30 hover:border-red-400/50 shadow-lg'
                        }`}
                        disabled={isStopDisabled}
                        onClick={handleStop}
                    >
                        {isServerStopping ? (
                            <i className="fa-solid fa-spinner fa-spin" style={{ fontSize: '14px' }}></i>
                        ) : (
                            <>
                                <i className="fa-solid fa-stop" style={{ fontSize: '14px' }}></i>
                                <span className="ml-2 hidden lg:inline">Stop</span>
                            </>
                        )}
                    </button>
                    <button 
                        className={`w-auto px-3 py-2 rounded font-medium transition-all duration-200 text-sm backdrop-blur-sm border flex items-center justify-center h-9 ${
                            isKillDisabled 
                                ? 'bg-gray-600/30 text-gray-400 cursor-not-allowed border-gray-600/40' 
                                : 'bg-red-800/20 hover:bg-red-800/40 text-white border-red-700/30 hover:border-red-600/50 shadow-lg'
                        }`}
                        disabled={isKillDisabled}
                        onClick={handleKill}
                    >
                        {isServerStopping ? (
                            <i className="fa-solid fa-spinner fa-spin" style={{ fontSize: '14px' }}></i>
                        ) : (
                            <>
                                <i className="fa-solid fa-skull" style={{ fontSize: '14px' }}></i>
                                <span className="ml-2 hidden lg:inline">Kill</span>
                            </>
                        )}
                    </button>
                    
                    {/* Copy Console Button - Icon only on mobile */}
                    <div 
                        className="relative"
                        onMouseEnter={() => setTooltipVisible(true)}
                        onMouseLeave={() => setTooltipVisible(false)}
                    >
                        <button
                            className="w-auto px-3 py-2 rounded font-medium text-sm backdrop-blur-sm border bg-blue-600/20 hover:bg-blue-600/40 text-white border-blue-500/30 hover:border-blue-400/50 shadow-lg flex items-center justify-center h-9"
                            onClick={() => copyToClipboard(logData, 'Console logs copied!')}
                        >
                            <i className="fa-solid fa-copy" style={{ fontSize: '14px' }}></i>
                            <span className="ml-2 hidden lg:inline">Console</span>
                        </button>
                    </div>

                    {/* Admin Button - Only show for root admins */}
                    {rootAdmin && internalId && (
                        <button
                            className="w-auto px-3 py-2 rounded font-medium text-sm backdrop-blur-sm border bg-purple-600/20 hover:bg-purple-600/40 text-white border-purple-500/30 hover:border-purple-400/50 shadow-lg flex items-center justify-center h-9"
                            onClick={openAdminArea}
                        >
                            <i className="fa-solid fa-arrow-up-right-from-square" style={{ fontSize: '14px' }}></i>
                        </button>
                    )}
                </div>
            </div>
            </div>
        </>
    );
};

export default serverControl;
