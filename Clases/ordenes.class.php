<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class ordenes extends conexion {    

    private $tabla = "ordenes";
    //Columnas de la tabla de Ordenes
    private $ordenID;
    private $sesionID;
    private $finalizoMozoID;
    private $usuarioID;
    private $nuevaFecha;
    private $activaFecha;
    private $listaFecha;
    private $finalizadaFecha;
    private $estado;
    private $solicitante;
    private $domicilio;
    private $mesaID;
    private $total;
    //Atributos de uso local
    private $campoLugar;
    private $lugar;
    private $numOrden;
    private $pedidos;
    private $mozoID;

    public function obtenerOrdenes($sesionID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE sesionID = '" . $sesionID . "'";        
        $datosProudctos = parent::obtenerDatos($query);        
        if($datosProudctos) {
            return $datosProudctos;
        } else {
            return 0;
        }
    }

    public function obtenerOrden($ordenID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE ordenID = '" . $ordenID . "'";
        $datosProudctos = parent::obtenerDatos($query);
        if($datosProudctos) {
            return $datosProudctos[0];
        } else {
            return 0;
        }
    }

    //No necesita Token porque el cliente puede crear ordenes (INSERT en ordenes)
    public function post($postBody) {        
        $_respuestas = new respuestas;
        $_token = new token;
        $datos = json_decode($postBody, true);               
        if(!isset($datos['usuarioID']) || !isset($datos['estado']) || !isset($datos['solicitante']) || !isset($datos['total']) || !(isset($datos['mesaID']) || isset($datos['domicilio']))){
            return $_respuestas->error_400();
        }else{                  
            $this->usuarioID = $datos['usuarioID'];
            $this->fechaActual = date("Y-m-d H:i:s");
            $this->estado = $datos['estado'];
            $this->total = $datos['total'];
            $this->solicitante = $datos['solicitante'];
            $this->pedidos = $datos['pedidos'];
            $this->mesaID = $datos['mesaID'];
            $this->domicilio = $datos['domicilio'];

            //Caracteres para generar codigo aleatorio
            $permitted_chars = 'ABCDE0123456789';
            $this->numOrden = substr(str_shuffle($permitted_chars), 0, 10);

            if($this->mesaID != 0) {                
                $this->campoLugar = "mesaID";
                $this->lugar = $datos['mesaID'];
                $sesion = $this->obtenerSesionAbierta();
                if($sesion) {
                    $this->sesionID = $sesion["sesionID"];
                } else {
                    $this->insertarSesion("solicitada");
                }
            } else {
                $this->campoLugar = "domicilio";
                $this->lugar = $datos['domicilio'];
                $this->sesionID = 0;
            }

            $resp = $this->insertarOrden();
            if($this->estado == "nueva" && $resp) {
                $this->AgregarAvisoOrdenNueva();
            }
            $this->ordenID = $resp;
            $happy = $this->insertarPedidos();            
            if($resp){
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "ordenID" => $resp,
                    "nuevaFecha" => $this->fechaActual,
                    "numOrden" => $this->numOrden,
                    "happy" => $happy
                );
                return $respuesta;
            }else{
                return $_respuestas->error_500();
            }
        }
    }

    private function obtenerSesionAbierta() {
        $query = "SELECT * FROM sesiones WHERE usuarioID = '" . $this->usuarioID . "' AND mesaID = '" . $this->mesaID . "' AND (estado = 'abierta' OR estado = 'solicitada')";               
        $datosSesiones = parent::obtenerDatos($query);        
        if($datosSesiones) {
            return $datosSesiones[0];
        } else {
            return 0;
        }
    }

    private function insertarSesion($estado){
        $query = "INSERT INTO sesiones (usuarioID, mesaID, solicitadaFecha, estado) values ('" . $this->usuarioID . "','" . $this->mesaID ."','" . $this->fechaActual . "','" . $estado . "')";
        $this->sesionID = parent::nonQueryId($query);
    }

    private function insertarOrden(){
        $query = "INSERT INTO " . $this->tabla . " (usuarioID, nuevaFecha, estado, total, solicitante, numOrden, sesionID, " . $this->campoLugar . ") values ('" . $this->usuarioID . "','" . $this->fechaActual . "','" . $this->estado . "','" . $this->total . "','" . $this->solicitante . "','" . $this->numOrden . "','" . $this->sesionID . "','" . $this->lugar . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }

    private function AgregarAvisoOrdenNueva() {
        $query = "UPDATE sesiones SET ordenNueva = 1 WHERE sesionID = '" . $this->sesionID . "'"; 
        parent::nonQuery($query);
    }    

    private function insertarPedidos() {
        $happy = 0;
        foreach($this->pedidos as $pedido) {
            $productoID = $pedido["productoID"];
            $categoriaID = $pedido["categoriaID"];
            $categoriaNombre = $pedido["categoriaNombre"];
            $happy = $this->verificarHappy($categoriaID);
            $cantidad = $pedido["cantidad"];
            $nombre = $pedido["nombre"];
            $precio = $pedido["precio"];
            if(isset($pedido["comentario"])) {
                $comentario = $pedido["comentario"];
            } else {
                $comentario = null;
            }
            $query = "INSERT INTO pedidos (ordenID, productoID, categoriaID, categoriaNombre, usuarioID, sesionID, cantidad, nombre, comentario, precio, happy, cocina) VALUES ('". $this->ordenID . "','" . $productoID . "','" . $categoriaID . "','" . $categoriaNombre . "','" . $this->usuarioID . "','" . $this->sesionID . "','" . $cantidad . "','" . $nombre . "','" . $comentario . "','" . $precio . "','" . $happy . "',0 )";
            parent::nonQueryId($query);            
        }
        return $happy;
    }

    private function verificarHappy($categoriaID) {
        $query = "SELECT * FROM happy WHERE usuarioID = '" . $this->usuarioID . "' AND categoriaID = '" . $categoriaID . "'";                
        $datoshappy = parent::obtenerDatos($query);
        if($datoshappy) {
            $lunes = $datoshappy[0]["lunes"];
            $martes = $datoshappy[0]["martes"];
            $miercoles = $datoshappy[0]["miercoles"];
            $jueves = $datoshappy[0]["jueves"];
            $viernes = $datoshappy[0]["viernes"];
            $sabado = $datoshappy[0]["sabado"];
            $domingo = $datoshappy[0]["domingo"];
            $inicio = $datoshappy[0]["inicio"];
            $fin = $datoshappy[0]["fin"];
            $horaActual = date("G:i");
            $diaActual = date("N");
            if($diaActual == $lunes || $diaActual == $martes || $diaActual == $miercoles || $diaActual == $jueves || $diaActual == $viernes || $diaActual == $sabado || $diaActual == $domingo) {                                                
                return $this->estaEnRango($inicio, $fin, $horaActual);                
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function estaEnRango($inicio, $fin, $horaActual) {        
        $fechaDesde = DateTime::createFromFormat('G:i', $inicio);
        $fechaHasta = DateTime::createFromFormat('G:i', $fin);
        $fechaActual = DateTime::createFromFormat('G:i', $horaActual);
        if($fechaDesde <= $fechaActual && $fechaActual <= $fechaHasta) {
            return 1;
        } else {
            return 0;
        }
    }

    public function put($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1){
            if(!isset($datos["estado"]) || !isset($datos["ordenID"])){
                return $_respuestas->error_400();
            } else {
                $this->estado = $datos["estado"];
                $this->ordenID = $datos["ordenID"];
                if(isset($datos['sesionID'])) {
                    $this->sesionID = $datos["sesionID"];
                }
                if(isset($datos['mozoID'])) {
                    $this->mozoID = $datos["mozoID"];
                }
                if($this->estado == "activa") {
                    $resp = $this->cambiarOrdenActiva();
                    $this->cambiarMesaAbierta($this->mozoID ,$this->sesionID);
                    
                } else if ($this->estado == "finalizada") {
                    $resp = $this->cambiarOrdenFinalizada();
                    $this->quitarPedidosDeCocina();
                }
                $this->quitarAvisoOrdenNueva();
                if($resp) {                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok",                         
                        "ordenID" => $this->ordenID
                    );
                    return $respuesta;
                } else {
                    return $_respuestas->error_500("Error interno del servidor, el cambio no se guardo o no hubo modificaciones en el registro");
                }
            }

        } else {
            return $verificarToken;
        }
    }

    private function quitarAvisoOrdenNueva() {        
        $ordenesDeLaSesion = $this->obtenerOrdenes($this->sesionID);
        $ordenesNuevas = false;
        foreach($ordenesDeLaSesion as $orden) {
            if($orden["estado"] == "nueva") {
                $ordenesNuevas = true;
            }
        }        
        if(!$ordenesNuevas) {            
            $query = "UPDATE sesiones SET ordenNueva = 0 WHERE sesionID = '" . $this->sesionID . "'";
            parent::nonQuery($query);
        } 
    }

    private function cambiarOrdenActiva(){
        $fechaActual = date("Y-m-d H:i:s");
        $query = "UPDATE " . $this->tabla . " SET estado ='" . $this->estado . "', activaFecha = '" . $fechaActual . "', listaFecha = NULL, finalizadaFecha = NULL WHERE ordenID = '" . $this->ordenID . "'";         
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function cambiarOrdenFinalizada(){
        $fechaActual = date("Y-m-d H:i:s");
        $query = "UPDATE " . $this->tabla . " SET estado ='" . $this->estado . "', finalizadaFecha = '" . $fechaActual . "' WHERE ordenID = '" . $this->ordenID . "'";         
        $resp = parent::nonQuery($query);
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function cambiarMesaAbierta($mozoID, $sesionID) {
        $fechaActual = date("Y-m-d H:i:s");
        $query = "UPDATE sesiones SET estado = 'abierta', mozoID = '" . $mozoID . "', abiertaFecha = '" . $fechaActual . "' WHERE sesionID = '" . $sesionID . "'";         
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    private function quitarPedidosDeCocina(){        
        $query = "UPDATE pedidos SET cocina = 0 WHERE ordenID = '" . $this->ordenID . "'";
        $resp = parent::nonQueryUpdate($query);                          
        if($resp){
            return $resp;            
        } else {
            return 0;
        }
    }
}

?>