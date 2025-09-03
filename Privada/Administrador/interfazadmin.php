<?php
session_start();
//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) && $_SESSION['tipo_usuario'] != 'administrador') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilos/estilos.css">
    <title>Administrador</title>
</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>

    <div class="contenido">
        <div class="tarjeta">            
            <div class="user">
                <div class="img">
                    <img src="../../Imagenes/LOGO%20COSTA%20ALEGRE.svg" alt="Logo">
                </div>
                <div class="textoUsuario">
                    <p id="usuario">Administrador <?php echo $_SESSION['nombre']." ".$_SESSION['apellidos']?></p><br>
                    <p>Número de Cuenta<br><?php echo $_SESSION['usuario']?></p><br>
                </div>
            </div>

            <div class="funciones">
                <form action="alta_usuario.php"><button class="boton-opcion">Alta de Usuario</button></form>
                <form action="baja_usuario.php"><button class="boton-opcion">Baja de Usuario</button></form>
                <form action="mod_usuario.php"><button class="boton-opcion">Modificar Usuario</button></form>
                <form action="alta_asignatura.php"><button class="boton-opcion">Alta de Asignatura</button></form>
                <form action="baja_asignatura.php"><button class="boton-opcion">Baja de Asignatura</button></form>
                <form action="mod_asignatura.php"><button class="boton-opcion">Modificar Asignatura</button></form>
                <form action="alta_grupo.php"><button class="boton-opcion">Alta de Grupo</button></form>
                <form action="baja_grupo.php"><button class="boton-opcion">Baja de Grupo</button></form>
                <form action="matricular_grupo.php"><button class="boton-opcion">Matricular Grupo</button></form>
                <form action="gestionar_justificante.php"><button class="boton-opcion">Gestionar Justificante</button></form>
                <form action="gestionar_sabatino.php"><button class="boton-opcion">Gestionar Club Sabatino</button></form>
            </div>
        </div>
    </div>
    <?php /* Incluimos archivo footer */ include '../../Publica/principal/footer.php'; ?>
</body>
</html>
