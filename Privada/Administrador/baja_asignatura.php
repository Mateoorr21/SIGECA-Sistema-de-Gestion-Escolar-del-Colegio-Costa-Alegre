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

//Si se envió el formulario de eliminación solicitamos confirmación
if (isset($_POST['Accion'])) {
    $_SESSION['confirmar_eliminar'] = true; 

    // Restauramos los datos del formulario enviados
    $input = $_POST['creditos'];  //Contenido del input
    $creditos = (int) str_replace("Créditos: ", "", $input); //Quitamos la palabra creditos del valor del input

    $datos = [
        'id_asignatura' => $_POST['id_asignatura'],
        'nombre_asignatura' => $_POST['nombre_asignatura'],
        'creditos' => $creditos,
    ];
}

//Si se confirma la eliminacion
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'eliminar_asignatura') {
    $mensaje = eliminarasignatura(); //Llamamos a la función para eliminar la asignatura
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
    <title>Baja de Asignatura</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>

<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>

    <!-- Cuadro de confirmación personalizado, lo mostramos si se presiona eliminar-->
    <?php if (isset($_SESSION['confirmar_eliminar'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea eliminar la Asignatura?</p>
                <form method="POST"> <!--Formulario para confirmar la eliminación-->
                    <!--Campo oculto para enviar la asignatura a eliminar-->
                    <input type="hidden" name="id_asignatura" value="<?= $datos['id_asignatura'] ?>">

                    <input type="hidden" name="Confirmado" value="eliminar_asignatura"> <!--Campo oculto para confirmar-->
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
                <h2>Eliminar Asignatura</h2>
            </div>
            <!-- Flujo del periodo del semestre -->
             <?php if($habilitado): ?> <!-- Si nos arroja true quiere decir que no estamos en periodo de clases -->
                <form action="" method="POST" class="funciones">
                    <label for="nombre_asignatura">Asignatura:</label><br>
                    <input type="text" id="nombre_asignatura" name="nombre_asignatura" required><br><br>
                    <button type="submit" name="buscar">Buscar Asignatura</button>
                </form>

            <!--Solo si se envio busqueda y se encontro asignatura o si se intento eliminar, mostramos formulario con datos-->
            <?php if ((isset($_POST['buscar']) && $resultado != "Error. Asignatura no encontrada") || isset($_POST['Accion'])) : ?>
                <form action="" method="POST" class="funciones">
                    <br><fieldset>
                        <legend>Datos de la Asignatura</legend>
                        <input type="hidden" name="Accion" value="bajaasignatura">
                        <input type="hidden" name="id_asignatura" value="<?php echo $datos['id_asignatura']?>">
                        <input type="text" id="nombre_asignatura" name="nombre_asignatura" value="<?php echo $datos['nombre_asignatura']?>" 
                            readonly style="text-align:center;background-color:rgb(179, 255, 245);"><br><br>
                        <input type="text" id="creditos" name="creditos" value="<?php echo "Créditos:"." ".$datos['creditos']?>" 
                            readonly style="text-align:center;background-color:rgb(179, 255, 245);"><br><br>
                        <button type="submit" name="Eliminar" class="btn-eliminar" >Eliminar Asignatura</button><br>
                    </fieldset>
                </form>
            <?php endif; ?>

            <?php else: ?> <!-- En caso de que esté dentro del rango de clases nos arrojará mensaje de deshabilitado-->
                <br><div class="deshabilitado">
                    <form action="interfazadmin.php">
                        <p style="margin-top: 0px; font-size:20px; color: rgb(255, 255, 255); font-weight: bold;">Función deshabilitada. En periodo de semestre.</p>
                        <button type="submit">Regresar a Menú</button>
                    </form>
                </div>
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
