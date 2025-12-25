<?php
/* 
   Clase Cronometro (OOP en PHP) - SOLO CLASE
*/
class Cronometro
{

    private float $tiempo;
    private ?float $inicio;

    public function __construct()
    {
        $this->tiempo = 0.0;
        $this->inicio = null;
    }

    public function reiniciar(): void
    {
        $this->tiempo = 0.0;
        $this->inicio = null;
    }

    public function arrancar(): void
    {
        $this->inicio = microtime(true);
    }

    public function parar(): void
    {
        if ($this->inicio !== null) {
            $this->tiempo = microtime(true) - $this->inicio;
            $this->inicio = null;
        }
    }

    public function getTiempo(): int
    {
        return (int) round($this->tiempo);
    }

    public function mostrar(): string
    {
        $tiempoActual = $this->tiempo;

        if ($this->inicio !== null) {
            $tiempoActual = microtime(true) - $this->inicio;
        }

        $min = floor($tiempoActual / 60);
        $seg = $tiempoActual - ($min * 60);

        return sprintf("%02d:%04.1f", $min, $seg);
    }
}
