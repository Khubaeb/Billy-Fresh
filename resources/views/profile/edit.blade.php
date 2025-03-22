@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="h3 mb-4">{{ __('Profile') }}</h1>

            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">{{ __('Profile Information') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        {{ __("Update your account's profile information and email address.") }}
                    </p>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }}</label>
                            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $user->email) }}" required autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-muted">
                                        {{ __('Your email address is unverified.') }}
                                        <button form="send-verification" class="btn btn-link p-0 m-0 align-baseline text-decoration-underline">
                                            {{ __('Click here to re-send the verification email.') }}
                                        </button>
                                    </p>

                                    @if (session('status') === 'verification-link-sent')
                                        <div class="alert alert-success mt-2">
                                            {{ __('A new verification link has been sent to your email address.') }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

                            @if (session('status') === 'profile-updated')
                                <div class="text-success ms-3">{{ __('Saved.') }}</div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">{{ __('Update Password') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        {{ __('Ensure your account is using a long, random password to stay secure.') }}
                    </p>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                            <input id="current_password" name="current_password" type="password" 
                                class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('New Password') }}</label>
                            <input id="password" name="password" type="password" 
                                class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" 
                                class="form-control" autocomplete="new-password">
                        </div>

                        <div class="d-flex align-items-center">
                            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

                            @if (session('status') === 'password-updated')
                                <div class="text-success ms-3">{{ __('Saved.') }}</div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h2 class="h5 mb-0">{{ __('Delete Account') }}</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
                    </p>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmAccountDeletionModal">
                        {{ __('Delete Account') }}
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="confirmAccountDeletionModal" tabindex="-1" aria-labelledby="confirmAccountDeletionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" action="{{ route('profile.destroy') }}">
                                    @csrf
                                    @method('delete')
                                    
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmAccountDeletionModalLabel">{{ __('Are you sure you want to delete your account?') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <div class="modal-body">
                                        <p class="mb-3">
                                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                                        </p>
                                        
                                        <div class="mb-3">
                                            <label for="password" class="form-label">{{ __('Password') }}</label>
                                            <input id="password" name="password" type="password" class="form-control" placeholder="{{ __('Password') }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                        <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
