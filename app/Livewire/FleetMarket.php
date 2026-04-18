<?php

namespace App\Livewire;

use App\Models\FleetMarketListing;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FleetMarket extends Component
{
    use WithPagination;

    // Filters
    public string $search      = '';
    public string $typeFilter  = '';
    public string $condFilter  = '';
    public string $sortBy      = 'created_at';
    public string $sortDir     = 'desc';

    // Create / edit listing modal
    public bool   $showModal     = false;
    public ?int   $editingId     = null;

    public string $brand           = '';
    public string $model           = '';
    public string $machineType     = '';
    public string $year            = '';
    public string $price           = '';
    public string $currency        = 'ZAR';
    public string $condition       = 'used';
    public string $hoursOnMachine  = '';
    public string $description     = '';
    public string $contactName     = '';
    public string $contactEmail    = '';
    public string $contactPhone    = '';
    public string $location        = '';

    // Contact / enquiry modal
    public bool   $showEnquiryModal  = false;
    public ?int   $enquiryListingId  = null;

    protected function rules(): array
    {
        return [
            'brand'          => 'required|string|max:100',
            'model'          => 'required|string|max:100',
            'machineType'    => 'required|string|max:60',
            'year'           => 'nullable|integer|min:1970|max:' . (date('Y') + 1),
            'price'          => 'nullable|numeric|min:0',
            'currency'       => 'required|string|max:5',
            'condition'      => 'required|in:new,used,refurbished',
            'hoursOnMachine' => 'nullable|integer|min:0',
            'description'    => 'nullable|string|max:2000',
            'contactName'    => 'required|string|max:120',
            'contactEmail'   => 'required|email|max:200',
            'contactPhone'   => 'nullable|string|max:30',
            'location'       => 'nullable|string|max:150',
        ];
    }

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }
    public function updatedCondFilter(): void { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editListing(int $id): void
    {
        $listing = FleetMarketListing::where('team_id', Auth::user()->current_team_id)
            ->findOrFail($id);

        $this->editingId       = $listing->id;
        $this->brand           = $listing->brand;
        $this->model           = $listing->model;
        $this->machineType     = $listing->machine_type;
        $this->year            = (string) ($listing->year ?? '');
        $this->price           = (string) ($listing->price ?? '');
        $this->currency        = $listing->currency;
        $this->condition       = $listing->condition;
        $this->hoursOnMachine  = (string) ($listing->hours_on_machine ?? '');
        $this->description     = $listing->description ?? '';
        $this->contactName     = $listing->contact_name ?? '';
        $this->contactEmail    = $listing->contact_email ?? '';
        $this->contactPhone    = $listing->contact_phone ?? '';
        $this->location        = $listing->location ?? '';
        $this->showModal       = true;
    }

    public function saveListing(): void
    {
        $this->validate();

        $data = [
            'team_id'          => Auth::user()->current_team_id,
            'brand'            => $this->brand,
            'model'            => $this->model,
            'machine_type'     => $this->machineType,
            'year'             => $this->year ?: null,
            'price'            => $this->price ?: null,
            'currency'         => $this->currency,
            'condition'        => $this->condition,
            'hours_on_machine' => $this->hoursOnMachine ?: null,
            'description'      => $this->description ?: null,
            'contact_name'     => $this->contactName,
            'contact_email'    => $this->contactEmail,
            'contact_phone'    => $this->contactPhone ?: null,
            'location'         => $this->location ?: null,
            'status'           => 'active',
        ];

        if ($this->editingId) {
            FleetMarketListing::where('team_id', Auth::user()->current_team_id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->dispatch('notify', type: 'success', message: 'Listing updated successfully.');
        } else {
            FleetMarketListing::create($data);
            $this->dispatch('notify', type: 'success', message: 'Listing published to the Fleet Market.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function markAsSold(int $id): void
    {
        FleetMarketListing::where('team_id', Auth::user()->current_team_id)
            ->findOrFail($id)
            ->update(['status' => 'sold']);
        $this->dispatch('notify', type: 'success', message: 'Listing marked as sold.');
    }

    public function withdrawListing(int $id): void
    {
        FleetMarketListing::where('team_id', Auth::user()->current_team_id)
            ->findOrFail($id)
            ->update(['status' => 'withdrawn']);
        $this->dispatch('notify', type: 'success', message: 'Listing withdrawn.');
    }

    public function deleteListing(int $id): void
    {
        FleetMarketListing::where('team_id', Auth::user()->current_team_id)
            ->findOrFail($id)
            ->delete();
        $this->dispatch('notify', type: 'success', message: 'Listing deleted.');
    }

    public function openEnquiry(int $id): void
    {
        $this->enquiryListingId = $id;
        $this->showEnquiryModal = true;
    }

    public function closeEnquiry(): void
    {
        $this->showEnquiryModal = false;
        $this->enquiryListingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId      = null;
        $this->brand          = '';
        $this->model          = '';
        $this->machineType    = '';
        $this->year           = '';
        $this->price          = '';
        $this->currency       = 'ZAR';
        $this->condition      = 'used';
        $this->hoursOnMachine = '';
        $this->description    = '';
        $this->contactName    = '';
        $this->contactEmail   = '';
        $this->contactPhone   = '';
        $this->location       = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $listings = FleetMarketListing::with('team')
            ->active()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($inner) =>
                    $inner->where('brand', 'like', "%{$this->search}%")
                          ->orWhere('model', 'like', "%{$this->search}%")
                          ->orWhere('location', 'like', "%{$this->search}%")
                )
            )
            ->when($this->typeFilter, fn ($q) => $q->where('machine_type', $this->typeFilter))
            ->when($this->condFilter, fn ($q) => $q->where('condition', $this->condFilter))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(12);

        // My listings (own team, any status)
        $myListings = FleetMarketListing::where('team_id', Auth::user()->current_team_id)
            ->orderByDesc('created_at')
            ->get();

        $machineTypes = FleetMarketListing::active()
            ->distinct('machine_type')
            ->orderBy('machine_type')
            ->pluck('machine_type');

        $enquiryListing = $this->enquiryListingId
            ? FleetMarketListing::with('team')->find($this->enquiryListingId)
            : null;

        return view('livewire.fleet-market', compact(
            'listings',
            'myListings',
            'machineTypes',
            'enquiryListing',
        ))->layout('layouts.app');
    }
}
