<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the user's projects on the dashboard.
     */
    public function index(Request $request): Response
    {
        $projects = $request->user()->projects()->latest()->get();

        return Inertia::render('Dashboard', [
            'projects' => ProjectResource::collection($projects)->resolve()
        ]);
    }

    /**
     * Show the flowchart editor for a specific project or a new one.
     */
    public function show(Request $request, ?Project $project = null): Response
    {
        if ($project) {
            $this->authorize('view', $project);
            $project->load('user');
        }

        return Inertia::render('Flowchart', [
            'project' => $project ? (new ProjectResource($project))->resolve() : null
        ]);
    }

    /**
     * Save or update a project.
     */
    public function store(ProjectStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (isset($validated['id'])) {
            $project = Project::findOrFail($validated['id']);
            $this->authorize('update', $project);
            $project->update($validated);
        } else {
            $request->user()->projects()->create($validated);
        }

        return redirect()->route('dashboard')->with('message', 'Project saved successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('dashboard')->with('message', 'Project deleted successfully.');
    }
}
