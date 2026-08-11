<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Credenciales de la service account
    |--------------------------------------------------------------------------
    |
    | La API HTTP v1 de Firebase Cloud Messaging se autentica con la service
    | account del proyecto, no con el viejo "server key". Descargá el JSON
    | desde Firebase Console > Configuracion del proyecto > Cuentas de servicio
    | > Generar nueva clave privada.
    |
    | Acepta una ruta (absoluta o relativa a la raiz del proyecto) o el JSON
    | completo pegado en la variable de entorno.
    |
    */

    'credentials' => env('FCM_CREDENTIALS', storage_path('app/firebase/service-account.json')),

    /*
    |--------------------------------------------------------------------------
    | Project ID
    |--------------------------------------------------------------------------
    |
    | Opcional: si no se define, se toma el "project_id" del propio JSON de
    | credenciales.
    |
    */

    'project_id' => env('FCM_PROJECT_ID'),

];
