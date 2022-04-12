<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Metal Estructuras - Login</title>

    <link rel="stylesheet" href="{{ asset('vendors/core/core.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/feather-font/css/iconfont.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/flag-icon-css/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/demo_1/style.css') }}">
</head>
<body class="sidebar-dark">
<div class="main-wrapper">
    <div class="page-wrapper full-page">
        <div class="page-content d-flex align-items-center justify-content-center">
            <div class="row w-100 mx-0 auth-page">
                <div class="col-md-8 col-xl-6 mx-auto">
                    <div class="card">
                        @if(Session::has('info'))
                            <div style="background-color: #ff2015" class="alert alert-danger alert-dismissible fade show text-center text-white font-weight-bold" role="alert">
                                {{ Session::get('info') }}
                                <button type="button" class="close" style="color: black; outline: none" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <a href="{{ url('login') }}" class="noble-ui-logo d-block mb-2 text-dark">Metal<span style="color: #9B75A6 !important"> Estructuras</span></a>
                                    <h5 class="text-muted font-weight-normal mb-4">Log in to your Account Here.</h5>
                                    <form method="post" action="{{ url('login') }}" class="forms-sample">
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input value="{{ old('username') }}" type="text" name="username" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" placeholder="Enter Username">
                                            @if ($errors->has('username'))
                                                <span class="text-danger">
                                                    <small class="font-weight-bold">{{ $errors->first('username') }}</small>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter Password">
                                            @if ($errors->has('password'))
                                                <span class="text-danger">
                                                    <small class="font-weight-bold">{{ $errors->first('password') }}</small>
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-3">
                                            <input type="hidden" name="_token" value={{csrf_token()}}>
                                            <button type="submit" style="background-color: #9B75A6 !important; border-color: #9B75A6 !important" class="btn btn-primary mr-2 mb-2 mb-md-0 text-white">Login</button>
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
</div>

<script src="{{ asset('vendors/core/core.js') }}"></script>
<script src="{{ asset('vendors/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('js/template.js') }}"></script>
</body>
</html>