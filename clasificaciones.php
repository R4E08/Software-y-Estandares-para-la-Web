<?php

class Clasificacion {

    public $documento;
    public $xml;
    private $ns = "http://www.uniovi.es"; 

    public function __construct() {
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {
        if (!file_exists($this->documento)) {
            return null;
        }

        $this->xml = simplexml_load_file($this->documento);
        return $this->xml;
    }

    public function getGanador() {
        $root = $this->xml->children($this->ns);
        $v = $root->vencedorCarrera->children($this->ns);

        $tiempoIso = (string)$v->tiempoVencedor;

        return [
            "nombre" => (string)$v->nombreVencedor,
            "tiempo" => $this->formatearTiempo($tiempoIso)
        ];
    }

    public function getClasificacionMundial() {
        $root = $this->xml->children($this->ns);
        $lista = [];

        foreach ($root->clasificados->children($this->ns) as $piloto) {
            $lista[] = [
                "posicion" => (string)$piloto["posicion"],
                "nombre" => (string)$piloto
            ];
        }

        return $lista;
    }

    
    private function formatearTiempo(string $iso): string {

        if (!preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+(?:\.\d+)?)S)?$/', $iso, $m)) {
            
            return $iso;
        }

        $horas = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
        $min   = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
        $seg   = isset($m[3]) && $m[3] !== '' ? (float)$m[3] : 0.0;

        if ($horas > 0) {

            return sprintf('%02d:%02d:%06.3f', $horas, $min, $seg);
        } else {

            return sprintf('%02d:%06.3f', $min, $seg);
        }
    }
}

$clasificacion = new Clasificacion();
$xml = $clasificacion->consultar();

$ganador = $clasificacion->getGanador();
$lista = $clasificacion->getClasificacionMundial();

?>
<!DOCTYPE HTML>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>MotoGP Desktop - Clasificaciones</title>
    <link rel="icon" href="multimedia/favicon48px.ico">

    <meta name="author" content="Richard Roque" />
    <meta name="description" content="Clasificaciones MotoGP desde XML con PHP" />
    <meta name="keywords" content="MotoGP, XML, PHP, clasificaciones" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="estilo/estilo.css" />
    <link rel="stylesheet" href="estilo/layout.css" />
</head>

<body>

<header>
    <h1><a href="index.html">MotoGP Desktop</a></h1>
 <nav>
    <a href="index.html" title="Página principal">Inicio</a>
    <a href="piloto.html" title="Información del piloto">Piloto</a>
    <a href="circuito.html" title="Información del circuito">Circuito</a>
    <a href="meteorologia.html" title="Información de la meteorología">Meteorología</a>
    <a class="active" href="clasificaciones.php" title="Clasificaciones">Clasificaciones</a>
    <a  href="juegos.html" title="Listado de juegos">Juegos</a>
    <a href="ayuda.html" title="Ayuda del sitio">Ayuda</a>
  </nav>
</header>

<main>
    <p>Estás en: <a href="index.html">Inicio</a> | <strong>Clasificaciones</strong></p>

    <section>
        <h2>Ganador de la carrera</h2>
        <p><strong>Piloto:</strong> <?= $ganador["nombre"] ?></p>
        <p><strong>Tiempo:</strong> <?= $ganador["tiempo"] ?></p>
    </section>

    <section>
        <h2>Clasificación del mundial tras la carrera</h2>
        <ol>
            <?php foreach ($lista as $p): ?>
                <li>
                    <?= $p["posicion"] ?> — <?= $p["nombre"] ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>

</main>

</body>
</html>
