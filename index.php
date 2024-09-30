<?php
session_start();
include "dbo/conexion.php";
$email = "";
$num_items_carrito = 0;
$id = 0;

if (isset($_SESSION['usuario'])) {
    $email = $_SESSION['usuario'];
    $id = $_SESSION['id'];

    $sql = "SELECT SUM(cantidad) AS CantidadCarrito FROM carrito WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $num_items_carrito = $row['CantidadCarrito'];
    } else {
        $num_items_carrito = 0;
    }
} else {
    $num_items_carrito = 0;
}



?>




<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Tienda</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="admin/css/sb-admin-2.css" rel="stylesheet" />
    </head>
    <body>
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container px-4 px-lg-5">
                <a class="navbar-brand" href="index.php">Tienda</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <?php if ($email == ""): ?>
                            <li class="nav-item"><a class="nav-link" href="login.html">Iniciar sesión</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.html">Registrarse</a></li>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($email); ?>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="compras.php">Compras</a></li>
                                    <li><a class="dropdown-item" href="pagos_pendientes.php">Pagos Pendientes</a></li>
                                    <li><a class="dropdown-item" href="cambiar_contraseña.php">Cambiar Contraseña</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($email != ""): ?>
                        <div class="d-flex">
                            <button class="btn btn-outline-dark" id="btnCarrito">
                                <i class="bi-cart-fill me-1"></i>
                                Cart
                                <span class="badge bg-dark text-white ms-1 rounded-pill"><?php echo $num_items_carrito; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>


        <!-- Header-->
        <header class="bg-dark py-5">
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                    <h1 class="display-4 fw-bolder">Shop in style</h1>
                    <p class="lead fw-normal text-white-50 mb-0">With this shop hompeage template</p>
                </div>
            </div>
        </header>
        <!-- Section-->
        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5">
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                <?php
                    include 'dbo/conexion.php';
                    $query = "SELECT id_articulo, nombre, descripcion, precio, imagen_url FROM articulos WHERE estatus = 'alta'";
                    $result = $conexion->query($query);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id_articulo = $row['id_articulo'];
                            $nombre = $row['nombre'];
                            $descripcion = $row['descripcion'];
                            $precio = $row['precio'];
                            // Asegúrate de quitar los "../" de la imagen
                            $imagen = $row['imagen_url'] ? basename($row['imagen_url']) : 'https://dummyimage.com/450x300/dee2e6/6c757d.jpg';
                            echo '
                            <div class="col mb-5">
                                <div class="card h-100">
                                    <!-- Imagen del producto -->
                                    <img class="card-img-top" src="admin/IMG/' . $imagen . '" alt="' . $nombre . '" />
                                    <!-- Detalles del producto -->
                                    <div class="card-body p-4">
                                        <div class="text-center">
                                            <!-- Nombre del producto -->
                                            <h5 class="fw-bolder">' . $nombre . '</h5>
                                            <!-- Precio del producto -->
                                            <p>$' . number_format($precio, 2) . '</p>
                                        </div>
                                    </div>
                                    <!-- Acciones del producto -->
                                    <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                        <div class="text-center">
                                            <a class="btn btn-outline-dark mt-auto" onclick="agregarCarrito(' . $id_articulo . ')" href="#">Agregar al carrito</a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<p>No hay artículos disponibles.</p>';
                    }
                    $conexion->close();
                ?>


                </div>
            </div>
        </section>
        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container"><p class="m-0 text-center text-white">Copyright &copy; josueeb2001@gmail.com</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <!-- <script src="js/scripts.js"></script> -->
    </body>

<script>
function agregarCarrito(id_articulo) {
    var usuario=<?php echo $id; ?>;
    if (usuario === 0){
        alert("Inicia sesión para poder agregar articulos al carro");
        window.location.href="login.html";
        return;

    }



    const formData = new FormData();
    formData.append('id_usuario', <?php echo $id; ?>);
    formData.append('id_articulo', id_articulo);
    formData.append('cantidad', 1);
    formData.append('funcion', 'agregarAlCarrito');

    // Hacer la solicitud POST usando fetch
    fetch('controller/ArticuloController.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Verificar la respuesta del servidor
        if (data.ok === 0) {
            // location.reload();
        } else {
            alert(data.mensaje); // Mensaje de error
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

document.getElementById('btnCarrito').addEventListener('click', function() {
        // Redirige a la página deseada, por ejemplo, al carrito de compras
        window.location.href = 'Ver_Carrito.php';
    });




</script>



</html>
