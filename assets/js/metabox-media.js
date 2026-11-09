document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-media').forEach(function (button) {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const altId = this.getAttribute('data-alt');
            const imageInput = document.getElementById(targetId);
            const altInput = document.getElementById(altId);
            const preview = document.querySelector('[data-preview="' + targetId + '"]');

            const frame = wp.media({
                title: DafapSEOMediaL10n.chooseImage,
                button: { text: DafapSEOMediaL10n.useImage },
                multiple: false
            });

            frame.on('select', function () {
                const attachment = frame.state().get('selection').first().toJSON();
                imageInput.value = attachment.url;
                if (altInput && attachment.alt) {
                    altInput.value = attachment.alt;
                }
                if (preview) {
                    preview.innerHTML = '<img src="' + attachment.url + '" alt="' + (attachment.alt || '') + '" style="max-width:100%; margin-top:10px;" />';
                }
            });

            frame.open();
        });
    });
});