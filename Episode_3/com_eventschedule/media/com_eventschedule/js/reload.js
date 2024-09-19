function containerReload(element) {
    document.body.appendChild(document.createElement('joomla-core-loader'));
    Joomla.submitform(`event.reload`, element.form, false);
}