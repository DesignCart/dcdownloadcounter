document.addEventListener('DOMContentLoaded', function () {
    if (!window.DC_DOWNLOAD_COUNTER || !window.DC_DOWNLOAD_COUNTER.endpoint) {
        return;
    }

    var endpoint = window.DC_DOWNLOAD_COUNTER.endpoint;

    var links = document.querySelectorAll('a.dc-download-counter');

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var href = link.getAttribute('href');

            if (!href) {
                return;
            }

            e.preventDefault();

            var id    = link.getAttribute('data-dc-id');
            var badge = null;

            if (id) {
                badge = document.querySelector(
                    '.dc-download-counter-badge[data-dc-id="' + id + '"]'
                );
            }

            var formData = new FormData();
            formData.append('file', href);

            fetch(endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.success && typeof data.count !== 'undefined' && badge) {
                        badge.textContent = data.count;
                    }

                    // Po zliczeniu przenosimy usera do oryginalnego linku
                    window.location.href = href;
                })
                .catch(function () {
                    // W razie błędu i tak przekierowujemy
                    window.location.href = href;
                });
        });
    });
});
