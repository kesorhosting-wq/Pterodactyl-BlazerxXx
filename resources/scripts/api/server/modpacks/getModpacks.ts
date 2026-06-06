import http from '@/api/http';

export type ModpackProvider = 'modrinth' | 'curseforge';

export interface ModpackProject {
    id: string;
    slug: string;
    name: string;
    summary: string;
    icon_url: string | null;
    downloads: number;
    versions: string[];
}

export interface ModpackVersion {
    id: string;
    name: string;
    version_number: string;
    game_versions: string[];
    loaders: string[];
    date_published: string | null;
}

export interface ModpackLogEvent {
    type: 'log' | 'success' | 'error' | 'complete';
    message: string;
    data?: Record<string, string | number | null>;
    time?: string;
}

export const searchModpacks = async (
    uuid: string,
    provider: ModpackProvider,
    query: string,
    page: number
): Promise<ModpackProject[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/modpacks/search`, {
        params: { provider, query, page },
    });

    return data.data;
};

export const getModpackVersions = async (
    uuid: string,
    provider: ModpackProvider,
    projectId: string
): Promise<ModpackVersion[]> => {
    const { data } = await http.get(`/api/client/servers/${uuid}/modpacks/versions`, {
        params: { provider, project_id: projectId },
    });

    return data.data;
};

export const installModpack = async (
    uuid: string,
    provider: ModpackProvider,
    projectId: string,
    versionId: string,
    wipeFiles: boolean,
    onLog: (event: ModpackLogEvent) => void
): Promise<void> => {
    const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    const response = await fetch(`/api/client/servers/${uuid}/modpacks/install`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            Accept: 'application/x-ndjson',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        },
        body: JSON.stringify({ provider, project_id: projectId, version_id: versionId, wipe_files: wipeFiles }),
    });

    if (!response.ok) {
        const data = await response.json().catch(() => null);
        throw new Error(data?.errors?.[0]?.detail || response.statusText || 'The modpack install could not be started.');
    }

    if (!response.body) {
        throw new Error('The server did not return an install stream.');
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    let done = false;

    const readLine = (line: string) => {
        if (!line.trim()) return;

        const event = JSON.parse(line) as ModpackLogEvent;
        onLog(event);

        if (event.type === 'error') {
            throw new Error(event.message);
        }
    };

    while (!done) {
        const result = await reader.read();
        done = result.done;
        buffer += decoder.decode(result.value || new Uint8Array(), { stream: !done });

        const lines = buffer.split('\n');
        buffer = lines.pop() || '';

        for (const line of lines) {
            readLine(line);
        }

        if (done && buffer) {
            readLine(buffer);
        }
    }
};
