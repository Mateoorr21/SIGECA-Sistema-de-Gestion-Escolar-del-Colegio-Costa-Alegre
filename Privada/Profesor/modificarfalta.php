<?php
//Script de PHP para encontrar los alumnos de un grupo en específico
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();

//Recuperamos los valores desde GET para poder mantener la seleccion de materia y grupo
//Esto es útil para evitar que el formulario se envíe nuevamente al recargar la página
if (isset($_GET['materia'])) {
    $_POST['materia'] = $_GET['materia'];
}
if (isset($_GET['grupo'])) {
    $_POST['grupo'] = $_GET['grupo'];
}

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 'profesor' && $_SESSION['tipo_usuario'] != 'profesor_sabatino')) {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

include('Funcion.php'); //Usamos el arcihvo de funciones para obtener los datos de la base de datos

$_POST['id_profesor'] = $_SESSION['id_usuario']; //Obtenemos el id del profesor que inicio sesión

// Lineas de codigo para restaurar el valor de materia y gruop una vez se ha registrado falta
// Si se ha enviado el formulario para asignar una falta...
if (isset($_POST['accion']) && $_POST['accion'] === 'modificarfalta') {
    // Restauramos el valor de la materia seleccionada para que el formulario la conserve
    $_POST['materia'] = $_POST['id_materia'];
    // Restauramos el valor del grupo seleccionado para que también se conserve
    $_POST['grupo'] = $_POST['id_grupo'];
}

$materias = consultamaterias(); //Consulta para obtener las materias del profesor

if(isset($_POST['materia'])) { //Si se selelcciona una materia
    $grupos = consultagrupos(); //Consulta para obtener los grupos de la materia seleccionada
}
else $grupos = null; //Si no se selecciona una materia, no se muestran los grupos


if(isset($_POST['grupo'])) { //Si se selecciona un grupo
    $materia_seleccionada = $_POST['materia']; // Guardamos la materia seleccionada
    $alumnosfaltas = consultarfaltasporalumno($materia_seleccionada); //Consulta para obtener los alumnos y sus faltas
}
else $alumnosfaltas = null; //Si no se selecciona un grupo, no se muestran los alumnos

// Procesar el formulario si se envió
if (isset($_POST['accion']) && $_POST['accion'] == 'justificarfalta') { 
    // Aquí llamas la función que justifica la falta
    $_SESSION['mensaje'] = justificarfalta($_POST['id_falta']); // Justificamos la falta
    // Redirigir a la misma página para evitar reenviar el formulario
    header("Location: ".$_SERVER['PHP_SELF']."?materia=".$_POST['id_materia']."&grupo=".$_POST['id_grupo']);
    exit;
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Faltas</title>
    <link rel="stylesheet" href="Estilos/EstilosFunc.css">

</head>


<script> //Script de php para mostrar el formulario de asignar falta
function mostrarFormulario(idAlumno) {
    //Obtenemos el formulario de asignar falta para el alumno específico
    var filas = document.querySelectorAll(".falta-" + idAlumno);
    //Ciclo for para recorrer el registro de cada falta, cambiamos la visibilidad
    //Para cada fila que tenga la clase falta-idAlumno cambiamos visibilidad
    filas.forEach(function(fila) {
        fila.style.display = (fila.style.display === "none") ? "table-row" : "none";
    });
}
</script>


<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- Cuadro de diálogo personalizado -->
    <div id="dialogo-mensaje" style="display: <?= isset($_SESSION['mensaje']) ? 'flex' : 'none' ?>;" class="dialogo-overlay"> <!--Display none para que este oculto-->
        <div class="dialogo-contenido">
            <p><?= $_SESSION['mensaje'] ?></p> <!-- Mensaje de éxito o error -->
            <?php unset($_SESSION['mensaje']); ?> <!-- Limpiamos el mensaje después de mostrarlo -->
            <button onclick="cerrarDialogo()">Aceptar</button> <!-- Al dar click se oculta de nuevo el cuadro de dialogo-->
        </div>
    </div>

    <div class="contenedor">

            <div class="tarjeta">
                <!--Formulario para seleccionar la materia-->
                <!--Solo si se tienen materias y si no se ha seleccionado una materia, mostramos las materias-->
                <div class="titulo">
                    <h2>Modificar Faltas de Asistencia</h2>
                </div>  
                <?php if (!isset($_POST['materia']) && !isset($_POST['grupo']) && $materias->num_rows>0): ?>
                <form method="POST" action="">
                   <div class="selecMateria">
                    <div class="selecGrupo">
                    <label>Selecciona un grupo: </label>
                    </div>                    </div>                    <?php while ($materia = $materias->fetch_assoc()) { ?>
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
                    </div>                    <?php while ($grupo = $grupos->fetch_assoc()) { ?>
                        <!-- Mostramos cada grupo como un botón -->
                        <button type="submit" name="grupo" value="<?= $grupo['id_grupo'] ?>" class="boton-opcion">
                        <?php echo $grupo['grado'] ."°".$grupo['grupo'] ; ?>
                        </button>
                    <?php } ?>
                </form>
                <?php endif; ?>
                
                <!--Si se selecciona un grupo y hay faltas por modificar, mostramos los alumnos con sus faltas-->
                <?php if($alumnosfaltas && $alumnosfaltas->num_rows>0): ?> 
                <!-- Mostramos la tabla de alumnos en ese grupo -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Modificar Falta</th>    
                    </tr>
                    <?php $ultimoAlumno = null; // Variable para almacenar el último alumno procesado
                    // Filas de la tabla. Una fila por cada alumno devuelto en la consulta
                    while ($alumno = $alumnosfaltas->fetch_assoc()) { 
                        //Verificamos si el alumno actual es uno nuevo
                        if ($alumno['id_usuario'] != $ultimoAlumno) { 
                            $ultimoAlumno = $alumno['id_usuario']; // Actualizamos el último alumno ?>
                        <!-- Fila de la tabla para mostrar el nombre y apellidos del alumno -->
                        <tr id="fila-alumno" style="background-color: rgba(144, 251, 255, 0.452)">
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['apellidos']; ?></td>
                            <td>
                                <!-- Botón que muestra el formulario usando JavaScript -->
                                <button class="boton-opcion" type="button" style="width:95%;" onclick="mostrarFormulario('<?php echo $alumno['id_usuario']; ?>')">MODIFICAR FALTA</button>
                            </td>
                        </tr>
                        <?php } ?> <!--Fin del if para ver si el alumnoe es nuevo-->
                        <!-- Fila de cada falta en display none y con id para que se pueda mostrar al dar click -->

                        <tr class="falta-<?php echo $alumno['id_usuario'];?>" style="display: none; background-color: white;" >
                            <td colspan="2" style="padding-left: 30px;">
                                <span>Falta:  
                                    <span style="margin: 0 10px;">
                                        <?php echo $alumno['fecha_falta']; ?>
                                    </span>
                                </span></td>
                            <td>
                                <form method="POST" action="" style="width:95%;">
                                    <input type="hidden" name="accion" value="justificarfalta">
                                    <!--Input hidden para guardar el id de la falta a justificar-->
                                    <input type="hidden" name="id_falta" value="<?php echo $alumno['id_falta']; ?>">
                                    <!--Input hidden de materia y grupo para al justificar se regrese a la pagina-->
                                    <input type="hidden" name="id_materia" value="<?php echo $_POST['materia']; ?>">
                                    <input type="hidden" name="id_grupo" value="<?php echo $_POST['grupo']; ?>">
                                    <!--Boton para justificar la falta-->
                                    <button class="boton-opcion" type="submit" style="width: 100%; background-color: rgb(27, 4, 58);">Justificar</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php elseif($alumnosfaltas): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay faltas por modificar.</p>
                <?php endif; ?>

            </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>


<!-- Script para cerrar el cuadro de dialogo al dar click en aceptar-->
<script>
    function cerrarDialogo() {
        document.getElementById("dialogo-mensaje").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
    }
</script>

</html>