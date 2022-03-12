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

    //No necesita Token porque el cliente puede solicitar una mesa e inicia una sesion (INSERT en sesiones)
    public function post($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;
        $datos = json_decode($postBody, true);               
        if(!isset($datos['usuarioID']) || !isset($datos['mesaID']) || !isset($datos['estado'])){
            return $_respuestas->error_400();
        }else{
            $this->usuarioID = $datos['usuarioID'];
            $this->mesaID = $datos['mesaID'];
            $this->fechaActual = date("Y-m-d H:i:s");
            $this->estado = $datos['estado'];
            /* if(isset($datos['mozoID'])) {
                $this->mozoID = $datos['mozoID'];
            }
            if(isset($datos['abiertaFecha'])) {
                $this->abiertaFecha = $datos['abiertaFecha'];
            }
            if(isset($datos['cerradaFecha'])) {
                $this->cerradaFecha = $datos['cerradaFecha'];
            }
            if(isset($datos['ordenNueva'])) {
                $this->ordenNueva = $datos['ordenNueva'];
            }
            if(isset($datos['ordenLista'])) {
                $this->ordenLista = $datos['ordenLista'];
            } */
            $resp = $this->insertarSesion();
            if($resp){                
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "productoID" => $resp
                );
                return $respuesta;
            }else{
                return $_respuestas->error_500();
            }
        }       
    }

    private function insertarSesion(){
        $query = "INSERT INTO " . $this->tabla . " (usuarioID, mesaID, solicitadaFecha, estado) values ('" . $this->usuarioID . "','" . $this->mesaID ."','" . $this->fechaActual . "','" . $this->estado . "')";
        print_r($query);
        $this->sesionID = parent::nonQueryId($query);
        /* $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        } */
    }    
}

?>