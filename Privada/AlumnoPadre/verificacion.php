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

$justificantes = consultarjustificantes(); //Consultamos los justificantes del alumno
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
    <div class="contenedor">
        <div class="tarjeta">
                <div class="titulo">
                    <h2>Estado de justificantes</h2>
                </div>
                <!--Solo si hay justificantes solicitados por el alumno-->
                <?php if ($justificantes->num_rows>0): ?>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Motivo de Ausencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--Filas de la tabla, una fila por cada justificante solicitado por el alumno-->
                        <?php while ($justificante = $justificantes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $justificante['fecha_justificar']; ?></td>
                                <!--Usamos la clase y el texto correspondiente de acuerdo con el valor de estado-->
                                <td 
                                    <?php 
                                        if ($justificante['estado'] == 'aprobado') echo 'class="aprob"';
                                        elseif ($justificante['estado'] == 'rechazado') echo 'class="no-aprob"';
                                        else echo 'class="rev"';
                                    ?>>
                                    <?php 
                                        if ($justificante['estado'] == 'aprobado') echo 'Aprobado';
                                        elseif ($justificante['estado'] == 'rechazado') echo 'Rechazado';
                                        else echo 'En Revisión';
                                    ?>
                                    </td>
                                <td><?php echo $justificante['motivo']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <!--Si no hay justificantes solicitados por el alumno-->
                <?php elseif($justificantes->num_rows==0): ?>
                    <p style="margin-top: 0px; font-size:25px; color: rgb(255, 255, 255); font-weight: bold;">No hay justificantes solicitados por el alumno.</p>
                <?php endif; ?>
        </div>
    </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>