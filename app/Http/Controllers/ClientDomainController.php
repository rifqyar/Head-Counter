<?php

namespace App\Http\Controllers;

use App\Actions\CreateClientAction;
use App\Domain\Booking\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;

class ClientDomainController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::orderBy('company_name')->paginate(25);

        return $request->wantsJson() ? response()->json($clients) : view('domain.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('domain.clients.create', ['client' => new Client]);
    }

    public function store(StoreClientRequest $request, CreateClientAction $action)
    {
        $client = $action->execute($request->validated());

        return redirect()->route('clients.show', $client);
    }

    public function show(Client $client)
    {
        $this->authorize('view', $client);

        return view('domain.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('domain.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()->route('clients.show', $client);
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()->route('clients.index');
    }
}
