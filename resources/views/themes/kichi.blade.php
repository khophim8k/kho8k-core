@extends('themes::layout')

@section('body')
    <div
        style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; text-align: center; font-family: Arial, sans-serif;">
        <div>
            <h1 style="font-size: 6rem; color: #ff6b6b; font-weight: bold;">404</h1>
            <h2 style="font-size: 2rem; color: #343a40; margin-bottom: 1rem;">Oops! Page Not Found</h2>
            <p style="color: #6c757d; font-size: 1.2rem; margin-bottom: 2rem;">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <a href="/"
                style="padding: 0.75rem 1.5rem; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 0.5rem; font-size: 1rem;">
                Back to Home
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        console.log('404 Page Not Found loaded.');
    </script>
@endpush
