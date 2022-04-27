<header class="d-print-none">
    <div class="px-3 py-2 navbar-dark bg-primary">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="{{ url('/') }}"
                    class="d-block my-2 my-lg-0 me-lg-auto text-decoration-none @if (request()->segment(1) == null) text-white @else text-white-50 @endif">
                    <i class="bi bi-house-fill d-block text-center" style="font-size: 1rem;"></i>
                    Lumen Weather79
                </a>
                <form class="d-flex ms-4">
                    <input class="form-control me-2" type="search" placeholder="Search" id="search" aria-label="Search">
                    <button class="btn btn-success" type="submit">Reset</button>
                </form>
            </div>
        </div>
    </div>
</header>
