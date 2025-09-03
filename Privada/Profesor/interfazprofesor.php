<?php
//Script de PHP para encontrar los alumnos de un grupo en específico
if (session_status() === PHP_SESSION_NONE) session_start();

//Si no tenemos el usuario o si el usuario no es profesor lo mandamos el login
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 'profesor' && $_SESSION['tipo_usuario'] != 'profesor_sabatino')) {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilos/estilos.css">
    <title>Profesor - SIGECA</title>
</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- CONTENIDO DEL PROFESOR -->
     <div class="contenido">
        <div class="tarjeta">            
            <div class="user">
                <div class="img">
                    <img src="../../Imagenes/LOGO%20COSTA%20ALEGRE.svg" alt="">
                </div>
                <div class="textoUsuario">
                    <p id="usuario">Profesor <br><?php echo $_SESSION['nombre']." ".$_SESSION['apellidos']?></p><br>
                    <p>Número de cuenta: <br><?php echo $_SESSION['usuario']?></p>
                </div>
            </div>
            
            <div class="funciones">
                 <form action="listaralumnos.php"><button class="boton-opcion">Listar Alumnos</button></form>
                <form action="profesorescosta.php"><button class="boton-opcion">Profesores de Costalegre</button></form>
                <form action="asignarcalificaciones.php"><button class="boton-opcion">Asignar Calificaciones</button></form>
                <form action="modificarcalificaciones.php"><button class="boton-opcion">Modificar Calificaciones</button></form>
                <form action="asignarfalta.php"><button class="boton-opcion">Asignar Falta de Asistencia</button></form>
                <form action="modificarfalta.php"><button class="boton-opcion">Modificar Falta de Asistencia</button></form>
                <form action="listarfaltas.php"><button class="boton-opcion">Listar Faltas de Asistencia</button></form>

                <?php if($_SESSION['tipo_usuario'] == 'profesor_sabatino'): ?> <!--Solo si es profesor sabatino-->
                    <form action="asistenciaclubes.php"><button class="boton-opcion">Registrar Asistencia a Clubes Sabatinos</button></form>
                <?php endif; ?>
            </div>
        </div>
     </div>

    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>