<?php

namespace App\Http\Controllers;

use App\Domain\Hotel\Hotel;
use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        $hotels = Hotel::orderBy('name')->paginate(25);

        return $request->wantsJson() ? response()->json($hotels) : $this->viewOrRedirect($request, 'domain.hotels.index', compact('hotels'));
    }

    public function create(Request $request)
    {
        return $this->viewOrRedirect($request, 'domain.hotels.create', ['hotel' => new Hotel]);
    }

    public function store(StoreHotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());

        return redirect()->route('hotels.show', $hotel);
    }

    public function show(Request $request, Hotel $hotel)
    {
        $this->authorize('view', $hotel);

        return $this->viewOrRedirect($request, 'domain.hotels.show', compact('hotel'));
    }

    public function edit(Request $request, Hotel $hotel)
    {
        $this->authorize('manage', $hotel);

        return $this->viewOrRedirect($request, 'domain.hotels.edit', compact('hotel'));
    }

    public function update(UpdateHotelRequest $request, Hotel $hotel)
    {
        $hotel->update($request->validated());

        return redirect()->route('hotels.show', $hotel);
    }

    public function destroy(Hotel $hotel)
    {
        $this->authorize('manage', $hotel);
        $hotel->update(['status' => 'INACTIVE']);

        return redirect()->route('hotels.index');
    }
}
