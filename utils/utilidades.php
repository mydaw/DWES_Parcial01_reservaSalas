<?php
/* 
 * Ciclo FP Desarrollo de Aplicaciones Web
 * Instituto Virgen del Espino - Soria
 * @author eor
 */

/*********************************************************
 *      UTILIDADES PARA LA FUNCIONALIDAD DE LA APP
 *********************************************************/

//Si se ha enviado el formulario de login
if(isset($_POST['login'])){
    //y y ya hay una sesión iniciada
    if(isset($_COOKIE['login'])) {
        //La borramos para evitar problemas asignándole -1 día (cookie de 1 hora)
        setcookie('login','',time()-86400);
        //Refrescamos la página 
        header('Location: '.$_SERVER['REQUEST_URI']);
    }
    //Borramos también, de haberla, la cookie con el pedido
    if(isset($_COOKIE['miReserva'])) {
        setcookie('login','',time()-86400);
        //Refrescamos la página 
        header('Location: '.$_SERVER['REQUEST_URI']);
    }
    //Comprobamos si el login es correcto y almacenamos el resultado en uLogueado
    $uLogueado = compruebaLogin($_POST['usuario'],$_POST['pass']);
    //Si el usuario no es nulo
    if($uLogueado !== NULL) {
        //Clave secreta para añadir a la encriptación
        $claveSecreta="miGatoSeLlamaMojo";
        //Seteamos la cookie del login
        setcookie('login',
        //Mantenemos el id del usuario separado con '-' para recuperarlo más tarde
        //Le pasamos un encriptado md5 + nuestra 'clave secreta' para evitar
        //que puedan 'colarse' seteando en la cookie directamente el usuario
        $uLogueado.'-'.md5($uLogueado.$claveSecreta),time()+3600);
        //Redirigimos a la siguiente página
        header("Location: reservaMaterial.php");
    }
}


//Si está logueado y se ha producido un envío de material o de la reserva
if(isset($_COOKIE['login']) && (isset($_POST['reservaMaterial']) || isset($_POST['reserva']))){
   
    //Si ya se han seteado valores la cookie de reserva
    if(isset($_COOKIE['miReserva'])){
        //Volcamos el string de las opciones de reserva a un array
        $lasOpciones = string2array($_COOKIE['miReserva']); 
    }else{
        //iniciamos el array de las opciones
        $lasOpciones = array();  
    }
    
    //Recorremos el array de POST
    foreach($_POST as $key => $value) {
        //Vocamos los valores, siempre que no estén vacíos o sean el valor del propio botón
        if ($key !== 'reservaMaterial' && $key !== 'reserva' && !empty($value)){
            $lasOpciones[$key]= $value;
        }  
    }
    //Seteamos los valores a la reserva
    setcookie('miReserva', array2string($lasOpciones), time()+3600);
    //Refrescamos la página 
    header('Location: '.$_SERVER['REQUEST_URI']);
}

//Si está logueado y tenemos la cookie de reserva pero se ha pulsado el botón de vuelta a empezar
if(isset($_COOKIE['login']) && isset($_COOKIE['miReserva']) && isset($_POST['vueltaEmpezar'])){
    //Eliminamos la cookie de reserva
    setcookie('miReserva','',time()-86400);
    //Refrescamos la página 
    header('Location: '.$_SERVER['REQUEST_URI']);
}





/**
 * Función que pinta un formulario de login
 */
function pintaLogin(){
    try {
        //Formulario para el login
        echo "<form id='login' action='reservaMaterial.php' method='post'>";
        echo "<div class='campoEditable'>"
            . "<div class='etiqueta'>Usuario</div>"
            . "<div class='textoEditable'><input type='text' name='usuario' required></input>"
            . "</div>"
            . "</div>";
        echo "<div class='campoEditable'>"
            . "<div class='etiqueta'>Contraseña</div>"
            . "<div class='textoEditable'><input type='password' name='pass' required></input>"
            . "</div>"
            . "</div>";
        echo "<input type='submit' value='Login' name='login' /></div>";  
        echo "</form>";
    }catch(Exception $e) {
        //Recoge el mensaje de la excepción y lo pinta
        pintaError($e->getMessage());
        //Matamos la ejecución
        die();
    }
}


