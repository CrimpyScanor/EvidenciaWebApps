<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Welcome - Landing Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to app Chidori</h1>
        <p class="lead text-muted">This is the Landing page public to users had no get the authentication</p>
        
        <!-- Botón de Registro (Sign In / Register) -->
        <a href="{{ route('register') }}" class="btn btn-primary m-2">Register</a>

        <!-- Botón de Inicio de Sesión (Log In) -->
        <a href="{{ route('login') }}" class="btn btn-success m-2">Log in</a>
    </div>
</body>
</html>