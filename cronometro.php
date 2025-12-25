<?php
require_once __DIR__ . "/php/cronometroClase.php";
session_start();

/* 
   Gestión de sesión (persistencia del cronómetro)
    */
if (!isset($_SESSION["cronometro"]) || !($_SESSION["cronometro"] instanceof Cronometro)) {
    $_SESSION["cronometro"] = new Cronometro();
}

$cronometro = $_SESSION["cronometro"];
$mensaje = "";

/* 
   Gestión de la botonera
    */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"])) {

    if ($_POST["accion"] === "arrancar") {
        $cronometro->arrancar();
        $mensaje = "Cronómetro arrancado.";
    }

    if ($_POST["accion"] === "parar") {
        $cronometro->parar();
        $mensaje = "Cronómetro detenido.";
    }

    if ($_POST["accion"] === "mostrar") {
        $mensaje = "Tiempo: " . $cronometro->mostrar();
    }

    $_SESSION["cronometro"] = $cronometro;
}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cronómetro PHP - MotoGP Desktop</title>
    	<link rel="icon" href="multimedia/favicon48px.ico">

 <meta name="author" content="Richard Robin Roque Del Rio" />
    <meta name="description" content="Cronometro con PHP" />
    <meta name="keywords" content="MotoGP, PHP, cronometro" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Hojas de estilo del proyecto -->
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="estilo/layout.css">
    <script src="js/menu.js"></script>
</head>

<body>

<header>
    <h1><a href="index.html" title="Página principal">MotoGP Desktop</a></h1>
    <button type="button" aria-expanded="false"  title="Menu de navegacion">Menú</button>
 <nav>
    <a href="index.html" title="Página principal">Inicio</a>
    <a href="piloto.html" title="Información del piloto">Piloto</a>
    <a href="circuito.html" title="Información del circuito">Circuito</a>
    <a href="meteorologia.html" title="Información de la meteorología">Meteorología</a>
    <a href="clasificaciones.php" title="Clasificaciones">Clasificaciones</a>
    <a class="active" href="juegos.html" title="Listado de juegos">Juegos</a>
    <a href="ayuda.html" title="Ayuda del sitio">Ayuda</a>
  </nav>
</header>

<main>

    <p>Estás en: <a href="index.html" title="Página principal">Inicio</a> | <a href="juegos.html" title="Listado de juegos">Juegos</a> | <strong>Cronómetro PHP</strong></p>

    <section>
        <h2>Cronómetro en PHP</h2>

        <article>
            <h3>Cronómetro</h3>
            <p><?= htmlspecialchars($mensaje) ?></p>

            <form method="post">
                <p>
                    <button type="submit" name="accion" value="arrancar" aria-label="Arrancar el cronómetro">
                        Arrancar
                    </button>

                    <button type="submit" name="accion" value="parar" aria-label="Parar el cronómetro">
                        Parar
                    </button>

                    <button type="submit" name="accion" value="mostrar" aria-label="Mostrar el tiempo transcurrido">
                        Mostrar
                    </button>
                </p>
            </form>
        </article>
    </section>

</main>

</body>
</html>
