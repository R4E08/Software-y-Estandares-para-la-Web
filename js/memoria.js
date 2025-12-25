
/*
  Autor: Richard Roque Del Río – UO302698
*/

class Memoria {
    #tablero_bloqueado;
    #primera_carta;
    #segunda_carta;
    #cronometro;

    constructor() {
        this.#tablero_bloqueado = true;
        this.#primera_carta = null;
        this.#segunda_carta = null;

        this.#barajarCartas();
        this.#registrarEventos();

        this.#cronometro = new Cronometro();
        this.#cronometro.arrancar();

        this.#tablero_bloqueado = false;
    }

    #registrarEventos() {
        const cartas = document.querySelectorAll("main article");
        cartas.forEach(carta => {
            carta.addEventListener("click", (event) => {
                this.voltearCarta(event.currentTarget);
            });
        });
    }

    voltearCarta(carta) {
        if (this.#tablero_bloqueado) return;

        const estado = carta.getAttribute("data-estado");
        if (estado === "revelada" || estado === "volteada") return;

        // evita doble click en la misma carta como 2ª
        if (carta === this.#primera_carta) return;

        carta.setAttribute("data-estado", "volteada");

        if (this.#primera_carta === null) {
            this.#primera_carta = carta;
            return;
        }

        // ya hay 2 cartas: bloquear
        this.#segunda_carta = carta;
        this.#tablero_bloqueado = true;

        this.#comprobarPareja();
    }

    #barajarCartas() {
        const cartas = Array.from(document.querySelectorAll("main article"));
        if (cartas.length === 0) return;

        // contenedor real: el padre de los articles (aquí será main)
        const contenedor = cartas[0].parentElement;

        // Fisher–Yates
        for (let i = cartas.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [cartas[i], cartas[j]] = [cartas[j], cartas[i]];
        }

        // reinsertar en el mismo contenedor, manteniendo antes h2 y el p del cronómetro
        cartas.forEach(carta => contenedor.appendChild(carta));
    }

    #reiniciarAtributos() {
        this.#primera_carta = null;
        this.#segunda_carta = null;
        this.#tablero_bloqueado = false;
    }

    #deshabilitarCartas() {
        this.#primera_carta.setAttribute("data-estado", "revelada");
        this.#segunda_carta.setAttribute("data-estado", "revelada");

        this.#comprobarJuego();
        this.#reiniciarAtributos();
    }

    #cubrirCartas() {
        setTimeout(() => {
            this.#primera_carta.removeAttribute("data-estado");
            this.#segunda_carta.removeAttribute("data-estado");
            this.#reiniciarAtributos();
        }, 1500);
    }

    #comprobarPareja() {
        const img1 = this.#primera_carta.querySelector("img")?.src;
        const img2 = this.#segunda_carta.querySelector("img")?.src;

        if (img1 && img2 && img1 === img2) {
            this.#deshabilitarCartas();
        } else {
            this.#cubrirCartas();
        }
    }

    #comprobarJuego() {
        const cartas = Array.from(document.querySelectorAll("main article"));
        const todasReveladas = cartas.every(
            c => c.getAttribute("data-estado") === "revelada"
        );

        if (todasReveladas) {
            this.#cronometro.parar();
        }
    }
}

document.addEventListener("DOMContentLoaded", () => new Memoria());
