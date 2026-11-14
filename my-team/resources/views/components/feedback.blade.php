@if(session('status'))<p role="status" class="mb-4 rounded-lg border p-3">{{ session('status') }}</p>@endif
@if(session('error'))<p role="alert" class="mb-4 rounded-lg border border-red-500 p-3">{{ session('error') }}</p>@endif
@if($errors->any())
    <ul role="alert" class="mb-4 rounded-lg border border-red-500 p-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
@endif
