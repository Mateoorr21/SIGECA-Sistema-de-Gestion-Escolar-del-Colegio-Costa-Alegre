<?php
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();
include('Funcion.php'); //Incluimos el archivo de funciones

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

$grupos = consultargrupos(); //Consulta para obtener los grupos
$asignaturas = consultarasignaturas(); //Consulta para obtener las asignaturas
$profesores = consultarprofesores(); //Consulta para obtener los profesores

//Capturamos los datos enviados por el formulario para restaurarlo en caso de que se muestre un error
$dias = $_POST['dia'] ?? [];
$horas_inicio = $_POST['hora_inicio'] ?? [];
$horas_fin = $_POST['hora_fin'] ?? [];

// Validar el formulario si se envió
if (isset($_POST['Accion'])) { 
    $mensaje = validarmatriculargrupo(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;

    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_matriculacion'] = true;
}

//Si se confirma la matriculación, llamamos a la función para matricular el grupo
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'matricular_grupo') {
    $mensaje = matriculargrupo(); //Llamamos a la función para matricular el grupo
    $_SESSION['mensaje'] = $mensaje; //Guardamos el mensaje de la función
    header("Location: ".$_SERVER['PHP_SELF']); //Redirigimos a la misma página
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matricular Grupo</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_matriculacion'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar la matriculación del grupo?</p>
                <form method="POST"> <!--Formulario para confirmar la matriculación-->
                    <!--Campos ocultos para enviar los datos de la matriculación ya ingresados-->
                    <div>
                        <input type="hidden" name="grupo" value="<?= $_POST['grupo'] ?>">
                        <input type="hidden" name="asignatura" value="<?= $_POST['asignatura'] ?>">
                        <input type="hidden" name="profesor" value="<?= $_POST['profesor'] ?>">

                        <!--Campos ocultos para enviar los horarios seleccionados-->
                        <?php for ($i = 0; $i < count($_POST['dia']); $i++): ?>
                            <input type="hidden" name="dia[]" value="<?= $_POST['dia'][$i] ?>">
                            <input type="hidden" name="hora_inicio[]" value="<?= $_POST['hora_inicio'][$i] ?>">
                            <input type="hidden" name="hora_fin[]" value="<?= $_POST['hora_fin'][$i] ?>">
                        <?php endfor; ?> 
                    </div>

                    <input type="hidden" name="Confirmado" value="matricular_grupo"> <!--Campo oculto para confirmar-->
                    <button type="submit" class="boton-confirmar">Confirmar</button><br><br> <!--Al dar click se envia formulario y se confirma-->
                    <button type="button" class="boton-cancelar" onclick="cerrarConfirmacion()">Cancelar</button> <!--Boton para cancelar la matriculación--> 
                </form>     
            </div>
        </div>
        <?php unset($_SESSION['confirmar_matriculacion']); ?> <!--Limpiamos la variable de confirmación-->
    <?php endif; ?> <!--Fin del if para mostrar el cuadro de confirmación-->

   <!-- Cuadro de diálogo con mensaje personalizado -->
    <div id="dialogo-mensaje" style="display: <?= isset($_SESSION['mensaje']) ? 'flex' : 'none' ?>;" class="dialogo-overlay"> <!--Display none para que este oculto-->
        <div class="dialogo-contenido">
            <p><?= $_SESSION['mensaje'] ?></p> <!-- Mensaje de éxito o error -->
            <?php unset($_SESSION['mensaje']); ?> <!-- Limpiamos el mensaje después de mostrarlo -->
            <button class="boton-mensaje" onclick="cerrarDialogo()">Aceptar</button> <!-- Al dar click se oculta de nuevo el cuadro de dialogo-->
        </div>
    </div>
    <div class="contenido">
        <div class="tarjeta">
            <div class="titulo">
                <h2>Matricular Grupo a Asignatura</h2>
            </div>
            <?php if($grupos->num_rows>0 && $asignaturas->num_rows>0 && $profesores->num_rows>0): ?> <!-- Solo mostramos el apartado si hay grupos, profesores y asignaturas -->
            <!--Formulario para matricular un grupo a una asignatura-->
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="Accion" value="matricular_grupo">
                <!--Select para elegir un grupo-->
                Selecciona un grupo:
                <select name="grupo" id="grupo" required>
                    <!--Si se ha seleccionado un grupo previamente, lo seleccionamos por defecto-->
                    <?php while ($grupo = $grupos->fetch_assoc()): ?>
                        <option value="<?php echo $grupo['id_grupo']; ?>"
                        <?= (isset($_POST['grupo']) && $_POST['grupo'] == $grupo['id_grupo']) ? 'selected' : '' ?>>
                        <?php echo $grupo['grado']."°".$grupo['grupo']; ?></option>
                    <?php endwhile; ?>
                </select><br><br>

                <!--Select para elegir una materia-->
                Selecciona una materia:
                <select name="asignatura" id="asignatura" required>
                    <!--Si se ha seleccionado una materia previamente, lo seleccionamos por defecto-->
                    <?php while ($asignatura = $asignaturas->fetch_assoc()): ?>
                        <option value="<?php echo $asignatura['id_asignatura']; ?>"
                        <?= (isset($_POST['asignatura']) && $_POST['asignatura'] == $asignatura['id_asignatura']) ? 'selected' : '' ?>>
                        <?php echo $asignatura['nombre_asignatura']; ?></option>
                    <?php endwhile; ?>
                </select><br><br>
                
                <!--Select para elegir un profesor-->
                Selecciona un profesor:
                <select name="profesor" id="profesor" required>
                    <!--Si se ha seleccionado un profesor previamente, lo seleccionamos por defecto-->
                    <?php while ($profesor = $profesores->fetch_assoc()): ?>
                        <option value="<?php echo $profesor['id_usuario']; ?>"
                        <?= (isset($_POST['profesor']) && $_POST['profesor'] == $profesor['id_usuario']) ? 'selected' : '' ?>>
                        <?php echo $profesor['nombre']." ".$profesor['apellidos']; ?></option>
                    <?php endwhile; ?>
                </select><br><br>
                
                <!--Select para elegir un horario-->
                <div id="horarios">
                    <h4>Horarios</h4><br>   
                    
                    <?php if(!empty($dias)): //Si se ha enviado el formulario, mostramos los horarios seleccionados
                        for ($i = 0; $i < count($dias); $i++) { ?>
                            <div class="horario">
                                <fieldset>
                                <label>Día:</label>
                                <!--Si se ha seleccionado un dia previamente, lo seleccionamos por defecto-->
                                <select name="dia[]" required>
                                    <?php //Arreglo con los días de la semana, las opciones
                                    $dias_opciones = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
                                    //Recorremos el arreglo de los días de la semana para mostrarlos en el select
                                    foreach ($dias_opciones as $dia) {
                                        //Si el día es el que se ha seleccionado, lo seleccionamos por defecto
                                        $selected = ($dia == $dias[$i]) ? "selected" : "";
                                        echo "<option value=\"$dia\" $selected>$dia</option>"; //Mostramos el día
                                    }
                                    ?>
                                </select><br>

                                <label>Hora inicio:</label>
                                <!--Si se ha seleccionado una hora de inicio previamente, lo seleccionamos por defecto-->
                                <select name="hora_inicio[]" required>
                                    <?php //Arreglo con las horas del día y las horas a mostrar, las opciones
                                    $horas_valor = ["13:10", "14:00", "14:50", "15:40", "16:30", "17:20", "18:10", "19:00", "19:50"];
                                    $horas_mostrar = ["1:10 p.m.", "2:00 p.m.", "2:50 p.m.", "3:40 p.m.", "4:30 p.m.", "5:20 p.m.", "6:10 p.m.", "7:00 p.m.", "7:50 p.m."];
                                    //Recorremos el arreglo de las horas del dia para mostrarlas en el select
                                    for ($j = 0; $j < count($horas_valor); $j++) {
                                        $hora = $horas_valor[$j]; //Obtenemos el valor de la hora
                                        $mostrar = $horas_mostrar[$j]; //Obtenemos la hora a mostrar
                                        //Si la hora es la que se ha seleccionado, la seleccionamos por defecto
                                        $selected = ($hora == $horas_inicio[$i]) ? "selected" : "";
                                        echo "<option value=\"$hora\" $selected>$mostrar</option>"; //Mostramos la hora
                                    }
                                    ?>
                                </select><br>

                                <label>Hora fin:</label>
                                <!--Si se ha seleccionado una hora de fin previamente, lo seleccionamos por defecto-->
                                <select name="hora_fin[]" required>
                                    <?php //Arreglo con las horas del día y las horas a mostrar, las opciones
                                    $horas_valor = ["13:10", "14:00", "14:50", "15:40", "16:30", "17:20", "18:10", "19:00", "19:50", "20:40"];
                                    $horas_mostrar = ["1:10 p.m.", "2:00 p.m.", "2:50 p.m.", "3:40 p.m.", "4:30 p.m.", "5:20 p.m.", "6:10 p.m.", "7:00 p.m.", "7:50 p.m.", "8:40 p.m."];
                                    //Recorremos el arreglo de las horas del dia para mostrarlas en el select
                                    for ($j = 0; $j < count($horas_valor); $j++) {
                                        $hora = $horas_valor[$j]; //Obtenemos el valor de la hora
                                        $mostrar = $horas_mostrar[$j]; //Obtenemos la hora a mostrar
                                        //Si la hora es la que se ha seleccionado, la seleccionamos por defecto
                                        $selected = ($hora == $horas_fin[$i]) ? "selected" : "";
                                        echo "<option value=\"$hora\" $selected>$mostrar</option>"; //Mostramos la hora
                                    }
                                    ?>
                                </select><br><br>

                                <button type="button" onclick="eliminarHorario(this)" style="background-color:rgb(255, 185, 185);">Eliminar Horario</button><br>
                                </fieldset><br>
                            </div>
                    <?php
                        } ?>
                
                <!--Si no se ha enviado el formulario, mostramos un horario vacío-->
                <?php else: ?>
                    <div class="horario">
                        <fieldset>
                        <label>Día:</label>
                        <select name="dia[]" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                        </select><br>

                        <label>Hora inicio:</label>
                        <!--Select para elegir la hora de inicio-->
                        <select name="hora_inicio[]" required>
                            <option value="13:10">1:10 p.m.</option>
                            <option value="14:00">2:00 p.m.</option>
                            <option value="14:50">2:50 p.m.</option>
                            <option value="15:40">3:40 p.m.</option>
                            <option value="16:30">4:30 p.m.</option>
                            <option value="17:20">5:20 p.m.</option>
                            <option value="18:10">6:10 p.m.</option>
                            <option value="19:00">7:00 p.m.</option>
                            <option value="19:50">7:50 p.m.</option>
                        </select><br>

                        <!--Select para elegir la hora fin-->
                        <label>Hora fin:</label>
                        <select name="hora_fin[]" required>
                            <option value="13:10">1:10 p.m.</option>
                            <option value="14:00">2:00 p.m.</option>
                            <option value="14:50">2:50 p.m.</option>
                            <option value="15:40">3:40 p.m.</option>
                            <option value="16:30">4:30 p.m.</option>
                            <option value="17:20">5:20 p.m.</option>
                            <option value="18:10">6:10 p.m.</option>
                            <option value="19:00">7:00 p.m.</option>
                            <option value="19:50">7:50 p.m.</option>
                            <option value="20:40">8:40 p.m.</option>
                        </select><br><br>
                        
                        <!--Boton para eliminar el horario-->
                        <button type="button" onclick="eliminarHorario(this)" style="background-color:rgb(255, 185, 185);">Eliminar Horario</button><br>

                        </fieldset><br>
                    </div>

                <?php endif; ?> <!--Fin del else para mostrar un horario vacío-->
                </div> <!--Fin del div de horarios-->
                <br><button type="button" onclick="agregarHorario()" style="background-color:rgb(182, 255, 172);">Agregar Horario</button><br><br>

                <button type="submit" onclick="validarYConfirmar()" id="matricular" name="matricular" value="matricular" class="btn-matricular">Matricular Grupo</button><br><br>
                
            </form>
            <?php else: ?> <!-- En caso de que no haya alguno de grupos, profesores o asignaturas-->
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No se puede realizar operación: faltan grupos, asignaturas y/o profesores.</p>
            <?php endif; ?>    

        </div>
    </div>


    <!--Script de Java para poder agregar otro horario y eliminar un horario-->
    <script>
        //Funcion que se ejecuta al dar click en el boton de agregar horario
        function agregarHorario() {
            //Contenedor donde se agregara el nuevo horario
            const horariosDiv = document.getElementById('horarios');
            //Nuevo div que contendra el nuevo horario
            const nuevoHorario = document.createElement('div');
            nuevoHorario.className = 'horario'; //Le asignamos la clase de horario
            //Definimos el contenido del nuevo horario (formulario)
            nuevoHorario.innerHTML = `
            <fieldset>
                <label>Día:</label>
                        <select name="dia[]" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                        </select><br>

                        <label>Hora inicio:</label>
                        <!--Select para elegir la hora de inicio-->
                        <select name="hora_inicio[]" required>
                            <option value="13:10">1:10 p.m.</option>
                            <option value="14:00">2:00 p.m.</option>
                            <option value="14:50">2:50 p.m.</option>
                            <option value="15:40">3:40 p.m.</option>
                            <option value="16:30">4:30 p.m.</option>
                            <option value="17:20">5:20 p.m.</option>
                            <option value="18:10">6:10 p.m.</option>
                            <option value="19:00">7:00 p.m.</option>
                            <option value="19:50">7:50 p.m.</option>
                        </select><br>

                        <!--Select para elegir la hora fin-->
                        <label>Hora fin:</label>
                        <select name="hora_fin[]" required>
                            <option value="13:10">1:10 p.m.</option>
                            <option value="14:00">2:00 p.m.</option>
                            <option value="14:50">2:50 p.m.</option>
                            <option value="15:40">3:40 p.m.</option>
                            <option value="16:30">4:30 p.m.</option>
                            <option value="17:20">5:20 p.m.</option>
                            <option value="18:10">6:10 p.m.</option>
                            <option value="19:00">7:00 p.m.</option>
                            <option value="19:50">7:50 p.m.</option>
                            <option value="20:40">8:40 p.m.</option>
                        </select><br><br>

                        <button type="button" onclick="eliminarHorario(this)" style="background-color:rgb(255, 185, 185);">Eliminar Horario</button><br>
                    </fieldset><br>
                `;
            //Agregamos el nuevo horario al contenedor
            horariosDiv.appendChild(nuevoHorario);
        }
        
        // Función para eliminar un horario
        function eliminarHorario(boton) {
            // Eliminamos el div que tiene la clase horario
            const horario = boton.closest('.horario');
            horario.remove();
        }
    </script>

    <!-- Script controlar el cuadro de dialogo con mensajes de error y el de confirmacion-->
    <script>
        function cerrarDialogo() {
            document.getElementById("dialogo-mensaje").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
        }

        function cerrarConfirmacion() {
            document.getElementById("dialogo-confirmacion").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
        }
    </script>

    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>
</html>