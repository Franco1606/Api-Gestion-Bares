<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class estilos extends conexion {    

    private $tabla = "estilos";
    private $usuarioID;
    private $nombre;
    private $valor;
    private $estilos;

    public function obtenerEstilos($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosEstilos = parent::obtenerDatos($query);        
        if($datosEstilos) {
            return $datosEstilos;
        } else {
            return 0;
        }
    }

    public function obtenerEstiloPorNombre() {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";        
        $datosEstilos = parent::obtenerDatos($query);        
        if($datosEstilos) {
            return $datosEstilos[0];
        } else {
            return 0;
        }
    }

    public function post($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);        
        $verificarToken = $_token->verificarToken($datos);                 
        if($verificarToken == 1) {                       
            if(!isset($datos['estilos']) || !isset($datos['usuarioID'])){
                return $_respuestas->error_400();
            }else{
                $this->usuarioID = $datos["usuarioID"];
                $this->estilos = $datos["estilos"];                
                $resp = $this->insertarOModificarEstilo();
                if($resp){                
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok"
                    );
                    return $respuesta;
                }else{
                    return $_respuestas->error_500();
                }
            }
        }
        else {
            return $verificarToken; 
        }
    }

    //Insertar o Modificar de estilo 
    private function insertarOModificarEstilo() {
        $verif = true;         
        foreach($this->estilos as $estilo) {            
            $this->nombre = $estilo["nombre"];
            $this->valor = $estilo["valor"];
            $verificarExistencia = $this->obtenerEstiloPorNombre();
            if($verificarExistencia) {
                $resp = $this->modificarEstilo();
                if(!$resp){
                    $verif = false;
                }
            } else {
                $resp = $this->insertarEstilo();
                if(!$resp) {
                    $verif = false;
                }
            }
        }        
        return $verif;
    }

    private function insertarEstilo(){
        $query = "INSERT INTO " . $this->tabla . " (nombre, valor, usuarioID) values ('" . $this->nombre . "','" . $this->valor . "','" . $this->usuarioID . "')";         
        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }    

    private function modificarEstilo(){
        $query = "UPDATE " . $this->tabla . " SET nombre ='" . $this->nombre ."', valor = '" . $this->valor . "' WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";         
        $resp = parent::nonQueryUpdate($query);       
        if($resp){
             return 1;
        }else{
            return 0;
        }
    }    
}

?>