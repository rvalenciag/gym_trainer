// Gym Instructor: JS vanilla mínimo, sin AJAX. Todo lo pinta PHP; JS solo mueve el DOM.
(function () {
    'use strict';

    // Auto-cierre de alertas Bootstrap a los 5 segundos.
    var al = document.querySelector('.alert');
    if (al) { setTimeout(function () { al.classList.remove('show'); }, 5000); }

    // ---- CU06: tabla dinámica de la rutina (agregar/quitar filas, nada se guarda) ----
    // Las filas usan índices explícitos det[0], det[1]... (los [] vacíos romperían el POST).
    var btnAdd = document.getElementById('btn-add-det');
    var tbody = document.getElementById('det-body');
    var tpl = document.getElementById('det-row-tpl');
    if (btnAdd && tbody && tpl) {
        var nextIdx = parseInt(tbody.dataset.next || '0', 10);
        btnAdd.addEventListener('click', function () {
            var frag = tpl.content.cloneNode(true);
            frag.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.split('__IDX__').join(String(nextIdx));
            });
            nextIdx++;
            tbody.appendChild(frag);
        });
        tbody.addEventListener('click', function (ev) {
            var b = ev.target.closest('.btn-del-det');
            if (b) { b.closest('tr').remove(); } // N valida que quede >= 1 fila al guardar
        });
    }

    // ---- CU03/CU04/CU05: modo edición sin recarga (data-* de la fila al form superior) ----
    document.querySelectorAll('[data-edit]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var d = btn.dataset;
            Object.keys(d).forEach(function (k) {
                if (k === 'edit') { return; }
                var inp = document.getElementById('f-' + k);
                if (inp) { inp.value = d[k]; }
            });
            var btnSave = document.getElementById('btn-save');
            if (btnSave) { btnSave.textContent = btnSave.dataset.alt || btnSave.textContent; }
            var hid = document.getElementById('f-id');
            if (hid) { hid.value = d.edit; }
            var pass = document.getElementById('f-pass-wrap');
            if (pass) { pass.style.display = 'none'; } // CU05: sin contraseña al editar
            var cancel = document.getElementById('btn-cancel');
            if (cancel) { cancel.style.display = ''; }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
    var cancel = document.getElementById('btn-cancel');
    if (cancel) {
        cancel.addEventListener('click', function () {
            var form = cancel.closest('form');
            if (form) { form.reset(); }
            var hid = document.getElementById('f-id');
            if (hid) { hid.value = ''; }
            var btnSave = document.getElementById('btn-save');
            if (btnSave && btnSave.dataset.orig) { btnSave.textContent = btnSave.dataset.orig; }
            var pass = document.getElementById('f-pass-wrap');
            if (pass) { pass.style.display = ''; }
            cancel.style.display = 'none';
        });
    }

    // ---- CU04: mostrar input según tipo de multimedia ----
    var selTipo = document.getElementById('f-tipo');
    function toggleMedia() {
        if (!selTipo) { return; }
        var v = selTipo.value;
        var wFile = document.getElementById('w-archivo');
        var wTxt = document.getElementById('w-texto');
        var lbl = document.getElementById('lbl-texto');
        if (wFile) { wFile.style.display = v === 'IMAGEN' ? '' : 'none'; }
        if (wTxt) { wTxt.style.display = v === 'IMAGEN' ? 'none' : ''; }
        if (lbl) { lbl.textContent = v === 'VIDEO_URL' ? 'URL del video' : 'Código iframe'; }
    }
    if (selTipo) { selTipo.addEventListener('change', toggleMedia); toggleMedia(); }

    // ---- CU07: pestañas de días + desplegar guía (contenido ya pintado por PHP) ----
    document.querySelectorAll('[data-dia-btn]').forEach(function (b) {
        b.addEventListener('click', function (ev) {
            ev.preventDefault();
            var dia = b.dataset.diaBtn;
            document.querySelectorAll('[data-dia-btn]').forEach(function (x) {
                x.classList.toggle('active', x === b);
            });
            document.querySelectorAll('[data-dia-sec]').forEach(function (s) {
                s.style.display = s.dataset.diaSec === dia ? '' : 'none';
            });
        });
    });
    document.querySelectorAll('[data-guia-btn]').forEach(function (b) {
        b.addEventListener('click', function () {
            var g = document.getElementById(b.dataset.guiaBtn);
            if (g) { g.style.display = g.style.display === 'none' ? '' : 'none'; }
        });
    });
})();
