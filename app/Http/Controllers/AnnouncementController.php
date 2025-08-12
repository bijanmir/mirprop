<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Requests\UpdateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('viewAny', Announcement::class);
        
        $query = Announcement::with('creator');
        
        // Non-staff can only see sent announcements
        if (!auth()->user()->isStaff()) {
            $query->whereNotNull('sent_at');
            
            // Filter by audience
            if (auth()->user()->hasOrganizationRole('tenant')) {
                $query->whereIn('audience', ['tenants', 'all']);
            } elseif (auth()->user()->hasOrganizationRole('owner')) {
                $query->whereIn('audience', ['owners', 'all']);
            }
        }
        
        $announcements = $query
            ->when($request->search, function ($query, $search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            })
            ->when($request->audience, fn($q, $audience) => $q->where('audience', $audience))
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        if ($request->header('HX-Request')) {
            return view('announcements.partials.table', compact('announcements'));
        }
        
        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);
        
        if (request()->header('HX-Request')) {
            return view('announcements.partials.create-form');
        }
        
        return view('announcements.create');
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $announcement = Announcement::create($request->validated());
        
        if ($request->boolean('send_immediately')) {
            // TODO: Queue job to send announcement
            $announcement->update(['sent_at' => now()]);
        }
        
        if ($request->header('HX-Request')) {
            return response()
                ->view('announcements.partials.create-success', compact('announcement'))
                ->header('HX-Trigger', json_encode([
                    'close-modal' => true,
                    'refresh-table' => true,
                    'toast' => [
                        'message' => 'Announcement created successfully',
                        'type' => 'success'
                    ]
                ]));
        }
        
        return redirect()
            ->route('announcements.show', $announcement)
            ->with('success', 'Announcement created successfully');
    }

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        
        return view('announcements.show', compact('announcement'));
    }
}