<x-layout>
    <x-slot:title>
        Details Team Tasker
    </x-slot:title>
    <div class="card text-center mt-5">
        <div class="card-header">
            <b>TODO DETAILS</b>
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $todo->title }}</h5>
            <p class="card-text">{{ $todo->description }}</p>
            <a href="{{ route('edit', ['id' => $todo->id]) }}"><span class="btn btn-primary">Edit</span></a>
            <a href="{{ route('delete', ['id' => $todo->id]) }}"><span class="btn btn-danger">Delete</span></a>
        </div>
    </div>
</x-layout>
