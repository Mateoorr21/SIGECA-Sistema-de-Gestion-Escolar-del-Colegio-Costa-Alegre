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

$posiblestutores = consultartutores(); //Llamamos a la función para consultar los tutores

// Validar el formulario si se envió
if (isset($_POST['Accion'])) { 
    $mensaje = validaraltagrupo(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;
    
    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_alta'] = true;
}

//Si se confirma el alta, llamamos a la función para dar de de alta al grupo
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'alta_asignatura') {
    $mensaje = altagrupo(); //Llamamos a la función para dar de alta al grupo
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
    <title>Alta de Grupo</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_alta'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar el alta del grupo?</p>
                <form method="POST"> <!--Formulario para confirmar el alta-->
                    <!--Campos ocultos para enviar los datos del alta ya ingresados-->
                    <div>
                        <input type="hidden" name="grado" value="<?= $_POST['grado'] ?>">
                        <input type="hidden" name="grupo" value="<?= $_POST['grupo']?>">
                        <input type="hidden" name="tutor" value="<?= $_POST['tutor']?>">
                    </div>

                    <input type="hidden" name="Confirmado" value="alta_asignatura"> <!--Campo oculto para confirmar-->
                    <button type="submit" class="boton-confirmar">Confirmar</button><br><br> <!--Al dar click se envia formulario y se confirma-->
                    <button type="button" class="boton-cancelar" onclick="cerrarConfirmacion()">Cancelar</button> <!--Boton para cancelar la matriculación--> 
                </form>     
            </div>
        </div>
        <?php unset($_SESSION['confirmar_alta']); ?> <!--Limpiamos la variable de confirmación-->
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
            <h2>Registrar un Nuevo Grupo</h2>
            </div>
            <!--Mostramos el formulario para registrar un nuevo grupo solo si hay tutores disponibles-->
            <?php if($posiblestutores->num_rows> 0): ?>
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="Accion" value="altagrupo">
                <label for="grado">Grado:</label><br>
                <!-- Campo numérico para ingresar el grado del grupo, se restaura si ya se habia ingresado -->
                <input type="number" id="grado" name="grado" min="1" max="6" required pattern ="[1-6]{1}" title ="Ingrese valores válidos"
                value="<?php echo isset($_POST['grado']) ? $_POST['grado'] : '';?>"><br><br>
                <!-- Campo numérico para ingresar el grupo (mayúscula), se restaura si ya se habia ingresado -->
                <label for="grupo">Grupo (en mayúscula):</label><br>
                <input type="text" id="grupo" name="grupo" required pattern ="[A-Z\s]+" title="Ingrese valores válidos"
                value="<?php echo isset($_POST['grupo']) ? $_POST['grupo'] : '';?>"><br><br>
                
                <!-- Implementación de una lista de tutores disponibles -->
                <label for="tutor">Tutor:</label><br>
                <select name="tutor" required >
                    <?php
                    while($tutor = $posiblestutores->fetch_assoc()){
                        //Verificamos si el tutor ya fue seleccionado por el usuario
                        $selected = (isset($_POST['tutor']) && $_POST['tutor'] == $tutor['id_usuario']) ? 'selected' : '';
                        echo "<option value='".$tutor['id_usuario']."' $selected>".$tutor['nombre']." ".$tutor['apellidos']."</option>";
                    }
                    ?>
                </select>
                <br><br>
                <button type="submit" name="Registrar">Registrar grupo</button>
            </form>
            <?php elseif($posiblestutores->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay profesores disponibles para ser asignados como tutores.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Script controlar el cuadro de dialogo con mensajes de error, exito y el de confirmacion-->
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