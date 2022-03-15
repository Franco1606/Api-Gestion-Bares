<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class estilos extends conexion {    

    private $tabla = "estilos";
    private $usuarioID;
    private $nombre;
    private $valor;
    private $mostrar;
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

    public function obtenerEstiloPorNombre($usuarioID, $nombre) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "' AND nombre = '" . $nombre . "'";  
        $datosEstilo = parent::obtenerDatos($query);                    
        if($datosEstilo) {
            return $datosEstilo[0];
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
            $this->mostrar = $estilo["mostrar"];
            $verificarExistencia = $this->obtenerEstiloPorNombre($this->usuarioID, $this->nombre);            
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
        $query = "INSERT INTO " . $this->tabla . " (nombre, valor, mostrar, usuarioID) values ('" . $this->nombre . "','" . $this->valor . "','" . $this->mostrar . "','" . $this->usuarioID . "')";         
        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }    

    private function modificarEstilo(){
        $query = "UPDATE " . $this->tabla . " SET nombre ='" . $this->nombre . "', valor = '" . $this->valor . "', mostrar = '" . $this->mostrar . "' WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";                 
        $resp = parent::nonQueryUpdate($query);       
        if($resp){
             return 1;
        }else{
            return 0;
        }
    }

    public function put($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1){
            if(!isset($datos["mostrar"]) || !isset($datos["nombre"]) || !isset($datos["usuarioID"])){
                return $_respuestas->error_400();
            } else {
                $this->usuarioID = $datos["usuarioID"];
                $this->mostrar = $datos["mostrar"];
                $this->nombre = $datos["nombre"];
                $resp = $this->modificarMostrar();                               
                if($resp) {                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok"                        
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

    private function modificarMostrar(){
        $query = "UPDATE " . $this->tabla . " SET mostrar ='" . $this->mostrar ."' WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";         
        $resp = parent::nonQueryUpdate($query);       
        if($resp >= 1){
             return 1;
        }else{
            return 0;
        }
    }

}

?>