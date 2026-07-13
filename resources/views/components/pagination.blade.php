@if($items->hasPages())

    <div class="d-flex justify-content-center mt-3">

        {{ $items->links() }}

    </div>

@endif