function pintaEstadoReserva(){
    //Comprobamos si el día y la hora de hoy
    try{
        //Si tenemos la cookie de la reserva seteada
        if(isset($_COOKIE['miReserva'])){
            //Volcamos el string de las opciones de reserva a un array
            $opcionesReserva = string2array($_COOKIE['miReserva']);
            //Sacamos a una variable las opciones de la reserva
            $horaReserva = $opcionesReserva['franjaHoraria'];
            $diaReserva = $opcionesReserva['diaReserva'];
            
            $comentariosReserva = NULL;
            if(isset($opcionesReserva['comentarios'])){
                $comentariosReserva = $opcionesReserva['comentarios'];
            //Si no tenemos la opción de comentarios (no se han incluido)
            }
            $materialReserva = NULL;
            foreach($opcionesReserva as $k => $v){
                //La key de los materiales de reserva es un número
                if(is_int($k)){
                    $materialReserva += $v.", ";
                }
            }

            //Si el día de la reserva es hoy y la hora es anterior a 'ahora'
            if($diaReserva == date("Y-m-d") && date('H') > date('H', strtotime($horaReserva))){
                throw new Exception("Son las ".date('H:i')." y estás intentando hacer una reserva para ".date('H:i', strtotime($horaReserva))." de hoy. "
                        . "Creemos que tienes el DeLorean en segunda fila, pero vuelve a intentarlo.");
            }else{
                //Combinamos la hora y la fecha de inicio en una sola fecha (YYYY-MM-DD_HH:MM)
                $fechaReserva = date('Y-m-d H:i:s', strtotime("$horaReserva $diaReserva"));
                //Iniciamos la conexión
                $conexion = conexionUsuario();
                $sql = "SELECT *"
                    . " FROM reservas";
                $consulta = $conexion->prepare($sql);
                //Ejecutamos la consulta
                $consulta->execute(); 
                //Almacenamos los resultados en el array $datos
                $datos= $consulta->fetchAll();  ; 

                foreach($datos as $reserva){ 
                    //Comprobamos si existe la reserva con la misma fecha y hora
                    if($reserva['idSala'] == $opcionesReserva['salaReserva'] && $reserva['inicio'] == $fechaReserva){
                        //Si existe, lanza una excepción
                        throw new Exception('La sala está reservada');
                    }
                }
                //String para añadir los valores a la reserva
                $sql2="INSERT INTO reservas (idSala, idUsuario, inicio, material, comentarios)
                        VALUES ('".$opcionesReserva['salaReserva']."', '".muestraUsuarioSesion()."', '".$fechaReserva."', '".$materialReserva."' , '".$comentariosReserva."');" ;       
                $consulta2 = $conexion->prepare($sql2);
                //Si la insercción de datos se ha realiza correctamente, devuelve true
                if($consulta2->execute()){
                    echo "Reserva realizada con éxito"; 
                }else{
                    throw new Exception('No se ha podido realizar la reserva');
                }
            }
        }else{
            throw new Exception('No se ha podido realizar la reserva');  
        }
    }catch(Exception $e) {
       //Pinta el error y matamos la ejecución
        pintaError($e->getMessage());
        die();   
    }finally{
        //Cerramos la conexión
        $conexion = null;
    }
}



/**
 * Lee el id del usuario de la cookie login
 * @return 
 */
function muestraUsuarioSesion(){
    //Clave secreta
    $claveSecreta="miGatoSeLlamaMojo";
    //Si tenemos una cookie de login
    if ($_COOKIE['login']) {
        //Separamos el id de usuario del hash
        list($idUsuario,$cookie_hash) = explode('-',$_COOKIE['login']);
        //Si el hash de la cookie es igual al de la encriptación
        if (md5($idUsuario.$claveSecreta) == $cookie_hash) {
            //Devuelve el id de usuario
            return $idUsuario;
        } else {
            return NULL;
        }
    }   
}


/**
 * Función que crea un objeto PDO con los parámetros dados para un usuario
 * por el ejercicio y lo devuelve.
 * @return PDO conexión
 */
