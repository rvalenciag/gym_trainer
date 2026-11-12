// Gym Instructor: JS vanilla mínimo, sin AJAX. Todo lo pinta PHP; JS solo mueve el DOM.
(function() {
'use strict';

// Auto-cierre de alertas Bootstrap a los 5 segundos.
var al = document.querySelector('.alert');
if (al) {
    setTimeout(function() {
        al.classList.remove('show');
    }, 5000);
}

// ---- CU06: constructor semanal de rutina ----
var btnAdd = document.getElementById('btn-add-det');
var tbody = document.getElementById('det-body');
var tpl = document.getElementById('det-row-tpl');
var dayTabs = document.querySelectorAll('[data-routine-day]');
var dayNames = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

function refrescarFilasRutina() {
    if (!tbody) {
        return;
    }
    var dia = tbody.dataset.activeDay || '1';
    tbody.querySelectorAll('[data-routine-day-row]').forEach(function(fila) {
        fila.style.display = fila.dataset.routineDayRow === dia ? '' : 'none';
    });
    var titulo = document.getElementById('routine-day-title');
    if (titulo) {
        titulo.textContent = 'Día ' + dia + ' · ' + dayNames[Number(dia) - 1];
    }
    var hint = document.getElementById('routine-add-hint');
    if (hint) {
        hint.textContent = 'Se añadirá al ' + dayNames[Number(dia) - 1].toLowerCase() + ' (Día ' + dia + ')';
    }
}

dayTabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
        if (!tbody) {
            return;
        }
        tbody.dataset.activeDay = tab.dataset.routineDay;
        dayTabs.forEach(function(item) {
            item.classList.toggle('active', item === tab);
        });
        refrescarFilasRutina();
    });
});
if (btnAdd && tbody && tpl) {
    var nextIdx = parseInt(tbody.dataset.next || '0', 10);
    btnAdd.addEventListener('click', function() {
        var dia = tbody.dataset.activeDay || '1';
        var html = tpl.innerHTML.split('__IDX__').join(String(nextIdx)).split('__DAY__').join(dia);
        nextIdx++;
        tbody.insertAdjacentHTML('beforeend', html);
        refrescarFilasRutina();
    });
    tbody.addEventListener('click', function(ev) {
        var b = ev.target.closest('.btn-del-det');
        if (b) {
            b.closest('tr').remove();
            refrescarFilasRutina();
        }
    });
    refrescarFilasRutina();
}

function textoBoton(btn, texto) {
    var etiqueta = btn ? btn.querySelector('span') : null;
    if (etiqueta) {
        etiqueta.textContent = texto;
    } else if (btn) {
        btn.textContent = texto;
    }
}

// ---- CU03/CU04/CU05: modo edición sin recarga (data-* de la fila al form superior) ----
var multimediaActual = null;
var archivoSeleccionado = null;
var urlArchivoTemporal = '';

document.querySelectorAll('[data-edit]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var d = btn.dataset;
        if (d.tipo !== undefined) {
            liberarArchivoTemporal(true);
            multimediaActual = {
                tipo: d.tipo,
                url: d.url || ''
            };
        }
        Object.keys(d).forEach(function(k) {
            if (k === 'edit') {
                return;
            }
            var inp = document.getElementById('f-' + k);
            if (inp) {
                inp.value = d[k];
            }
        });
        var btnSave = document.getElementById('btn-save');
        if (btnSave) {
            textoBoton(btnSave, btnSave.dataset.alt || btnSave.textContent);
        }
        var hid = document.getElementById('f-id');
        if (hid) {
            hid.value = d.edit;
        }
        var pass = document.getElementById('f-pass-wrap');
        if (pass) {
            pass.style.display = 'none';
        }  // CU05: sin contraseña al editar
        var cancel = document.getElementById('btn-cancel');
        if (cancel) {
            cancel.style.display = '';
        }
        if (typeof toggleMedia === 'function') {
            toggleMedia();
        }
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
});
var cancel = document.getElementById('btn-cancel');
if (cancel) {
    cancel.addEventListener('click', function() {
        var form = cancel.closest('form');
        if (form) {
            form.reset();
        }
        var hid = document.getElementById('f-id');
        if (hid) {
            hid.value = '';
        }
        var btnSave = document.getElementById('btn-save');
        if (btnSave && btnSave.dataset.orig) {
            textoBoton(btnSave, btnSave.dataset.orig);
        }
        var pass = document.getElementById('f-pass-wrap');
        if (pass) {
            pass.style.display = '';
        }
        multimediaActual = null;
        liberarArchivoTemporal(true);
        if (typeof toggleMedia === 'function') {
            toggleMedia();
        }
        actualizarVistaPreviaMultimedia();
    });
}

