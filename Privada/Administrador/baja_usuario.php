<?php
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != 'administrador') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

//Incluimos el archivo de funciones
include_once('Funcion.php');

//Si el formulario de busqueda se envió
if (isset($_POST['buscar'])) {
    $_SESSION['no_se_puede_eliminar'] = false; //Variable no se puede eliminar comienza en falso
    $resultado= buscarusuarioeliminar(); //Llamamos a la función para buscar el usuario
    $datos = $resultado; //Variable con los datos del usuario a eliminar

    //Si la función retorna un mensaje de error, lo mostramos con el cuadro de dialogo
    if($resultado == "Error. Usuario no encontrado" || $resultado == "Error. No se puede eliminar administrador") {
        $_SESSION['mensaje'] = $resultado; //Guardamos el mensaje de la función
        //Redirige a la misma página con GET para evitar reenviar el formulario
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    //Si el usuario es alumno, buscamos sus datos de grupo y si esta inscrito a sabatinos
    if($datos['tipo_usuario'] == 'alumno') {
        $datos_alumno = buscaralumno(); //Si el usuario es un alumno, buscamos sus datos
    }

    //Si el tipo de usuario es profesor o profesor sabatino consultamos horarios
    if($datos['tipo_usuario'] == 'profesor_sabatino' || $datos['tipo_usuario'] == 'profesor') {
        $horariosAgrupados = consultarhorarios();
    }

    //Si el tipo de usuario es profesor sabatino consultamos los posibles reemplazos
    if($datos['tipo_usuario'] == 'profesor_sabatino') $posibles_profesores = consultarprofesoresposibles();
} 

//Si se envió el formulario de modificación validamos
if (isset($_POST['Accion'])) { 
    $_SESSION['confirmar_eliminar'] = true; //Solicitamos la confirmación

    //Restauramos los datos del usuario
    $datos = [
        'id_usuario' => $_POST['id_usuario'],
        'tipo_usuario' => $_POST['tipo_usuario'],
        'usuario' => $_POST['usuario'],
        'nombre' => $_POST['nombre_usuario'],
        'apellidos' => $_POST['apellido_usuario'],
        'email' => $_POST['correo'],
    ];

    //Si es alumno restauramos los datos del alumno
    if( $datos['tipo_usuario'] == 'alumno') {
        $datos_alumno = [
            'id_grupo' => $_POST['id_grupo'],
            'grado' => $_POST['grado'],
            'grupo' => $_POST['grupo_letra'], // agregamos este input en el formulario
            'esta_sabatinos' => $_POST['club'],
        ];
    }

    //Si es un profesor sabatino volvemos a llamar a consultar posibles reemplazos
    if($datos['tipo_usuario'] == 'profesor_sabatino') $posibles_profesores = consultarprofesoresposibles();
}

//Si se confirma la modificación
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'eliminar_usuario') {
    $mensaje = eliminarusuario(); //Llamamos a la función para eliminar el usuario
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
    <title>Baja de Usuario</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>

    <!-- Cuadro de confirmación personalizado, lo mostramos si se presiona eliminar-->
    <?php if (isset($_SESSION['confirmar_eliminar'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea eliminar el Usuario?</p>
                <form method="POST"> <!--Formulario para confirmar la eliminación-->
                    <!--Campo oculto para enviar el usuario a eliminar-->
                    <input type="hidden" name="id_usuario" value="<?php echo $datos['id_usuario'];?>">
                    <input type="hidden" name="tipo_usuario" value="<?php echo $datos['tipo_usuario'];?>">

                    <!--Si es un profesor con horas pasamos los reemplazos como campos ocultos-->
                    <?php if (($_POST['tipo_usuario'] == 'profesor_sabatino' || $_POST['tipo_usuario'] == 'profesor')  && isset($_POST['horarios_agrupados'])): ?>
                        <!--Convertimos el string enviado a un array con json_decode-->
                        <?php $horariosAgrupados = json_decode($_POST['horarios_agrupados'], true); // `true` para obtener array asociativo?>
                        <?php $i = 0; //Contador para recorrer los reemplazos?>
                        <!--Para cada uno de los grupo-asignatura-->
                        <?php foreach ($horariosAgrupados as $clave => $horarios): ?>
                            <!--Para cada uno de los horarios de ese grupo-asignatura-->
                            <?php foreach ($horarios as $h): ?>
                                <!--Input oculto con la clave de asignatura-grupo que guarda el id de cada uno de los horarios de esa clave-->
                                <input type="hidden" name="horarios_por_asignatura_grupo[<?= $clave ?>][]" value="<?= $h['id_horario'] ?>">
                            <?php endforeach; ?>

                            <!--Input oculto para el reemplazo para ese grupo-asignatura-->
                            <input type="hidden" name="reemplazo_para_asignatura_grupo[<?= $clave ?>]" value="<?= $_POST['reemplazo'][$i] ?>">
                            <?php $i++; //Sumamos uno a contador?>
                        <?php endforeach; ?> 
                    <?php endif; ?>

                    <!--Si es un profesor sabatino pasamos el reemplazo del sabatino-->
                    <?php if ($_POST['tipo_usuario'] == 'profesor_sabatino' && isset($_POST['nuevo_profesor'])): ?>
                        <input type="hidden" name="nuevo_profesor" value="<?= $_POST['nuevo_profesor'] ?>">
                    <?php endif; ?>

                    <!--Si es un alumno input hidden con el id del grupo-->
                    <?php if ($_POST['tipo_usuario'] == 'alumno'): ?>
                        <input type="hidden" name="id_grupo" value="<?= $_POST['id_grupo'] ?>">
                    <?php endif; ?>

                    <input type="hidden" name="Confirmado" value="eliminar_usuario"> <!--Campo oculto para confirmar-->
                    <button type="submit" class="boton-confirmar">Confirmar</button><br><br> <!--Al dar click se envia formulario y se confirma-->
                    <button type="button" class="boton-cancelar" onclick="cerrarConfirmacion()">Cancelar</button> <!--Boton para cancelar la matriculación--> 
                </form>     
            </div>
        </div>
        <?php unset($_SESSION['confirmar_eliminar']); ?> <!--Limpiamos la variable de confirmación-->
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
            <h2>Eliminar un Usuario</h2>
            </div>
            <!-- Implementamos un formulario para buscar si el usuario existe-->
            <form action="" method="POST" class="funciones">
                <label for="id_usuario">No. de Cuenta del usuario:</label><br>
                <input type="text" id="usuario" name="usuario" required><br><br>
                <button type="submit" id="buscar" name="buscar" value="buscar">Buscar Usuario</button><br>
            </form>

            <!--Solo si se envio busqueda y o si se intento eliminar, mostramos formulario con datos-->
            <?php if ((isset($_POST['buscar'])) || isset($_POST['Accion'])) : ?>
                <form action="" method="POST" class="funciones">
                    <fieldset>
                        <legend>Datos del Usuario a Eliminar</legend>
                        <!--Inputs ocultos con datos a restaurar-->
                        <input type="hidden" name="Accion" value="bajausuario">
                        <input type="hidden" name="id_usuario" value="<?php echo $datos['id_usuario']?>">
                        <input type="hidden" name="tipo_usuario" value="<?php echo $datos['tipo_usuario']?>">
                        <input type="hidden" name="nombre_usuario" value="<?php echo $datos['nombre']?>">
                        <input type="hidden" name="apellido_usuario" value="<?php echo $datos['apellidos']?>">
                        
                        <!--Campos de solo lectura con la información del usuario-->
                        <input type="text" name="usuario" value="<?php echo $datos['usuario']?>" class="input-info" readonly><br><br>
                        <input type="text" name="nombre" value="<?php echo $datos['nombre']." ".$datos['apellidos']?>" class="input-info" readonly><br><br>
                        <input type="text" name="correo" value="<?php echo $datos['email']?>" class="input-info" readonlysip br><br><br>
                        <input type="text" name="tipo" value="<?php if($datos['tipo_usuario'] == 'alumno') echo "Alumno";
                        else if ($datos['tipo_usuario'] == 'profesor') echo 'Profesor';
                        else if ($datos['tipo_usuario'] == 'profesor_sabatino') echo 'Profesor Sabatino';?>" class="input-info" readonly><br>
                        <?php if($datos['tipo_usuario'] == 'alumno') echo "<br>";?> <!--Imprimimos salto extra si se mostraran mas datos--> 

                        <!--Si es alumno añadimos campos de grado y grupo, y si esta inscrito a sabatinos-->
                        <?php if($datos['tipo_usuario']== 'alumno'):?>
                        <!--Input hidden para el grado y grupo del usuario-->
                        <input type="hidden" name="id_grupo" value="<?php echo $datos_alumno['id_grupo']?>">
                        <input type="hidden" name="grado" value="<?php echo $datos_alumno['grado']?>">
                        <input type="hidden" name="grupo_letra" value="<?php echo $datos_alumno['grupo']?>">
                        <input type="text" id="grupo" name="grupo" value="<?php echo $datos_alumno['grado']." ° ".$datos_alumno['grupo']?>" readonly class="input-info"><br><br>
                        <input type="text" name="club" value="<?php if($datos_alumno['esta_sabatinos'] == 1) echo "Está inscrito a sabatinos";
                        else echo "No está inscrito a sabatinos"?>" readonly class="input-info"><br>
                        <?php endif; ?> <!--Fin del if si es alumno-->
                    </fieldset><br>
                    
                    <!--Si el usuario es profesor con horas formulario para asignar nuevos reemplazos-->
                    <?php if(($datos['tipo_usuario'] == 'profesor' || $datos['tipo_usuario'] == 'profesor_sabatino') && $horariosAgrupados != "Sin horas"):?>
                    <br><fieldset>
                        <legend>Gestión de Horarios</legend>
                        <!--Input oculto con los horarios agrupados, utilizamos json_encode para convertir el array de php a string-->
                        <input type="hidden" name="horarios_agrupados" value='<?php echo json_encode($horariosAgrupados)?>'>
                        <!--php para mostrar los horarios del profesor-->
                        <?php $i = 0; //Inicializamos contador
                         foreach ($horariosAgrupados as $clave => $horarios) { ?>
                            <fieldset>
                            <!-- Mostrar nombre de asignatura y grupo en input de solo lectura -->
                            <input type="text" value="<?php echo $horarios[0]['nombre_asignatura']."    -    ". 
                            $horarios[0]['grado']."°".$horarios[0]['grupo']; ?>" readonly class="input-hor"><br>
                            
                            <!-- Tabla con horarios -->
                             <div class="tablita">
                                <table class="tabla">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Hora Inicio</th>
                                        <th>Hora Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horarios as $h): ?>
                                    <tr>
                                        <td><?php echo $h['dia']; ?></td>
                                        <td><?php echo date("g:i", strtotime($h['horaInicio']))." p.m."; ?></td>
                                        <td><?php echo date("g:i", strtotime($h['horaFin']))." p.m."; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                             </div>
                            
                            <?php
                                //Incluimos el archivo de funciones
                                include_once('Funcion.php');
                                //Obtenemos los profesores que pueden impartir la asignatura al grupo
                                $profesoresDisponibles = consultarprofesoresdisponibles($horarios[0]['id_asignatura'], $horarios[0]['id_grupo']);
                            ?>

                            <!--Si no hay profesor disponible para alguna asignatura cambiamos a true el no se puede eliminar-->
                            <?php if ($profesoresDisponibles == "No hay disponibles") :
                                if (session_status() === PHP_SESSION_NONE) session_start(); //Iniciamos la sesion si no hay
                                $_SESSION['no_se_puede_eliminar'] = true; ?>
                            <?php else: ?>
                            <!--Select de posibles profesores para reemplazo-->
                            <label>Profesor de Reemplazo:</label>
                            <select name="reemplazo[]" required>            
                                <!--Bucle para recorrrer los profesores disponibles-->
                                <?php foreach ($profesoresDisponibles as $profesor) { ?>
                                    <?php
                                        // Restaurar la selección si ya fue enviada
                                        $seleccionado = (isset($_POST['reemplazo'][$i]) && $_POST['reemplazo'][$i] == $profesor['id_usuario']) ? 'selected' : '';
                                    ?>
                                    <option value=<?php echo $profesor['id_usuario'];?> <?php echo $seleccionado; ?>>
                                    <?php echo $profesor['nombre']." ".$profesor['apellidos'];?></option>
                                <?php } ?>
                            </select><br>
                            <?php endif; ?>
                        </fieldset><br>
                        <?php $i++;/*Aumentamos la variable contador*/} ?>
                    </fieldset><br>
                    <?php endif; ?>

                    <!--Si el usuario es profesor sabatino formulario para asignar un nuevo encargado-->
                    <?php if($datos['tipo_usuario']== 'profesor_sabatino'):?>
                    <br><fieldset>
                        <legend>Gestión de Club Sabatino</legend>
                        <label for="nuevo_profesor">Seleccione un nuevo encargado:</label><br>
                        <select name="nuevo_profesor" id="nuevo_profesor" required>
                            <?php foreach ($posibles_profesores as $posible): ?>
                                <?php //Evaluamos si el profesor actual coincide con el enviado por el formulario
                                    // Comparamos con el valor enviado si existe
                                    $seleccionado = (isset($_POST['nuevo_profesor']) && $_POST['nuevo_profesor'] == $posible['id_usuario']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $posible['id_usuario']; ?>" <?php echo $seleccionado; ?>><?php echo $posible['nombre']." ".$posible['apellidos']; ?></option>
                            <?php endforeach; ?>
                        </select><br>
                    </fieldset><br>
                    <?php endif; ?>
                    
                    <!--PHP para ver si el boton de eliminar estará habilitado o deshabilitado-->
                    <?php 
                    if (session_status() === PHP_SESSION_NONE) session_start(); //Iniciamos la sesion si no hay una
                    $disabled = ""; //Deshabilitado empieza vacío
                    $title = ""; //No tiene titulo por default
                    if (isset($_SESSION['no_se_puede_eliminar']) && $_SESSION['no_se_puede_eliminar'] == true) {
                        $disabled = "disabled";
                        $title = "title='El usuario no se puede eliminar ya que no hay profesor que reemplace alguno de sus horarios'";
                    }
                    ?>

                    <button type="submit" name="Eliminar" class="btn-eliminar" <?php echo $disabled . " " . $title;?>>Eliminar Usuario</button><br>
                </form>
            <?php endif; ?>
                
        </div>
    </div>

    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>

    <!-- Script controlar el cuadro de dialogo con mensajes de error, exito y el de confirmacion-->
    <script>
        function cerrarDialogo() {
            document.getElementById("dialogo-mensaje").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
        }

        function cerrarConfirmacion() {
            document.getElementById("dialogo-confirmacion").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
        }
    </script>
</body>

</html>
