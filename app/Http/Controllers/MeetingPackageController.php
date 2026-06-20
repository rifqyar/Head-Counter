<?php

namespace App\Http\Controllers;

use App\Domain\Catering\MeetingPackage;
use App\Domain\Catering\PackageEntitlement;
use App\Http\Requests\StoreMeetingPackageRequest;
use App\Http\Requests\UpdateMeetingPackageRequest;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class MeetingPackageController extends Controller
{
    public function index(Request $request)
    {
        $packages = MeetingPackage::with('entitlements')->orderBy('code')->paginate(25);

        return $request->wantsJson() ? response()->json($packages) : view('domain.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('domain.packages.create', ['package' => new MeetingPackage]);
    }

    public function store(StoreMeetingPackageRequest $request)
    {
        $hotelId = app(TenantContext::class)->hotelId() ?: $request->user()->hotel_id;
        abort_if($hotelId === null, 422, 'Select a hotel context before creating a package.');

        $package = DB::transaction(function () use ($request, $hotelId) {
            $data = Arr::except($request->validated(), ['entitlement_type', 'entitlement_quantity']);
            $package = MeetingPackage::create(array_merge($data, ['hotel_id' => $hotelId]));
            $this->syncEntitlement($package, $request->validated());

            return $package;
        });

        return redirect()->route('packages.show', $package)->with('status', 'Package created.');
    }

    public function show(MeetingPackage $package)
    {
        $this->authorize('view', $package);

        return view('domain.packages.show', compact('package'));
    }

    public function edit(MeetingPackage $package)
    {
        $this->authorize('update', $package);

        return view('domain.packages.edit', compact('package'));
    }

    public function update(UpdateMeetingPackageRequest $request, MeetingPackage $package)
    {
        $this->authorize('update', $package);

        DB::transaction(function () use ($request, $package) {
            $package->update(Arr::except($request->validated(), ['entitlement_type', 'entitlement_quantity']));
            $this->syncEntitlement($package, $request->validated());
        });

        return redirect()->route('packages.show', $package)->with('status', 'Package updated.');
    }

    public function destroy(MeetingPackage $package)
    {
        $this->authorize('delete', $package);
        $package->update(['is_active' => false]);

        return redirect()->route('packages.index')->with('status', 'Package deactivated.');
    }

    private function syncEntitlement(MeetingPackage $package, array $data): void
    {
        if (empty($data['entitlement_type'])) {
            return;
        }

        PackageEntitlement::updateOrCreate(
            ['package_id' => $package->id, 'entitlement_type' => $data['entitlement_type']],
            ['quantity' => (int) ($data['entitlement_quantity'] ?? 1), 'metadata' => ['source' => 'phase_3_ui']]
        );
    }
}
