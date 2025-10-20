// loader-map.js
document.addEventListener("DOMContentLoaded", function() {
    const spinner = document.getElementById('spinner-overlay');
    const content = document.getElementById('mainContent');

    if (!spinner || !content) return;

    // Variables de synchronisation
    let mapReady = false;
    let domReady = false;
    let markersReady = true;
    let map, markers, tileLayer;

    // Fonction d’initialisation : à appeler depuis ta page principale
    window.initMapLoader = function(m, layer, clusterGroup) {
        map = m;
        tileLayer = layer;
        markers = clusterGroup;

        // Quand Leaflet a fini de charger ses tuiles
        tileLayer.on('load', () => {
            setTimeout(() => {
                mapReady = true;
                maybeHideSpinner();
            }, 150);
        });

        // DOM ready (petit délai pour le rendu complet)
        window.requestAnimationFrame(() => {
            setTimeout(() => {
                domReady = true;
                maybeHideSpinner();
            }, 300);
        });
    };

    // Fonction utilitaire : masque le spinner quand tout est prêt
    function maybeHideSpinner() {
        if (mapReady && domReady && markersReady) {
            setTimeout(() => {
                spinner.classList.add('hidden');
                spinner.addEventListener('transitionend', () => {
                    spinner.style.display = "none";
                }, { once: true });

                content.classList.remove('d-none');

                // Corrige le problème de carte non rendue
                setTimeout(() => {
                    map.invalidateSize();
                    if (markers?.getLayers?.().length) {
                        map.fitBounds(markers.getBounds());
                    } else {
                        map.setView([46.5, 2.5], 6); // vue par défaut France
                    }
                }, 100);
            }, 200);
        }
    }
});
