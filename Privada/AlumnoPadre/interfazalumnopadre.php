<?php
session_start();
//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != 'alumno') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/Login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Estilos/estilos.css">
    <title>Alumno/Padre</title>
</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- Creamos nuestra cabecera -->
     <div class="contenido">
        <div class="tarjeta">      
            <div class="user">
            <div class="img">
                <img src="../../Imagenes/LOGO%20COSTA%20ALEGRE.svg" alt="">
            </div>
            <div class="textoUsuario">
                <p id="usuario">Alumno<br><?php echo $_SESSION['nombre']." ".$_SESSION['apellidos']?></p><br>
                <p>Número de Cuenta<br><?php echo $_SESSION['usuario']?></p><br>
                <p>Grado y Grupo<br><?php echo $_SESSION['grado']." ° ".$_SESSION['grupo']?></p><br>
            </div>
            </div>
            
            <div class="funciones">
        
                <form action="horario.php"><button class="boton-opcion">Horario de Clase</button></form>
                <form action="profesores.php"><button class="boton-opcion">Profesores de Clase</button></form>
                <form action="compañeros.php"><button class="boton-opcion">Compañeros de Clase</button></form>
                <form action="calificaciones.php"><button class="boton-opcion">Calificaciones</button></form>
                <form action="solicitud.php"><button class="boton-opcion">Solicitud de Justificantes</button></form>
                <form action="verificacion.php"><button class="boton-opcion">Estado de Justificante</button></form>
                <form action="falta.php"><button class="boton-opcion">Faltas de Asistencia</button></form>
                <form action="clubes.php"><button class="boton-opcion">Asistencias a clubes sabatinos</button></form>

            </div>
        </div>
     </div>
   <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>