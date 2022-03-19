<?php 
require_once "clases/respuestas.class.php";
require_once "clases/pedidos.class.php";

$_pedidos = new pedidos;
$_respuestas = new respuestas;

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["ordenID"])) {
        $ordenID = $_GET["ordenID"];        
        $datosPedidos = $_pedidos->obtenerPedidos($ordenID);
    } else if(isset($_GET["sesionID"])) {
        $sesionID = $_GET["sesionID"];        
        $datosPedidos = $_pedidos->obtenerPedidosPorSesion($sesionID);
    } 
    header("Content-Type: application/json");
    echo json_encode($datosPedidos);
    http_response_code(200);

} else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    //Recibir de datos
    $postBody = file_get_contents("php://input");
    //Envio de datos al manejador
    $respuesta = $_pedidos->put($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($respuesta);

} else {    
    header("Content-Type: application/json");
    $respuesta = $_respuestas->error_405();
    echo json_encode($respuesta);
} 

?>