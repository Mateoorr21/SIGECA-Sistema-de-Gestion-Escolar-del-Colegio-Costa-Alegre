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

$cuenta_nuevo_usuario = numcuentanuevousuario(); //Llamamos a la función de id nuevo usuario
$grupos = consultargrupos(); //Llamamos a la función de consultar grupos

//Capturamos los datos enviados por el formulario para restaurarlo en caso de que se muestre un error
$nombre = $_POST['nombre'] ?? null;
$apellido = $_POST['hora_inicio'] ?? null;
$email = $_POST['email'] ?? null;
$contraseña = $_POST['contraseña'] ?? null; 
$tipo_usuario = $_POST['tipo_usuario'] ?? null;
$grupo = $_POST['grupo'] ?? null;
$club = $_POST['club'] ?? null;

// Validar el formulario si se envió
if (isset($_POST['Accion']) && $_POST['Accion'] == "altausuario") { 
    $mensaje = validaraltausuario(); //Llamamos a la función para validar lo ingresado en el formulario

    // Si hay error mostramos el mensaje
    if ($mensaje !== true) $_SESSION['mensaje'] = $mensaje;

    //Si hay exito en las validaciones, activamos la confirmación
    else $_SESSION['confirmar_alta'] = true;
}

//Si se confirma el alta, llamamos a la función para dar de alta al usuario
if(isset($_POST['Confirmado']) && $_POST['Confirmado'] == 'alta_usuario') {
    $mensaje = altausuario(); //Llamamos a la función para dar de alta al usuario
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
    <title>Alta de Usuario</title>
    <link rel="stylesheet" href="Estilos/estilosFunc.css">
</head>
<body>
    <script>
        //Implementamos un script para mostrar un formulario dependiendo del tipo de usuario
        function campos(){
            //Almacenamos el valor que obtenemos del select
            const rol = document.getElementById('rol').value;
            //Almacenamos el div en la variable alumno
            const alumno = document.getElementById('campoAlumno');

            //Ocultamos de mientras el contenedor
            alumno.style.display = 'none';
            //Verificamos que el valor del select sea alumno (para mostrarle a que grupo se puede matricular)
            if(rol == "alumno"){
                //En caso de que sea alumno pues mostramos el div
                alumno.style.display = 'block';
            }
        }
    </script>

    <?php /*Incluimos archivo nav*/ include '../../Publica/principal/nav.php'; ?>

    <!-- Cuadro de confirmación personalizado, lo mostramos si se pasaron las validaciones -->
    <?php if (isset($_SESSION['confirmar_alta'])): ?>
        <div id="dialogo-confirmacion" class="dialogo-overlay" style="display: flex;">
            <div class="dialogo-contenido">
                <p>¿Desea realizar el alta del usuario?</p>
                <form method="POST"> <!--Formulario para confirmar el alta de usuario-->
                    <!--Campos ocultos para enviar los datos del alta ya ingresados-->
                    <div>
                        <input type="hidden" name="nombre" value="<?= $_POST['nombre'] ?>">
                        <input type="hidden" name="usuario" value="<?= $_POST['usuario']+1 ?>">
                        <input type="hidden" name="apellido" value="<?= $_POST['apellido'] ?>">
                        <input type="hidden" name="email" value="<?= $_POST['email'] ?>">
                        <input type="hidden" name="contraseña" value="<?= $_POST['contraseña'] ?>">
                        <input type="hidden" name="tipo_usuario" value="<?= $_POST['tipo_usuario'] ?>">
                        <input type="hidden" name="grupo" value="<?= $_POST['grupo'] ?>">
                        <input type="hidden" name="club" value="<?= $_POST['club'] ?>">
                    </div>

                    <input type="hidden" name="Confirmado" value="alta_usuario"> <!--Campo oculto para confirmar-->
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
            <h2>Registrar un Nuevo Usuario</h2>
            </div>

            <div class="formulario">
            <form action="" method="POST" class="funciones">
                <input type="hidden" name="Accion" value="altausuario">
                <label for="nombre">Nombre(s):</label><br>
                <!-- implementamos un pattern (que son expresiones regulares que indican que datos pueden ingresar)-->
                <!-- En caso de que haya un error, se mostrara el valor que ingreso el usuario -->
                <input type="text" id="nombre" name="nombre" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios"
                value="<?php echo isset($_POST['nombre']) ? $_POST['nombre'] : ''; ?>"><br><br>

                <label for="nombre">Apellido(s):</label><br>
                <!-- implementamos un pattern (que son expresiones regulares que indican que datos pueden ingresar)-->
                <input type="text" id="apellido" name="apellido" required pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios"
                value="<?php echo isset($_POST['apellido']) ? $_POST['apellido'] : ''; ?>"><br><br>

                <label for="correo">Correo electrónico:</label><br>
                <!-- implementamos un pattern (que son expresiones regulares que indican que datos pueden ingresar)-->
                <input type="email" id="correo" name="email" required pattern="[A-Za-z0-9_\-]+@[A-Za-z0-9\-]+\.[A-Za-z]{2,}" title="Solo letras sin acento, números, guiones y guiones bajos."
                value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>"><br><br>

                <label for="password">Contraseña:</label><br>
                <input type="password" id="password" name="contraseña" required pattern="[A-Za-z0-9]{5}" title="La contraseña debe ser de 5 letras y/o números, sin símbolos, sin ñ ni espacios"
                value="<?php echo isset($_POST['contraseña']) ? $_POST['contraseña'] : ''; ?>"><br><br>

                <label for="rol">Rol:</label><br>
                <select id="rol" name="tipo_usuario" required onchange="campos()"> <!-- Implementamos el evento onchange cada vez que cambie un valor dentro del select y que se ejecute la función) -->
                    <option value="">Seleccione una opción</option>
                    <!--Se restaurar el valor ya ingresado por el usuario en caso de que haya un error-->
                    <option value="profesor"
                    <?php if(isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] === 'profesor') echo 'selected'; ?>>Profesor</option>
                    <option value="alumno"
                    <?php if(isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] === 'alumno') echo 'selected'; ?>>Alumno/Padre</option>
                </select><br><br>

                <!-- Implementamos los campos dinámicos para los ciertos tipos de usuario -->
                <!--campo dinamico de alumno/padre, si ya se habia seleccionado restauramos -->
                <div id="campoAlumno" style="display: <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] === 'alumno') ? 'block' : 'none'; ?>;">
                    <label>Grupo:</label>
                    <select id="grupo" name="grupo">
                        <!-- acá implementaremos todos los grupos de forma dinámica -->
                         <?php
                         while ($registro = $grupos->fetch_assoc()) {
                            //Verificamos si el grupo ya fue seleccionado por el usuario
                            $selected = (isset($_POST['grupo']) && $_POST['grupo'] == $registro['id_grupo']) ? 'selected' : '';
                            echo "<option value='" . $registro['id_grupo'] . "' $selected>" . $registro['grado'] . " ° " . $registro['grupo'] . "</option>";
                        }
                         ?>
                    </select><br><br>
                    <label>Inscripción a los Clubes Sabatinos:</label><br>
                    <!-- Preseleccionamos uno para no tener que usar un required -->
                    <select name="club" id="clubes">
                        <!--Si el Si ya estaba seleccionado lo restauramos, si no lo dejamos en No -->  
                        <!--Implementamos 1 para determinar que si quiere inscribirse a un club sabatino -->
                        <option value="1" <?php echo (isset($_POST['club']) && $_POST['club'] == '1') ? 'selected' : ''; ?>>Si</option>
                        <!--Implementamos 0 pra determinar que no quiere inscribirse a un club sabatino -->
                        <option value="0" selected>No</option>
                    </select><br><br>
                </div>

                <label for="usuario">Usuario:</label><br>
                <input type="text" id="usuario" name="usuario" value="<?php echo $cuenta_nuevo_usuario;?>" readonly required style="text-align:center"> <!--Ponemos readonly para que no pueda modificar y se pueda enviar en el formulario-->
                <br><br>
                <button type="submit" name="Registrar">Registrar usuario</button>
            </form>


            </div>
        </div>
    </div>
    
    <?php /* Incluimos archivo footer */ include '../../Publica/principal/footer.php'; ?>
    
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
