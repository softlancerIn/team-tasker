<x-mail::message>
# You have been assigned a new task!

Hello **{{ $task->user->name }}**,

A new task has been assigned to you. Here are the details:

**Task Title:** {{ $task->title }}  
**Priority:** {{ ucfirst($task->priority) }}  
**Due Date:** {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No due date' }}  

@if($task->project)
**Project:** {{ $task->project->name }}  
@endif

<x-mail::panel>
{{ $task->description ?: 'No description provided.' }}
</x-mail::panel>

<x-mail::button :url="url('/admin/projects?project_id=' . ($task->project_id ?? ''))">
View Task
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
