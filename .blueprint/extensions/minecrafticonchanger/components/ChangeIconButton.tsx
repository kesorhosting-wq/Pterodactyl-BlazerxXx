import React, { useRef, useState } from 'react';
import { ServerContext } from '@/state/server';
import Can from '@/components/elements/Can';
import updateIcon from './api/updateIcon';
import getData from './api/getData';
import { Button } from '@/components/elements/button/index';
import { useFlashKey } from '@/plugins/useFlash';
import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useSWR from 'swr';

export default function ChangeIconButton() {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [isLoading, setLoading] = useState(false);
    const { mutate } = useFileManagerSwr();
    const inputRef = useRef<HTMLInputElement>(null);
    const { clearAndAddHttpError } = useFlashKey('files');

    const { data } = useSWR(['minecraft', 'icon', uuid], () => getData(uuid), {
        revalidateOnFocus: false,
        refreshInterval: 0,
    });

    if (!data) {
        return null;
    }

    return (
        <>
            <input
                type={'file'}
                onChange={(e) => {
                    setLoading(true);
                    updateIcon(uuid, e.target.files![0])
                        .catch((e) => clearAndAddHttpError(e))
                        .then(() => mutate())
                        .finally(() => setLoading(false));
                }}
                accept={'image/png, image/jpeg, image/jpg'}
                ref={inputRef}
                style={{ display: 'none' }}
            />

            <Can action={'file.create'}>
                <Button.Text onClick={() => inputRef.current?.click()} disabled={isLoading}>
                    Change Icon
                </Button.Text>
            </Can>
        </>
    );
}