function conexionUsuario(){
   try {
        //Variable que almacena el servidor de la bbdd
        $servidor = "localhost";
        //Variable que almacena el nombre de la bbdd a la que nos conectamos
        $bbdd = "reservaSalas";  
        //Variable para el usuario y su contraseña
        $usuario = "root";
        $pass = "root";
        //Definimos charset UFT-8 para las transacciones y evitamos más tarde tener que realizar conversiones
        $connUsuario = new PDO("mysql:host=$servidor;dbname=$bbdd;charset=utf8", $usuario, $pass);
        //Poner el modo de error PDO en excepción
        $connUsuario->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //Devolvemos la conexión
        return $connUsuario;
   }catch(Exception $e) {
       //Pinta el error y matamos la ejecución
        pintaError($e->getMessage());
        die();   
    }
}



/** 
 * Comprueba si existe el usuario y la contraseña del usuario
 * @param string $usuario Usuario del login
 * @param string $pass Contraseña
 * @return NULL si no es válido, el ID del usuario logueado si es válido
 */
function compruebaLogin($usuario, $pass){
    try{ 
        $conexion = conexionUsuario();
        //Consulta de la tabla en la que se encuentran
        //los usuarios y las contraseñas
            $sql = "SELECT id"
                    . " FROM usuarios"
                    . " WHERE usuario = '".$usuario."'"
                    . " AND contrasena = '".$pass."'";
            $consulta = $conexion->prepare($sql);
            //Ejecutamos la consulta
            $consulta->execute(); 
            $datos= $consulta->fetch(PDO::FETCH_ASSOC); 
            //Almacenamos los resultados en el array $datos
            if($datos){ 
                return $datos['id'];
            }else{
                return NULL;
            }
    }catch(Exception $e) {
        //Recoge el mensaje de la excepción y lo pinta
        pintaError($e->getMessage());
        //Matamos la ejecución
        die();
    }finally{
        //Acabado el proceso, cerramos la conexión
        $conexion = NULL;
    } 
}

/**
 * Pinta las opciones de material de la sala
 */
function pintaOpcionesMaterial(){
    
    echo "<form id='' action='reservaSalas.php' method='post'>";
    
    echo "<div class='campoEditable'>";
    echo "<div class='etiqueta'><span>Elija material:</span></div>";
    echo "<div class='textoEditable'>";
    pintaCheckbox();
    echo "</div></div>";
    
    echo "<div class='campoEditable'>";
    echo "<div class='etiqueta'><span>Comentarios:</span></div>";
    echo "<div class='textoEditable'>";
    echo "<textarea name='comentarios' rows='6' maxlength='150'>";
        if(isset($_COOKIE['miReserva'])){
            //Volcamos el string de las opciones de reserva a un array
            $opcionesSeleccionadas = string2array($_COOKIE['miReserva']);
            //Si estaba en las opciones de la selección, lo volcamos
            if(array_key_exists('comentarios', $opcionesSeleccionadas)){
                echo $opcionesSeleccionadas['comentarios'];
            }
        }
    echo "</textarea>";
    echo "</div></div>";
    
    //Añadimos el submit
    echo "<div class='botonConsulta'><input type='submit' name='reservaMaterial' value='Siguiente'></div>";
    
    echo "</form>";

}

function pintaMaterialSeleccionado(){
    
    if(isset($_COOKIE['miReserva'])){
        //Volcamos el string de las opciones de reserva a un array
        $opcionesSeleccionadas = string2array($_COOKIE['miReserva']);
        echo "Está reservando una sala con: <br />";
        foreach($opcionesSeleccionadas as $k => $v){
            if(is_int($k)){
                echo $v." ";
            }
        }
        
        //Si estaba en las opciones de la selección, lo volcamos
        if(array_key_exists('comentarios', $opcionesSeleccionadas)){
            echo "<br />Sus comentarios a la reserva: ";
            echo $opcionesSeleccionadas['comentarios'];
        }

    }
    
}

/**
 * Pinta checkboz con las opciones de material de la sala
 */
