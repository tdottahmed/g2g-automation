<x-layouts.admin.guest>
    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                            <div class="row g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <a href="/" class="d-block">
                                                    <img src="/assets/admin/images/logo-light.png" alt=""
                                                        height="18">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">{{ __('Welcome !') }}</h5>
                                            <p class="text-muted"> {{ __('Sign up to continue.') }}</p>
                                        </div>

                                        <div class="mt-4">
                                            <form action="{{ route('register') }}" method="POST">
                                                @csrf
                                                <x-data-entry.input type="text" name="name" label="Name"
                                                    type="text" placeholder="Name" required />
                                                <x-data-entry.input type="email" name="email" label="Email"
                                                    type="email" placeholder="Email" required />

                                                <x-data-entry.input type="password" name="password" label="Password"
                                                    type="password" placeholder="Password" required />

                                                <x-data-entry.input type="password" name="password_confirmation"
                                                    label="Confirm Password" type="password"
                                                    placeholder="confirm Password" required />

                                                <x-data-entry.input type="checkbox" name="remember"
                                                    label="Remember Me" />

                                                <div class="mt-4">
                                                    <button class="btn btn-success w-100"
                                                        type="submit">{{ __('Sign Up') }}</button>
                                                </div>

                                                <div class="mt-4 text-center">
                                                    <div class="signin-other-title">
                                                        <h5 class="fs-13 mb-4 title">{{ __('Sign Up using') }}</h5>
                                                    </div>

                                                    <div>
                                                        <button type="button"
                                                            class="btn btn-primary btn-icon waves-effect waves-light"><i
                                                                class="ri-facebook-fill fs-16"></i></button>
                                                        <button type="button"
                                                            class="btn btn-danger btn-icon waves-effect waves-light"><i
                                                                class="ri-google-fill fs-16"></i></button>
                                                        <button type="button"
                                                            class="btn btn-dark btn-icon waves-effect waves-light"><i
                                                                class="ri-github-fill fs-16"></i></button>
                                                        <button type="button"
                                                            class="btn btn-info btn-icon waves-effect waves-light"><i
                                                                class="ri-twitter-fill fs-16"></i></button>
                                                    </div>
                                                </div>

                                            </form>
                                        </div>

                                        <div class="mt-5 text-center">
                                            <p class="mb-0">{{ __('Already have an account ?') }} <a
                                                    href="{{ route('login') }}"
                                                    class="fw-semibold text-primary text-decoration-underline">
                                                    {{ __('Login') }}</a> </p>
                                        </div>
                                    </div>
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->

                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->
        <!-- footer -->
        <footer class="footer galaxy-border-none">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0">&copy;
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>Crafted with <i class="mdi mdi-heart text-danger"></i>
                                by Tanbir
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
</x-layouts.admin.guest>
