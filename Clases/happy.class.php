<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class happy extends conexion {    

    private $tabla = "happy";
    private $categoriaID;
    private $estado;
    private $inicio;
    private $fin;
    private $lunes;
    private $martes;
    private $miercoles;
    private $jueves;
    private $viernes;
    private $sabado;
    private $domingo;

    public function obtenerHappy($usuarioID, $categoriaID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "' AND categoriaID = '" . $categoriaID . "'";                
        $datoshappy = parent::obtenerDatos($query);        
        if($datoshappy) {
            return $datoshappy[0];
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
            if(!isset($datos['categoriaID']) || !isset($datos['usuarioID']) || !isset($datos['estado']) || !isset($datos['inicio']) || !isset($datos['fin'])){
                return $_respuestas->error_400();
            }else{                
                $this->usuarioID = $datos['usuarioID'];
                $this->categoriaID = $datos['categoriaID'];
                $this->estado = $datos['estado'];
                $this->inicio = $datos['inicio'];
                $this->fin = $datos['fin'];                
                if(isset($datos["lunes"])) {$this->lunes = $datos["lunes"];}
                if(isset($datos["martes"])) {$this->martes = $datos["martes"];}
                if(isset($datos["miercoles"])) {$this->miercoles = $datos["miercoles"];}
                if(isset($datos["jueves"])) {$this->jueves = $datos["jueves"];}
                if(isset($datos["viernes"])) {$this->viernes = $datos["viernes"];}
                if(isset($datos["sabado"])) {$this->sabado = $datos["sabado"];}
                if(isset($datos["domingo"])) {$this->domingo = $datos["domingo"];}                

                $verificarExistencia = $this->obtenerHappy($this->usuarioID, $this->categoriaID);                
                $verif = true;                
                if($verificarExistencia) {
                    $resp = $this->modificarHappy();
                    if(!$resp){
                        $verif = false;
                    }
                } else {
                    $resp = $this->insertarHappy();
                    if(!$resp) {
                        $verif = false;
                    }
                }
                if($verif){                
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

    private function insertarHappy(){
        $query = "INSERT INTO " . $this->tabla . " (usuarioID, categoriaID, estado, inicio, fin, lunes, martes, miercoles, jueves, viernes, sabado, domingo) values ('" . $this->usuarioID . "','" . $this->categoriaID . "','" . $this->estado . "','" . $this->inicio . "','" . $this->fin . "','" . $this->lunes . "','" . $this->martes . "','" . $this->miercoles . "','" . $this->jueves . "','" . $this->viernes . "','" . $this->sabado . "','" . $this->domingo . "')";              
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }

    private function modificarHappy(){
        $query = "UPDATE " . $this->tabla . " SET estado ='" . $this->estado . "', inicio = '" . $this->inicio . "', fin = '" . $this->fin . "', lunes = '" . $this->lunes . "', martes = '" . $this->martes . "', miercoles = '" . $this->miercoles . "', jueves = '" . $this->jueves . "', viernes = '" . $this->viernes . "', sabado = '" . $this->sabado . "', domingo = '" . $this->domingo . "' WHERE usuarioID = '" . $this->usuarioID . "' AND categoriaID = '" . $this->categoriaID . "'";        
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
            if(!isset($datos["nombre"]) || !isset($datos["comentario"]) || !isset($datos["mitad"]) || !isset($datos["categoriaID"])){
                return $_respuestas->error_400();
            } else {
                $this->nombre = $datos["nombre"];
                $this->comentario = $datos["comentario"];
                $this->mitad = $datos["mitad"];
                $this->categoriaID = $datos["categoriaID"];
                $resp = $this->modificarCategoria();                               
                if($resp) {                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok",                         
                        "categoriaID" => $this->categoriaID
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

    private function modificarCategoria(){
        $query = "UPDATE " . $this->tabla . " SET nombre ='" . $this->nombre . "', comentario = '" . $this->comentario . "', mitad = '" . $this->mitad . "' WHERE categoriaID = '" . $this->categoriaID . "'";         
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }

    public function delete($postBody){
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);                
        if($verificarToken == 1){
            if(!isset($datos['categoriaID']) || !isset($datos['usuarioID'])){
                return $_respuestas->error_400();
            }else{
                $this->categoriaID = $datos['categoriaID'];
                $this->usuarioID = $datos['usuarioID'];
                $resp = $this->eliminarHappy();
                if($resp){                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "happyID" => $this->categoriaID
                    );
                    return $respuesta;
                }else{
                    return $_respuestas->error_500("Error interno del servidor, no se pudo borrar el registro o el registro no existia");
                }
            }

        } else {
            return $verificarToken;
        }            
    }

    private function eliminarHappy(){
        $query = "DELETE FROM " . $this->tabla . " WHERE usuarioID = '" . $this->usuarioID . "' AND categoriaID = '" . $this->categoriaID . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>