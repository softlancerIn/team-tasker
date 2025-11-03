<x-layout>
    <x-slot:title>
        Edit Team Tasker
    </x-slot:title>
    <form action="{{ route('update') }}" method="post" class="mt-4 p-4">
        @csrf
        <input type="hidden" name="id" value="{{ $todo->id }}">
        <div class="form-group m-3">
            <label for="name">Todo Title</label>
            <input type="text" class="form-control" name="title" value="{{ $todo->title }}">
            @if($errors->has('title'))
                <div class="error">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group m-3">
            <label for="description">Todo Description</label>
            <textarea class="form-control" rows="3" name="description">{{ $todo->description }}</textarea>
            @if($errors->has('description'))
                <div class="error">{{ $errors->first('description') }}</div>
            @endif
        </div>
        <div class="form-group m-3">
            <input type="submit" class="btn btn-primary float-end" value="Update">
        </div>
    </form>
</x-layout>
