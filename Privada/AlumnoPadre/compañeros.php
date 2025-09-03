<?php
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();
include('funcion.php'); //Incluimos el archivo de funciones

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != 'alumno') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

$alumnos = consultaralumnos(); //Consultamos los alumnos
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Compañeros</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
        <div class="contenedor">

            <div class="tarjeta">
            <div class="titulo">
            <h2>Compañeros de Clase</h2>
            </div>
            <!--Solo si se esta inscrito a un grupo-->
            <?php if($alumnos->num_rows>0): ?>
             <!-- Mostramos la tabla de alumnos del grupo del alumno -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                         <th>Apellidos</th>
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada alumno devuelto en la consulta-->
                    <?php while ($alumno = $alumnos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['apellidos']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            <?php elseif($alumnos->num_rows==0): ?>
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">El alumno no inscrito a ningún grupo.</p>
            <?php endif; ?>     
            </div>
        </div> 
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>