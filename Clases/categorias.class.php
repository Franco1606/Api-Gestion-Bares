<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class categorias extends conexion {    

    private $tabla = "categorias";
    private $categoriaID;
    private $nombre;
    private $usuarioID;   

    public function obtenerCategorias($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosCategorias = parent::obtenerDatos($query);        
        if($datosCategorias) {
            return $datosCategorias;
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
            if(!isset($datos['nombre']) || !isset($datos['usuarioID'])){
                return $_respuestas->error_400();
            }else{
                
                $this->usuarioID = $datos['usuarioID'];
                $this->nombre = $datos['nombre'];                
                $resp = $this->insertarCategoria();
                if($resp){                
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "categoriaID" => $resp
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

    private function insertarCategoria(){
        $query = "INSERT INTO " . $this->tabla . " (nombre, usuarioID) values ('" . $this->nombre . "','" . $this->usuarioID . "')";         
        $resp = parent::nonQueryId($query);
        if($resp){
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
            if(!isset($datos['categoriaID'])){
                return $_respuestas->error_400();
            }else{
                $this->categoriaID = $datos['categoriaID'];
                $resp = $this->eliminarCategoria();
                if($resp){                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "categoriaID" => $this->categoriaID
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

    private function eliminarCategoria(){
        $query = "DELETE FROM " . $this->tabla . " WHERE categoriaID = '" . $this->categoriaID . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>