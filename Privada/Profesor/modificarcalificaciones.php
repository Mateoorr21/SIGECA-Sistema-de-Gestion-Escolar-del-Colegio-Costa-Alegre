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
if (isset($_POST['accion']) && $_POST['accion'] === 'modificarcalificaciones') {
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
    $alumnosAgrupados = consultarcalificacionesporalumno($materia_seleccionada); //Consulta para obtener los alumnos y sus calificaciones
}
else $alumnosAgrupados = null; //Si no se selecciona un grupo, no se muestran los alumnos

// Procesar el formulario si se envió
if (isset($_POST['accion']) && $_POST['accion'] == 'modificarcalificaciones') { 
    // Aquí llamas la función que modifica las calificaciones
    $_SESSION['mensaje'] = modificarcalificaciones($_POST['id_usuario'], $_POST['id_materia'], $_POST['parcial1'], $_POST['parcial2'], $_POST['parcial3']);

    // Redirigir a la misma página para evitar reenviar el formulario
    header("Location: ".$_SERVER['PHP_SELF']."?materia=".$_POST['id_materia']."&grupo=".$_POST['id_grupo']);
    exit;
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Calificaciones</title>
    <link rel="stylesheet" href="Estilos/EstilosFunc.css">

</head>


<script> //Script de php para mostrar el formulario de asignar falta
function mostrarFormulario(idAlumno) {
    //Obtenemos el formulario de asignar falta para el alumno específico
    var fila = document.getElementById("formulario-" + idAlumno);
    //Si el display actual es none mostramos el formulario como fila de tabla, si no lo ocultamos
    fila.style.display = (fila.style.display === "none") ? "table-row" : "none";
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
            <div class="titulo">
                <h2>Modificar Calificaciones</h2>
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
                
                <?php if($alumnosAgrupados): ?> <!--Si se selecciona un grupo, mostramos los alumnos con sus calificaciones-->
                
                <!-- Mostramos la tabla de alumnos en ese grupo -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Parcial 1</th>
                        <th>Parcial 2</th>
                        <th>Parcial 3</th>
                        <th>Promedio</th>
                        <th>MODIFICAR</th>    
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada alumno-->
                    <?php foreach ($alumnosAgrupados as $id => $alumno) { ?>
                        <tr>
                            <form method="POST" action="">
                                <!--Input oculto para la acción de modificar calificaciones-->
                                <input type="hidden" name="accion" value="modificarcalificaciones">

                                <td><?php echo $alumno['nombre']; ?></td>
                                <td><?php echo $alumno['apellidos']; ?></td>

                                <!-- Inputs para editar las notas tipo numerico obligatorio -->
                                <td><input type="number" name="parcial1" value="<?php echo $alumno['parcial_1']; ?>" min="0" max="10" step="0.1" required
                                 <?php  //Si no hay calificacion, lo hacemos de solo lectura, 
                                        // y ponemos el mensaje de que se debe entrar a asignar
                                    if ($alumno['parcial_1'] == 0): ?>
                                    readonly 
                                    title="No hay calificación. Entre a asignar calificaciones para ingresarla."
                                <?php endif; ?>></td>
                                
                                <!-- Input para editar parcial 2 -->
                                <td><input type="number" name="parcial2" value="<?php echo $alumno['parcial_2']; ?>" min="0" max="10" step="0.1" required
                                <?php  //Si no hay calificación, lo hacemos de solo lectura, 
                                        // y ponemos el mensaje de que se debe entrar a asignar
                                    if ($alumno['parcial_2'] == 0): ?>
                                    readonly 
                                    title="No hay calificación. Entre a asignar calificaciones para ingresarla."
                                <?php endif; ?>></td></td>

                                <!-- Input para editar parcial 3 -->
                                <td><input type="number" name="parcial3" value="<?php echo $alumno['parcial_3']; ?>" min="0" max="10" step="0.1" required
                                <?php  //Si no hay una calificación, lo hacemos de solo lectura, 
                                        // y ponemos el mensaje de que se debe entrar a asignar
                                    if ($alumno['parcial_3'] == 0): ?>
                                    readonly 
                                    title="No hay calificación. Entre a asignar calificaciones para ingresarla."
                                <?php endif; ?>></td></td>
                                
                                <!-- Celda para mostrar el promedio -->
                                <td><?php echo $alumno['promedio']; ?></td>

                                <!-- Enviamos también el ID del alumno, asignatura y grupo ocultos-->
                                <input type="hidden" name="id_usuario" value="<?php echo $id; ?>">
                                <input type="hidden" name="id_materia" value="<?php echo $_POST['materia']; ?>">
                                <input type="hidden" name="id_grupo" value="<?php echo $_POST['grupo']; ?>">

                                <?php //Validamos si el alumno tiene calificaciones asignadas
                                    $noasignada1 = $alumno['parcial_1'] == 0;
                                    $noasignada2 = $alumno['parcial_2'] == 0;
                                    $noasignada3 = $alumno['parcial_3'] == 0;
                                    $ningunaAsignada = $noasignada1 && $noasignada2 && $noasignada3;?>
                                <td>
                                    <button class="boton-opcion" type="submit"
                                    <?php if ($ningunaAsignada) echo 'disabled 
                                    title="Ninguna calificación ha sido asignada."';?>>
                                    MODIFICAR</button>
                                </td>
                            </form>
                        </tr>
                        
                    <?php } ?>
                </table>
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