@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
                <h4 class="fw-bold text-primary">Register</h4>
            </div>
            <div class="card-body p-4">
                <form id="register-form">
                    <div class="mb-3">
                        <label for="name" class="form-label text-muted">Name</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted">Email Address</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label text-muted">Password</label>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label text-muted">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                    <div class="text-center">
                        <a href="/login" class="text-decoration-none">Already have an account?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="module">
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const password_confirmation = document.getElementById('password_confirmation').value;
        
        try {
            const response = await axios.post('/api/register', { 
                name, email, password, password_confirmation 
            });
            localStorage.setItem('token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            window.location.href = '/chat';
        } catch (error) {
            alert('Register Failed: ' + (error.response?.data?.message || 'Error'));
        }
    });
</script>
@endsection
