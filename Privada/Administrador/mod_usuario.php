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
include('Funcion.php');

//Si el formulario de busqueda se envió
if (isset($_POST['buscar'])) {
    $resultado= buscarusuario(); //Llamamos a la función para buscar el usuario

    //Si la función retorna un mensaje de error, lo mostramos con el cuadro de dialogo
    if($resultado == "Error. Usuario no encontrado") {
        $_SESSION['mensaje'] = $resultado; //Guardamos el mensaje de la función
        //Redirige a la misma página con GET para evitar reenviar el formulario
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    $datos = $resultado->fetch_assoc(); //Si no hay error, guardamos los datos del usuario

    //Si el usuario es alumno, buscamos sus datos de grupo y sabatinos
    if($datos['tipo_usuario'] == 'alumno') {
        $datos_alumno = buscaralumno(); //Si el usuario es un alumno, buscamos sus datos
        $grupos = consultargruposvalidos(); //Llamamos a la función para consultar los grupos
    }
} 

//Si se envió el formulario de modificación validamos
if (isset($_POST['Accion'])) { 
    $mensaje = validarmodificarusuario(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;

    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_modificar'] = true;

    // Restauramos los datos del formulario enviados
    $datos = [
        'id_usuario' => $_POST['id_usuario'],
        'usuario' => $_POST['usuario'],
        'tipo_usuario' => $_POST['tipo_usuario'],
        'nombre' => $_POST['nombre'],
        'apellidos' => $_POST['apellido'],
        'contraseña' => $_POST['contraseña'],
        'email' => $_POST['email']
    ];

    // Si el tipo es alumno, restauramos también los campos de grupo y sabatinos
    if ($_POST['tipo_usuario'] == 'alumno') {
        $datos_alumno = [
            'id_grupo' => $_POST['grupo'],
            'esta_sabatinos' => $_POST['club']
        ];
        $grupos = consultargruposvalidos(); // Recargar los grupos disponibles
    }
}

//Si se confirma la modificación
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'mod_usuario') {
    $mensaje = modificarusuario(); //Llamamos a la función para modificar el usuario
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
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
   <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>

   <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_modificar'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar la modificación del Usuario?</p>
                <form method="POST"> <!--Formulario para confirmar ña actualizacion-->
                    <!--Campos ocultos para enviar los datos de la modificacion ya ingresados-->
                    <div>
                        <input type="hidden" name="id_usuario" value="<?= $_POST['id_usuario']?>">
                        <input type="hidden" name="nombre" value="<?= $_POST['nombre'] ?>">
                        <input type="hidden" name="usuario" value="<?= $_POST['usuario']?>">
                        <input type="hidden" name="apellido" value="<?= $_POST['apellido'] ?>">
                        <input type="hidden" name="email" value="<?= $_POST['email'] ?>">
                        <input type="hidden" name="contraseña" value="<?= $_POST['contraseña'] ?>">
                        <input type="hidden" name="tipo_usuario" value="<?= $_POST['tipo_usuario'] ?>">
                        <!--Si el usuario es alumno, enviamos ocultos los datos de grupo y sabatinos-->
                        <?php if ($_POST['tipo_usuario'] == 'alumno'): ?>
                            <input type="hidden" name="grupo" value="<?= $_POST['grupo'] ?>">
                            <input type="hidden" name="club" value="<?= $_POST['club'] ?>">
                        <?php endif; ?>
                    </div>

                    <input type="hidden" name="Confirmado" value="mod_usuario"> <!--Campo oculto para confirmar-->
                    <button type="submit" class="boton-confirmar">Confirmar</button><br><br> <!--Al dar click se envia formulario y se confirma-->
                    <button type="button" class="boton-cancelar" onclick="cerrarConfirmacion()">Cancelar</button> <!--Boton para cancelar la matriculación--> 
                </form>     
            </div>
        </div>
        <?php unset($_SESSION['confirmar_modificar']); ?> <!--Limpiamos la variable de confirmación-->
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
                <h2>Modificar un Usuario</h2>
            </div>
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="buscar" value="buscarusuario">
                <label for="usuario">No. de cuenta del usuario:</label><br>
                <!--Campo input para ingresar el id del usuario a buscar, con pattern de solo digitos-->
                <input type="text" id="usuario" name="usuario" pattern="^\d+$" required title="Solo puede ingresar digitos"><br><br>
                <button type="submit" id="buscar" name="buscarusuario" value="buscarusuario">Buscar Usuario</button><br><br>
            </form>    

            <!--Solo si se envio busqueda y se encontro persona o si se intento modificar, mostramos formulario con datos-->
            <?php if ((isset($_POST['buscar']) && $resultado != "Error. Usuario no encontrado") || isset($_POST['Accion'])) : ?>
                <form action="" method="POST" class="funciones">
                    <input type="hidden" name="Accion" value="modusuario">
                    <!-- utilizamos "datos[""]; para que obtener los valores obtenidos de la variable fila-->
                    <input type="hidden" name="id_usuario" value="<?php echo $datos['id_usuario']; ?>">
                    <input type="hidden" name="usuario" value="<?php echo $datos['usuario']; ?>">
                    <input type="hidden" name="tipo_usuario" value="<?php echo $datos['tipo_usuario']; ?>">
                    <fieldset>
                        <label for="nuevo_nombre">Nombre:</label><br>
                        <input type="text" id="nuevo_nombre" name="nombre" required pattern = "[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Ingrese valores válidos" value="<?php echo $datos['nombre']; ?>" required><br><br>

                        <label for="nuevo_apellido">Apellido:</label><br>
                        <input type="text" id="nuevo_apellido" name="apellido" required pattern = "[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Ingrese valores válidos" value="<?php echo $datos['apellidos']; ?>" required><br><br>
                        
                        <label for="nuevo_apellido">Tipo de Usuario:</label><br>
                        <input type="text" name="tipo" value="<?php if($datos['tipo_usuario'] == 'alumno') echo "Alumno";
                        else if ($datos['tipo_usuario'] == 'profesor') echo 'Profesor';
                        else if ($datos['tipo_usuario'] == 'profesor_sabatino') echo 'Profesor Sabatino';
                        else echo 'Administrador';?>" readonly><br><br>

                        <label for="nueva_contraseña">Contraseña de la cuenta (Máximo 5 caracteres):</label><br>
                        <input type="text" id="nueva_contraseña" name="contraseña" required pattern = "[A-Za-z0-9]{5}" title = "Ingrese valores válidos" value="<?php echo $datos['contraseña']; ?>" required><br><br>

                        <label for="nuevo_email">Email:</label><br>
                        
                        <input type="email" id="nuevo_email" name="email" required pattern = "[A-Za-z0-9_\-]+@[A-Za-z0-9\-]+\.[A-Za-z]{2,}" title ="Ingrese valores válidos" value="<?php echo $datos['email']; ?>" required><br><br>
                        
                        <!--campo de alumno/padre con información del grupo y sabatinos-->
                        <?php if ($datos['tipo_usuario'] == 'alumno'): ?>
                        <div id="campoAlumno" style="display: <?php echo ($datos['tipo_usuario'] == 'alumno') ? 'block' : 'none'; ?>;">
                            <label>Grupo:</label>
                            <select id="grupo" name="grupo">
                                <!-- Listamos todos los grupos -->
                                <?php
                                while ($registro = $grupos->fetch_assoc()) {
                                    //Verificamos el grupo al que pertenece el usuario, seleccionamos el grupo correspondiente
                                    $selected = ($datos_alumno['id_grupo'] == $registro['id_grupo']) ? 'selected' : '';
                                    echo "<option value='" . $registro['id_grupo'] . "' $selected>" . $registro['grado'] . " ° " . $registro['grupo'] . "</option>";
                                }
                                ?>
                            </select><br><br>
                            <label>Inscripción a los Clubes Sabatinos:</label><br>
                            <select name="club" id="clubes">
                                <!--Seleccionamos la opcion corresopndiente-->
                                <option value="1" <?php echo ($datos_alumno['esta_sabatinos'] == 1) ? 'selected' : ''; ?>>Si</option>
                                <option value="0" <?php echo ($datos_alumno['esta_sabatinos'] == 0) ? 'selected' : ''; ?>>No</option>
                            </select><br><br>
                        </div>
                        <?php endif; ?>

                        <button type="submit" name="actualizar">Actualizar usuario</button>
                    </fieldset>
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