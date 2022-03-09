<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class productos extends conexion {    

    private $tabla = "productos";
    private $productoID;
    private $nombre;
    private $descripcion;
    private $precio;
    private $mostrar;
    private $usuarioID;
    private $categoriaID;       

    public function obtenerProductos($usuarioID, $categoriaID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "' AND categoriaID = '" . $categoriaID . "'";        
        $datosProudctos = parent::obtenerDatos($query);        
        if($datosProudctos) {
            return $datosProudctos;
        } else {
            return 0;
        }
    }

    public function obtenerProducto($productoID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE productoID = '" . $productoID . "'";
        $datosProudctos = parent::obtenerDatos($query);
        if($datosProudctos) {
            return $datosProudctos[0];
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
            if(!isset($datos['nombre']) || !isset($datos['precio']) || !isset($datos['usuarioID']) || !isset($datos['categoriaID'])){
                return $_respuestas->error_400();
            }else{
                $this->nombre = $datos['nombre'];
                if(isset($datos['descripcion'])) {
                    $this->descripcion = $datos['descripcion'];
                }
                $this->precio = $datos['precio'];
                $this->mostrar = 1;
                $this->usuarioID = $datos['usuarioID'];
                $this->categoriaID = $datos['categoriaID'];
                $resp = $this->insertarProdcuto();
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
        else {
            return $verificarToken; 
        }
    }

    private function insertarProdcuto(){
        $query = "INSERT INTO " . $this->tabla . " (nombre, descripcion, precio, mostrar, usuarioID, categoriaID) values ('" . $this->nombre . "','" . $this->descripcion ."','" . $this->precio . "',1 ,'" . $this->usuarioID . "','" . $this->categoriaID . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
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
            if(!isset($datos["nombre"]) || !isset($datos["descripcion"]) || !isset($datos["precio"]) || !isset($datos["mostrar"]) || !isset($datos["productoID"])){
                return $_respuestas->error_400();
            } else {
                $this->nombre = $datos['nombre'];
                if(isset($datos['descripcion'])) {
                    $this->descripcion = $datos['descripcion'];
                }
                $this->precio = $datos['precio'];
                $this->mostrar = $datos['mostrar'];
                $this->productoID = $datos['productoID'];
                $resp = $this->modificarProducto();
                if($resp) {                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "status" => "ok",                         
                        "productoID" => $this->productoID
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

    private function modificarProducto(){
        $query = "UPDATE " . $this->tabla . " SET nombre ='" . $this->nombre . "',descripcion = '" . $this->descripcion . "', precio = '" . $this->precio . "', mostrar = '" . $this->mostrar . "' WHERE productoID = '" . $this->productoID . "'"; 
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
            if(!isset($datos['productoID'])){
                return $_respuestas->error_400();
            }else{
                $this->productoID = $datos['productoID'];
                $resp = $this->eliminarProducto();
                if($resp){                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "productoID" => $this->productoID
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

    private function eliminarProducto(){
        $query = "DELETE FROM " . $this->tabla . " WHERE productoID = '" . $this->productoID . "'";
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>