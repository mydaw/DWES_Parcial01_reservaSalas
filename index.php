<!DOCTYPE html>
<!-- 
 * Ciclo FP Desarrollo de Aplicaciones Web
 * Instituto Virgen del Espino - Soria
 * @author eor
-->

<html>
    <head>
        <meta charset="UTF-8">
        <!-- Dado que la utilización de la plantilla es sólo una recomendación,
        hemos decidido utilizar una propia-->
        <link rel="stylesheet" href="./css/styles.css">
        <title>Tarea 3 de DWES</title>
        <?php
        //Incluimos el archivo utilidades.php 
        include "utils/utilidades.php";
        ?>
    </head>
    <body>   
        <!-- Comienza bloque para el encabezado -->
        <div class="encabezado">
            <h1 id="logo">Reserva<span>Salas</span></h1>
            <span id="subtitulo">Aplicación para el uso de espacios comunes</span>
        </div>
        <!-- Fin bloque para el encabezado -->
        <!-- Comienza bloque para el contenido -->
        <div class="contenido">
            <h2>Acceso</h2>
            <p style="font-style: italic">Sistema de reserva de salas destinadas a los jefes de departamento. 
                Cada jefe puede reservar salas para los miembros de su departamento</p>
            <p>Introduzca su usuario y contraseña</p>
            <!-- Bloque para el contenedor-->
            <div class="contenedor">
                <div>
                <!-- Llamada a la función que pinta el desplegable selector (select) -->
                <?php pintaLogin();?>
                </div>
            </div>
            <div>
              
            </div>
        </div>
        <!-- Fin bloque para el contenido -->
    </body>
</html>

