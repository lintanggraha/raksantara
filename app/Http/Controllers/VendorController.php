<?php

namespace App\Http\Controllers;

use App\Support\RiskAccess;
use App\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the vendors.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $vendors = Vendor::paginate(10);
        return view('vendors.index', compact('vendors'));
    }

    /**
     * Show the form for creating a new vendor.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('vendors.create');
    }

    /**
     * Store a newly created vendor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:Active,Inactive',
            'criticality_level' => 'nullable|in:Low,Medium,High',
        ]);

        Vendor::create($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified vendor.
     *
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\View\View
     */
    public function show(Vendor $vendor)
    {
        $riskQuery = $vendor->risks()->getQuery()->with('residualAnalysis');
        RiskAccess::applyVisibilityScope($riskQuery, auth()->user());

        $risks = $riskQuery
            ->latest('updated_at')
            ->get();

        return view('vendors.show', compact('vendor', 'risks'));
    }

    /**
     * Show the form for editing the specified vendor.
     *
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\View\View
     */
    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    /**
     * Update the specified vendor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:Active,Inactive',
            'criticality_level' => 'nullable|in:Low,Medium,High',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified vendor from storage.
     *
     * @param  \App\Vendor  $vendor
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Vendor $vendor)
    {
        // Nullify vendor_id in risks or let foreign key constraint (nullOnDelete) handle it
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
