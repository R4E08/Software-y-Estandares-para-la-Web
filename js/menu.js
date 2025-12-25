
/*
  Autor: Richard Roque Del Río – UO302698
*/

class MenuAccesible {
    #header;
    #btn;
    #nav;

    constructor(header) {
        this.#header = header;
        this.#btn = header.querySelector('button[type="button"]');
        this.#nav = header.querySelector("nav");

        if (!this.#btn || !this.#nav) return;

        this.#inicializarAtributos();
        this.#registrarEventos();
    }

    #inicializarAtributos() {
        this.#btn.setAttribute("aria-label", "Abrir menú");
        this.#btn.setAttribute("aria-expanded", "false");
    }

    #registrarEventos() {
        this.#btn.addEventListener("click", this.#onClick.bind(this));
    }

    #onClick(e) {
        e.preventDefault();
        const next = this.#btn.getAttribute("aria-expanded") !== "true";
        this.#btn.setAttribute("aria-expanded", next ? "true" : "false");
    }

    cerrar() {
        if (!this.#btn) return;
        this.#btn.setAttribute("aria-expanded", "false");
    }
}

/* Controlador global: instancia menús y cierra con ESC */
class ControladorMenus {
    #menus = [];

    constructor() {
        this.#crearMenus();
        this.#registrarEscape();
    }

    #crearMenus() {
        const headers = document.querySelectorAll("body > header");
        headers.forEach(h => {
            const menu = new MenuAccesible(h);
            this.#menus.push(menu);
        });
    }

    #registrarEscape() {
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") this.cerrarTodos();
        });
    }

    cerrarTodos() {
        this.#menus.forEach(m => m.cerrar());
    }
}

document.addEventListener("DOMContentLoaded", () => {
    new ControladorMenus();
});
