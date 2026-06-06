import tw from 'twin.macro';
import { ApplicationStore } from '@/state';
import { VersionSearchRow } from './SearchRow';
import { ServerContext } from '@/state/server';
import React, { useState, useMemo } from 'react';
import Spinner from '@/components/elements/Spinner';
import { Dialog } from '@/components/elements/dialog';
import { useStoreActions, Actions } from 'easy-peasy';
import GreyRowBox from '@/components/elements/GreyRowBox';
import { faDownload } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { fetchModVersions, installModVersion } from '@/blueprint/extensions/mcmods/api/getVersions';

interface ModsVersionContainerProps {
  provider: string;
  modId: string | number;
  modName: string;
  modUrl: string;
  textSearch: string;
  textSearchBox: string;
  textVersion: string;
  textLoader: string;
  textVersionList: string;
  textVersionsNotFound: string;
  textVersionDownloads: string;
  textInstallSuccess: string;
  textInstallFailed: string;
  textInstallButton: string;
  textRedirectUrl: string;
}

const ModsVersionContainer: React.FC<ModsVersionContainerProps> = ({
  provider,
  modId,
  modName,
  modUrl,
  textSearch,
  textSearchBox,
  textVersion,
  textLoader,
  textVersionList,
  textVersionsNotFound,
  textVersionDownloads,
  textInstallSuccess,
  textInstallFailed,
  textInstallButton,
  textRedirectUrl,
}) => {
  const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
  const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
  const [externalUrl, setExternalUrl] = useState<{ open: boolean; version?: any }>({ open: false });
  const [search, setSearch] = useState('');
  const [minecraftVersion, setMinecraftVersion] = useState('');
  const [loader, setLoader] = useState('');

  const { data: versions, error, isValidating } = fetchModVersions(uuid, provider, modId);

  const isStableVersion = (version: string) => {
    return (
      version.startsWith('1.') &&
      !version.includes('w') &&
      // !version.includes('pre') &&
      // !version.includes('rc') &&
      !version.includes('CB') &&
      !version.includes('Snapshot')
    );
  };

  const filteredVersions = versions?.filter((version) => {
    const matchesSearch = version.versionName.toLowerCase().includes(search.toLowerCase());
    const matchesMinecraftVersion = minecraftVersion ? version.game_versions?.includes(minecraftVersion) : true;
    let matchesLoader = true;

    if (loader) {
      if (provider === 'modrinth' && version.loaders) {
        matchesLoader = version.loaders.includes(loader);
      } else if (provider === 'curseforge') {
        matchesLoader = version.game_versions?.includes(loader);
      }
    }

    return matchesSearch && matchesMinecraftVersion && matchesLoader;
  });

  const handleInstall = async (versionId: string | number, modName: string) => {
    try {
      clearFlashes('mcmods:install');
      await installModVersion(uuid, provider, modId, versionId);
      addFlash({
        type: 'success',
        key: 'mcmods:install',
        message: textInstallSuccess.replace('%_MOD_NAME_%', modName),
      });
    } catch (error) {
      addFlash({
        type: 'error',
        key: 'mcmods:install',
        title: 'Error',
        message: textInstallFailed.replace('%_MOD_NAME_%', modName),
      });
    }
  };

  const buttonStyle = useMemo(() => {
    const color = getComputedStyle(document.documentElement).getPropertyValue('--color-blue-700')
      ? `rgb(var(--color-blue-700) / var(--tw-bg-opacity, 1))`
      : getComputedStyle(document.documentElement).getPropertyValue('--pageButtonDefault')
      ? 'var(--pageButtonDefault)'
      : 'rgba(37, 99, 235)';
    const hoverColor = getComputedStyle(document.documentElement).getPropertyValue('--color-blue-600')
      ? `rgb(var(--color-blue-600) / var(--tw-bg-opacity, 1))`
      : getComputedStyle(document.documentElement).getPropertyValue('--pageButtonHover')
      ? 'var(--pageButtonHover)'
      : 'rgba(59, 130, 246)';
    return {
      backgroundColor: color,
      ':hover': { backgroundColor: hoverColor },
      borderRadius: 'var(--borderRadius, 0.25rem)',
    };
  }, []);

  const secondarybuttonStyle = useMemo(() => {
    const color = getComputedStyle(document.documentElement).getPropertyValue('--color-gray-600')
      ? `rgb(var(--color-gray-600) / var(--tw-bg-opacity, 1))`
      : getComputedStyle(document.documentElement).getPropertyValue('--pageSecondaryActive')
      ? 'var(--pageSecondaryActive)'
      : 'hsla(209, 14%, 37%)';
    const hoverColor = getComputedStyle(document.documentElement).getPropertyValue('--color-gray-500')
      ? `rgb(var(--color-gray-500) / var(--tw-bg-opacity, 1))`
      : getComputedStyle(document.documentElement).getPropertyValue('--pageSecondaryHover')
      ? 'var(--pageSecondaryHover)'
      : 'hsla(211, 12%, 43%)';
    return {
      borderRadius: 'var(--borderRadius, 0.25rem)',
      backgroundColor: color,
      ':hover': { backgroundColor: hoverColor },
    };
  }, []);

  const backgroundStyle = useMemo(() => {
    const color = getComputedStyle(document.documentElement).getPropertyValue('--color-gray-600')
      ? `rgb(var(--color-gray-600) / var(--tw-bg-opacity, 1))`
      : getComputedStyle(document.documentElement).getPropertyValue('--pageSecondaryActive')
      ? 'var(--pageSecondaryActive)'
      : 'hsla(209, 14%, 37%)';
    return { backgroundColor: color };
  }, []);

  return (
    <div css={tw`p-2`}>
      <h1 css={tw`w-full text-center text-2xl pb-4`}>
        {textVersionList.replace('%_MOD_NAME_%', modName).concat(versions ? ` (${versions.length})` : '')}{' '}
        {isValidating && <Spinner size={'small'} css={tw`inline-block`}></Spinner>}
      </h1>
      <div css={tw`grid gap-4`}>
        <VersionSearchRow
          onSearch={setSearch}
          minecraftVersion={minecraftVersion}
          setMinecraftVersion={setMinecraftVersion}
          provider={provider}
          loader={loader}
          setLoader={setLoader}
          textSearch={textSearch}
          textSearchBox={textSearchBox}
          textVersion={textVersion}
          textLoader={textLoader}
        />
      </div>

      {isValidating ? (
        <div css={tw`flex items-center justify-center h-full mt-4 mb-4`}>
          <Spinner size='large' />
        </div>
      ) : filteredVersions && filteredVersions.length > 0 ? (
        <div css={tw`space-y-4 mt-4`}>
          {filteredVersions.map((version) => (
            <GreyRowBox key={version.versionId} css={tw`flex flex-col md:flex-row md:justify-between`}>
              <div css={tw`flex-1`}>
                <span css={tw`font-semibold text-left`}>{version.versionName}</span>
                {version.downloads && version.downloads > 0 && (
                  <p css={tw`text-sm text-neutral-400 flex items-center`}>
                    <FontAwesomeIcon icon={faDownload} css={tw`w-3 h-3 mr-1`} />
                    <span>
                      {textVersionDownloads.replace('%_VERSION_DOWNLOADS_%', version.downloads.toLocaleString())}
                    </span>
                  </p>
                )}
                {version.loaders && version.loaders.length > 0 && (
                  <div css={tw`flex items-center gap-2 mt-1`}>
                    <span css={tw`text-sm text-neutral-300`}>{textLoader}:</span>
                    <div css={tw`flex gap-1`}>
                      {version.loaders.map((loader) => (
                        <span
                          key={loader}
                          css={[
                            tw`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-800 text-gray-300`,
                            backgroundStyle,
                          ]}
                        >
                          {loader.charAt(0).toUpperCase() + loader.slice(1)}
                        </span>
                      ))}
                    </div>
                  </div>
                )}
                {version.game_versions && version.game_versions.length > 0 && (
                  <div css={tw`flex items-center gap-2 mt-1`}>
                    <span css={tw`text-sm text-neutral-300`}>{textVersion}:</span>
                    <span css={tw`text-sm text-neutral-400`}>
                      {version.game_versions.filter(isStableVersion).join(', ')}
                    </span>
                  </div>
                )}
              </div>
              <div css={tw`flex justify-center md:justify-end items-center mt-4 md:mt-0`}>
                <div css={tw`flex space-x-2`}>
                  <button
                    onClick={() => setExternalUrl({ open: true, version: version })}
                    css={[tw`bg-neutral-600 hover:bg-neutral-500 text-white p-2.5 rounded`, secondarybuttonStyle]}
                  >
                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512' css={tw`w-4 h-4 fill-current`}>
                      <path d='M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l82.7 0L201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3l0 82.7c0 17.7 14.3 32 32 32s32-14.3 32-32l0-160c0-17.7-14.3-32-32-32L320 0zM80 32C35.8 32 0 67.8 0 112L0 432c0 44.2 35.8 80 80 80l320 0c44.2 0 80-35.8 80-80l0-112c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 112c0 8.8-7.2 16-16 16L80 448c-8.8 0-16-7.2-16-16l0-320c0-8.8 7.2-16 16-16l112 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L80 32z' />
                    </svg>
                  </button>
                  <button
                    css={[
                      tw`bg-primary-600 hover:bg-primary-500 text-white px-4 py-2 rounded text-sm flex items-center`,
                      buttonStyle,
                    ]}
                    onClick={() => handleInstall(version.versionId, modName)}
                  >
                    <FontAwesomeIcon icon={faDownload} css={tw`mr-2`} />
                    {textInstallButton}
                  </button>
                </div>
              </div>
            </GreyRowBox>
          ))}
        </div>
      ) : (
        <div css={tw`text-gray-300 mt-4`}>{textVersionsNotFound}</div>
      )}
      <Dialog.Confirm
        open={externalUrl.open}
        onClose={() => setExternalUrl({ open: false })}
        title={`Redirect To Mod's Website`}
        confirm={'Open'}
        onConfirmed={() => {
          if (provider === 'modrinth' && externalUrl.version) {
            const versionUrl = `https://modrinth.com/mod/${modId}/version/${externalUrl.version.versionId}`;
            window.open(versionUrl, '_blank');
          } else if (provider === 'curseforge') {
            const versionUrl = `${modUrl}/files/${externalUrl.version.versionId}`;
            window.open(versionUrl, '_blank');
          } else {
            window.open(modUrl!, '_blank');
          }
          setExternalUrl({ open: false });
        }}
      >
        {textRedirectUrl.replace('&apos;', "'")}
      </Dialog.Confirm>
    </div>
  );
};
export default ModsVersionContainer;
