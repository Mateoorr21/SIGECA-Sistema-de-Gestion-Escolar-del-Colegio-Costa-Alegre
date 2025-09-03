<?php
//Iniciamos una sesión si no se ha iniciado
if (session_status() === PHP_SESSION_NONE) session_start();
include('funcion.php'); //Incluimos el archivo de funciones

//Si no tenemos el usuario lo mandamos el login
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != 'alumno') {
    // Si no hay sesión activa, redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}

//Obtenemos las materias a las que el alumno está inscrito para verificar si está inscrito al semestre o no
$materias = consultarasignaturas(); //Consultamos las asignaturas del alumno

$fechasfaltas = consultarfechasfaltas(); //Consultamos las fechas de las faltas que tenga el alumno

// Procesar el formulario si se envió
if (isset($_POST['accion']) && $_POST['accion'] == 'solicitarjustificante') {
    // Verificamos que se haya seleccionado al menos alguna fecha
    if (isset($_POST['fechas']) && is_array($_POST['fechas'])) {
        foreach ($_POST['fechas'] as $fecha) {
            solicitarjustificante($fecha); //Para cada fecha seleccionada, llamamos a la función que solicita el justificante
        }
        $_SESSION['mensaje'] = "Justificante solicitado con éxito."; //Mensaje de justificante solicitado con éxito
    }

    else $_SESSION['mensaje'] = "No se seleccionó ninguna fecha.";

    // Redirigir a la misma página para evitar reenviar el formulario
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} 

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumno/Padre</title>
    <link rel="stylesheet" href="Estilos/EstilosFunciones.css">
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

    <div class="contenedor">
        <div class="tarjeta">
            <!--Solo si está inscrito al semestre-->
            <?php if ($materias->num_rows>0): ?>

                <div class="titulo">
                        <h2>Solicitud de Justificante</h2>
                </div>

                <!--Solo si el alumno tiene faltas registradas-->
                <?php if ($fechasfaltas->num_rows>0): ?>

                <form id="form-justificante" method="POST" action="">
                    <input type="hidden" name="accion" value="solicitarjustificante">

                    <div class="formulario">
                        <label for="alumno">Alumno:</label>                    
                        <input type="text" id="alumno" value="<?php echo $_SESSION['nombre']." ".$_SESSION['apellidos']?>" readonly>
                    </div>
                    
                    <div class="formulario">
                        <label for="grado">Grado y Grupo:</label>
                        <input type="text" id="grado" value="<?php echo $_SESSION['grado']." ° ".$_SESSION['grupo']?>">
                    </div>
                    
                    <div class="formulario">
                        <label>Selecciona las faltas a justificar:</label>
                        <div class="faltas-list">
                            <?php 
                            //Mostramos las fechas de las faltas que el alumno puede justificar como checkbox id="falta<?php echo $contador;
                            while ($falta = $fechasfaltas->fetch_assoc()) { ?>
                                <div class="falta-item">
                                <input type="checkbox" name="fechas[]" value="<?php echo $falta['fecha_falta'];?>">
                                <!--Mostramos la fecha de la falta con formato-->
                                <?php echo date("d/m/Y", strtotime($falta['fecha_falta']));?>
                            </div>
                            <!--Cerramos el bucle while-->
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="formulario">
                        <label for="detalle">Motivo:</label>
                        <textarea required name="motivo" id="detalle" placeholder="Describe brevemente el motivo de la falta..."></textarea>
                    </div>

                    <button type="submit">Solicitar Justificante</button>
                </form>

                <!--Mensaje si no hay falta que se tengan que justificar-->
                <?php elseif($fechasfaltas->num_rows==0): ?>
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No se tienen faltas por justificar.</p>
                <?php endif; ?>
            </div>
            <!--Si el alumno no está inscrito al semestre-->
            <?php elseif($materias->num_rows==0): ?>
                <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">El alumno no está matriculado en ninguna asignatura.</p>
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