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
    //Atributos de uso local
    private $campoLugar;
    private $lugar;
    private $numOrden;

    //Columnas de la tabla de Sesiones


    public function obtenerOrdenes($usuarioID, $sesionID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "' AND sesionID = '" . $sesionID . "'";        
        $datosProudctos = parent::obtenerDatos($query);        
        if($datosProudctos) {
            return $datosProudctos;
        } else {
            return 0;
        }
    }

    public function obtenerOrden($ordenID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE sesionID = '" . $ordenID . "'";
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
        if(!isset($datos['usuarioID']) || !isset($datos['estado']) || !isset($datos['solicitante']) || !(isset($datos['mesaID']) || isset($datos['domicilio']))){
            return $_respuestas->error_400();
        }else{                  
            $this->usuarioID = $datos['usuarioID'];
            $this->fechaActual = date("Y-m-d H:i:s");
            $this->estado = $datos['estado'];
            $this->solicitante = $datos['solicitante'];

            //Caracteres para generar codigo aleatorio
            $permitted_chars = 'ABCDE0123456789';
            $this->numOrden = substr(str_shuffle($permitted_chars), 0, 10);

            if(isset($datos['mesaID'])) {
                $this->mesaID = $datos['mesaID'];
            }
            if(isset($datos['domicilio'])) {
                $this->domicilio = $datos['domicilio'];
            }

            if($this->mesaID != null) {                
                $this->campoLugar = "mesaID";
                $this->lugar = $datos['mesaID'];
                $sesion = $this->obtenerSesionAbierta();
                if($sesion) {
                    $this->sesionID = $sesion["sesionID"];                    
                } else {
                    $this->insertarSesion("solicitada");                   
                }                
            } else if ($this->domicilio != null ) {
                $this->campoLugar = "domicilio";
                $this->lugar = $datos['domicilio'];
                $this->sesionID = 0;
            }           

            $resp = $this->insertarOrden();
            if($resp){                
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(
                    "ordenID" => $resp,
                    "nuevaFecha" => $this->fechaActual,
                    "numOrden" => $this->numOrden
                );
                return $respuesta;
            }else{
                return $_respuestas->error_500();
            }
        }       
    }

    private function obtenerSesionAbierta() {
        $query = "SELECT * FROM sesiones WHERE usuarioID = '" . $this->usuarioID . "' AND mesaID = '" . $this->lugar . "' AND estado = 'abierta' OR estado = 'solicitada'";
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
        $query = "INSERT INTO " . $this->tabla . " (usuarioID, nuevaFecha, estado, solicitante, numOrden, sesionID, " . $this->campoLugar . ") values ('" . $this->usuarioID . "','" . $this->fechaActual ."','" . $this->estado . "','" . $this->solicitante . "','" . $this->numOrden . "','" . $this->sesionID . "','" . $this->lugar . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }
}

?>