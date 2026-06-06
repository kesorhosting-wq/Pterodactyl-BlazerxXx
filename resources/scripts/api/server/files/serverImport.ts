import http from '@/api/http';

export interface ServerImportValues {
    protocol: 'sftp' | 'ftp';
    host: string;
    port: number;
    username: string;
    password: string;
    path: string;
}

export interface ServerImportVerification {
    path: string;
    entries: number;
}

export interface ServerImportLogEvent {
    type: 'log' | 'success' | 'error' | 'complete';
    message: string;
    data?: {
        files?: number;
        directories?: number;
    };
    time?: string;
}

export const verifyServerImport = (uuid: string, values: ServerImportValues): Promise<ServerImportVerification> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/servers/${uuid}/files/import/verify`, values)
            .then(({ data }) => resolve(data.data))
            .catch(reject);
    });
};

export const importServerFiles = async (
    uuid: string,
    values: ServerImportValues,
    wipeFiles: boolean,
    onLog: (event: ServerImportLogEvent) => void
): Promise<{ files: number; directories: number }> => {
    const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    const response = await fetch(`/api/client/servers/${uuid}/files/import`, {
        method: 'POST',
        credentials: 'include',
        headers: {
            Accept: 'application/x-ndjson',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
        },
        body: JSON.stringify({ ...values, wipe_files: wipeFiles }),
    });

    if (!response.ok) {
        const data = await response.json().catch(() => null);
        const detail = data?.errors?.[0]?.detail || response.statusText || 'The server import could not be started.';

        throw new Error(detail);
    }

    if (!response.body) {
        throw new Error('The server did not return a transfer stream.');
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let buffer = '';
    let summary = { files: 0, directories: 0 };

    const readLine = (line: string) => {
        if (!line.trim()) return;

        const event = JSON.parse(line) as ServerImportLogEvent;
        onLog(event);

        if (event.type === 'complete') {
            summary = {
                files: event.data?.files || 0,
                directories: event.data?.directories || 0,
            };
        }

        if (event.type === 'error') {
            throw new Error(event.message);
        }
    };

    let streamDone = false;

    while (!streamDone) {
        const result = await reader.read();
        streamDone = result.done;
        buffer += decoder.decode(result.value || new Uint8Array(), { stream: !streamDone });

        const lines = buffer.split('\n');
        buffer = lines.pop() || '';

        for (const line of lines) {
            readLine(line);
        }

        if (streamDone) {
            if (buffer) {
                readLine(buffer);
            }

            break;
        }
    }

    return summary;
};
