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
$materias = consultarasignaturas(); //Consultamos las asignaturas del alumno

if(isset($_POST['materia'])) { //Si se selelcciona una materia
    $calificaciones = consultarcalificaciones(); //Consulta para obtener el horario de la materia seleccionada del alumno
    
}
else $calificaciones = null; //Si no se selecciona una materia, no se muestran los horarios
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Calificaciones</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <div class="contenedor">
        <div class="tarjeta">
                <div class="titulo">
                    <h2>Consultar Calificaciones</h2>
                </div>

                <!--Formulario para seleccionar la materia-->
                <!--Solo si se tienen materias-->
                <?php if ($materias->num_rows>0): ?>
                <form method="POST" action="">
                   <div class="selecMateria">
                        <label>Selecciona una materia:</label>
                    </div>
                    <?php while ($materia = $materias->fetch_assoc()) { ?>
                        <!-- Mostramos cada materia como un botón -->
                        <button type="submit" name="materia" value="<?= $materia['id_asignatura'] ?>" class="boton-opcion">
                        <?php echo $materia['nombre_asignatura']; ?>
                        </button>
                    <?php } ?>

                    <!--Boton para ver calificaciones de todas las materias-->
                    <button type="submit" name="materia" value="0" class="boton-opcion">General</button>
                </form>
                <?php elseif($materias->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">Alumno no inscrito en el semestre.</p>
                <?php endif; ?>
                
                <?php if($calificaciones): ?> <!--Si se selecciona una materia-->
                <br>
                <div class="tablita">
                <table class="tabla">
                    <thead>
                        <tr>   
                            <th>Asignatura</th>
                            <th>Parcial 1</th>
                            <th>Parcial 2</th>
                            <th>Parcial 3</th>  
                            <th>Promedio</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de la tabla. Una fila por cada materia en la consulta-->
                        <?php foreach ($calificaciones as $id_materia => $asignatura) { ?>
                        <tr>
                            <td><?php echo $asignatura['nombre_asignatura']; ?></td>
                            <!--Damos formato a los decimales, si es 0.0 lo mostramos como 0-->
                            <td><?php echo ($asignatura['parcial_1'] == 0.0 || $asignatura['parcial_1'] == null) ? '0' : $asignatura['parcial_1']; ?></td>
                            <td><?php echo ($asignatura['parcial_2'] == 0.0 || $asignatura['parcial_2'] == null) ? '0' : $asignatura['parcial_2']; ?></td>
                            <td><?php echo ($asignatura['parcial_3'] == 0.0 || $asignatura['parcial_3'] == null) ? '0' : $asignatura['parcial_3']; ?></td>
                            <td><?php echo $asignatura['promedio']; ?></td>
                        </tr>
                        <?php } ?>                      
                    </tbody>
                </table>

                </div>
                <?php endif; ?>
                </div>
        </div>
    </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>