<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Account Settings</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Preferences</h5>
                            
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-control @error('timezone') is-invalid @enderror" 
                                        id="timezone" 
                                        name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Denver">Mountain Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
                                </select>
                                
                                @error('timezone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="language" class="form-label">Language</label>
                                <select class="form-control @error('language') is-invalid @enderror" 
                                        id="language" 
                                        name="language">
                                    <option value="en">English</option>
                                    <option value="es">Spanish</option>
                                    <option value="fr">French</option>
                                    <option value="de">German</option>
                                </select>
                                
                                @error('language')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="theme" class="form-label">Theme</label>
                                <select class="form-control @error('theme') is-invalid @enderror" 
                                        id="theme" 
                                        name="theme">
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                    <option value="auto">Auto</option>
                                </select>
                                
                                @error('theme')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Notifications</h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="email_notifications" 
                                           name="email_notifications" 
                                           value="1">
                                    <label class="form-check-label" for="email_notifications">
                                        Email Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Receive notifications via email</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="sms_notifications" 
                                           name="sms_notifications" 
                                           value="1">
                                    <label class="form-check-label" for="sms_notifications">
                                        SMS Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Receive notifications via SMS</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="marketing_emails" 
                                           name="marketing_emails" 
                                           value="1">
                                    <label class="form-check-label" for="marketing_emails">
                                        Marketing Emails
                                    </label>
                                </div>
                                <small class="text-muted">Receive promotional emails and updates</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="newsletter" 
                                           name="newsletter" 
                                           value="1">
                                    <label class="form-check-label" for="newsletter">
                                        Newsletter
                                    </label>
                                </div>
                                <small class="text-muted">Subscribe to our newsletter</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Privacy</h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="profile_public" 
                                           name="profile_public" 
                                           value="1">
                                    <label class="form-check-label" for="profile_public">
                                        Public Profile
                                    </label>
                                </div>
                                <small class="text-muted">Make your profile visible to other users</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="show_email" 
                                           name="show_email" 
                                           value="1">
                                    <label class="form-check-label" for="show_email">
                                        Show Email
                                    </label>
                                </div>
                                <small class="text-muted">Display your email address on your profile</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3">Security</h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="two_factor" 
                                           name="two_factor" 
                                           value="1">
                                    <label class="form-check-label" for="two_factor">
                                        Two-Factor Authentication
                                    </label>
                                </div>
                                <small class="text-muted">Enable 2FA for enhanced security</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="login_alerts" 
                                           name="login_alerts" 
                                           value="1">
                                    <label class="form-check-label" for="login_alerts">
                                        Login Alerts
                                    </label>
                                </div>
                                <small class="text-muted">Get notified of new login attempts</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
