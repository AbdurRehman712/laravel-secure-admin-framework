<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title">Welcome, {{ auth()->user()->name ?? 'User' }}!</h1>
                <p class="card-text">Welcome to your dashboard. Here you can manage your account and access various features.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-person-circle"></i>
                    Your Profile
                </h5>
                <p class="card-text">Update your profile information and manage your account settings.</p>
                <div class="mt-auto">
                    <a href="/profile" class="btn btn-primary">View Profile</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-gear"></i>
                    Settings
                </h5>
                <p class="card-text">Customize your account settings and preferences to suit your needs.</p>
                <div class="mt-auto">
                    <a href="/settings" class="btn btn-secondary">Manage Settings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Account Status</h5>
                <p class="card-text">
                    <span class="badge bg-success fs-6">Active</span>
                </p>
                <small class="text-muted">Member since {{ auth()->user()->created_at ?? now()->format('M Y') }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Last Login</h5>
                <p class="card-text">
                    <strong>{{ now()->format('M d, Y') }}</strong>
                </p>
                <small class="text-muted">{{ now()->format('g:i A') }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="/profile" class="btn btn-outline-light btn-sm">Edit Profile</a>
                    <a href="/settings" class="btn btn-outline-light btn-sm">Settings</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item bg-transparent border-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Account Created</h6>
                            <small>{{ auth()->user()->created_at ?? now()->format('M d') }}</small>
                        </div>
                        <p class="mb-1">Your account was successfully created.</p>
                    </div>
                    
                    <div class="list-group-item bg-transparent border-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Profile Updated</h6>
                            <small>{{ now()->subDays(2)->format('M d') }}</small>
                        </div>
                        <p class="mb-1">You updated your profile information.</p>
                    </div>
                    
                    <div class="list-group-item bg-transparent border-0">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Login</h6>
                            <small>{{ now()->format('M d') }}</small>
                        </div>
                        <p class="mb-1">You logged into your account.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
