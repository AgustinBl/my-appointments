<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Services\Fcm;
use App\User;

class FirebaseController extends Controller
{
    public function sendAll(Request $request, Fcm $fcm)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        if (! $fcm->isConfigured()) {
            $notification = 'Falta configurar las credenciales de Firebase (FCM_CREDENTIALS).';
            return back()->with(compact('notification'));
        }

        $recipients = User::whereNotNull('device_token')
            ->pluck('device_token')
            ->toArray();

        if (! $recipients) {
            $notification = 'No hay dispositivos registrados para notificar.';
            return back()->with(compact('notification'));
        }

        // La API v1 manda de a un dispositivo: sendToTokens ya deduplica.
        $result = $fcm->sendToTokens($recipients, $data['title'], $data['body']);

        // Tokens que Firebase reporto como muertos: los sacamos de la base.
        if ($result['invalid_tokens']) {
            User::whereIn('device_token', $result['invalid_tokens'])
                ->update(['device_token' => null]);
        }

        $notification = "Notificación enviada a {$result['sent']} dispositivo(s).";

        if ($result['failed']) {
            $notification .= " {$result['failed']} envío(s) fallaron: {$result['error']}";
        }

        return back()->with(compact('notification'));
    }
}
