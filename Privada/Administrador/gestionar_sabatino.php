<?php
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();
include('Funcion.php'); //Incluimos el archivo de funciones

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) && $_SESSION['tipo_usuario'] != 'administrador') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

//Obtenemos el nombre completo y el id del profesor encargado del club sabatino
$profesorsabatino = consultarprofesorsabatino(); //Consulta para obtener el nombre del profesor encargado del club sabatino
$profesorsabatino_id = $_SESSION['id_profesor_sabatino']; //Obtenemos el id del profesor encargado del club sabatino

$posibles_profesores = consultarprofesoresposibles(); //Consulta para obtener los posibles profesores que pueden ser encargados del club sabatino

// Procesar el formulario si se envió
if (isset($_POST['Accion']) && $_POST['Accion'] == 'gestionarsabatino') { 
    // Aquí llamas la función que cambia el profesor encargado del club sabatino
    $_SESSION['mensaje'] = gestionarsabatino();

    // Redirigir a la misma página para evitar reenviar el formulario
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
} 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Sabatino</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
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
    <div class="contenido">
        <div class="tarjeta">
            <div class="titulo">
            <h2>Gestionar Encargado de Club Sabatino</h2>
            </div>
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="Accion" value="gestionarsabatino">
                <input type="hidden" name="id_profesor_sabatino" value="<?php echo $profesorsabatino_id; ?>">
                Profesor encargado: <input type="text" readonly name="profesor_sabatino" value="<?php echo $profesorsabatino; ?>"><br><br>
                <label for="nuevo_profesor">Seleccione un nuevo encargado:</label><br>
                <select name="nuevo_profesor" id="nuevo_profesor" required>
                    <?php foreach($posibles_profesores as $posible): ?>
                        <option value="<?php echo $posible['id_usuario']; ?>"><?php echo $posible['nombre']." ".$posible['apellidos']; ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <button type="submit">Designar nuevo encargado</button>
            </form>
        </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>

    <!-- Script para cerrar el cuadro de dialogo al dar click en aceptar-->
    <script>
        function cerrarDialogo() {
            document.getElementById("dialogo-mensaje").style.display = "none"; // Cambiamos el display a none para ocultar el cuadro de dialogo
        }
    </script>
</body>

</html>