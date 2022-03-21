<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class sesiones extends conexion {    

    private $tabla = "sesiones";
    private $sesionID;
    private $usuarioID;
    private $mozoID;
    private $mesaID;
    private $solicitadaFecha;
    private $abiertaFecha;
    private $cerradaFecha;
    private $estado;
    private $ordenNueva;
    private $ordenLista;
    private $llamarMozo;

    public function obtenerSesiones($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosSesiones = parent::obtenerDatos($query);
        if($datosSesiones) {
            return $datosSesiones;
        } else {
            return 0;
        }
    }

    public function obtenerSesion($sesionID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE sesionID = '" . $sesionID . "'";
        $datosSesion = parent::obtenerDatos($query);
        if($datosSesion) {
            return $datosSesion[0];
        } else {
            return 0;
        }
    }

    public function verificarSesionAbierta() {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $this->usuarioID . "' AND mesaID = '" .$this->mesaID. "' AND (estado = 'abierta' OR estado = 'solicitada')";
        $datosSesion = parent::obtenerDatos($query);
        if($datosSesion) {
            return $datosSesion[0];
        } else {
            return 0;
        }
    }
    
    public function post($postBody) {
        $datos = json_decode($postBody, true);
        if(isset($datos["usuarioID"])) {
            $this->usuarioID = $datos['usuarioID'];
        }
        if(isset($datos["mesaID"])) {
            $this->mesaID = $datos['mesaID'];            
        }
        $this->fechaActual = date("Y-m-d H:i:s");

        if(isset($datos["llamarMozo"])) {
            $this->llamarMozo = $datos["llamarMozo"];
            $datosSesion = $this->verificarSesionAbierta();                        
            if($datosSesion) {
                $this->sesionID = $datosSesion["sesionID"];                
                $this->llamarMozo();
            } else {
                $this->estado = "solicitada";
                $this->sesionID = $this->insertarSesion();
                $this->llamarMozo();
            }
        } else {
            $_respuestas = new respuestas;
            $_token = new token;
            if(!isset($datos['usuarioID']) || !isset($datos['mesaID']) || !isset($datos['estado'])){
                return $_respuestas->error_400();
            }else{                
                $this->estado = $datos['estado'];            
                $resp = $this->insertarSesion();
                if($resp){                
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "sesionID" => $resp
                    );
                    return $respuesta;
                }else{
                    return $_respuestas->error_500();
                }
            }
        }
    }

    private function insertarSesion(){
        $query = "INSERT INTO " . $this->tabla . " (usuarioID, mesaID, solicitadaFecha, estado) values ('" . $this->usuarioID . "','" . $this->mesaID ."','" . $this->fechaActual . "','" . $this->estado . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }

    private function llamarMozo(){
        $query = "UPDATE " . $this->tabla . " SET llamarMozo = '" . $this->llamarMozo . "' WHERE sesionID = '" . $this->sesionID . "'";                
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    public function put($postBody) {
        $datos = json_decode($postBody, true); 
        $_respuestas = new respuestas;
        $_token = new token;
        $verificarToken = $_token->verificarToken($datos);
            if($verificarToken == 1){
            if(!isset($datos["estado"]) || !isset($datos["sesionID"])){
                return $_respuestas->error_400();
            } else {
                $this->estado = $datos["estado"];
                $this->sesionID = $datos["sesionID"];
                if($this->estado == "cerrada") {
                    $resp = $this->cambiarCerrada();
                }    
                if($resp) {                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok",
                        "ordenID" => $this->sesionID
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

    private function cambiarCerrada(){
        $fechaActual = date("Y-m-d H:i:s");
        $query = "UPDATE " . $this->tabla . " SET estado ='" . $this->estado . "', cerradaFecha = '" . $fechaActual . "' WHERE sesionID = '" . $this->sesionID . "'";         
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }    
}

?>