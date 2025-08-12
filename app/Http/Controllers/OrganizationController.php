<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = auth()->user()->organizations()->get();
        
        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('organizations.create');
    }

    public function store(StoreOrganizationRequest $request)
    {
        $organization = Organization::create($request->validated());
        
        // Attach user as owner
        $organization->users()->attach(auth()->id(), ['role' => 'owner']);
        
        // Set as current organization
        auth()->user()->update(['current_organization_id' => $organization->id]);
        
        return redirect()
            ->route('dashboard')
            ->with('success', 'Organization created successfully');
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
        
        $organization->update($request->validated());
        
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
            abort(403);
        }
        
        auth()->user()->update(['current_organization_id' => $organization->id]);
        
        return redirect()
            ->route('dashboard')
            ->with('success', "Switched to {$organization->name}");
    }
}