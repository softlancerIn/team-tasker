<x-layout>
    <x-slot:title>
        Index Team Tasker
    </x-slot:title>
    <div class="row mt-3">
        <div class="col-12 align-self-center">
            <ul class="list-group">
                @foreach ($todos as $todo)
                    <li class="list-group-item">
                        <a href="{{ route('details', ['id' => $todo->id]) }}">{{ $todo->title }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-layout>
