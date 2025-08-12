<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrganizationController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $organizations = auth()->user()->organizations()->get();
        
        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('orgs.create');
    }

    public function store(StoreOrganizationRequest $request)
    {
        $validated = $request->validated();
        
        // Create organization with settings
        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'settings' => $validated['settings'] ?? ['currency' => 'USD'],
        ]);
        
        // Attach user as owner
        $organization->users()->attach(auth()->id(), ['role' => 'owner']);
        
        // Set as current organization
        auth()->user()->update(['current_organization_id' => $organization->id]);
        
        return redirect()
            ->route('dashboard')
            ->with('success', 'Organization created successfully! Welcome to ' . $organization->name . '.');
    }

    public function show(Organization $organization)
    {
        $this->authorize('view', $organization);
        
        return view('organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $this->authorize('update', $organization);
        
        if (request()->header('HX-Request')) {
            return view('organizations.partials.edit-form', compact('organization'));
        }
        
        return view('organizations.edit', compact('organization'));
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $this->authorize('update', $organization);
        
        $validated = $request->validated();
        
        $organization->update($validated);
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('organizations.partials.updated', compact('organization'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Organization updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('organizations.index')
            ->with('success', 'Organization updated successfully');
    }

    public function switch(Request $request, Organization $organization)
    {
        // Verify user belongs to organization
        if (!auth()->user()->organizations->contains($organization)) {
            abort(403, 'You do not have access to this organization.');
        }
        
        auth()->user()->update(['current_organization_id' => $organization->id]);
        
        return redirect()
            ->route('dashboard')
            ->with('success', "Switched to {$organization->name}");
    }
}