// ---- CU04: mostrar input según tipo de multimedia ----
var selTipo = document.getElementById('f-tipo');

function ocultarVistaPreviaMultimedia() {
    var contenedor = document.getElementById('media-actual');
    if (!contenedor) {
        return;
    }
    contenedor.replaceChildren();
    contenedor.style.display = 'none';
}

function liberarArchivoTemporal(limpiarCampo) {
    if (urlArchivoTemporal !== '') {
        URL.revokeObjectURL(urlArchivoTemporal);
    }
    archivoSeleccionado = null;
    urlArchivoTemporal = '';
    if (limpiarCampo) {
        var campoArchivo = document.getElementById('f-archivo');
        if (campoArchivo) {
            campoArchivo.value = '';
        }
    }
}

function mostrarMensajeSinRecurso(contenedor, titulo) {
    var encabezado = document.createElement('strong');
    encabezado.textContent = titulo;
    var mensaje = document.createElement('div');
    mensaje.className = 'text-muted small mt-1';
    mensaje.textContent = 'No hay un recurso multimedia guardado.';
    contenedor.append(encabezado, mensaje);
    contenedor.style.display = '';
}

function mostrarImagenOVideo(contenedor, recurso) {
    var esVideo = recurso.esVideo;
    var etiqueta = document.createElement('strong');
    etiqueta.textContent = esVideo
        ? (recurso.temporal ? 'Video seleccionado' : 'Video actual')
        : (recurso.temporal ? 'Imagen seleccionada' : 'Imagen actual');
    var elemento = document.createElement(esVideo ? 'video' : 'img');
    elemento.src = recurso.url;
    elemento.className = 'img-fluid rounded border d-block mt-2';
    elemento.style.maxHeight = '280px';
    if (esVideo) {
        elemento.controls = true;
    } else {
        elemento.alt = 'Recurso multimedia del ejercicio';
    }
    contenedor.append(etiqueta, elemento);
    contenedor.style.display = '';
}

function mostrarIframe(contenedor, codigo, temporal) {
    var documento = new DOMParser().parseFromString(codigo, 'text/html');
    var iframeOriginal = documento.querySelector('iframe');
    var src = iframeOriginal ? iframeOriginal.getAttribute('src') : '';
    if (!src || !/^https:\/\//i.test(src)) {
        ocultarVistaPreviaMultimedia();
        return;
    }

    var etiqueta = document.createElement('strong');
    etiqueta.textContent = temporal ? 'Vista previa del iframe' : 'Iframe actual';
    var iframe = document.createElement('iframe');
    iframe.src = src;
    iframe.title = iframeOriginal.getAttribute('title') || 'Vista previa multimedia';
    iframe.className = 'w-100 rounded border d-block mt-2';
    iframe.height = '280';
    iframe.loading = 'lazy';
    iframe.allowFullscreen = true;
    contenedor.append(etiqueta, iframe);
    contenedor.style.display = '';
}

