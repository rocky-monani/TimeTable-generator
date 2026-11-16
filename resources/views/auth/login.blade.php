<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Timetable System | Login</title>

  <!-- AdminLTE Styles -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition login-page">

<div class="login-box">

  <!-- Styled Login Card -->
  <div class="card card-outline card-primary">

    <!-- Header -->
    <div class="card-header text-center">
      <a href="{{ url('/') }}" class="h1"><b>Time</b>Table</a>
    </div>

    <!-- Body -->
    <div class="card-body login-card-body">

      <p class="login-box-msg">Sign in to manage your timetable</p>

      <form method="POST" action="/login">
        @csrf

        <!-- Email -->
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <!-- Remember Me -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="icheck-primary">
              <input type="checkbox" id="remember">
              <label for="remember">Remember Me</label>
            </div>
          </div>
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn btn-primary btn-block">
          Login
        </button>

      </form>

    </div>

  </div>

</div>

<!-- Scripts -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

</body>
</html>