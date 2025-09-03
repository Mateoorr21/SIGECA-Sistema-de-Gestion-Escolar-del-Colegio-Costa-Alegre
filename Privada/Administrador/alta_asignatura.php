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

// Validar el formulario si se envió
if (isset($_POST['Accion'])) { 
    $mensaje = validaraltamateria(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;
    
    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_alta'] = true;
}

//Si se confirma el alta, llamamos a la función para dar de de alta la asignatura
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'alta_asignatura') {
    $mensaje = altamateria(); //Llamamos a la función para dar de alta la asignatura
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
    <title>Alta de Asignatura</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_alta'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar el alta de la asignatura?</p>
                <form method="POST"> <!--Formulario para confirmar el alta-->
                    <!--Campos ocultos para enviar los datos del alta ya ingresados-->
                    <div>
                        <input type="hidden" name="nombre" value="<?= $_POST['nombre'] ?>">
                        <input type="hidden" name="creditos" value="<?= $_POST['creditos']?>">
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
                <h2>Registrar una Nueva Asignatura</h2>
            </div>
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="Accion" value="altaasignatura">
                <label for="nombre">Nombre de la asignatura:</label><br>
                <!-- Campo de texto para ingresar el nombre de la asignatura, se restaura si ya se habia ingresado -->
                <input type="text" id="nombre" name="nombre" required pattern = "[A-Za-ZÁÉÍÓÚáéíóúÑñ\s]+" title = "Datos no válidos"
                value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : '';?>"><br><br>
                <label for="creditos">Créditos (horas por semana):</label><br>
                <!-- Campo de numérico para ingresar los créditos de la asignatura, se restaura si ya se habia ingresado -->
                <input type="number" id="creditos" name="creditos" min="1" max="10" required pattern = "[0-9]{2}" title = "Datos no válidos"
                value="<?php echo isset($_POST['creditos']) ? $_POST['creditos'] : '';?>"><br><br>
                <button type="submit" name="Registrar">Registrar Asignatura</button>
            </form>
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
