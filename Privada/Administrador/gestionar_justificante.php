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

//Obtenemos los justificantes que los alumnos han solicitado
$justificantes = consultarjustificantes(); //Consulta

// Procesar el formulario si se envió
if (isset($_POST['Accion'])) { 
    // Aquí llamas la función que gestiona el justificante
    $_SESSION['mensaje'] = gestionarjustificante();

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
    <title>Gestionar Justificante</title>
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
            <h2>Gestionar Justificantes</h2>
            </div>
            <!--Solo si se tienen justificantes-->
            <?php if ($justificantes->num_rows>0): ?>
                <div class="tablita">
                <!-- Mostramos la tabla de justificantes pendientes -->
                <table class="tabla">          
                    <!-- Encabezado de la tabla -->
                    <tr> 
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Grupo</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                        <th colspan="2">Sentencia</th>
                         
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada justificante devuelto en la consulta-->
                    <?php while ($justificante = $justificantes->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $justificante['nombre'];?></td>
                            <td><?php echo $justificante['apellidos'];?></td>
                            <td><?php echo $justificante['grado']."°".$justificante['grupo'];?></td>
                            <td><?php echo $justificante['motivo'];?></td>
                            <td><?php echo date("d/m/Y", strtotime($justificante['fecha_justificar']));?></td>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="id_justificante" value="<?php echo $justificante['id_justificante'];?>">
                                <td><button name="Accion" value="aprobar" class="boton-aprobar" type="submit">Aprobar</button></td>
                                <td><button name="Accion" value="rechazar" class="boton-rechazar" type="submit">Rechazar</button></td>
                            </form>
                            <td>
                        </tr>
                    <?php } ?>
                </table>
                </div>

            <!--Mensaje si no hay justificantes por gestionar-->
            <?php elseif($justificantes->num_rows==0): ?>
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay justificantes pendientes.</p>
            <?php endif; ?>
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