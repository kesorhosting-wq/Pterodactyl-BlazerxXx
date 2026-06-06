<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Models\Subdomain;
use Pterodactyl\Models\Domain;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;

class DomainController extends Controller
{
    public function index(Request $request, $server)
    {
        $subdomains = Subdomain::where('server_id', $server->id)->with(['domain', 'allocation'])->get();
        $domains = Domain::all();
        return response()->json([
            'subdomain' => $subdomains ?? null,
            'domains' => $domains,
        ]);
    }

    public function store(Request $request, $server)
    {
        $request->validate([
            'domain' => 'required|integer|exists:domains,id',
            // Allow only letters, numbers and dashes and dots
            'subdomain' => 'required|string|max:64|regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?$/|unique:subdomains,subdomain',
            'allocation_id' => 'required|integer|exists:allocations,id|unique:subdomains,allocation_id,' . $server->id . ',server_id',
        ]);
        $domain = Domain::where('id', $request->domain)->first();
        $allocation = $server->allocations()->where('id', $request->allocation_id)->first();
        // Create subdomain
        $subdomain = new Subdomain();
        $subdomain->subdomain = $request->subdomain;
        $subdomain->allocation_id = $request->allocation_id;
        $subdomain->domain_id = $domain->id;
        $subdomain->server_id = $server->id;
        $subdomain->save();

        Activity::event("server:domain.new")
            ->property(['domain' => $request->subdomain . '.' . $request->domain])
            ->log();
       
        return response()->json([
            'success' => 'Subdomain successfully added.',
            'subdomain' => $subdomain->load('domain', 'allocation'),
        ]);
    }

    public function delete(Request $request, Server $server, $subdomain)
    {
        $subdomain = Subdomain::where('id', $subdomain)->first();
        if (!$subdomain) {
            return response()->json([
                'error' => 'Subdomain not found.',
            ], 404);
        }
        if ($subdomain->server_id != $server->id) {
            return response()->json([
                'error' => 'Subdomain not found.',
            ], 404);
        }

        Activity::event("server:domain.delete")
            ->property(['domain' => $subdomain->subdomain . '.' . $subdomain->domain->domain])
            ->log();
        $subdomain->delete();

        return response()->json([
            'success' => 'Subdomain successfully deleted.',
        ]);
    }
}
