<x-layout>
    <x-slot:title>
        Create Team Tasker
    </x-slot:title>
    <form action="{{ route('store') }}" method="post" class="mt-4 p-4">
        @csrf
        <div class="form-group m-3">
            <label for="name">Todo Title</label>
            <input type="text" class="form-control" name="title">
            @if($errors->has('title'))
                <div class="error">{{ $errors->first('title') }}</div>
            @endif
        </div>
        <div class="form-group m-3">
            <label for="description">Todo Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
            @if($errors->has('description'))
                <div class="error">{{ $errors->first('description') }}</div>
            @endif
        </div>
        <div class="form-group m-3">
            <input type="submit" class="btn btn-primary float-end" value="Submit">
        </div>
    </form>
</x-layout>
