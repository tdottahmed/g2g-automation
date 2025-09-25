<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $levels = Level::all();
        return view('admin.levels.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.levels.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'level_type'    => 'required|string|max:255',
            'level_value'   => 'required|string|max:255',
            'active'        => 'required|boolean',
        ]);

        $data = [
            'type'    => $request->level_type,
            'value'   => $request->level_value,
            'active'        => $request->active,
        ];

        try {
            $level = Level::create($data);
            return redirect()
                ->route('levels.index')
                ->with('success', 'Level created successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error creating level: ' . $th->getMessage());
            return back()
                ->with('error', 'An error occurred while creating the level. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Level $level)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level)
    {
        return view('admin.levels.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Level $level)
    {
        $request->validate([
            'level_type'    => 'required|string|max:255',
            'level_value'   => 'required|string|max:255',
            'active'        => 'required|boolean',
        ]);
        $data = [
            'type'    => $request->level_type,
            'value'   => $request->level_value,
            'active'        => $request->active,
        ];

        try {
            $level->update($data);
            return redirect()
                ->route('levels.index')
                ->with('success', 'Level updated successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error updating level: ' . $th->getMessage());
            return back()
                ->withErrors(['error' => 'An error occurred while updating the level. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        try {
            $level->delete();
            return redirect()
                ->route('levels.index')
                ->with('success', 'Level deleted successfully.');
        } catch (\Throwable $th) {
            logger()->error('Error deleting level: ' . $th->getMessage());
            return back()
                ->withErrors(['error' => 'An error occurred while deleting the level. Please try again.']);
        }
    }
}
