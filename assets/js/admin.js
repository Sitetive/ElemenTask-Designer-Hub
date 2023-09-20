document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.gv-completar-tarea');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            completarTarea(this);
        });
    });
});

function completarTarea(element) {
    let post_id = element.getAttribute("data-id");
    
    let xhr = new XMLHttpRequest();
    let formData = new FormData();

    formData.append("action", "gv_completar_tarea");
    formData.append("post_id", post_id);
    formData.append("nonce", gv_vars.nonce);

    xhr.open("POST", gv_vars.ajax_url, true);
    
    /*xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            alert(xhr.responseText);
        }
    };*/

    xhr.send(formData);
}
