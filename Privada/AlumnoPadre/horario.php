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
    $horarios = consultarhorario(); //Consulta para obtener el horario de la materia seleccionada del alumno
    
}
else $horarios = null; //Si no se selecciona una materia, no se muestran los horarios
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Horario</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <div class="contenedor">
        <div class="tarjeta">
                <div class="titulo">
                    <h2>Horario</h2>
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
                </form>
                <?php elseif($materias->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">El alumno no está matriculado en ninguna asignatura.</p>
                <?php endif; ?>
                
                <?php if($horarios): ?> <!--Si se selecciona una materia-->
                <br>
                <table class="tabla">
                    <thead>
                        <tr>   
                            <th>Asignatura</th>
                            <th>Día</th>
                            <th>Hora de Inicio</th>
                            <th>Hora de Fin</th>   
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de la tabla. Una fila por cada horario devuelto en la consulta-->
                        <?php while ($horario = $horarios->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $horario['nombre_asignatura']; ?></td>
                            <td><?php echo $horario['dia']; ?></td>
                            <!--Usamos date para darle formato a la hora-->
                            <td><?php echo date("G:i", strtotime($horario['horaInicio']));; ?></td>
                            <td><?php echo date("G:i", strtotime($horario['horaFin'])); ?></td>
                        </tr>
                        <?php } ?>                      
                    </tbody>
                </table>
                <?php endif; ?>
                </div>
        </div>
    </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>
</html>