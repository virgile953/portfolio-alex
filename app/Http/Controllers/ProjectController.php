<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     */
    public function index()
    {
        $projects = Project::all();

        // Add this for debugging
        Log::info(message: 'Projects count: ' . $projects->count());

        // Use the new Index component in the projects folder
        return Inertia::render('projects/Index', [
            'projects' => $projects
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Use the new Create component
        return Inertia::render('projects/Create');
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'materials' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'featured' => 'boolean',
        ]);

        // Handle file uploads if present
        if ($request->hasFile('images')) {
            $imageFiles = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects/images', 'public');
                $imageFiles[] = $path;
            }
            $validated['images'] = json_encode($imageFiles);
        }

        if ($request->hasFile('stl_files')) {
            $stlFiles = [];
            foreach ($request->file('stl_files') as $stl) {
                $path = $stl->store('projects/models', 'public');
                $stlFiles[] = $path;
            }
            $validated['stl_files'] = json_encode($stlFiles);
        }

        // Handle specifications if present
        if ($request->has('specifications')) {
            $validated['specifications'] = is_array($request->specifications)
                ? json_encode($request->specifications)
                : $request->specifications;
        }

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('message', 'Project created successfully');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        // Use the new Show component
        return Inertia::render('projects/Show', [
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Use the new Edit component
        return Inertia::render('projects/Edit', [
            'project' => $project
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        // First, validate the basic fields without file validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'materials' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'featured' => 'boolean',
        ]);

        // Separate validation for new file uploads only
        if ($request->hasFile('images')) {
            $request->validate([
                'images.*' => 'file|image|max:5120',
            ]);
        }

        if ($request->hasFile('stl_files')) {
					$request->validate([
							'stl_files.*' => 'file|mimetypes:application/octet-stream,application/vnd.ms-pki.stl,model/stl|max:100240',
					]);
			}

        // Update basic fields
        $project->title = $validated['title'];
        $project->description = $validated['description'];
        $project->type = $validated['type'];
        $project->url = $request->url;
        $project->materials = $request->materials;
        $project->completion_date = $request->completion_date;
        $project->featured = (bool)$request->featured;

        // Handle images
        if ($request->has('existing_images')) {
            // Use existing images
            $project->images = $request->input('existing_images');
        } elseif ($request->hasFile('images')) {
            // Process new image uploads
            $imageFiles = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('projects/images', 'public');
                $imageFiles[] = $path;
            }
            $project->images = json_encode($imageFiles);
        }

        // Handle STL files
        if ($request->has('existing_stl_files')) {
            // Use existing STL files
            $project->stl_files = $request->input('existing_stl_files');
        } elseif ($request->hasFile('stl_files')) {
            // Process new STL uploads
            $stlFiles = [];
            foreach ($request->file('stl_files') as $stl) {
                $path = $stl->store('projects/models', options: 'public');
                $stlFiles[] = $path;
            }
            $project->stl_files = json_encode($stlFiles);
        }

        // Handle specifications if present
        if ($request->has('specifications')) {
            $project->specifications = is_array($request->specifications)
                ? json_encode($request->specifications)
                : $request->specifications;
        }

        $project->save();

        return redirect()->route('projects.show', $project)
            ->with('message', 'Project updated successfully');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('message', 'Project deleted successfully');
    }
}
