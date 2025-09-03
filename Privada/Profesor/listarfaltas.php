<?php
//Script de PHP para encontrar los alumnos de un grupo en específico
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 'profesor' && $_SESSION['tipo_usuario'] != 'profesor_sabatino')) {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

include('Funcion.php'); //Usamos el arcihvo de funciones para obtener los datos de la base de datos
$_POST['id_profesor'] = $_SESSION['id_usuario']; //Obtenemos el id del profesor que inicio sesión
$materias = consultamaterias(); //Consulta para obtener las materias del profesor

if(isset($_POST['materia'])) { //Si se selelcciona una materia
    $grupos = consultagrupos(); //Consulta para obtener los grupos de la materia seleccionada
}
else $grupos = null; //Si no se selecciona una materia, no se muestran los grupos


if(isset($_POST['grupo'])) { //Si se selecciona un grupo
    $materia_seleccionada = $_POST['materia']; // Guardamos la materia seleccionada
    $alumnos = consultaralumnosfaltas($materia_seleccionada); //Consulta para obtener los alumnos y sus faltas
}
else $alumnos = null; //Si no se selecciona un grupo, no se muestran los alumnos
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Faltas</title>
    <link rel="stylesheet" href="Estilos/EstilosFunc.css">

</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <div class="contenedor"> 
            <div class="tarjeta">
                <div class="titulo">
                <h2>Listar Faltas de Asistencia</h2>
                </div> 
                <!--Formulario para seleccionar la materia-->
                <!--Solo si se tienen materias y si no se ha seleccionado una materia, mostramos las materias-->
                <?php if (!isset($_POST['materia']) && !isset($_POST['grupo']) && $materias->num_rows>0): ?>
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
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">Maestro no registrado para impartir materias.</p>
                <?php endif; ?>
                
                <!--Si se selecciona una materia y no se ha elegido grupo, mostramos los grupos-->
                <?php if ($grupos && !isset($_POST['grupo'])): ?>
                <!--Formulario para seleccionar el grupo-->
                <form method="POST" action=""> 
                    <!-- Campo de tipo hidden para mantener la materia que se seleccionó -->
                    <input type="hidden" name="materia" value="<?= $_POST['materia'] ?>">
                    <div class="selecGrupo">
                    <label>Selecciona un grupo: </label>
                    </div>
                    <?php while ($grupo = $grupos->fetch_assoc()) { ?>
                        <!-- Mostramos cada grupo como un botón -->
                        <button type="submit" name="grupo" value="<?= $grupo['id_grupo'] ?>" class="boton-opcion">
                        <?php echo $grupo['grado'] ."°".$grupo['grupo'] ; ?>
                        </button>
                    <?php } ?>
                </form>
                <?php endif; ?>
                
                <?php if($alumnos): ?> <!--Si se selecciona un grupo, mostramos los alumnos con sus faltas-->
                <!-- Mostramos la tabla de alumnos en ese grupo -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Número de Faltas</th>    
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada alumno devuelto en la consulta-->
                    <?php while ($alumno = $alumnos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['apellidos']; ?></td>
                            <td><?php echo $alumno['num_faltas']; ?></td>
                        </tr>
                    <?php } ?>
                </table>
                <?php endif; ?>

            </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>