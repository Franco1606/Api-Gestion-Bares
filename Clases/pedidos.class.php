<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class pedidos extends conexion {    

    private $tabla = "pedidos";
    //Columnas de la tabla de pedidos
    private $pedidoID;
    private $ordenID;
    private $finalizoMozoID;
    private $categoriaID;
    private $productoID;
    private $usuarioID;
    private $sesionID;
    private $cantidad;
    private $nombre;
    private $precio;
    private $cocina;
    private $comentario;

    public function obtenerPedidos($ordenID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE ordenID = '" . $ordenID . "'";
        $datosProudctos = parent::obtenerDatos($query);        
        if($datosProudctos) {
            return $datosProudctos;
        } else {
            return 0;
        }
    }

    public function obtenerPedidosPorSesion($sesionID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE sesionID = '" . $sesionID . "'";
        $datosProudctos = parent::obtenerDatos($query);        
        if($datosProudctos) {
            return $datosProudctos;
        } else {
            return 0;
        }
    }

    public function put($postBody) {        
        $_respuestas = new respuestas;
        $_token = new token;
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1) {
            $arrayPedidos = $datos["pedido"];            
            $resp = $this->modificarPedidos($arrayPedidos);            
            if($resp) {            
                $respuesta = $_respuestas->response;
                $respuesta["result"] = array(                
                    "status" => "ok"                         
                );                
                return $respuesta;
            } else {
                return $_respuestas->error_500("Error interno del servidor, el cambio no se guardo o no hubo modificaciones en el registro");
            }
        } else {
            return $verificarToken;
        }
    }

    private function modificarPedidos($arrayPedidos){                       
        $verificador = true;
        foreach($arrayPedidos as $pedido) {
            $this->cocina = $pedido['cocina'];
            $this->pedidoID = $pedido['pedidoID'];            
            $query = "UPDATE " . $this->tabla . " SET cocina = '" . $this->cocina ."' WHERE pedidoID = '" . $this->pedidoID . "'";
            $resp = parent::nonQueryUpdate($query);                          
            if($resp == 0){
                $verificador = false;
            }
        }
        return $verificador;
    }


}

?>