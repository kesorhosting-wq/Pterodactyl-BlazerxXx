import React, { useEffect, useState } from 'react';
import classNames from 'classnames';
import { faBoxOpen, faSearch, faTrashAlt } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { AnimatePresence, motion } from 'framer-motion';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import FlashMessageRender from '@/components/FlashMessageRender';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
import Spinner from '@/components/elements/Spinner';
import { Button } from '@/components/elements/button';
import { Dialog } from '@/components/elements/dialog';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import {
    getModpackVersions,
    installModpack,
    ModpackLogEvent,
    ModpackProject,
    ModpackProvider,
    ModpackVersion,
    searchModpacks,
} from '@/api/server/modpacks/getModpacks';

const fadeUp = {
    initial: { opacity: 0, y: 14 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -8 },
};

const stagger = {
    animate: {
        transition: {
            staggerChildren: 0.04,
        },
    },
};

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addFlash, clearFlashes } = useFlash();
    const [provider, setProvider] = useState<ModpackProvider>('modrinth');
    const [query, setQuery] = useState('');
    const [page, setPage] = useState(1);
    const [projects, setProjects] = useState<ModpackProject[]>([]);
    const [versions, setVersions] = useState<ModpackVersion[]>([]);
    const [selectedProject, setSelectedProject] = useState<ModpackProject | null>(null);
    const [selectedVersion, setSelectedVersion] = useState<ModpackVersion | null>(null);
    const [loading, setLoading] = useState(false);
    const [installing, setInstalling] = useState(false);
    const [wipeFiles, setWipeFiles] = useState(false);
    const [logs, setLogs] = useState<ModpackLogEvent[]>([]);

    const loadModpacks = (nextPage = page) => {
        setLoading(true);
        clearFlashes('server:modpacks');
        setSelectedProject(null);
        setSelectedVersion(null);
        setVersions([]);

        searchModpacks(uuid, provider, query, nextPage)
            .then(setProjects)
            .catch((error) => addFlash({ key: 'server:modpacks', type: 'error', title: 'Search failed', message: error.message }))
            .then(() => setLoading(false));
    };

    useEffect(() => {
        setPage(1);
        loadModpacks(1);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [provider]);

    const onSearch = () => {
        setPage(1);
        loadModpacks(1);
    };

    const onPage = (nextPage: number) => {
        const normalized = Math.max(1, nextPage);
        setPage(normalized);
        loadModpacks(normalized);
    };

    const onSelectProject = (project: ModpackProject) => {
        setSelectedProject(project);
        setSelectedVersion(null);
        setVersions([]);
        setLoading(true);

        getModpackVersions(uuid, provider, project.id)
            .then(setVersions)
            .catch((error) => addFlash({ key: 'server:modpacks', type: 'error', title: 'Versions failed', message: error.message }))
            .then(() => setLoading(false));
    };

    const onInstall = () => {
        if (installing) return;
        if (!selectedProject || !selectedVersion) return;

        setInstalling(true);
        setLogs([{ type: 'log', message: 'Preparing installer...', time: new Date().toISOString() }]);
        clearFlashes('server:modpacks');

        installModpack(uuid, provider, selectedProject.id, selectedVersion.id, wipeFiles, (event) =>
            setLogs((current) => [...current, event])
        )
            .then(() => {
                addFlash({
                    key: 'server:modpacks',
                    type: 'success',
                    message: `${selectedProject.name} was installed and startup was changed to ./start.sh.`,
                });
            })
            .catch((error) => addFlash({ key: 'server:modpacks', type: 'error', title: 'Install failed', message: error.message }))
            .then(() => setInstalling(false));
    };

    return (
        <ServerContentBlock title={'Modpack'}>
            <FlashMessageRender byKey={'server:modpacks'} />
            <motion.div
                variants={fadeUp}
                initial={'initial'}
                animate={'animate'}
                className={'mt-4 overflow-hidden rounded border border-gray-700 bg-gray-800 shadow-xl shadow-black/20'}
            >
                <div className={'border-b border-gray-700 px-5 py-5'}>
                    <div className={'flex items-start'}>
                        <motion.div
                            whileHover={{ scale: 1.06, rotate: 2 }}
                            className={'mr-4 flex h-12 w-12 items-center justify-center rounded bg-primary-900 text-primary-200 ring-1 ring-primary-700'}
                        >
                            <FontAwesomeIcon icon={faBoxOpen} />
                        </motion.div>
                        <div>
                            <p className={'text-xl font-semibold text-gray-50'}>Modpack Installer</p>
                            <p className={'mt-1 text-sm text-gray-400'}>
                                Browse Modrinth or CurseForge modpacks by page, install server files, optionally wipe first, and set startup to ./start.sh.
                            </p>
                        </div>
                    </div>
                    <div className={'mt-5 grid grid-cols-1 gap-3 md:grid-cols-5'}>
                        <Select value={provider} onChange={(event) => setProvider(event.currentTarget.value as ModpackProvider)}>
                            <option value={'modrinth'}>Modrinth</option>
                            <option value={'curseforge'}>CurseForge</option>
                        </Select>
                        <div className={'relative md:col-span-3'}>
                            <FontAwesomeIcon icon={faSearch} className={'absolute left-3 top-3 text-gray-500'} />
                            <Input value={query} onChange={(event) => setQuery(event.currentTarget.value)} className={'pl-10'} placeholder={'Filter modpacks, or leave blank for all'} />
                        </div>
                        <Button onClick={onSearch} disabled={loading}>
                            Load
                        </Button>
                    </div>
                    <div className={'mt-4 flex items-center justify-between rounded bg-gray-900 px-4 py-3'}>
                        <Button.Text disabled={loading || page <= 1} onClick={() => onPage(page - 1)}>
                            Previous
                        </Button.Text>
                        <p className={'text-sm font-semibold text-gray-300'}>Page {page}</p>
                        <Button.Text disabled={loading || projects.length < 20} onClick={() => onPage(page + 1)}>
                            Next
                        </Button.Text>
                    </div>
                </div>

                <AnimatePresence>
                    {loading && (
                        <motion.div
                            variants={fadeUp}
                            initial={'initial'}
                            animate={'animate'}
                            exit={'exit'}
                            className={'py-8'}
                        >
                            <Spinner centered />
                        </motion.div>
                    )}
                </AnimatePresence>

                <div className={'grid grid-cols-1 divide-y divide-gray-700 lg:grid-cols-2 lg:divide-x lg:divide-y-0'}>
                    <motion.div variants={stagger} initial={'initial'} animate={'animate'} className={'min-h-[240px]'}>
                        {projects.map((project) => (
                            <motion.button
                                variants={fadeUp}
                                whileHover={{ x: 4 }}
                                key={project.id}
                                type={'button'}
                                onClick={() => onSelectProject(project)}
                                className={classNames(
                                    'flex w-full items-center border-b border-gray-700 px-5 py-4 text-left hover:bg-gray-700 hover:bg-opacity-40',
                                    selectedProject?.id === project.id && 'bg-primary-900 bg-opacity-20'
                                )}
                            >
                                {project.icon_url && <img src={project.icon_url} className={'mr-4 h-10 w-10 rounded'} />}
                                <span className={'min-w-0'}>
                                    <span className={'block font-semibold text-gray-100'}>{project.name}</span>
                                    <span className={'block truncate text-sm text-gray-400'}>{project.summary}</span>
                                </span>
                            </motion.button>
                        ))}
                    </motion.div>
                    <motion.div variants={stagger} initial={'initial'} animate={'animate'} className={'min-h-[240px]'}>
                        {versions.map((version) => (
                            <motion.button
                                variants={fadeUp}
                                whileHover={{ x: 4 }}
                                key={version.id}
                                type={'button'}
                                onClick={() => setSelectedVersion(version)}
                                className={classNames(
                                    'flex w-full items-center justify-between border-b border-gray-700 px-5 py-4 text-left hover:bg-gray-700 hover:bg-opacity-40',
                                    selectedVersion?.id === version.id && 'bg-primary-900 bg-opacity-20'
                                )}
                            >
                                <span>
                                    <span className={'block font-semibold text-gray-100'}>{version.name}</span>
                                    <span className={'block text-sm text-gray-400'}>{version.game_versions.slice(0, 4).join(', ')}</span>
                                </span>
                                <Button size={Button.Sizes.Small} onClick={() => setSelectedVersion(version)}>
                                    Select
                                </Button>
                            </motion.button>
                        ))}
                    </motion.div>
                </div>
            </motion.div>

            <Dialog.Confirm
                open={!!selectedProject && !!selectedVersion}
                title={'Install Modpack'}
                confirm={installing ? 'Installing' : 'Install'}
                onClose={() => {
                    if (installing) return;
                    setSelectedVersion(null);
                    setWipeFiles(false);
                    setLogs([]);
                }}
                onConfirmed={onInstall}
            >
                <p className={'text-sm text-gray-300'}>
                    Install {selectedProject?.name} version {selectedVersion?.name}. Startup will be changed to ./start.sh using this server&apos;s RAM limit.
                </p>
                <label className={'mt-4 flex items-start rounded border border-gray-700 bg-gray-800 px-4 py-3 text-sm text-gray-300'}>
                    <Input type={'checkbox'} checked={wipeFiles} onChange={(event) => setWipeFiles(event.currentTarget.checked)} className={'mt-1 mr-3'} />
                    <span>
                        <span className={'block font-semibold text-gray-100'}>
                            <FontAwesomeIcon icon={faTrashAlt} className={'mr-2 text-red-300'} />
                            Wipe delete all current server files before installing
                        </span>
                        <span className={'mt-1 block text-gray-400'}>Leave unchecked to merge the modpack into existing files.</span>
                    </span>
                </label>
                {logs.length > 0 && (
                    <motion.div
                        variants={fadeUp}
                        initial={'initial'}
                        animate={'animate'}
                        className={'mt-4 max-h-64 overflow-y-auto rounded bg-gray-900 p-3 font-mono text-xs'}
                    >
                        <AnimatePresence initial={false}>
                            {logs.map((entry, index) => (
                            <motion.p
                                variants={fadeUp}
                                initial={'initial'}
                                animate={'animate'}
                                exit={'exit'}
                                key={`${entry.time || 'log'}:${index}`}
                                className={entry.type === 'error' ? 'text-red-300' : entry.type === 'success' || entry.type === 'complete' ? 'text-green-300' : 'text-gray-300'}
                            >
                                {entry.message}
                            </motion.p>
                            ))}
                        </AnimatePresence>
                    </motion.div>
                )}
            </Dialog.Confirm>
        </ServerContentBlock>
    );
};
