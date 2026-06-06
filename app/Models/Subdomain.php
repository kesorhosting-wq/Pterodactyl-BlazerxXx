<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;

class Subdomain extends Model
{
    protected $table = 'subdomains';
    protected $fillable = [
        'server_id',
        'allocation_id',
        'domain_id',
        'subdomain',
        'cloudflare_id',
    ];
        
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(Allocation::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    // event listeners for creating and deleting subdomains
    protected static function boot()
    {
        parent::boot();

        static::created(function ($subdomain) {
            $subdomain->createCloudflareRecord();
        });

        static::deleting(function ($subdomain) {
            $subdomain->deleteCloudflareRecord();
        });
    }
    
        
    private function cloudflare($url, $method, $data = [])
    {
        return Http::withHeaders([
            'X-Auth-Email' => $this->blueprint->dbGet("subdomains","email"),
            'Authorization' => 'Bearer ' . $this->blueprint->dbGet("subdomains","api_key"),
        ])->$method($url, $data);
    }

    private function getDNSIds($subdomain, $domain)
    {
        $url = 'https://api.cloudflare.com/client/v4/zones/' . $domain->zone_id . '/dns_records';
        $response = $this->cloudflare($url, 'get', ['comment' => 'id:' . $subdomain]);

        return $response->json()['result'];
    }

    public function createCloudflareRecord()
    {
        $this->blueprint = resolve(BlueprintExtensionLibrary::class);

        // Create subdomain in cloudflare
        $url = 'https://api.cloudflare.com/client/v4/zones/' . $this->domain->zone_id . '/dns_records';
        $this->cloudflare($url, 'post', [
            'type' => 'A',
            'name' => $this->subdomain,
            'content' => $this->allocation->ip,
            'ttl' => 1,
            'proxied' => false,
            'comment' => 'id:' . $this->id,
        ]);

        // Create SRV record
        $url = 'https://api.cloudflare.com/client/v4/zones/' . $this->domain->zone_id . '/dns_records';
        $this->cloudflare($url, 'post', [
            'name' => '_minecraft._tcp.' . $this->subdomain,
            'type' => 'SRV',
            'data' => [
                'weight' => 0,
                'port' => $this->allocation->port,
                'priority' => 0,
                'target' => $this->subdomain . '.' . $this->domain->domain,
            ],
            'ttl' => 1,
            'proxied' => false,
            'comment' => 'id:' . $this->id,
        ]);
    }

    public function deleteCloudflareRecord()
    {
        $this->blueprint = resolve(BlueprintExtensionLibrary::class);

        $zoneIds = $this->getDNSIds($this->id, $this->domain);

        $arecord = $zoneIds[0]['id'] ?? null;
        $srvrecord = $zoneIds[1]['id'] ?? null;
        // Delete subdomain in cloudflare
        if ($arecord) {
            $url = 'https://api.cloudflare.com/client/v4/zones/' . $this->domain->zone_id . '/dns_records/' . $arecord;
            $this->cloudflare($url, 'delete');
        }

        // Delete SRV record
        if ($srvrecord) {
            $url = 'https://api.cloudflare.com/client/v4/zones/' . $this->domain->zone_id . '/dns_records/' . $srvrecord;
            $this->cloudflare($url, 'delete');
        }
    }

}