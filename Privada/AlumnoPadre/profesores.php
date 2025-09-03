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

//Consultamos las asignaturas y los profesores que imparten esas asignaturas
$asignaturas_profesores = consultarasignaturasprofesores();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Profesores</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
        <div class="contenedor">
            <div class="tarjeta">
                 <div class="titulo">
            <h2>Profesores de Clase</h2>
            </div>
            
            <!--Solo si se tiene clases asignadas-->
            <?php if($asignaturas_profesores->num_rows>0): ?>
            <!-- Mostramos la tabla de asignaturas del alumno y el profesor que la imparte -->
            <table class="tabla">          
                <!-- Encabezado de la tabla -->
                <tr> 
                    <th>Asignatura</th>
                    <th>Profesor</th>
                </tr>
                <!-- Filas de la tabla. Una fila por cada asignatura devuelta por la consulta-->
                <?php while ($asignatura_profesor = $asignaturas_profesores->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $asignatura_profesor['nombre_asignatura']; ?></td>
                        <td><?php echo $asignatura_profesor['nombre']. " " .$asignatura_profesor['apellidos']; ?></td>
                    </tr>
                <?php } ?>
            </table>
            <?php elseif($asignaturas_profesores->num_rows==0): ?>
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">El alumno no está matriculado en ninguna asignatura.</p>
            <?php endif; ?>    
            </div>
            </div>
           
        </div>    
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>

</body>
</html>