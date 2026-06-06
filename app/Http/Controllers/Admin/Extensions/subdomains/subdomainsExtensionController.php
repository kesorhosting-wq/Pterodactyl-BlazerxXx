<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\subdomains;

use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\Domain;
use Pterodactyl\Models\Subdomain;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
use Illuminate\Support\Facades\Http;
use Prologue\Alerts\AlertsMessageBag;


class subdomainsExtensionController extends Controller
{
  public function __construct(
    private ViewFactory $view,
    private BlueprintExtensionLibrary $blueprint,
    private ConfigRepository $config,
    private SettingsRepositoryInterface $settings,
    private AlertsMessageBag $alert
  ) {
  }

  public function index(): View
  {
    $has_cloudflare = $this->blueprint->dbGet("subdomains","api_key") && $this->blueprint->dbGet("subdomains","email");

    if(!$has_cloudflare) {
      $this->alert->warning('Please set Cloudflare API Key and Email.')->flash();
    }


    return $this->view->make(
      'admin.extensions.subdomains.index',
      [
        'domains' => Domain::all(),
        'subdomains' => Subdomain::all(),

        'root' => "/admin/extensions/subdomains",
        'blueprint' => $this->blueprint,
      ]
    );
  }

  private function cloudflare($url, $method, $data = [])
  {
      return Http::withHeaders([
          'X-Auth-Email' => $this->blueprint->dbGet("subdomains","email"),
          'Authorization' => 'Bearer ' . $this->blueprint->dbGet("subdomains","api_key"),
      ])->$method($url, $data);
  }

  public function post(Request $request): RedirectResponse
  {
    $this->validate($request, [
      'action' => 'required|string|in:subdomain-delete,delete,create,api-key',
    ]);

    $action = $request->input('action');

    if ($action === 'subdomain-delete') {
      $this->validate($request, [
        'subdomain_id' => 'required|integer|exists:subdomains,id',
      ]);

      $subdomain = Subdomain::find($request->input('subdomain_id'));
      $subdomain->delete();

      $this->alert->success('Subdomain deleted.')->flash();
    } else if ($action === 'delete') {
      $this->validate($request, [
        'domain_id' => 'required|integer|exists:domains,id',
      ]);

      if (Subdomain::where('domain_id', $request->input('domain_id'))->count() > 0) {
        $this->alert->danger('Domain has subdomains.')->flash();
        return redirect()->route('admin.extensions.^#identifier#^.index');
      }

      $domain = Domain::find($request->input('domain_id'));
      $domain->delete();

      $this->alert->success('Domain deleted.')->flash();
    } else if ($action === 'create') {
      $this->validate($request, [
        'domain' => 'required|string',
      ]);

      // Check if cloudflare has domain
      $url = 'https://api.cloudflare.com/client/v4/zones';
      $response = Http::withHeaders([
        'X-Auth-Email' => $this->blueprint->dbGet("subdomains","email"),
        'Authorization' => 'Bearer ' . $this->blueprint->dbGet("subdomains","api_key"),
      ])->get($url);
      $domains = $response->json()['result'];
      if (!in_array($request->domain, array_column($domains, 'name'))) {
        $this->alert->danger('Domain not found in Cloudflare.')->flash();
        return redirect()->route('admin.extensions.^#identifier#^.index');
      }
      $domain = new Domain([
        'domain' => $request->domain,
        'zone_id' => array_values(array_filter($domains, function ($zone) use ($request) {
          return $zone['name'] == $request->domain;
        }))[0]['id'],
      ]);
      $domain->save();
    } else if ($action === 'api-key') {
      $this->validate($request, [
        'api_key' => 'required|string',
        'email' => 'required|email',
      ]);

      $api_key = $request->input('api_key');
      $email = $request->input('email');

      $this->blueprint->dbSet("subdomains","api_key",$api_key);
      $this->blueprint->dbSet("subdomains","email",$email);

      $this->alert->success('API Key and Email saved.')->flash();

    }

    return redirect()->route('admin.extensions.subdomains.index');
  }
}
