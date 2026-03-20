/**
 * Copyright (c) 2006-2024, JGraph Holdings Ltd
 * Copyright (c) 2006-2024, draw.io AG
 */
// Overrides of global vars need to be pre-loaded
window.DRAWIO_PUBLIC_BUILD = true;
window.EXPORT_URL = 'REPLACE_WITH_YOUR_IMAGE_SERVER';
window.PLANT_URL = 'REPLACE_WITH_YOUR_PLANTUML_SERVER';
window.DRAWIO_BASE_URL = window.location.protocol + '//' + window.location.host + '/drawio';
window.DRAWIO_VIEWER_URL = null;
window.DRAWIO_LIGHTBOX_URL = null;
window.DRAW_MATH_URL = 'math4/es5';
window.DRAWIO_CONFIG = null;
urlParams['sync'] = 'manual';
window.DRAWIO_SERVER_URL = window.DRAWIO_BASE_URL;
// Disable service worker to avoid 404 errors
window.DRAWIO_SERVICE_WORKER = false;
if ('serviceWorker' in navigator)
{
    navigator.serviceWorker.getRegistrations().then(function(registrations)
    {
        for (var i = 0; i < registrations.length; i++)
        {
            registrations[i].unregister();
        }
    });
}
