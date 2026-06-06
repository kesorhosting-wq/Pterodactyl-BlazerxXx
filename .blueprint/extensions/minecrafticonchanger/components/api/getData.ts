import http from '@/api/http';

export default async (uuid: string): Promise<boolean> => {
    const { data } = await http.get(`/api/client/extensions/minecrafticonchanger/servers/${uuid}`);

    return data.minecraft;
};
