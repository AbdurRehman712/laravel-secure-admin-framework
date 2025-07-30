<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\ProjectWorkspaceContent;
use Livewire\Component;
use Livewire\Attributes\On;

class WorkspaceContentList extends Component
{
    public Project $project;
    public string $role;

    protected $listeners = [
        'content-created' => 'refreshContent',
        'role-switched' => 'updateRole',
    ];

    public function mount(Project $project, string $role)
    {
        $this->project = $project;
        $this->role = $role;
    }

    public function updateRole($role)
    {
        $this->role = $role;
    }

    #[On('content-created')]
    public function refreshContent()
    {
        // This will trigger a re-render
        $this->render();
    }

    public function viewContent($contentId)
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        // Emit event to parent component to show content details
        $this->dispatch('showContentDetails', $content->id);
    }

    public function editContent($contentId)
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        // Emit event to parent component to edit content
        $this->dispatch('editContent', $content->id);
    }

    public function generateCode($contentId)
    {
        $content = ProjectWorkspaceContent::findOrFail($contentId);

        // Emit event to parent component to generate code
        $this->dispatch('generateCode', $content->id);
    }

    public function getWorkspaceContent()
    {
        return $this->project->getWorkspaceContentByRole($this->role)
            ->with('admin')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.workspace-content-list', [
            'content' => $this->getWorkspaceContent(),
        ]);
    }
}
