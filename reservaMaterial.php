
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
            <h2>¿Qué necesita para la reunión? </h2>
            <p>Seleccione los elementos que necesita para la reunión.</p>
            <!-- Bloque para el contenedor-->
            <div class="contenedor">
                <div>
                    <?php
                        if(isset($_COOKIE['login'])) {
                            pintaOpcionesMaterial();
                            pintaBotonAtras();
                            
                        }else{
                             echo "Su sesión ha expirado";
                            pintaBotonVueltaInicio();
                        }
                    ?>
                </div>
            </div>
            <div>
              
            </div>
        </div>
        <!-- Fin bloque para el contenido -->
    </body>
</html>

