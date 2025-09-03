<?php //Script de PHP para obtener los alumnos inscritos a sabatinos
    //Iniciamos una sesión si no se ha iniciado
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    //Si no tenemos el usuario lo mandamos el login
    if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] != 'profesor' && $_SESSION['tipo_usuario'] != 'profesor_sabatino')) {
        // Si no hay sesión activa, redirige al login
        header("Location: ../../Publica/login/login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Profesores</title>
    <link rel="stylesheet" href="Estilos/EstilosFunc.css">
</head>


<body>
    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>
        <div class="contenedor">

            
            <?php //Script de PHP para obtener los profesores del colegio
            include ('Funcion.php'); //Incluimos el archivo de funciones
            $profesores = consultarprofesores(); //Consulta para obtener los profesores del colegio
            ?>
            <!--Tabla con los profesore del colegio-->
            <div class="tarjeta">
            <div class="titulo">
            <h2>Profesores del Colegio Costalegre</h2>
            </div>
            <table class="tabla">
                    <tr>    <!-- Encabezado de la tabla -->           
                        <th>Nombre</th>
                        <th>Apellidos</th>      
                    </tr>
                    <!-- Filas de la tabla. Una fila por cada profesor devuelto en la consulta-->
                    <?php while ($profesor = $profesores->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $profesor['nombre']; ?></td>
                            <td><?php echo $profesor['apellidos']; ?></td>
                        </tr>
                    <?php } ?>
                </table>          
            </div>
        </div>
    <?php /*Incluimos archivo footer*/ include '../../Publica/principal/footer.php'; ?>
</body>

</html>