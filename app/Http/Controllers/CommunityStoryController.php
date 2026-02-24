<?php

namespace App\Http\Controllers;

use App\Models\CommunityStory;
use Illuminate\Http\Request;

class CommunityStoryController extends Controller
{
    public function index()
    {
        $stories = CommunityStory::approved()->latest()->paginate(12);
        $featured = CommunityStory::featured()->approved()->latest()->take(3)->get();

        return view('stories.index', compact('stories', 'featured'));
    }

    public function create()
    {
        return view('stories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'location' => 'nullable|string|max:255',
            'child_name' => 'nullable|string|max:255',
            'child_age' => 'nullable|integer|min:0|max:100',
            'title' => 'required|string|max:255',
            'story' => 'required|string|min:50',
            'favorite_park' => 'nullable|string|max:255',
            'favorite_tip' => 'nullable|string|max:2000',
            'photo_consent' => 'boolean',
        ]);

        $validated['photo_consent'] = $request->boolean('photo_consent');

        CommunityStory::create($validated);

        return redirect()->back()->with('success', 'Thank you for sharing your story! We\'ll review it and get it published soon. 💛');
    }
}
