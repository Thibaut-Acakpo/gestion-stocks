let pendingConfirmation = null;

document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!form || !(form instanceof HTMLFormElement)) return;
    if (form.dataset.confirmed === 'true') {
        delete form.dataset.confirmed;
        return;
    }

    const submitter = event.submitter;
    const message = form.dataset.confirmMessage || (submitter && submitter.dataset.confirmMessage);
    if (!message) return;

    event.preventDefault();
    pendingConfirmation = { form: form, submitter: submitter };

    const overlay = document.getElementById('app-confirm-modal');
    if (!overlay) return;
    document.getElementById('app-confirm-title').textContent =
        form.dataset.confirmTitle || (submitter && submitter.dataset.confirmTitle) || 'Confirmer cette action ?';
    document.getElementById('app-confirm-message').textContent = message;
    document.getElementById('app-confirm-cancel').textContent = 'Annuler';
    const confirmButton = document.getElementById('app-confirm-submit');
    confirmButton.textContent =
        form.dataset.confirmLabel || (submitter && submitter.dataset.confirmLabel) || 'Confirmer';
    const type = form.dataset.confirmType || (submitter && submitter.dataset.confirmType) || 'danger';
    confirmButton.className = 'btn ' + (type === 'primary' ? 'btn-primary' : 'btn-danger');
    overlay.hidden = false;
    document.getElementById('app-confirm-cancel').focus();
}, true);

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('app-confirm-modal');
    if (!overlay) return;

    const close = function () {
        overlay.hidden = true;
        pendingConfirmation = null;
    };

    document.getElementById('app-confirm-cancel').addEventListener('click', close);
    document.getElementById('app-confirm-submit').addEventListener('click', function () {
        if (!pendingConfirmation) return;
        const form = pendingConfirmation.form;
        const submitter = pendingConfirmation.submitter;
        overlay.hidden = true;
        form.dataset.confirmed = 'true';
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter || undefined);
        } else {
            form.submit();
        }
        pendingConfirmation = null;
    });
    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) close();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !overlay.hidden) close();
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.app-toast.is-visible');
    if (toast) {
        setTimeout(function () { toast.remove(); }, 4000);
    }
});
