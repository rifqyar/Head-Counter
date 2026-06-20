<?php

namespace App\Http\Controllers;

use App\Actions\CreateClientAction;
use App\Domain\Booking\Client;
use App\Domain\Hotel\Hotel;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

class ClientDomainController extends Controller
{
    public function index(Request $request)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        $clients = Client::with('hotels')
            ->when($hotelId, fn ($query) => $query->associatedWithHotel((int) $hotelId))
            ->orderBy('company_name')
            ->paginate(25);

        return $request->wantsJson() ? response()->json($clients) : $this->viewOrRedirect($request, 'domain.clients.index', compact('clients'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.clients.create', [
            'client' => new Client,
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'currentHotel' => app(TenantContext::class)->hotel(),
        ]);
    }

    public function store(StoreClientRequest $request, CreateClientAction $action)
    {
        $hotelIds = $this->hotelAssociationIds($request);
        abort_if($hotelIds === [], 422, 'Select a hotel context before creating a client.');

        $data = collect($request->validated())->except(['hotel_ids'])->all();
        $data['hotel_id'] = $hotelIds[0];

        $client = $action->execute($data, $hotelIds);

        return redirect()->route('clients.show', $client)->with('status', 'Client created and associated with hotel.');
    }

    public function show(Request $request, Client $client)
    {
        $this->authorize('view', $client);

        return $this->viewOrRedirect($request, 'domain.clients.show', ['client' => $client->load('hotels')]);
    }

    public function edit(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        return $this->viewOrRedirect($request, 'domain.clients.edit', [
            'client' => $client->load('hotels'),
            'hotels' => Hotel::where('status', 'ACTIVE')->orderBy('name')->get(),
            'currentHotel' => app(TenantContext::class)->hotel(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $hotelIds = $this->hotelAssociationIds($request, $client);
        $data = collect($request->validated())->except(['hotel_ids'])->all();
        $data['hotel_id'] = $hotelIds[0] ?? $client->hotel_id;

        $client->update($data);
        if ($hotelIds !== []) {
            $sync = collect($hotelIds)->mapWithKeys(fn ($hotelId) => [
                $hotelId => [
                    'hotel_specific_code' => $client->external_id,
                    'status' => 'ACTIVE',
                    'metadata' => json_encode(['source' => 'client_form']),
                ],
            ])->all();
            $client->hotels()->syncWithoutDetaching($sync);
        }

        return redirect()->route('clients.show', $client)->with('status', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client deleted.');
    }

    private function hotelAssociationIds(Request $request, ?Client $client = null): array
    {
        $contextHotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;

        if (! $request->user()->isSuperAdmin()) {
            return $contextHotelId ? [(int) $contextHotelId] : [];
        }

        $ids = collect($request->input('hotel_ids', []))
            ->when($request->filled('hotel_id'), fn ($items) => $items->push($request->input('hotel_id')))
            ->when($contextHotelId, fn ($items) => $items->push($contextHotelId))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids === [] && $client?->hotel_id) {
            return [(int) $client->hotel_id];
        }

        return $ids;
    }
}
