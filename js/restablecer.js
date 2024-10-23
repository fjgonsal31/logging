document.querySelector('form').onsubmit = function(e) {
    var password = document.querySelector('input[name="nueva_password"]').value;
    var confirm = document.querySelector('input[name="confirmar_password"]').value;
    if (password !== confirm) {
        alert('Las contraseñas no coinciden');
        e.preventDefault();
    }
};