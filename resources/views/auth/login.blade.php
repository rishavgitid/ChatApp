@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                <h4 class="fw-bold text-primary">Login</h4>
            </div>
            <div class="card-body p-4">
                <form id="login-form">
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted">Email Address</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-muted">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                    <div class="text-center">
                        <a href="/register" class="text-decoration-none">Create an account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="module">
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        try {
            const response = await axios.post('/api/login', { email, password });
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            window.location.href = '/chat';
        } catch (error) {
            alert('Login Failed: ' + (error.response?.data?.message || 'Error'));
        }
    });
</script>
@endsection
