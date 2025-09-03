<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si se detecta el parámetro logout en la URL, cerramos la sesión
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Elimina todas las variables de sesión
    session_unset();
    session_destroy();

    // Redirige al login
    header("Location: ../../Publica/login/login.php");
    exit;
}
?>

<!--Obtener el nombre del archivo PHP actual-->
<?php $page = basename($_SERVER['PHP_SELF']); ?>

<!-- Si el nombre del archivo es principal.php, usamos estilo Principal-->
<?php if($page == 'principal.php') echo '<link rel="stylesheet" href="navEstiloPrincipal.css">'; ?>

<!-- Si el nombre del archivo no es principal.php, ocupamos salidas de carpeta-->
<?php if($page != 'principal.php') echo '<link rel="stylesheet" href="../../Publica/principal/navEstiloPrivado.css">'; ?>

<?php 
    //Si el nav es para la página principal, incluimos la opción de LOGIN
    if($page == 'principal.php') { echo '<nav>
        <div class="sigeca">
        <h1>
            SIGECA
        </h1>
        </div>
        
        <ul #login>
            <li>
            <form action="../login/login.php">
                <button>LOGIN</button>
            </form>
            </li>
        </ul>
    </nav>';
    }

    //Si el nav es para la interfaz de cada tipo de usuario, incluimos el CERRAR SESIÓN
    //utilizamos un enlace para que nos mande a la misma pagina en la que estamos (nav.php)
    //Al estar puesto el logout se redireccionara desde esta pagina al login
    elseif ($page == 'interfazadmin.php'||
        $page == 'interfazprofesor.php'||
        $page == 'interfazalumnopadre.php') { echo 
        '<nav>
            <div class="sigeca">
                <h1>SIGECA</h1>
                <ul>
                    <a href="?logout=true">
                        <button>Cerrar Sesión</button>
                    </a>
                </ul>
            </div>
        </nav>'; 
    }
    

    //Si el nav es para alguna de las funciones de cada tipo de usuario
    //Incluimos la opción para regresar a la interfaz del usuario
    else {
        $menu = '#'; // valor por defecto

        //Si se tiene una sesion iniciada, se obtiene el tipo de usuario
        if (isset($_SESSION['tipo_usuario'])) {
            //Asignamos la interfaz según el tipo de usuario que inicio sesión
            switch ($_SESSION['tipo_usuario']) {
                case 'administrador':
                    $menu = 'interfazadmin.php';
                    break;
                case 'profesor':
                    $menu = 'interfazprofesor.php';
                    break;
                case 'profesor_sabatino':
                    $menu = 'interfazprofesor.php';
                    break;
                case 'alumno':
                    $menu = 'interfazalumnopadre.php';
                    break;
            }
        }

        echo '<nav>
                <div class="sigeca">
                    <h1>SIGECA</h1>
                    <ul>
                        <form action="'. $menu .'">
                            <button>Volver al Menú</button>
                        </form>
                    </ul>
                </div>
            </nav>';
    };
 ?>