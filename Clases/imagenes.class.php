<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class imagenes extends conexion {    

    private $tabla = "imagenes";
    private $imagenID;
    private $usuarioID;
    private $nombre;
    private $nombreArchivo;
    private $imgData;    
    private $mostrar;

    public function obtenerImagenes($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosImagenes = parent::obtenerDatos($query);        
        if($datosImagenes) {
            return $datosImagenes;
        } else {
            return 0;
        }
    }

    public function obtenerImagenPorNombre($usuarioID, $nombre) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "' AND nombre = '" . $nombre . "'";        
        $datosImagenes = parent::obtenerDatos($query);        
        if($datosImagenes) {
            return $datosImagenes[0];
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
            if(!isset($datos['usuarioID']) || !isset($datos['nombre']) || !isset($datos['nombreArchivo']) || !isset($datos['imgData']) || !isset($datos['mostrar'])){
                return $_respuestas->error_400();
            }else{                
                $this->usuarioID = $datos["usuarioID"];
                $this->nombre = $datos["nombre"];
                $this->nombreArchivo = $datos["nombreArchivo"];
                $this->imgData = $datos["imgData"];                
                $this->mostrar = $datos["mostrar"];                
                $resp = $this->insertarOModificarImagen();
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

    //Insertar o Modificar imagen 
    private function insertarOModificarImagen() {
        $verif = true;
        $verificarExistencia = $this->obtenerImagenPorNombre($this->usuarioID, $this->nombre);        
        if($verificarExistencia) {
            $resp = $this->modificarImagen();
            if(!$resp){
                $verif = false;
            }
        } else {
            $resp = $this->insertarImagen();
            if(!$resp) {
                $verif = false;
            }
        }              
        return $verif;
    }

    private function insertarImagen(){
        $query = "INSERT INTO " . $this->tabla . " (nombre, nombreArchivo, imgData, mostrar, usuarioID) values ('" . $this->nombre . "','" . $this->nombreArchivo . "','" . $this->imgData . "','" . $this->mostrar . "','" . $this->usuarioID . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }    

    private function modificarImagen(){
        $query = "UPDATE " . $this->tabla . " SET imgData = '" . $this->imgData . "', nombreArchivo = '" . $this->nombreArchivo . "', mostrar = '" . $this->mostrar . "' WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";         
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
                $resp = $this->modificarMostrarImg();                               
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

    private function modificarMostrarImg(){
        $query = "UPDATE " . $this->tabla . " SET mostrar ='" . $this->mostrar ."' WHERE usuarioID = '" . $this->usuarioID . "' AND nombre = '" . $this->nombre . "'";         
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }
}

?>