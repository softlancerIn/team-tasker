<x-admin title="Project Details">
    <div class="top-bar-premium">
        <div>
            <a href="{{ route('admin.projects.index') }}" class="text-primary text-decoration-none small mb-2 d-inline-block"><i class="fas fa-arrow-left me-1"></i> Back to Projects</a>
            <h1 class="h3 fw-semibold mb-1 text-high">{{ $project->name }}</h1>
            <p class="text-low mb-0" style="font-size: 0.9rem;">Project Details and Tasks</p>
        </div>
        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn-premium btn-premium-secondary">
            <i class="fas fa-edit"></i> Edit Project
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-3" style="color: var(--text-high);">Description</h5>
                <p class="text-medium mb-0" style="white-space: pre-wrap;">{{ $project->description ?: 'No description provided.' }}</p>
            </div>
            
            <div class="glass-card" style="border: 1px solid var(--border-main);">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0" style="color: var(--text-high);">Project Tasks</h5>
                    <a href="{{ route('index') }}?project_id={{ $project->id }}" class="btn-premium btn-premium-secondary btn-sm px-3 py-1">View All</a>
                </div>
                
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3 py-2 border-0">Task Title</th>
                                <th class="py-2 border-0">Status</th>
                                <th class="pe-3 py-2 border-0 text-end">Progress</th>
                            </tr>
                        </thead>
                        <tbody style="border: none;">
                            @forelse($project->tasks as $task)
                                <tr class="align-middle" style="border-bottom: 1px solid var(--border-subtle); background: transparent !important;">
                                    <td class="ps-3 py-3" style="border: none;">
                                        <a href="{{ route('details', $task->id) }}" class="fw-bold text-decoration-none" style="color: var(--text-high); font-size: 0.85rem;">{{ $task->title }}</a>
                                    </td>
                                    <td style="border: none;">
                                        <span class="badge-premium" style="background: var(--bg-input); color: var(--text-medium); border: 1px solid var(--border-subtle);">
                                            {{ $task->status->name ?? 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="pe-3 py-3 text-end" style="border: none;">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <div class="progress-premium" style="height: 4px; width: 60px;">
                                                <div class="progress-bar-premium" style="width: {{ $task->progress ?? 0 }}%; background: var(--primary);"></div>
                                            </div>
                                            <span class="text-low small">{{ $task->progress ?? 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-low italic" style="border: none;">No tasks in this project yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="glass-card mb-4" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-4" style="color: var(--text-high);">Project Info</h5>
                
                <div class="mb-3">
                    <div class="text-low small mb-1">Status</div>
                    @php
                        $statusColors = [
                            'active' => 'var(--accent)',
                            'completed' => 'var(--primary)',
                            'on_hold' => '#f59e0b',
                            'archived' => 'var(--text-medium)'
                        ];
                        $color = $statusColors[$project->status] ?? 'var(--text-medium)';
                    @endphp
                    <span class="badge-premium" style="background: color-mix(in srgb, {{ $color }} 15%, transparent); color: {{ $color }}; border: 1px solid color-mix(in srgb, {{ $color }} 30%, transparent); text-transform: capitalize;">
                        {{ str_replace('_', ' ', $project->status) }}
                    </span>
                </div>
                
                <div class="mb-3">
                    <div class="text-low small mb-1">Project Manager</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($project->user)
                            <div class="avatar-premium" style="width: 32px; height: 32px; font-size: 0.8rem; background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                {{ substr($project->user->name, 0, 1) }}
                            </div>
                            <span class="text-high fw-medium">{{ $project->user->name }}</span>
                        @else
                            <span class="text-low italic">Unassigned</span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="text-low small mb-1">Start Date</div>
                    <div class="text-medium">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'Not Set' }}</div>
                </div>
                
                <div>
                    <div class="text-low small mb-1">Deadline</div>
                    <div class="text-medium">{{ $project->deadline ? $project->deadline->format('M d, Y') : 'Not Set' }}</div>
                </div>
            </div>
            
            <div class="glass-card" style="border: 1px solid var(--border-main);">
                <h5 class="fw-bold mb-3" style="color: var(--text-high);">Quick Actions</h5>
                <a href="{{ route('create') }}?project_id={{ $project->id }}" class="btn btn-outline-primary shadow-none w-100 mb-2">
                    <i class="fas fa-plus me-1"></i> Add Task
                </a>
            </div>
        </div>
    </div>
</x-admin>
