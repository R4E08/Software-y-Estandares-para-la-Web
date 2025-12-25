/*
  Autor: Richard Roque Del Río – UO302698
*/

class Cronometro {
  #tiempo;
  #inicio;
  #corriendo;

  constructor() {
    this.#tiempo = 0;
    this.#inicio = null;
    this.#corriendo = null;

    
    const botones = document.querySelectorAll("main button");
    if (botones.length >= 3) {
      botones[0].addEventListener("click", () => this.arrancar());
      botones[1].addEventListener("click", () => this.parar());
      botones[2].addEventListener("click", () => this.reiniciar());
    }
  }

  arrancar() {
    if (this.#corriendo !== null) {
      clearInterval(this.#corriendo);
    }

    this.#tiempo = 0;

    try {
      if (typeof Temporal !== "undefined") {
        this.#inicio = Temporal.Now.instant();
      } else {
        throw new Error();
      }
    } catch {
      this.#inicio = new Date();
    }

    this.#corriendo = setInterval(this.#actualizar.bind(this), 100);
  }

  #actualizar() {
    let ahora;

    try {
      if (typeof Temporal !== "undefined") {
        ahora = Temporal.Now.instant();
        const duracion = ahora.since(this.#inicio);
        this.#tiempo = Math.floor(
          duracion.total({ unit: "milliseconds" })
        );
      } else {
        throw new Error();
      }
    } catch {
      ahora = new Date();
      this.#tiempo = Math.floor(ahora - this.#inicio);
    }

    this.#mostrar();
  }

  #mostrar(textoExtra = "") {
    const marcador = document.querySelector("main > p");
    if (marcador) {
      marcador.textContent = `${this.getTiempo()} ${textoExtra}`;
    }
  }

  parar() {
    if (this.#corriendo !== null) {
      clearInterval(this.#corriendo);
      this.#corriendo = null;
    }
  }

  reiniciar() {
    if (this.#corriendo !== null) {
      clearInterval(this.#corriendo);
      this.#corriendo = null;
    }
    this.#tiempo = 0;
    this.#mostrar();
  }

  getTiempo() {
    const minutos = Math.floor(this.#tiempo / 60000);
    const segundos = Math.floor((this.#tiempo % 60000) / 1000);
    const decimas = Math.floor((this.#tiempo % 1000) / 100);

    return `${String(minutos).padStart(2, "0")}:` +
      `${String(segundos).padStart(2, "0")}.` +
      `${decimas}`;
  }
}

/* Inicialización */
document.addEventListener("DOMContentLoaded", () => {
  new Cronometro();
});
