<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Log disk configuration
        Log::info('Storage disk configuration', [
            'default' => config('filesystems.default'),
            'disks' => array_keys(config('filesystems.disks')),
            'store_exists' => config('filesystems.disks.store'),
        ]);

        // Handle file uploads if present
        if ($request->hasFile('images')) {
            $imageFiles = [];
            Log::info('Processing image uploads', [
                'count' => count($request->file('images')),
                'file_names' => array_map(fn($file) => $file->getClientOriginalName(), $request->file('images')),
            ]);

            foreach ($request->file('images') as $image) {
                try {
                    $path = $image->store('projects/images', 's3');
                    $imageFiles[] = $path;
                    Log::info('Image stored successfully', [
                        'original_name' => $image->getClientOriginalName(),
                        'stored_path' => $path,
                        'disk' => 's3'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to store image', [
                        'original_name' => $image->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            $validated['images'] = json_encode($imageFiles);
            Log::info('All images processed', ['paths' => $imageFiles]);
        }

        if ($request->hasFile('stl_files')) {
            $stlFiles = [];
            Log::info('Processing STL uploads', [
                'count' => count($request->file('stl_files')),
                'file_names' => array_map(fn($file) => $file->getClientOriginalName(), $request->file('stl_files')),
            ]);

            foreach ($request->file('stl_files') as $stl) {
                try {
                    $path = $stl->store('projects/models', 's3');
                    $stlFiles[] = $path;
                    Log::info('STL file stored successfully', [
                        'original_name' => $stl->getClientOriginalName(),
                        'stored_path' => $path,
                        'disk' => 's3'
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to store STL file', [
                        'original_name' => $stl->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            $validated['stl_files'] = json_encode($stlFiles);
            Log::info('All STL files processed', ['paths' => $stlFiles]);
        }

        // Handle specifications if present
        if ($request->has('specifications')) {
            $validated['specifications'] = is_array($request->specifications)
                ? json_encode($request->specifications)
                : $request->specifications;
        }

        $project = Project::create($validated);
        Log::info('Project created successfully', ['project_id' => $project->id, 'title' => $project->title]);

        return redirect()->route('projects.index')
            ->with('message', 'Project created successfully');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        return Inertia::render('projects/Show', [
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
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

        // Log disk configuration
        Log::info('Storage disk configuration during update', [
            'default' => config('filesystems.default'),
            'disks' => array_keys(config('filesystems.disks')),
            'store_exists' => config(key: 'filesystems.disks.store'),
            'project_id' => $project->id
        ]);

        // Log details of the existing data
        Log::info('Project before update', [
            'id' => $project->id,
            'title' => $project->title,
            'images' => $project->images,
            'stl_files' => $project->stl_files,
        ]);

        // Log request details
        Log::info('Update request details', [
            'has_existing_images' => $request->has('existing_images'),
            'has_new_images' => $request->hasFile('images'),
            'has_existing_stl_files' => $request->has('existing_stl_files'),
            'has_new_stl_files' => $request->hasFile('stl_files'),
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
            $existing = $request->input('existing_images');
            $project->images = $existing;
            Log::info('Using existing images', [
                'images' => $existing,
                'project_id' => $project->id
            ]);
        } elseif ($request->hasFile('images')) {
            // Process new image uploads and remove old ones
            $imageFiles = [];
            Log::info('Processing new image uploads', [
                'count' => count($request->file('images')),
                'project_id' => $project->id
            ]);

            foreach ($request->file('images') as $image) {
                try {
                    $path = $image->store('projects/images', 's3');
                    $imageFiles[] = $path;
                    Log::info('New image stored successfully', [
                        'original_name' => $image->getClientOriginalName(),
                        'stored_path' => $path,
                        'disk' => 's3',
                        'project_id' => $project->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to store new image', [
                        'original_name' => $image->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'project_id' => $project->id
                    ]);
                }
            }

            // Delete old images if they exist
            try {
                $oldImages = json_decode($project->images ?? '[]', true);
                Log::info('Deleting old images', [
                    'images' => $oldImages,
                    'project_id' => $project->id
                ]);

                foreach ($oldImages as $oldImage) {
                    try {
                        $result = Storage::disk('s3')->delete($oldImage);
                        Log::info('Deleted old image', [
                            'path' => $oldImage,
                            'result' => $result ? 'success' : 'failed',
                            'project_id' => $project->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete old image', [
                            'path' => $oldImage,
                            'error' => $e->getMessage(),
                            'project_id' => $project->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process old images', [
                    'error' => $e->getMessage(),
                    'project_id' => $project->id
                ]);
            }

            $project->images = json_encode($imageFiles);
            Log::info('Updated project images', [
                'new_images' => $imageFiles,
                'project_id' => $project->id
            ]);
        }

        // Handle STL files
        if ($request->has('existing_stl_files')) {
            $existing = $request->input('existing_stl_files');
            $project->stl_files = $existing;
            Log::info('Using existing STL files', [
                'stl_files' => $existing,
                'project_id' => $project->id
            ]);
        } elseif ($request->hasFile('stl_files')) {
            // Process new STL uploads and remove old ones
            $stlFiles = [];
            Log::info('Processing new STL uploads', [
                'count' => count($request->file('stl_files')),
                'project_id' => $project->id
            ]);

            foreach ($request->file('stl_files') as $stl) {
                try {
                    $path = $stl->store('projects/models', 's3');
                    $stlFiles[] = $path;
                    Log::info('New STL file stored successfully', [
                        'original_name' => $stl->getClientOriginalName(),
                        'stored_path' => $path,
                        'disk' => 's3',
                        'project_id' => $project->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to store new STL file', [
                        'original_name' => $stl->getClientOriginalName(),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'project_id' => $project->id
                    ]);
                }
            }

            // Delete old STL files if they exist
            try {
                $oldStlFiles = json_decode($project->stl_files ?? '[]', true);
                Log::info('Deleting old STL files', [
                    'stl_files' => $oldStlFiles,
                    'project_id' => $project->id
                ]);

                foreach ($oldStlFiles as $oldStlFile) {
                    try {
                        $result = Storage::disk('s3')->delete($oldStlFile);
                        Log::info('Deleted old STL file', [
                            'path' => $oldStlFile,
                            'result' => $result ? 'success' : 'failed',
                            'project_id' => $project->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete old STL file', [
                            'path' => $oldStlFile,
                            'error' => $e->getMessage(),
                            'project_id' => $project->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to process old STL files', [
                    'error' => $e->getMessage(),
                    'project_id' => $project->id
                ]);
            }

            $project->stl_files = json_encode($stlFiles);
            Log::info('Updated project STL files', [
                'new_stl_files' => $stlFiles,
                'project_id' => $project->id
            ]);
        }

        // Handle specifications if present
        if ($request->has('specifications')) {
            $project->specifications = is_array($request->specifications)
                ? json_encode($request->specifications)
                : $request->specifications;
        }

        $project->save();
        Log::info('Project updated successfully', [
            'project_id' => $project->id,
            'title' => $project->title,
            'final_images' => $project->images,
            'final_stl_files' => $project->stl_files
        ]);

        return redirect()->route('projects.show', $project)
            ->with('message', 'Project updated successfully');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        // Delete associated files before removing the project
        try {
            // Delete images
            $images = json_decode($project->images ?? '[]', true);
            foreach ($images as $image) {
                Storage::delete($image);
            }

            // Delete STL files
            $stlFiles = json_decode($project->stl_files ?? '[]', true);
            foreach ($stlFiles as $stlFile) {
                Storage::delete($stlFile);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete project files: ' . $e->getMessage());
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('message', 'Project deleted successfully');
    }
}
