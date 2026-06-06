import useSWR from 'swr';
import http from '@/api/http';

export interface Version {
  versionId: string | number;
  versionName: string;
  downloads?: number;
  game_versions: string[];
  loaders: string[];
}

export const rawDataToVersion = (data: any): Version => {
  return {
    versionId: data.versionId,
    versionName: data.versionName,
    downloads: data.downloads,
    game_versions: data.game_versions,
    loaders: data.loaders,
  };
};

export const fetchModVersions = (uuid: string, provider: string, modId: string | number) => {
  return useSWR<Version[]>(
    ['mcmods_versions', uuid, provider, modId],
    async () => {
      const response = await http.get(`/api/client/extensions/mcmods/servers/${uuid}/version`, {
        params: {
          provider,
          modId,
        },
      });
      return response.data.data.map((item: any) => rawDataToVersion(item));
    },
    { revalidateOnFocus: false }
  );
};

export const installModVersion = (
  uuid: string,
  provider: string,
  modId: string | number,
  versionId: string | number
): Promise<void> => {
  return new Promise((resolve, reject) => {
    http
      .post(`/api/client/extensions/mcmods/servers/${uuid}/install`, {
        provider,
        modId,
        versionId,
      })
      .then(() => resolve())
      .catch(reject);
  });
};
