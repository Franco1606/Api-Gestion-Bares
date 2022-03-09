<?php 
require_once "clases/respuestas.class.php";
require_once "clases/productos.class.php";

$_productos = new productos;
$_respuestas = new respuestas;

if($_SERVER["REQUEST_METHOD"] == "GET") {    
    if(isset($_GET["usuarioID"]) && isset($_GET["categoriaID"])) {
        $usuarioID = $_GET["usuarioID"];
        $categoriaID = $_GET["categoriaID"];
        $datosProductos = $_productos->obtenerProductos($usuarioID, $categoriaID);
    } else if(isset($_GET["productoID"])) {
        $productoID = $_GET["productoID"];
        $datosProductos = $_productos->obtenerProducto($productoID);
    }
    header("Content-Type: application/json");
    echo json_encode($datosProductos);
    http_response_code(200);

} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");   
    //Envio de datos al manejador
    $respuesta = $_productos->post($postBody);
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
    $respuesta = $_productos->put($postBody);
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
    $respuesta = $_productos->delete($postBody);
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