<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContactController extends Controller
{
    use AuthorizesRequests;
    public function __construct()
    {
        $this->authorizeResource(Contact::class, 'contact');
    }

    public function index(Request $request)
    {
        $contacts = Contact::when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->orderBy($request->get('sort', 'name'), $request->get('direction', 'asc'))
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('contacts.partials.table', compact('contacts'));
        }
        
        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        if (request()->header('HX-Request')) {
            return view('contacts.partials.create-form');
        }
        
        return view('contacts.create');
    }

    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('contacts.partials.row', compact('contact'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'toast' => [
                        'message' => 'Contact created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact created successfully');
    }

    public function show(Contact $contact)
    {
        $contact->load(['leases.unit.property', 'payments', 'maintenanceTickets']);
        
        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        if (request()->header('HX-Request')) {
            return view('contacts.partials.edit-form', compact('contact'));
        }
        
        return view('contacts.edit', compact('contact'));
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('contacts.partials.row', compact('contact'))
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Contact updated successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('contacts.show', $contact)
            ->with('success', 'Contact updated successfully');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        
        if (request()->header('HX-Request')) {
            return response('')
                ->header('HX-Trigger', json_encode([
                    'toast' => [
                        'message' => 'Contact deleted successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('contacts.index')
            ->with('success', 'Contact deleted successfully');
    }
}