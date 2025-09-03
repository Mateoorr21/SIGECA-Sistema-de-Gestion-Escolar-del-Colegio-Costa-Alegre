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

//Establecemos las fechas de inicio y fin del semestre
$fecha_inicio = new DateTime("2025-02-01"); //Primero de febrero
$fecha_fin = new DateTime("2025-06-01"); //Primero de junio

//$fecha_actual = new DateTime(); //Definimos nuestra fecha actual
//$fecha_formato = $fecha_actual->format('Y-m-d'); //Formato de fecha
$fecha_actual = new DateTime("2025-08-05"); //Definimos nuestra fecha

//Comparamos si nuestra fecha está dentro del periodo de clases
$habilitado = ($fecha_actual < $fecha_inicio || $fecha_actual > $fecha_fin); //Si estamos fuera del rango arroja true

//Si el formulario de busqueda se envió
if (isset($_POST['buscar'])) {
    $resultado= buscarasignatura(); //Llamamos a la función para buscar la asignatura

    //Si la función retorna un mensaje de error, lo mostramos con el cuadro de dialogo
    if($resultado == "Error. Asignatura no encontrada") {
        $_SESSION['mensaje'] = $resultado; //Guardamos el mensaje de la función
        //Redirige a la misma página con GET para evitar reenviar el formulario
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    $datos = $resultado->fetch_assoc(); //Si no hay error, guardamos los datos del usuario
} 

//Si se envió el formulario de modificación validamos
if (isset($_POST['Accion'])) { 
    $mensaje = validarmodificarasignatura(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;

    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_modificar'] = true;

    // Restauramos los datos del formulario enviados
    $datos = [
        'id_asignatura' => $_POST['id_asignatura'],
        'nombre_asignatura' => $_POST['nombre_asignatura'],
        'creditos' => $_POST['creditos'],
    ];
}

//Si se confirma la modificación
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'mod_asignatura') {
    $mensaje = modificarasignatura(); //Llamamos a la función para modificar la asignatura
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
    <title>Modificar Asignatura</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
    
    <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_modificar'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar la modificación de la Asignatura?</p>
                <form method="POST"> <!--Formulario para confirmar la actualizacion-->
                    <!--Campos ocultos para enviar los datos de la modificacion ya ingresados-->
                    <div>
                        <input type="hidden" name="id_asignatura" value="<?= $_POST['id_asignatura'] ?>">
                        <input type="hidden" name="nombre_asignatura" value="<?= $_POST['nombre_asignatura'] ?>">
                        <input type="hidden" name="creditos" value="<?= $_POST['creditos']?>">
                    </div>

                    <input type="hidden" name="Confirmado" value="mod_asignatura"> <!--Campo oculto para confirmar-->
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
            <h2>Modificar asignatura</h2>
            </div>
            <!--Formulario para ingresar una asignatura a buscar-->
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="buscar" value="modasignatura">
                <label for="id_asignatura">Asignatura:</label><br>
                <input type="text" id="nombre_asignatura" name="nombre_asignatura" required><br><br>
                <button type="submit" id="buscar" name="buscar" value="buscar">Buscar Asignatura</button><br><br>
            </form>

            <!--Solo si se envio busqueda y se encontro asignatura o si se intento modificar, mostramos formulario con datos-->
            <?php if ((isset($_POST['buscar']) && $resultado != "Error. Asignatura no encontrada") || isset($_POST['Accion'])) : ?>
                <form action="" method="POST" class="funciones">
                    <input type="hidden" name="Accion" value="modasignatura">
                    <!-- utilizamos "datos[""]; para que obtener los valores obtenidos de la variable fila-->
                    <input type="hidden" name="id_asignatura" value="<?php echo $datos['id_asignatura']; ?>">
                    <fieldset>
                        <label for="nuevo_nombre">Asignatura:</label><br>
                        <input type="text" id="nuevo_nombre" name="nombre_asignatura" required pattern = "[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Ingrese valores válidos" value="<?php echo $datos['nombre_asignatura']; ?>" required><br><br>

                        <label for="nuevo_creditos">Creditos:</label><br>
                        <!--Campo numérico para modificar la cantidad de créditos, solo habilitada cuando no se está en periodo de semestre-->
                        <input type="number" id="nuevo_creditos" name="creditos" value="<?php echo $datos['creditos']; ?>" required 
                        <?php echo (!$habilitado) ? 'readonly title="No se pueden modificar créditos en periodo de semestre"' : 'title="Ingrese valores válidos"'?>><br><br>
                        <button type="submit" name="Modificar">Actualizar Asignatura</button>
                        <br><br>
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
