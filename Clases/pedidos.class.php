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
}

?>