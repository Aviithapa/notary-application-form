@if (session('success'))
    <div class="npc__auth-alert npc__auth-alert--success" role="status">
        <i class="fa-solid fa-circle-check"></i>
        <div>
            <p>{{ session('success') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="npc__auth-alert" role="alert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif
