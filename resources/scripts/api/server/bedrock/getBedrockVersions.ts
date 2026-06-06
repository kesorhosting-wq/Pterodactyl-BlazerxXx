import useSWR from 'swr';
import http from '@/api/http';

export interface BedrockVersion {
    channel: 'release' | 'preview';
    version: string;
    latest: boolean;
}

export interface InstalledBedrockVersion {
    channel: 'release' | 'preview';
    version: string;
    installed_at: string;
    source: string;
}

interface BedrockVersionsResponse {
    data: BedrockVersion[];
    meta: {
        current: InstalledBedrockVersion | null;
        source: string;
    };
}

export const useBedrockVersions = (uuid: string) => {
    return useSWR<BedrockVersionsResponse>(
        ['bedrock-versions', uuid],
        async () => {
            const { data } = await http.get(`/api/client/servers/${uuid}/bedrock-versions`);

            return data;
        },
        { revalidateOnFocus: false }
    );
};

export const installBedrockVersion = (
    uuid: string,
    channel: string,
    version: string,
    wipeFiles: boolean
): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(
            `/api/client/servers/${uuid}/bedrock-versions/install`,
            { channel, version, wipe_files: wipeFiles },
            {
                timeout: 600000,
                timeoutErrorMessage: 'The Bedrock install is still running or took too long to complete.',
            }
        )
            .then(() => resolve())
            .catch(reject);
    });
};
