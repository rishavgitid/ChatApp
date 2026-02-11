<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">CHATApp</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item guest-only">
                    <a class="nav-link text-white" href="/login">Login</a>
                </li>
                <li class="nav-item guest-only">
                    <a class="btn btn-light text-primary fw-bold" href="/register">Register</a>
                </li>
                <li class="nav-item auth-only d-none">
                    <a class="nav-link position-relative text-white" href="/chat">
                        <i class="bi bi-chat-dots-fill"></i> Messages
                        <span id="global-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                            0
                        </span>
                    </a>
                </li>
                <li class="nav-item auth-only d-none ms-3">
                    <button class="btn btn-outline-light btn-sm" id="logout-btn">Logout</button>
                </li>
            </ul>
        </div>
    </div>
</nav>
