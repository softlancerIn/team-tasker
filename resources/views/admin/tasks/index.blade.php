<x-admin>
    <x-slot:title>
        My Tasks | Team Tasker
    </x-slot:title>

    <div class="top-bar">
        <h3 class="mb-0">My Tasks</h3>
        <a href="{{ route('create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New Task
        </a>
    </div>

    <div class="glass-card mt-4">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 bg-transparent">
                <thead>
                    <tr class="text-muted small uppercase">
                        <th class="border-0 px-4">Title</th>
                        <th class="border-0">Assigned To</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Created</th>
                        <th class="border-0 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td class="px-4">
                                <a href="{{ route('details', $task->id) }}"
                                    class="text-white text-decoration-none fw-medium">
                                    {{ $task->title }}
                                </a>
                            </td>
                            <td>
                                @if ($task->assignedTo)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar"
                                            style="width: 24px; height: 24px; font-size: 0.6rem; background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                                            {{ substr($task->assignedTo->name, 0, 1) }}
                                        </div>
                                        <span class="small">{{ $task->assignedTo->name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if ($task->status == 'completed')
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">Completed</span>
                                @elseif($task->status == 'in_progress')
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill">In
                                        Progress</span>
                                @else
                                    <span
                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill">Pending</span>
                                @endif
                            </td>
                            <td class="text-muted"><span class="small text-white">
                                    {{ $task->created_at->format('M d, Y') }} </span></td>
                            <td class="text-end px-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('details', $task->id) }}" class="btn btn-outline-info btn-sm"
                                        title="View Details">
                                        <i class="fas fa-info-circle"></i>
                                    </a>
                                    <a href="{{ route('edit', $task->id) }}" class="btn btn-outline-primary btn-sm"
                                        title="Edit Task">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('delete', $task->id) }}" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete this task?')"
                                        title="Delete Task">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                No tasks found. Start by creating one!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin>
