import React, { useState } from 'react';
import { faCheckCircle, faExchangeAlt, faKey, faPlug, faServer, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { AnimatePresence, motion } from 'framer-motion';
import { Button } from '@/components/elements/button/index';
import { Dialog } from '@/components/elements/dialog';
import Input from '@/components/elements/Input';
import Label from '@/components/elements/Label';
import Select from '@/components/elements/Select';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerContext } from '@/state/server';
import { WithClassname } from '@/components/types';
import useFlash, { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import {
    importServerFiles,
    ServerImportLogEvent,
    ServerImportValues,
    ServerImportVerification,
    verifyServerImport,
} from '@/api/server/files/serverImport';

const defaultValues: ServerImportValues = {
    protocol: 'sftp',
    host: '',
    port: 2022,
    username: '',
    password: '',
    path: '/',
};

type ParsedEndpoint = Partial<ServerImportValues>;

const fadeUp = {
    initial: { opacity: 0, y: 12 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -8 },
};

export default ({ className }: WithClassname) => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [open, setOpen] = useState(false);
    const [values, setValues] = useState<ServerImportValues>(defaultValues);
    const [verification, setVerification] = useState<ServerImportVerification | null>(null);
    const [wipeFiles, setWipeFiles] = useState(false);
    const [loading, setLoading] = useState(false);
    const [importComplete, setImportComplete] = useState(false);
    const [logs, setLogs] = useState<ServerImportLogEvent[]>([]);
    const { mutate } = useFileManagerSwr();
    const { addFlash, clearFlashes } = useFlash();
    const { clearAndAddHttpError } = useFlashKey('files:server-import');

    const defaultPort = (protocol: ServerImportValues['protocol']) => (protocol === 'ftp' ? 21 : 2022);

    const parseEndpoint = (value: string, protocol: ServerImportValues['protocol']): ParsedEndpoint => {
        const trimmed = value.trim();
        const hasScheme = /^[a-z][a-z0-9+\-.]*:\/\//i.test(trimmed);
        const hasPort = /^[^/]+:\d+$/.test(trimmed);

        if (!hasScheme && !hasPort) {
            return { host: value };
        }

        try {
            const parsed = new URL(hasScheme ? trimmed : `${protocol}://${trimmed}`);
            const parsedProtocol = parsed.protocol.replace(':', '').toLowerCase();

            if (parsedProtocol !== 'sftp' && parsedProtocol !== 'ftp') {
                return { host: value };
            }

            const next: ParsedEndpoint = {
                protocol: parsedProtocol,
                host: parsed.hostname,
                port: parsed.port ? Number(parsed.port) : defaultPort(parsedProtocol),
            };

            if (parsed.pathname && parsed.pathname !== '/') {
                next.path = parsed.pathname;
            }

            return next;
        } catch {
            return { host: value };
        }
    };

    const updateValue = (key: keyof ServerImportValues, value: string) => {
        setVerification(null);
        setImportComplete(false);
        setLogs([]);

        if (key === 'host') {
            setValues((current) => ({ ...current, ...parseEndpoint(value, current.protocol) }));

            return;
        }

        if (key === 'protocol') {
            const protocol = value as ServerImportValues['protocol'];
            setValues((current) => ({
                ...current,
                protocol,
                port: current.port === defaultPort(current.protocol) ? defaultPort(protocol) : current.port,
            }));

            return;
        }

        setValues((current) => ({
            ...current,
            [key]: key === 'port' ? Number(value) : value,
        }));
    };

    const close = () => {
        if (loading) return;

        setOpen(false);
        setVerification(null);
        setWipeFiles(false);
        setImportComplete(false);
        setLogs([]);
        clearFlashes('files:server-import');
    };

    const onVerify = () => {
        setLoading(true);
        setImportComplete(false);
        setLogs([]);
        clearFlashes('files:server-import');

        verifyServerImport(uuid, values)
            .then((data) => setVerification(data))
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setLoading(false));
    };

    const onImport = () => {
        setLoading(true);
        setImportComplete(false);
        setLogs([
            {
                type: 'log',
                message: 'Preparing import...',
                time: new Date().toISOString(),
            },
        ]);
        clearFlashes('files:server-import');

        importServerFiles(uuid, values, wipeFiles, (event) => setLogs((current) => [...current, event]))
            .then((data) => {
                addFlash({
                    key: 'files',
                    type: 'success',
                    message: `Imported ${data.files} files and ${data.directories} directories from the source server.`,
                });
                mutate();
                setLoading(false);
                setImportComplete(true);
            })
            .catch((error) => {
                clearAndAddHttpError(error);
                setLoading(false);
            });
    };

    const canContinue = values.host.trim() && values.username.trim() && values.password && values.port > 0;

    return (
        <>
            <Dialog open={open} title={'Server Import'} onClose={close} preventExternalClose={loading}>
                <FlashMessageRender byKey={'files:server-import'} />
                {!verification ? (
                    <motion.div variants={fadeUp} initial={'initial'} animate={'animate'} className={'mt-4'}>
                        <div className={'rounded border border-gray-700 bg-gray-800 px-4 py-3 shadow-lg shadow-black/10'}>
                            <div className={'flex items-center text-gray-100'}>
                                <motion.span whileHover={{ scale: 1.08 }} className={'mr-3 text-primary-300'}>
                                    <FontAwesomeIcon icon={faPlug} />
                                </motion.span>
                                <p className={'font-semibold'}>Connect to a source server</p>
                            </div>
                            <p className={'mt-1 text-sm text-gray-400'}>
                                Continue will verify the connection before any files are changed. Full URLs such as
                                sftp://host:2022 are parsed automatically.
                            </p>
                        </div>

                        <div className={'mt-5 grid grid-cols-1 gap-4 sm:grid-cols-4'}>
                            <div>
                                <Label>Protocol</Label>
                                <Select
                                    value={values.protocol}
                                    onChange={(event) => updateValue('protocol', event.currentTarget.value)}
                                >
                                    <option value={'sftp'}>SFTP</option>
                                    <option value={'ftp'}>FTP</option>
                                </Select>
                            </div>
                            <div className={'sm:col-span-2'}>
                                <Label>Host</Label>
                                <Input
                                    value={values.host}
                                    onChange={(event) => updateValue('host', event.currentTarget.value)}
                                    placeholder={'sftp://node.example.com:2022'}
                                    autoFocus
                                />
                            </div>
                            <div>
                                <Label>Port</Label>
                                <Input
                                    type={'number'}
                                    min={1}
                                    max={65535}
                                    value={values.port}
                                    onChange={(event) => updateValue('port', event.currentTarget.value)}
                                />
                            </div>
                        </div>

                        <div className={'mt-4'}>
                            <Label>Username</Label>
                            <Input
                                value={values.username}
                                onChange={(event) => updateValue('username', event.currentTarget.value)}
                                placeholder={values.protocol === 'ftp' ? 'username' : 'username.server'}
                            />
                        </div>

                        <div className={'mt-4'}>
                            <Label>Password</Label>
                            <Input
                                type={'password'}
                                value={values.password}
                                onChange={(event) => updateValue('password', event.currentTarget.value)}
                            />
                        </div>

                        <div className={'mt-4'}>
                            <Label>Source Path</Label>
                            <Input
                                value={values.path}
                                onChange={(event) => updateValue('path', event.currentTarget.value)}
                                placeholder={'/'}
                            />
                        </div>
                    </motion.div>
                ) : (
                    <motion.div variants={fadeUp} initial={'initial'} animate={'animate'} className={'mt-4'}>
                        <div className={'rounded border border-green-800 bg-green-900 bg-opacity-25 px-4 py-3 shadow-lg shadow-black/10'}>
                            <div className={'flex items-center text-green-100'}>
                                <motion.span initial={{ scale: 0.8 }} animate={{ scale: 1 }} className={'mr-3'}>
                                    <FontAwesomeIcon icon={faCheckCircle} />
                                </motion.span>
                                <p className={'font-semibold'}>Connection verified</p>
                            </div>
                            <p className={'mt-1 text-sm text-green-200'}>
                                Found {verification.entries} items in {verification.path}.
                            </p>
                        </div>

                        <label className={'mt-5 flex items-start rounded border border-gray-700 bg-gray-800 px-4 py-3 text-sm text-gray-300'}>
                            <Input
                                type={'checkbox'}
                                checked={wipeFiles}
                                onChange={(event) => setWipeFiles(event.currentTarget.checked)}
                                className={'mt-1 mr-3'}
                            />
                            <span>
                                <span className={'block font-semibold text-gray-100'}>
                                    <FontAwesomeIcon icon={faTrashAlt} className={'mr-2 text-red-300'} />
                                    Wipe all current server files before importing
                                </span>
                                <span className={'mt-1 block text-gray-400'}>
                                    Leave this unchecked to merge the imported files into the current server.
                                </span>
                            </span>
                        </label>

                        {logs.length > 0 && (
                            <motion.div
                                variants={fadeUp}
                                initial={'initial'}
                                animate={'animate'}
                                className={'mt-5 overflow-hidden rounded border border-gray-700 bg-gray-900 shadow-xl shadow-black/20'}
                            >
                                <div className={'flex items-center justify-between border-b border-gray-700 px-4 py-3'}>
                                    <p className={'text-sm font-semibold text-gray-100'}>Transfer Console</p>
                                    <p className={'text-xs text-gray-400'}>
                                        {loading ? 'Running' : importComplete ? 'Complete' : 'Ready'}
                                    </p>
                                </div>
                                <div className={'max-h-64 overflow-y-auto px-4 py-3 font-mono text-xs leading-relaxed'}>
                                    <AnimatePresence initial={false}>
                                        {logs.map((entry, index) => (
                                            <motion.p
                                                variants={fadeUp}
                                                initial={'initial'}
                                                animate={'animate'}
                                                exit={'exit'}
                                                key={`${entry.time || 'log'}:${index}`}
                                                className={
                                                    entry.type === 'error'
                                                        ? 'text-red-300'
                                                        : entry.type === 'success' || entry.type === 'complete'
                                                            ? 'text-green-300'
                                                            : 'text-gray-300'
                                                }
                                            >
                                                <span className={'text-gray-500'}>
                                                    {entry.time ? new Date(entry.time).toLocaleTimeString() : '--:--:--'}
                                                </span>
                                                <span className={'mx-2 text-gray-600'}>|</span>
                                                {entry.message}
                                            </motion.p>
                                        ))}
                                    </AnimatePresence>
                                </div>
                            </motion.div>
                        )}
                    </motion.div>
                )}

                <Dialog.Footer>
                    <Button.Text className={'w-full sm:w-auto'} onClick={close} disabled={loading}>
                        {importComplete ? 'Close' : 'Cancel'}
                    </Button.Text>
                    {verification && (
                        <Button.Text
                            className={'w-full sm:w-auto'}
                            onClick={() => {
                                setVerification(null);
                                setImportComplete(false);
                                setLogs([]);
                            }}
                            disabled={loading}
                        >
                            Back
                        </Button.Text>
                    )}
                    {!importComplete && (
                        <Button
                            className={'w-full sm:w-auto'}
                            disabled={!canContinue || loading}
                            onClick={verification ? onImport : onVerify}
                        >
                            <FontAwesomeIcon icon={verification ? faExchangeAlt : faKey} className={'mr-2'} />
                            {loading ? 'Transferring' : verification ? 'Transfer' : 'Continue'}
                        </Button>
                    )}
                </Dialog.Footer>
            </Dialog>
            <motion.span whileHover={{ y: -1 }} whileTap={{ scale: 0.98 }} className={'inline-flex'}>
                <Button className={className} onClick={() => setOpen(true)}>
                <FontAwesomeIcon icon={faServer} className={'mr-2'} />
                Server Import
                </Button>
            </motion.span>
        </>
    );
};