function actualizarVistaPreviaMultimedia() {
    var contenedor = document.getElementById('media-actual');
    if (!contenedor || !selTipo) {
        return;
    }

    var tipo = selTipo.value;
    var texto = document.getElementById('f-texto');
    contenedor.replaceChildren();

    if (tipo === 'VIDEO_URL') {
        ocultarVistaPreviaMultimedia();
        return;
    }

    if (tipo === 'IMAGEN' && archivoSeleccionado !== null) {
        mostrarImagenOVideo(contenedor, {
            url: urlArchivoTemporal,
            esVideo: /^video\//i.test(archivoSeleccionado.type)
                || /\.(mp4|webm)$/i.test(archivoSeleccionado.name),
            temporal: true
        });
        return;
    }

    if (tipo === 'IFRAME_HTML' && texto && texto.value.trim() !== '') {
        mostrarIframe(contenedor, texto.value, true);
        return;
    }

    if (!multimediaActual || tipo !== multimediaActual.tipo) {
        ocultarVistaPreviaMultimedia();
        return;
    }

    if (tipo === 'IMAGEN') {
        if (multimediaActual.url === '') {
            mostrarMensajeSinRecurso(contenedor, 'Imagen / Video actual');
            return;
        }
        mostrarImagenOVideo(contenedor, {
            url: multimediaActual.url,
            esVideo: /\.(mp4|webm)(?:[?#].*)?$/i.test(multimediaActual.url),
            temporal: false
        });
        return;
    }

    if (tipo === 'IFRAME_HTML') {
        if (multimediaActual.url === '') {
            mostrarMensajeSinRecurso(contenedor, 'Vista previa del iframe');
            return;
        }
        mostrarIframe(contenedor, multimediaActual.url, false);
        return;
    }

    ocultarVistaPreviaMultimedia();
}

function toggleMedia() {
    if (!selTipo) {
        return;
    }
    var v = selTipo.value;
    var wFile = document.getElementById('w-archivo');
    var wTxt = document.getElementById('w-texto');
    var lbl = document.getElementById('lbl-texto');
    if (wFile) {
        wFile.style.display = v === 'IMAGEN' ? '' : 'none';
    }
    if (wTxt) {
        wTxt.style.display = v === 'IMAGEN' ? 'none' : '';
    }
    if (lbl) {
        lbl.textContent = v === 'VIDEO_URL' ? 'URL del video' : 'Código iframe';
    }
    document.querySelectorAll('[data-media-type]').forEach(function(tab) {
        tab.classList.toggle('active', tab.dataset.mediaType === v);
    });
    actualizarVistaPreviaMultimedia();
}
if (selTipo) {
    selTipo.addEventListener('change', toggleMedia);
    document.querySelectorAll('[data-media-type]').forEach(function(tab) {
        tab.addEventListener('click', function() {
            selTipo.value = tab.dataset.mediaType;
            toggleMedia();
        });
    });
    toggleMedia();
}

var campoArchivo = document.getElementById('f-archivo');
if (campoArchivo) {
    campoArchivo.addEventListener('change', function() {
        liberarArchivoTemporal(false);
        if (campoArchivo.files && campoArchivo.files[0]) {
            archivoSeleccionado = campoArchivo.files[0];
            urlArchivoTemporal = URL.createObjectURL(archivoSeleccionado);
        }
        actualizarVistaPreviaMultimedia();
    });
}

var campoTextoMultimedia = document.getElementById('f-texto');
if (campoTextoMultimedia) {
    campoTextoMultimedia.addEventListener('input', actualizarVistaPreviaMultimedia);
}

// Login y alta de clientes: alternar visibilidad sin modificar el valor enviado.
document.querySelectorAll('[data-password-toggle]').forEach(function(button) {
    button.addEventListener('click', function() {
        var input = document.getElementById(button.dataset.passwordToggle);
        if (!input) {
            return;
        }
        var oculto = input.type === 'password';
        input.type = oculto ? 'text' : 'password';
        button.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
});

// ---- CU07: pestañas de días + desplegar guía (contenido ya pintado por PHP) ----
document.querySelectorAll('[data-dia-btn]').forEach(function(b) {
    b.addEventListener('click', function(ev) {
        ev.preventDefault();
        var dia = b.dataset.diaBtn;
        document.querySelectorAll('[data-dia-btn]').forEach(function(x) {
            x.classList.toggle('active', x === b);
        });
        document.querySelectorAll('[data-dia-sec]').forEach(function(s) {
            s.style.display = s.dataset.diaSec === dia ? '' : 'none';
        });
    });
});
document.querySelectorAll('[data-guia-btn]').forEach(function(b) {
    b.addEventListener('click', function() {
        var g = document.getElementById(b.dataset.guiaBtn);
        if (g) {
            g.style.display = g.style.display === 'none' ? '' : 'none';
        }
    });
});
})();
