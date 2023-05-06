<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="XG Proyect | Admin CP">
    <meta name="author" content="XG Proyect">

    <title>XG Proyect | Admin CP</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('admin/css/admin.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5" style="top: 50%;">
                    <div class="card-body p-0 bg-login-image">
                        <!-- Nested Row within Card Body -->
                        <div class="row" style="background-color: rgba(0,0,0,0.2);">
                            <div class="col-lg-6 d-none d-lg-block my-auto text-center">
                                <img src="https://xgproyect.org/wp-content/uploads/2019/10/xgp-new-logo-white.png"
                                    alt="XG Proyect Logo" title="XG Proyect" width="250px">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-white mb-4">{{ __('admin/login.lg_welcome_back') }}</h1>
                                    </div>
                                    <form class="user" method="post" action="admin.php?page=login&redirect={{ $redirect }}">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user" id="inputEmail"
                                                name="inputEmail" aria-describedby="emailHelp"
                                                placeholder="{{ __('admin/login.lg_enter_email_address') }}">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="inputPassword" name="inputPassword" placeholder="{{ __('admin/login.lg_password') }}">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block"
                                            name="signin">{{ __('admin/login.lg_login') }}</button>
                                    </form>
                                    <br>
                                    <x-alert :dismissible=false/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>