function pintaCheckbox(){
    try{
        $conexion = conexionUsuario();
        // Almacenamos en la var sql la consulta 
        $sql = "SELECT *
               FROM material
               ORDER BY item";
        //Preparamos la consulta    
        $consulta = $conexion->prepare($sql);
        //Ejecutamos la consulta
        $consulta->execute();    
        //Almacenamos todos los resultados en el array $datos
        $datos= $consulta->fetchAll();       

        //Recorremos cada fila de resultados
        foreach($datos as $row){
            //Setemos los valores
            echo "<input type='checkbox' id='".$row['item']."' name='".$row['id']."' value='".$row['item']."' ";
            //Si ya se habían seleccionado previamente
            if(isset($_COOKIE['miReserva'])){
                //Volcamos el string de las opciones de reserva a un array
                $opcionesSeleccionadas = string2array($_COOKIE['miReserva']);
                //Si estaba en las opciones de marcadas, lo volcamos
                if(array_key_exists($row['id'], $opcionesSeleccionadas)){
                    echo "checked ";
                }
            }
            echo ">";
            //Añadimos la label para los checkbox
            echo "<label for='".$row['id']."'> Necesito ".$row['item']."</label><br>";
        }                 
    }catch(Exception $e) {
        //Recoge el mensaje de la excepción y lo pinta
        pintaError($e->getMessage());
        //Matamos la ejecución
        die();
    }finally{
       $conexion = null; 
    }
}



/**
 * Pinta las opciones de reserva de sala
 */
function pintaOpcionesReserva(){
    pintaMaterialSeleccionado();
    echo "<form id='reservaSala' action='reservaFinal.php' method='post'>";
    
    echo "<div class='campoEditable'>";
    echo "<div class='etiqueta'><span>Sala:</span></div>";
    echo "<div class='textoEditable'>";
    pintaSalas();
    echo "</div></div>";
    
    echo "<div class='campoEditable'>";
    echo "<div class='etiqueta'><span>Fecha:</span></div>";
    echo "<div class='textoEditable'>";
    pintaCalendario();
    echo "</div></div>";
    
    echo "<div class='campoEditable'>";
    echo "<div class='etiqueta'><span>Hora:</span></div>";
    echo "<div class='textoEditable'>";
    pintaFranjaHoraria();
    echo "</div></div>";
    
    //Añadimos el submit
    echo "<div class='botonConsulta'><input type='submit' name='reserva' value='Reserva'></div>";
    
    echo "</form>";
    
    
}

/**
 * Pinta las salas que podemos reservar
 */
function pintaSalas(){
    try{
        $conexion = conexionUsuario();
        // Almacenamos en la var sql la consulta 
        $sql = "SELECT sala, id
               FROM salas
               ORDER BY sala";
        //Preparamos la consulta    
        $consulta = $conexion->prepare($sql);
        //Ejecutamos la consulta
        $consulta->execute();    
        //Almacenamos los resultados en el array $datos
        $datos= $consulta->fetchAll();       
        //Todas las salas estarán dentro de este select
        echo "<select name='salaReserva' id='salaReserva'></div>";
        //Recorremos el array y vamos asignando cada resultado a una de las opciones
        //del desplegable. 
        foreach($datos as $row){
            echo "<option value=".$row['id'].">".$row['sala']."</option>";
        }           
        echo "</select>";
    }catch(Exception $e) {
        //Recoge el mensaje de la excepción y lo pinta
        pintaError($e->getMessage());
        //Matamos la ejecución
        die();
    }finally{
       $conexion = null; 
    }

    
}

/**
 * Pinta el input para elegir la fecha
 */
function pintaCalendario(){ 
    //Variable para el día de hoy 
    $ahora=time();
    $hoy = date("Y-m-d");
    $fechaPorDefecto = $hoy;
    //Sólo puedes reservar sala para el próximo mes
    $final = date("Y-m-d", strtotime("+1 month", $ahora));
    
    //Si tenemos seteada la cookie de reserva
    if(isset($_COOKIE['miReserva'])){
        //Volcamos el string de las opciones de reserva a un array
        $opcionesSeleccionadas = string2array($_COOKIE['miReserva']);
        //Si ya había una opción de fecha marcada
        if(isset($opcionesSeleccionadas['diaReserva']) && array_key_exists($opcionesSeleccionadas['diaReserva'], $opcionesSeleccionadas)){
            //La seteamos como fecha por defecto
            $fechaElegida = $opcionesSeleccionadas['diaReserva']; 
            $fechaPorDefecto = date('Y-m-d', strtotime("$fechaElegida"));
             
        }
    }
    echo "<input type='date' name='diaReserva' min='".$hoy."' max='".$final."' value='".$fechaPorDefecto."' required>";
}

