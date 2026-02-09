<x-admin>
    <x-slot:title>
        Search Results | Team Tasker
    </x-slot:title>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Search Results for "{{ $query }}"</h3>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    @if ($tasks->count() > 0)
        <h5 class="mb-3">Tasks ({{ $tasks->count() }})</h5>
        <div class="row g-4 mb-5">
            @foreach ($tasks as $task)
                <div class="col-md-6 col-lg-4">
                    <div class="glass-card h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span
                                class="badge bg-{{ $task->status->color ?? 'secondary' }} bg-opacity-25 text-{{ $task->status->color ?? 'secondary' }} border border-{{ $task->status->color ?? 'secondary' }} border-opacity-25">
                                {{ $task->status->name ?? 'Unknown' }}
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-link text-white p-0" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
                                    <li><a class="dropdown-item text-white"
                                            href="{{ route('details', $task->id) }}">View Details</a></li>
                                    <li><a class="dropdown-item text-white" href="{{ route('edit', $task->id) }}">Edit
                                            Task</a></li>
                                </ul>
                            </div>
                        </div>
                        <h5 class="mb-2"><a href="{{ route('details', $task->id) }}"
                                class="text-white text-decoration-none">{{ $task->title }}</a></h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            {{ Str::limit(strip_tags($task->description), 100) }}</p>

                        <div
                            class="mt-auto pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                            <div class="d-flex -align-items-center gap-2">
                                <div class="avatar" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                    {{ substr($task->assignedTo->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="small text-muted">{{ $task->assignedTo->name ?? 'Unassigned' }}</span>
                            </div>
                            <span class="small text-muted">{{ $task->created_at->format('M d') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-secondary bg-transparent border-secondary text-center py-4 mb-4">
            <i class="fas fa-tasks fa-2x mb-3 text-muted"></i>
            <p class="mb-0 text-muted">No tasks found matching "{{ $query }}".</p>
        </div>
    @endif

    @if (Auth::user()->hasPermission('users.view') && $users->count() > 0)
        <h5 class="mb-3">Users ({{ $users->count() }})</h5>
        <div class="row g-4">
            @foreach ($users as $user)
                <div class="col-md-6 col-lg-3">
                    <div class="glass-card text-center p-4">
                        <div class="avatar mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem;">
                            @if ($user->profile_image)
                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt=""
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <h6 class="mb-1">{{ $user->name }}</h6>
                        <p class="text-muted small mb-2">{{ $user->email }}</p>
                        <span
                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">
                            {{ $user->role->name ?? 'No Role' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(Auth::user()->hasPermission('users.view') && $query)
        <div class="alert alert-secondary bg-transparent border-secondary text-center py-4">
            <i class="fas fa-users fa-2x mb-3 text-muted"></i>
            <p class="mb-0 text-muted">No users found matching "{{ $query }}".</p>
        </div>
    @endif

</x-admin>
