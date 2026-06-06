import useSWR from 'swr';
import http, { getPaginationSet, PaginatedResult } from '@/api/http';

export interface Mod {
  provider: string;
  id: number | string;
  name: string;
  description: string;
  icon: string;
  downloads: number;
  url: string;
}

export const rawDataToMod = (data: any): Mod => {
  return {
    provider: data.provider,
    id: data.id,
    name: data.name,
    description: data.description,
    icon: data.icon,
    downloads: data.downloads,
    url: data.url,
  };
};

export type ModsResponse = PaginatedResult<Mod>;

export const fetchMods = (
  uuid: string,
  provider: string,
  page: number,
  pageSize: number,
  searchQuery: string,
  loader: string,
  sortBy: string,
  minecraftVersion: string
) => {
  const { data, error } = useSWR<ModsResponse>(
    ['mcmods', uuid, provider, page, pageSize, searchQuery, loader, sortBy, minecraftVersion],
    async () => {
      const response = await http.get(`/api/client/extensions/mcmods/servers/${uuid}`, {
        params: {
          provider: provider,
          page: page,
          page_size: pageSize,
          search_query: searchQuery,
          loader: loader,
          sort_by: sortBy,
          minecraft_version: minecraftVersion,
        },
      });

      const mods = response.data.data.map((item: any) => rawDataToMod(item));
      const pagination = getPaginationSet(response.data.pagination);

      return {
        items: mods,
        pagination: pagination,
      };
    }
  );

  return {
    mods: data?.items,
    pagination: data?.pagination,
    loading: !error && !data,
    error: error,
  };
};

export const installMod = (uuid: string, provider: string, modId: string | number): Promise<void> => {
  return new Promise((resolve, reject) => {
    http
      .post(`/api/client/extensions/mcmods/servers/${uuid}/install`, {
        provider,
        modId,
      })
      .then(() => resolve())
      .catch(reject);
  });
};
