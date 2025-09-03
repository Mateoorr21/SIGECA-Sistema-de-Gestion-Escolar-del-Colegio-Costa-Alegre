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

//Obtenemos el nombre completo del profesor encargado del club sabatino
$profesorsabatino = consultarprofesorsabatino(); //Consulta para obtener el nombre del profesor encargado del club sabatino

//Obtenemos si el alumno está inscrito al club sabatino
$inscrito_sabatino = estasabatinos(); //Consulta para saber si el alumno está inscrito al club sabatino

//Si esta inscrito a clubes sabatinos, obtenemos las asistencias
if($inscrito_sabatino == 1) { 
    $asistencias = consultarasistenciassabatinos(); //Consulta para obtener las asistencias del alumno al club sabatino
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumno/Padre</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <div class="contenedor">
        <div class="tarjeta">
            <div class="modulo-clubes">
                <div class="header-clubes">
                    <h2 class="titulo-clubes">Asistencia a Clubes Sabatinos</h2>
                </div>
                
                <?php if ($inscrito_sabatino == 1): ?>
                <div class="contenedor-clubes">
                    <!-- Club Sabatino -->
                    <article class="club-card">
                        <div class="club-header">
                            <div class="club-info">
                                <h3 class="club-title">Club Sabatino</h3>
                                <p class="club-instructor">Instructor: <?php echo $profesorsabatino?></p>
                            </div>
                            <span class="club-horario">9:00 - 11:00 hrs</span>
                        </div>
                        
                        <p class="club-description">
                            Desarrollo de habilidades de pensamiento estratégico y resolución de problemas a través del juego y las ciencias.
                        </p>
                        
                        <!--Mostramos la tabla de asistencias si se tiene asistencias registradas a sabatinos-->
                        <?php if ($asistencias->num_rows>0): ?>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Asistencia</th>
                                </tr>
                            </thead>
                            <!-- Filas de la tabla. Una fila por cada asistencia devuelta por la consulta-->
                            <tbody>
                                <?php while ($asistencia = $asistencias->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $asistencia['fecha_asistencia']; ?></td>
                                        <td class="presente">Presente</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <!--Mostramos mensaje si no hay asistencias registradas-->
                        <?php else: ?>
                            <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay asistencias registradas.</p>
                        <?php endif; ?>
                    </article>
                </div>
                <!--Mensaje de no inscripción a clubes sabatinos-->
                <?php else: ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No está registrado a clubes sabatinos.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>