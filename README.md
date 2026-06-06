<!-- Header -->
<br/><p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://github.com/BlueprintFramework/framework/assets/103201875/c0072c61-0135-4931-b5fa-ce4ee7d79f4a">
    <source media="(prefers-color-scheme: light)" srcset="https://github.com/BlueprintFramework/framework/assets/103201875/a652a6e7-b53f-4dcd-ae4e-2051f5c9c7b9">
    <img alt="Blueprint" src="https://github.com/BlueprintFramework/framework/assets/103201875/c0072c61-0135-4931-b5fa-ce4ee7d79f4a" height="30">
  </picture>
  <br/>
  Open-source modding framework for the Pterodactyl panel.
  <br/><br/>
  <a href="https://blueprint.zip">Website</a> <b>·</b>
  <a href="https://discord.com/servers/blueprint-1063548024825057451">Community</a> <b>·</b>
  <a href="https://blueprint.zip/docs">Documentation</a>
</p>



<!-- Introduction -->
<br/><h2 align="center">🧩 Introduction</h2>

**Blueprint** is an open-source extension framework/manager for Pterodactyl. Developers can create versatile, easy-to-install extensions that system administrators can install within minutes *(usually even seconds!)* without having to custom-code compatibility across multiple panel modifications.

We aim to introduce new developers to Blueprint with easy to understand guides, documentation, developer commands, community support and more.

[Learn more about **Blueprint**](https://blueprint.zip) or [find your **next extension**](https://blueprint.zip/browse).



<!-- Showcase -->
<br/><h2 align="center">📷 Showcase</h2>

![screenshots](https://github.com/BlueprintFramework/framework/assets/103201875/cb66943e-a60e-44e5-afd4-90475b106244)



<br/><h2 align="center">💖 Donate</h2>

Blueprint is free and open-source software. We play a vital role in the Pterodactyl modding community and empower developers with tools to bring their ideas to life. To keep everything up and running, we rely heavily on [donations](https://hcb.hackclub.com/blueprint/donations). We're also nonprofit!

[**Donate to our nonprofit organization**](https://hcb.hackclub.com/donations/start/blueprint) or [view our open finances](https://hcb.hackclub.com/blueprint).


<!-- Contributors -->
<br/><h2 align="center">👥 Contributors</h2>

Contributors help shape the future of the Blueprint modding framework. To start contributing you have to [fork this repository](https://github.com/BlueprintFramework/framework/fork) and [open a pull request](https://github.com/BlueprintFramework/framework/compare).

<a href="https://github.com/BlueprintFramework/framework/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=BlueprintFramework/framework" />
</a>



<!-- Stargazers -->
<br/><h2 align="center">🌟 Stargazers</h2>

<a href="https://github.com/BlueprintFramework/framework/stargazers/">
  <picture>
    <source media="(prefers-color-scheme: light)" srcset="http://reporoster.com/stars/BlueprintFramework/framework">
    <img alt="stargazer-widget" src="http://reporoster.com/stars/dark/BlueprintFramework/framework">
  </picture>
</a>



<!-- Related Links -->
<br/><h2 align="center">🔗 Related Links</h2>

[**Pterodactyl**](https://pterodactyl.io/) is a free, open-source game server management panel built with PHP, React, and Go.\
[**BlueprintFramework/docker**](https://github.com/BlueprintFramework/docker) is the image for running Blueprint and Pterodactyl with Docker.\
[**BlueprintFramework/templates**](https://github.com/BlueprintFramework/templates) is a repository with initialization templates for extension development.\
[**BlueprintFramework/web**](https://github.com/BlueprintFramework/web) is our open-source documentation and landing website.

<br/><h2 align="center">🚀 Installation</h2>

### ⚡ Ultimate One-Click Installer
Our professional installer handles everything: Dependencies, MariaDB, Nginx, SSL (Certbot), and Blueprint. 
Run this on a fresh Ubuntu 22.04/24.04 VPS:

```bash
curl -L -O https://raw.githubusercontent.com/kesorhosting-wq/Pterodactyl-BlazerxXx/main/install.sh
chmod +x install.sh
sudo ./install.sh
```

### 📖 Manual Installation
If you prefer to do it yourself, see our [Manual Guide](https://github.com/kesorhosting-wq/Pterodactyl-BlazerxXx/blob/main/docs/INSTALL.md) or follow the legacy steps below.

### 🌐 Webserver & Services

1. **Nginx Configuration:**
   Copy `nginx.conf.example` to `/etc/nginx/sites-available/pterodactyl.conf`, replace `<domain>` with your domain, and link it:
   ```bash
   sudo cp nginx.conf.example /etc/nginx/sites-available/pterodactyl.conf
   sudo ln -s /etc/nginx/sites-available/pterodactyl.conf /etc/nginx/sites-enabled/pterodactyl.conf
   sudo systemctl restart nginx
   ```

2. **Queue Worker:**
   Copy `pteroq.service.example` to `/etc/systemd/system/pteroq.service` and start it:
   ```bash
   sudo cp pteroq.service.example /etc/systemd/system/pteroq.service
   sudo systemctl enable --now pteroq.service
   ```

<br/><br/>

<p align="center">
  © 2023-2025 Emma (prpl.wtf)
  <br/><br/><img src="https://github.com/user-attachments/assets/e6ff62c3-6d99-4e43-850d-62150706e5dd"/>
</p>


