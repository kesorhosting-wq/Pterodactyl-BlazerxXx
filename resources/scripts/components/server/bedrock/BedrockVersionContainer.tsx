import React, { useMemo, useState } from 'react';
import classNames from 'classnames';
import { Actions, useStoreActions } from 'easy-peasy';
import { motion } from 'framer-motion';
import {
    faCheckCircle,
    faCodeBranch,
    faDownload,
    faExclamationTriangle,
    faRocket,
    faSearch,
    faServer,
    faSyncAlt,
    faTrashAlt,
} from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import FlashMessageRender from '@/components/FlashMessageRender';
import { httpErrorToHuman } from '@/api/http';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import Input from '@/components/elements/Input';
import { Dialog } from '@/components/elements/dialog';
import { Button } from '@/components/elements/button';
import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';
import { BedrockVersion, installBedrockVersion, useBedrockVersions } from '@/api/server/bedrock/getBedrockVersions';

type Channel = 'release' | 'preview' | 'all';

const fadeUp = {
    initial: { opacity: 0, y: 14 },
    animate: { opacity: 1, y: 0 },
};

const stagger = {
    animate: {
        transition: {
            staggerChildren: 0.035,
        },
    },
};

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const { data, error, isValidating, mutate } = useBedrockVersions(uuid);
    const [channel, setChannel] = useState<Channel>('release');
    const [query, setQuery] = useState('');
    const [wipeFiles, setWipeFiles] = useState(false);
    const [installing, setInstalling] = useState(false);
    const [selected, setSelected] = useState<BedrockVersion | null>(null);

    const versions = useMemo(() => {
        const value = query.trim().toLowerCase();

        return (data?.data || []).filter((item) => {
            const matchesChannel = channel === 'all' || item.channel === channel;
            const matchesQuery = !value || item.version.toLowerCase().includes(value);

            return matchesChannel && matchesQuery;
        });
    }, [data, channel, query]);

    const latestRelease = data?.data.find((item) => item.channel === 'release' && item.latest);
    const latestPreview = data?.data.find((item) => item.channel === 'preview' && item.latest);
    const current = data?.meta.current;
    const releaseCount = data?.data.filter((item) => item.channel === 'release').length || 0;
    const previewCount = data?.data.filter((item) => item.channel === 'preview').length || 0;
    const installedAt = current?.installed_at ? new Date(current.installed_at) : null;
    const installedAtText =
        installedAt && !Number.isNaN(installedAt.getTime())
            ? installedAt.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' })
            : null;

    const onInstall = async () => {
        if (!selected || installing) return;

        setInstalling(true);
        clearFlashes('server:bedrock-version');

        try {
            await installBedrockVersion(uuid, selected.channel, selected.version, wipeFiles);
            addFlash({
                type: 'success',
                key: 'server:bedrock-version',
                message: `Bedrock ${selected.version} has been installed and the startup command was changed to ./bedrock_server.`,
            });
            setSelected(null);
            setWipeFiles(false);
            mutate();
        } catch (error) {
            addFlash({
                type: 'error',
                key: 'server:bedrock-version',
                title: 'Install failed',
                message: httpErrorToHuman(error),
            });
        }

        setInstalling(false);
    };

    return (
        <ServerContentBlock title={'Bedrock Version'}>
            <FlashMessageRender byKey={'server:bedrock-version'} />
            {error && (
                <div className={'mt-4 rounded border border-red-800 bg-red-900 bg-opacity-25 px-4 py-3 text-sm text-red-200'}>
                    <FontAwesomeIcon icon={faExclamationTriangle} className={'mr-2'} />
                    Bedrock version data could not be loaded from EndstoneMC.
                </div>
            )}

            <motion.div
                initial={'initial'}
                animate={'animate'}
                variants={fadeUp}
                className={
                    'mt-4 overflow-hidden rounded border border-gray-700 bg-gray-800 shadow-xl shadow-black/20'
                }
            >
                <div className={'border-b border-gray-700 bg-gray-900 px-5 py-5'}>
                    <div className={'flex flex-col lg:flex-row lg:items-center lg:justify-between'}>
                        <div className={'flex items-start'}>
                            <motion.div
                                whileHover={{ scale: 1.06, rotate: -2 }}
                                className={'mr-4 flex h-12 w-12 items-center justify-center rounded bg-primary-900 text-primary-200 ring-1 ring-primary-700'}
                            >
                                <FontAwesomeIcon icon={faServer} />
                            </motion.div>
                            <div>
                                <p className={'text-xl font-semibold text-gray-50'}>Bedrock Version Manager</p>
                                <p className={'mt-1 max-w-2xl text-sm text-gray-400'}>
                                    Pick an official Minecraft Bedrock Linux server build, upload it through Wings, and
                                    set startup to ./bedrock_server.
                                </p>
                            </div>
                        </div>
                        <Button
                            size={Button.Sizes.Small}
                            className={'mt-4 lg:mt-0'}
                            disabled={isValidating}
                            onClick={() => mutate()}
                        >
                            <FontAwesomeIcon icon={faSyncAlt} className={classNames('mr-2', { 'fa-spin': isValidating })} />
                            Refresh
                        </Button>
                    </div>
                </div>

                <div className={'grid grid-cols-1 divide-y divide-gray-700 md:grid-cols-3 md:divide-x md:divide-y-0'}>
                    <motion.div whileHover={{ y: -2 }} className={'px-5 py-4'}>
                        <div className={'flex items-center justify-between'}>
                            <p className={'text-xs font-semibold uppercase text-gray-400'}>Installed</p>
                            {current && <FontAwesomeIcon icon={faCheckCircle} className={'text-green-400'} />}
                        </div>
                        <p className={'mt-2 text-2xl font-semibold text-gray-50'}>{current ? current.version : 'Unknown'}</p>
                        <p className={'mt-1 text-xs capitalize text-gray-400'}>
                            {current ? `${current.channel}${installedAtText ? ` - ${installedAtText}` : ''}` : 'No marker file found'}
                        </p>
                    </motion.div>
                    <motion.div whileHover={{ y: -2 }} className={'px-5 py-4'}>
                        <div className={'flex items-center justify-between'}>
                            <p className={'text-xs font-semibold uppercase text-gray-400'}>Latest Release</p>
                            <FontAwesomeIcon icon={faRocket} className={'text-green-400'} />
                        </div>
                        <p className={'mt-2 text-2xl font-semibold text-gray-50'}>{latestRelease?.version || '-'}</p>
                        <p className={'mt-1 text-xs text-gray-400'}>{releaseCount} stable builds available</p>
                    </motion.div>
                    <motion.div whileHover={{ y: -2 }} className={'px-5 py-4'}>
                        <div className={'flex items-center justify-between'}>
                            <p className={'text-xs font-semibold uppercase text-gray-400'}>Latest Preview</p>
                            <FontAwesomeIcon icon={faCodeBranch} className={'text-yellow-300'} />
                        </div>
                        <p className={'mt-2 text-2xl font-semibold text-gray-50'}>{latestPreview?.version || '-'}</p>
                        <p className={'mt-1 text-xs text-gray-400'}>{previewCount} preview builds available</p>
                    </motion.div>
                </div>
            </motion.div>

            <motion.div
                initial={'initial'}
                animate={'animate'}
                variants={fadeUp}
                transition={{ delay: 0.08 }}
                className={'mt-6 rounded border border-gray-700 bg-gray-800 shadow-xl shadow-black/10'}
            >
                <div className={'flex flex-col gap-4 border-b border-gray-700 px-5 py-4 xl:flex-row xl:items-center xl:justify-between'}>
                    <div>
                        <p className={'text-base font-semibold text-gray-100'}>Available Versions</p>
                        <p className={'mt-1 text-sm text-gray-400'}>{versions.length} versions match the current filters.</p>
                    </div>
                    <div className={'flex flex-col gap-3 sm:flex-row sm:items-center'}>
                        <div className={'relative sm:w-72'}>
                            <FontAwesomeIcon icon={faSearch} className={'absolute left-3 top-3 text-gray-500'} />
                            <Input
                                value={query}
                                onChange={(event) => setQuery(event.currentTarget.value)}
                                placeholder={'Search versions'}
                                className={'pl-10'}
                            />
                        </div>
                        <div className={'flex rounded bg-gray-900 p-1'}>
                            {(['release', 'preview', 'all'] as Channel[]).map((value) => (
                                <button
                                    key={value}
                                    type={'button'}
                                    onClick={() => setChannel(value)}
                                    className={classNames(
                                        'rounded px-3 py-2 text-xs font-semibold uppercase transition-colors duration-100',
                                        channel === value
                                            ? 'bg-primary-600 text-white shadow'
                                            : 'text-gray-400 hover:bg-gray-800 hover:text-gray-100'
                                    )}
                                >
                                    {value}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {!data && isValidating ? (
                    <div className={'py-12'}>
                        <Spinner centered size={'large'} />
                    </div>
                ) : versions.length < 1 ? (
                    <p className={'px-5 py-10 text-center text-sm text-gray-400'}>No Bedrock versions found.</p>
                ) : (
                    <motion.div variants={stagger} initial={'initial'} animate={'animate'} className={'divide-y divide-gray-700'}>
                        {versions.map((item) => {
                            const isInstalled = current?.version === item.version && current.channel === item.channel;

                            return (
                                <motion.div
                                    variants={fadeUp}
                                    whileHover={{ x: 4 }}
                                    key={`${item.channel}:${item.version}`}
                                    className={classNames(
                                        'flex flex-col px-5 py-4 transition-colors duration-100 sm:flex-row sm:items-center sm:justify-between',
                                        isInstalled ? 'bg-primary-900 bg-opacity-20' : 'hover:bg-gray-700 hover:bg-opacity-40'
                                    )}
                                >
                                    <div className={'min-w-0'}>
                                        <div className={'flex flex-wrap items-center gap-2'}>
                                            <p className={'text-lg font-semibold text-gray-50'}>{item.version}</p>
                                            <span
                                                className={classNames(
                                                    'rounded px-2 py-1 text-xs font-semibold uppercase',
                                                    item.channel === 'release'
                                                        ? 'bg-green-900 text-green-200'
                                                        : 'bg-yellow-900 text-yellow-200'
                                                )}
                                            >
                                                {item.channel}
                                            </span>
                                            {item.latest && (
                                                <span className={'rounded bg-primary-900 px-2 py-1 text-xs font-semibold uppercase text-primary-200'}>
                                                    Latest
                                                </span>
                                            )}
                                            {isInstalled && (
                                                <span className={'rounded bg-gray-900 px-2 py-1 text-xs font-semibold uppercase text-gray-200'}>
                                                    Installed
                                                </span>
                                            )}
                                        </div>
                                        <p className={'mt-1 text-sm text-gray-400'}>
                                            Official Linux server archive from EndstoneMC metadata.
                                        </p>
                                    </div>
                                    <Button
                                        size={Button.Sizes.Small}
                                        className={'mt-4 sm:mt-0 sm:ml-4'}
                                        disabled={installing || isInstalled}
                                        onClick={() => setSelected(item)}
                                    >
                                        <FontAwesomeIcon icon={installing ? faSyncAlt : faDownload} className={'mr-2'} />
                                        {isInstalled ? 'Installed' : 'Install'}
                                    </Button>
                                </motion.div>
                            );
                        })}
                    </motion.div>
                )}
            </motion.div>

            <Dialog.Confirm
                open={!!selected}
                title={'Install Bedrock Version'}
                confirm={installing ? 'Installing' : 'Install'}
                onClose={() => {
                    if (installing) return;

                    setSelected(null);
                    setWipeFiles(false);
                }}
                onConfirmed={onInstall}
            >
                {selected
                    ? (
                          <div>
                              <div className={'rounded border border-gray-700 bg-gray-800 px-4 py-3'}>
                                  <p className={'font-semibold text-gray-100'}>Minecraft Bedrock {selected.version}</p>
                                  <p className={'mt-1 text-sm capitalize text-gray-400'}>{selected.channel} channel</p>
                              </div>
                              <p className={'mt-4 text-sm text-gray-300'}>
                                  The panel will download the official Linux server archive, upload it to Wings, extract
                                  it, and set startup to ./bedrock_server.
                              </p>
                              <label className={'mt-4 flex items-start rounded border border-gray-700 bg-gray-800 px-4 py-3 text-sm text-gray-300'}>
                                  <Input
                                      type={'checkbox'}
                                      checked={wipeFiles}
                                      onChange={(event) => setWipeFiles(event.currentTarget.checked)}
                                      className={'mt-1 mr-3'}
                                  />
                                  <span>
                                      <span className={'block font-semibold text-gray-100'}>
                                          <FontAwesomeIcon icon={faTrashAlt} className={'mr-2 text-red-300'} />
                                          Delete current server files
                                      </span>
                                      <span className={'mt-1 block text-gray-400'}>
                                          Use this when changing between clean Bedrock versions. Leave it unchecked to
                                          keep existing worlds and files.
                                      </span>
                                  </span>
                              </label>
                          </div>
                      )
                    : ''}
            </Dialog.Confirm>
        </ServerContentBlock>
    );
};
