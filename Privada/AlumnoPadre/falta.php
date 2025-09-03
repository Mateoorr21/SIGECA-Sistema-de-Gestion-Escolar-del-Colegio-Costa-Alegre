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
$materiasfaltas = consultarasignaturasfaltas(); //Consultamos las asignaturas del alumno

if(isset($_POST['materia'])) { //Si se selelcciona una materia
    $faltas = consultarfaltas(); //Consulta para obtener el horario de la materia seleccionada del alumno
    
}
else $faltas = null; //Si no se selecciona una materia, no se muestran los horarios
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Faltas</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <div class="contenedor">
        <div class="tarjeta">
            
            <div class="titulo">
            <h2>Consultar Faltas</h2>
            </div>
                <!--Formulario para seleccionar la materia-->
                <!--Solo si se tienen materias con faltas-->
                <?php if ($materiasfaltas->num_rows>0): ?>
                <form method="POST" action="">
                   <div class="selecMateria">
                        <label>Selecciona una materia:</label>
                    </div>
                    <?php while ($materia = $materiasfaltas->fetch_assoc()) { ?>
                        <!-- Mostramos cada materia como un botón -->
                        <button type="submit" name="materia" value="<?= $materia['id_asignatura'] ?>" class="boton-opcion">
                        <?php echo $materia['nombre_asignatura']; ?>
                        </button>
                    <?php } ?>
                </form>
                <?php elseif($materiasfaltas->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay faltas registradas.</p>
                <?php endif; ?>
                
                <?php if($faltas): ?> <!--Si se selecciona una materia-->
                <br><table class="tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Materia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filas de la tabla. Una fila por cada falta devuelta en la consulta-->
                        <?php while ($falta = $faltas->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $falta['fecha_falta']; ?></td>
                            <td><?php echo $falta['nombre_asignatura']; ?></td>
                            <!--Usamos la clase y el texto correspondiente de acuerdo con el valor de justificada-->
                            <td  class="<?php echo $falta['justificada'] == '1' ? 'justificada' : 'no-justificada'; ?>">
                            <?php echo $falta['justificada'] == '1' ? 
                                'Justificada' : 
                                'No Justificada'; ?>
                        </tr>
                        <?php } ?>
                </table>
                <?php endif; ?>
            </div>
    </div>
<?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>
</html>