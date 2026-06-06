import React from 'react';

/* blueprint/import *//* VersionchangerImportStart */import VersionchangerQihezbnewb from '@blueprint/extensions/versionchanger/VersionChangerContainer';/* VersionchangerImportEnd *//* ServersplitterImportStart */import ServersplitterWmqddqbfhq from '@blueprint/extensions/serversplitter/ServerSplitterContainer';/* ServersplitterImportEnd *//* MinecraftplayermanagerImportStart */import MinecraftplayermanagerZqdathhbmm from '@blueprint/extensions/minecraftplayermanager/PlayerManagerContainer';/* MinecraftplayermanagerImportEnd *//* SubdomainsImportStart */import SubdomainsYprpseujoe from '@blueprint/extensions/subdomains/SubdomainContainer';/* SubdomainsImportEnd *//* McmodsImportStart */import McmodsTpyogafpuh from '@blueprint/extensions/mcmods/ModsManagerContainer';/* McmodsImportEnd */

interface RouteDefinition {
  path: string;
  name: string | undefined;
  component: React.ComponentType;
  exact?: boolean;
  adminOnly: boolean | false;
  identifier: string;
}
interface ServerRouteDefinition extends RouteDefinition {
  permission: string | string[] | null;
}
interface Routes {
  account: RouteDefinition[];
  server: ServerRouteDefinition[];
}

export default {
  account: [
    /* routes/account *//* VersionchangerAccountRouteStart *//* VersionchangerAccountRouteEnd *//* ServersplitterAccountRouteStart *//* ServersplitterAccountRouteEnd *//* MinecraftplayermanagerAccountRouteStart *//* MinecraftplayermanagerAccountRouteEnd *//* SubdomainsAccountRouteStart *//* SubdomainsAccountRouteEnd *//* McmodsAccountRouteStart *//* McmodsAccountRouteEnd */
  ],
  server: [
    /* routes/server *//* VersionchangerServerRouteStart */{ path: '/minecraft/versions', permission: 'file.update', name: 'Versions', component: VersionchangerQihezbnewb, adminOnly: false, identifier: 'versionchanger' },/* VersionchangerServerRouteEnd *//* ServersplitterServerRouteStart */{ path: '/splitter', permission: 'splitter.read', name: 'Splitter', component: ServersplitterWmqddqbfhq, adminOnly: false, identifier: 'serversplitter' },/* ServersplitterServerRouteEnd *//* MinecraftplayermanagerServerRouteStart */{ path: '/minecraft/players', permission: 'control.console', name: 'Players', component: MinecraftplayermanagerZqdathhbmm, adminOnly: false, identifier: 'minecraftplayermanager' },/* MinecraftplayermanagerServerRouteEnd *//* SubdomainsServerRouteStart */{ path: '/subdomains', permission: null, name: 'Subdomain', component: SubdomainsYprpseujoe, adminOnly: false, identifier: 'subdomains' },/* SubdomainsServerRouteEnd *//* McmodsServerRouteStart */{ path: '/mcmods', permission: null, name: 'Mods Installer', component: McmodsTpyogafpuh, adminOnly: false, identifier: 'mcmods' },/* McmodsServerRouteEnd */
  ],
} as Routes;
