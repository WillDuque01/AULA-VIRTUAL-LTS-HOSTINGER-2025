<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Integration Flags
    |--------------------------------------------------------------------------
    |
    | Permiten forzar temporalmente los modos gratuitos aunque haya credenciales
    | configuradas. Útiles para depurar o trabajar en entornos de prueba.
    |
    */
    'force_free_storage' => (bool) env('FORCE_FREE_STORAGE', false),
    'force_free_realtime' => (bool) env('FORCE_FREE_REALTIME', false),
    'force_youtube_only' => (bool) env('FORCE_YOUTUBE_ONLY', false),

    /*
    |--------------------------------------------------------------------------
    | Estado calculado
    |--------------------------------------------------------------------------
    |
    | El IntegrationConfigurator rellenará este arreglo durante el bootstrap
    | para exponer estados resumidos de cada integración.
    |
    */
    'status' => [],
];
