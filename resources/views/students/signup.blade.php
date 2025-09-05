@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Student Registration') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('student.signup') }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">{{ __('First Name') }}</label>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus>
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label">{{ __('Last Name') }}</label>
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name">
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="middle_name" class="form-label">{{ __('Middle Name') }}</label>
                                <input id="middle_name" type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                       name="middle_name" value="{{ old('middle_name') }}" autocomplete="middle_name">
                                @error('middle_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="student_number" class="form-label">{{ __('Student Number') }}</label>
                                <input id="student_number" type="text" class="form-control @error('student_number') is-invalid @enderror" 
                                       name="student_number" value="{{ old('student_number') }}" required autocomplete="student_number">
                                @error('student_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="section" class="form-label">{{ __('Section') }}</label>
                                <select id="section" class="form-control @error('section') is-invalid @enderror" name="section" required>
                                    <option value="">Select Section</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->section_name }}" {{ old('section') == $section->section_name ? 'selected' : '' }}>
                                            {{ $section->section_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('section')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="specialization" class="form-label">{{ __('Specialization') }}</label>
                                <input id="specialization" type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                       name="specialization" value="{{ old('specialization') }}" required autocomplete="specialization">
                                @error('specialization')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
                                <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       name="phone" value="{{ old('phone') }}" required autocomplete="phone">
                                @error('phone')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">{{ __('Birth Date') }}</label>
                                <input id="birth_date" type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                       name="birth_date" value="{{ old('birth_date') }}" required>
                                @error('birth_date')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">{{ __('Address') }}</label>
                            <textarea id="address" class="form-control @error('address') is-invalid @enderror" 
                                      name="address" rows="3" required>{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                            <input id="password-confirm" type="password" class="form-control" 
                                   name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
