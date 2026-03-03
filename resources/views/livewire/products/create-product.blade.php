<div>
  {{-- Flash --}}
  @if(session('error'))
    <div style="margin-bottom:16px;padding:10px 14px;border-radius:var(--r);
                background:var(--red-dim);color:var(--red);font-size:13px;font-weight:600">
      {{ session('error') }}
    </div>
  @endif

  @include('livewire.products._form', ['mode' => 'create'])
</div>
