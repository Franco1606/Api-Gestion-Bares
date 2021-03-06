<?php 
require_once "clases/respuestas.class.php";
require_once "clases/ordenes.class.php";

$ordenes = new ordenes;
$_respuestas = new respuestas;

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["sesionID"])) {
        $sesionID = $_GET["sesionID"];
        $datosordenes = $ordenes->obtenerOrdenes($sesionID);
    } else if(isset($_GET["ordenID"])) {
        $ordenID = $_GET["ordenID"];
        $datosordenes = $ordenes->obtenerOrden($ordenID);
    } else if(isset($_GET["usuarioID"]) && isset($_GET["cocina"])) {
        $usuarioID = $_GET["usuarioID"];
        $cocina = $_GET["cocina"];
        $datosordenes = $ordenes->obtenerOrdenesPorUsuario($usuarioID, $cocina);
    } 
    header("Content-Type: application/json");
    echo json_encode($datosordenes);
    http_response_code(200);

} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");    
    //Envio de datos al manejador    
    $respuesta = $ordenes->post($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else {
        http_response_code(200);
    }
    echo json_encode($respuesta);

} else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    //Recibir de datos
    $postBody = file_get_contents("php://input");
    //Envio de datos al manejador
    $respuesta = $ordenes->put($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($respuesta);

} else if($_SERVER["REQUEST_METHOD"] == "DELETE") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");
    //Envio de datos al manejador
    $respuesta = $ordenes->delete($postBody);
    //Respuesta
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else {
        http_response_code(200);
    }
    echo json_encode($respuesta);    
}

else {    
    header("Content-Type: application/json");
    $respuesta = $_respuestas->error_405();
    echo json_encode($respuesta);
} 

?>