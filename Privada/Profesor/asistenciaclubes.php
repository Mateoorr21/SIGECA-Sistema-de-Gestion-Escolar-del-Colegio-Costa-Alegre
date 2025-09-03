<?php //Script de PHP para obtener los alumnos inscritos a sabatinos
    //Iniciamos una sesión si no se ha iniciado
    if (session_status() === PHP_SESSION_NONE) session_start();
    include ('Funcion.php'); //Incluimos el archivo de funciones
    $sabatinos = consultaralumnossabatinos(); //Consulta para obtener los alumnos inscritos a sabatinos

    //Si no tenemos el usuario lo mandamos el login
    if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 'profesor' && $_SESSION['tipo_usuario'] != 'profesor_sabatino')) {
        // Si no hay sesión activa, redirige al login
        header("Location: ../../Publica/login/login.php");
        exit;
    }

    // Procesar el formulario si se envió
    if (isset($_POST['accion']) && $_POST['accion'] == 'registrarasistencia') { 
        // Aquí llamas la función que asigna la falta
        $_SESSION['mensaje'] = registrarasistenciasabatino($_POST['id_usuario'], $_POST['fecha']);

        // Redirigir a la misma página para evitar reenviar el formulario
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia Sabatinos</title>
    <link rel="stylesheet" href="Estilos/EstilosFunc.css">
</head>

<script> //Script de php para mostrar el formulario de registrar asistencia
function mostrarFormulario(idAlumno) {
    //Obtenemos el formulario de registrar asistencia para el alumno específico
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

            
            <!--Tabla con los alumnos inscritos a sabatinos-->
            <div class="tarjeta">
            <div class="titulo">
                <h2>Asistencia Clubes Sabatinos</h2>
            </div>
             <?php if($sabatinos->num_rows>0): ?> <!--Si se tienen alumnos inscritos a sabatinos-->
                <!-- Mostramos la tabla de alumnos en ese grupo -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Registrar Asistencia</th>    
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada alumno devuelto en la consulta-->
                    <?php while ($alumno = $sabatinos->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $alumno['nombre']; ?></td>
                            <td><?php echo $alumno['apellidos']; ?></td>
                            <td>
                                <!-- Botón que muestra el formulario usando JavaScript -->
                                <button class="boton-opcion" type="button" onclick="mostrarFormulario('<?php echo $alumno['id_usuario']; ?>')">REGISTRAR ASISTENCIA</button>
                            </td>
                        </tr>
                        <!-- Formulario para registrar asistencia, inicialmente oculto -->
                        <tr id="formulario-<?php echo $alumno['id_usuario']; ?>" style="display:none;">
                            <td colspan="3"> <!-- Ocupamos la celda completa para el formulario -->
                                <form method="POST" action="">
                                    <!-- Enviar los datos necesarios -->
                                    <input type="hidden" name="accion" value="registrarasistencia">
                                    <!-- Enviar el id del alumno, materia y el grupo -->
                                    <input type="hidden" name="id_usuario" value="<?php echo $alumno['id_usuario']; ?>">
                                    
                                    <!-- Campo para seleccionar la fecha de la asistencia -->
                                    <label>Fecha:</label>
                                    <input type="date" class="fecha-club" name="fecha" required>

                                    <!--Script de JavaScript para validar la fecha (que se haya ingresado un sábado)-->
                                    <script>
                                        document.querySelectorAll(".fecha-club").forEach(function(campoFecha) {
                                            campoFecha.addEventListener("input", function () {
                                                const fecha = new Date(this.value);
                                                const diaSemana = fecha.getDay();

                                                if (diaSemana === 1 || diaSemana === 2 || diaSemana === 3 || 
                                                    diaSemana === 4 || diaSemana === 0 || diaSemana === 6) { // Si NO es sábado (5)
                                                    alert("Los clubes sabatinos solo se realizan los sábados.");
                                                    this.value = ""; // Borra la fecha seleccionada
                                                }
                                            });
                                        });
                                    </script>

                                    <!--Botón para guardar la asistencia-->
                                    <button class="boton-opcion" type="submit" name="registrar_asistencia">Registrar Asistencia</button> 
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <?php elseif($sabatinos->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay alumnos registrados a clubes sabatinos.</p>
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