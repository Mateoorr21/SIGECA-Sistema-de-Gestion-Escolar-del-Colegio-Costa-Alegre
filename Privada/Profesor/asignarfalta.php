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
if (isset($_POST['accion']) && $_POST['accion'] === 'asignarfalta') {
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
    $alumnos = consultaralumnos(); //Consulta para obtener los alumnos y sus faltas
}
else $alumnos = null; //Si no se selecciona un grupo, no se muestran los alumnos

// Procesar el formulario si se envió
if (isset($_POST['accion']) && $_POST['accion'] == 'asignarfalta') { 
    // Aquí llamas la función que asigna la falta
    $_SESSION['mensaje'] = asignarfalta($_POST['id_usuario'], $_POST['id_materia'], $_POST['fecha']);

    // Redirigir a la misma página para evitar reenviar el formulario
    header("Location: ".$_SERVER['PHP_SELF']."?materia=".$_POST['id_materia']."&grupo=".$_POST['id_grupo']);
    exit;
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Faltas</title>
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
    <div class="contenedor">
    <!-- Cuadro de diálogo personalizado -->
    <div id="dialogo-mensaje" style="display: <?= isset($_SESSION['mensaje']) ? 'flex' : 'none' ?>;" class="dialogo-overlay"> <!--Display none para que este oculto-->
        <div class="dialogo-contenido">
            <p><?= $_SESSION['mensaje'] ?></p> <!-- Mensaje de éxito o error -->
            <?php unset($_SESSION['mensaje']); ?> <!-- Limpiamos el mensaje después de mostrarlo -->
            <button onclick="cerrarDialogo()">Aceptar</button> <!-- Al dar click se oculta de nuevo el cuadro de dialogo-->
        </div>
    </div>

            <div class="tarjeta">
                <div class="titulo">
                    <h2>Asignar Faltas de Asistencia</h2>
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
                        <th>Asignar Falta</th>    
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada alumno devuelto en la consulta-->
                    <?php while ($alumno = $alumnos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['apellidos']; ?></td>
                            <td>
                                <!-- Botón que muestra el formulario usando JavaScript -->
                                <button class="boton-opcion" type="button" onclick="mostrarFormulario('<?php echo $alumno['id_usuario']; ?>')">ASIGNAR FALTA</button>
                            </td>
                        </tr>
                        <!-- Formulario para asignar falta, inicialmente oculto -->
                        <tr id="formulario-<?php echo $alumno['id_usuario']; ?>" style="display:none;">
                            <td colspan="3"> <!-- Ocupamos la celda completa para el formulario -->
                                <form method="POST" action="">
                                    <!-- Enviar los datos necesarios -->
                                    <input type="hidden" name="accion" value="asignarfalta">
                                    <!-- Enviar el id del alumno, materia y el grupo -->
                                    <input type="hidden" name="id_usuario" value="<?php echo $alumno['id_usuario']; ?>">
                                    <input type="hidden" name="id_materia" value="<?php echo $_POST['materia']; ?>">
                                    <input type="hidden" name="id_grupo" value="<?php echo $_POST['grupo']; ?>">
                                    
                                    <!-- Campo para seleccionar la fecha de la falta -->
                                    <label>Fecha:</label>
                                    <input type="date" class="fecha" name="fecha" required>

                                    <!--Script de JavaScript para validar la fecha (que no se ingrese un sábado o domingo)-->
                                    <script>
                                        document.querySelectorAll(".fecha").forEach(function(campoFecha) {
                                            campoFecha.addEventListener("input", function () {
                                                const fecha = new Date(this.value);
                                                const diaSemana = fecha.getDay();

                                                if (diaSemana === 5 || diaSemana === 6) { // Sabado (5) o Domingo (6)
                                                    alert("Las clases solo se llevan a cabo de Lunes a Viernes.");
                                                    this.value = ""; // Borra la fecha seleccionada
                                                }
                                            });
                                        });
                                    </script>

                                    <button class="boton-opcion" type="submit" name="guardar_falta">Guardar Falta</button> <!--Botón para guardar la falta-->
                                </form>
                            </td>
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