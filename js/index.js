// Obtener la modal
var modalRegistro = document.getElementById("miModalRegistro");
var modalRecuperar = document.getElementById("miModalRecuperar");

// Obtener el botón que abre la modal
var btnRegistro = document.querySelector(".abrir-modal-registro");
var btnRecuperar = document.querySelector(".abrir-modal-recuperar");

// Obtener el elemento <span> que cierra la modal
var spanRegistro = document.querySelector(".cerrarRegistro");
var spanRecuperar = document.querySelector(".cerrarRecuperar");

// Cuando el usuario hace clic en el botón, se abre la modal 
btnRegistro.onclick = function() {
    modalRegistro.style.display = "flex";
}
btnRecuperar.onclick = function() {
    modalRecuperar.style.display = "flex";
}

// Cuando el usuario hace clic en <span> (x), cierra la modal
spanRegistro.onclick = function() {
    modalRegistro.style.display = "none";
}
spanRecuperar.onclick = function() {
    modalRecuperar.style.display = "none";
}

// Cuando el usuario hace clic fuera de la modal, se cierra
window.onclick = function(event) {
    if (event.target == modalRegistro) {
        modalRegistro.style.display = "none";
    }
    if(event.target == modalRecuperar){
        modalRecuperar.style.display = "none";
    }
}