/**
 * Pinta el input para elegir la hora
 */
function pintaFranjaHoraria(){
    $aperturaOficinas = "09:00";
    $cierreOficinas = "19:00";
    $horaPorDefecto = $aperturaOficinas;
    //Si tenemos seteada la cookie de reserva
    if(isset($_COOKIE['miReserva'])){
        //Volcamos el string de las opciones de reserva a un array
        $opcionesSeleccionadas = string2array($_COOKIE['miReserva']);
        //Si ya había una opción de hora marcada
        if(isset($opcionesSeleccionadas['franjaHoraria']) && array_key_exists($opcionesSeleccionadas['franjaHoraria'], $opcionesSeleccionadas)){
            //La seteamos como hora por defecto
            $horaPorDefecto = $opcionesSeleccionadas;
        }
    }
    //Con step 3600 bloqueamos la selección por minutos
    echo "<input type='time' name='franjaHoraria' min='".$aperturaOficinas."' max='".$cierreOficinas."' step='3600' value='".$horaPorDefecto."' required>";
    
}




/**
 * Función que, dado un mensaje de error, muestra un mensaje informando del fallo
 * producido durante la ejecución del programa y permite
 * 
 * @param $msgError Mensaje de error
 */
function pintaError($msgError){
    echo "<div class='info' style='display:block'>";  
    //Icono de error (Fuente: https://www.flaticon.com/free-icon/close_463612)
    echo "<div class='icono'><img src='./img/iconoError.png' title='Error ".$msgError."'></div>";
    //Mensaje de error recogido en la excepción
    echo "<div class='msgError'>¡Ups! Parece que se ha producido un error: <br />".$msgError."<br />¿Qué quieres hacer?</div>";
    pintaBotonAtras();
    pintaBotonVueltaAEmpezar();
    echo "</div>"; 
}


function pintaBotonAtras(){
    //Array en el que añadimos la página anterior
    $paginaAnterior[] = array();
    $paginaAnterior['/reservaSalas/reservaMaterial.php'] = 'index.php';
    $paginaAnterior['/reservaSalas/reservaSalas.php'] = 'reservaMaterial.php';
    $paginaAnterior['/reservaSalas/reservaFinal.php'] = 'reservaSalas.php';
    
    //Página actual
    $paginaActual=$_SERVER['REQUEST_URI'];
    //Si existe en el array
    if(array_key_exists($paginaActual, $paginaAnterior)){
        echo "<form id=frmInicio action='".$paginaAnterior[$paginaActual]."' method='post'><div><input type='submit' title='Volver a la página anterior' value='Atrás' name='atras'/></div></form>";
    }
    
}

/**
 * Pinta un botón que manda de vuelta al incio
 */                   
function pintaBotonVueltaInicio(){
   
    echo "<form id='vuelta' action='index.php' method='post'>";
    echo "<input type='submit' title='Volver a iniciar sesión'  value='Reiniciar' name='vuelta' /></div>";  
    echo "</form>";
}

function pintaBotonVueltaAEmpezar(){
   
    echo "<form id='vuelta' action='reservaMaterial.php' method='post'>";
    echo "<input type='submit' title='Volver al elegir las opciones de la sala'  value='Volver a empezar' name='vueltaEmpezar' /></div>";  
    echo "</form>";
}

       
/**
 * Convierte un array a un string codificado en Json.
 * @param Array
 * @return String Codificación Json
 */
function array2string($miArray){
    return $string = json_encode($miArray);
}

/**
 * Convierte el string codificado en Json (nombre-teléfono)
 * en un array asociativo
 *  @param String Codificación Json
 *  @return Array 
 */
function string2array($miString){
    return $array = json_decode($miString, true);
}