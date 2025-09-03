<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="loginEstilo.css">
</head>
<nav>
    <div class="sigeca">
    <h1>
        SIGECA
    </h1>
    </div>
    
    <ul>
        <li>
            <form action="..\..\Publica\principal\principal.php">
                <button>Home</button>
            </form>
        </li>
    </ul>
</nav>
<body>
    <form action="Funcionlogin.php" method="POST">
    <div class="glass_login">
        <h1>Login</h1>
            <div class="info">
                <input type="text" name="usuario" placeholder="Usuario" require>
                <input type="password" name="contrasenia" placeholder="Contraseña" require>
            </div>
        <br><br><br>
            <div class="btnCaja">
                <button type="submit" class="btn">Enviar</button>
            </div>
    </div>
    </form>
</body>
</html>