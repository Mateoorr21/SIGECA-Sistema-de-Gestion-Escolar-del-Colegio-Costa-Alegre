<!--Obtener el nombre del archivo PHP actual-->
<?php $page = basename($_SERVER['PHP_SELF']); ?>

<!--Archivo del footer-->
<?php if($page == 'principal.php') echo '<link rel="stylesheet" href="footerEstilo.css">' ?>

<!-- Si el nombre del archivo no es principal.php, ocupamos salidas de carpeta-->
<?php if($page != 'principal.php') echo '<link rel="stylesheet" href="../../Publica/principal/footerEstilo.css">' ?>

<!-- si el footer es para pagina privado aplicamos el footerprivado-->
<footer <?php if($page != 'principal.php') echo "class = footer-privado" ?>>
        <div class="direccion">
            <p><strong>Dirección: </strong>Av. Universidad No. 333, Las Víboras; CP 28040 Colima, Colima, México ✉️costalegre@uca.mx Transparencia que transforma</span></p>
        </div>
        <div class="footer2">
           <div class="contenedor-footer">
                <p>Nuestro objetivo como universidad de peces velaz está comprometida a traer la mejor educación en veracruz ixtapalapa.Transparencia que transforma</p>
            </div>
            <div class="contenedor-footer">
                <p>Conoce nuestras demás aplicaciones académicas.<br><br><a href="https://evpraxis.ucol.mx" target="_blank">📕EvPraxis</a><br><a href="https://www.classroom.google.com" target="_blank">📖Classroom 2</a></p>
            </div>
        </div>
</footer>