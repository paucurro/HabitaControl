<?php

namespace App\Notifications;

use App\Models\InvitacionAcceso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class InvitacionAccesoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $encryptedToken;

    public function __construct(public InvitacionAcceso $invitacion, string $token)
    {
        $this->encryptedToken = Crypt::encryptString($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tipo = $this->invitacion->tipo === 'propietario' ? 'propietario' : 'colaborador';

        return (new MailMessage)
            ->subject('Invitación de acceso a IComunidades')
            ->greeting('Hola')
            ->line("Has sido invitado como {$tipo} a {$this->invitacion->administracion->nombre}.")
            ->action('Activar acceso', route('invitaciones.show', Crypt::decryptString($this->encryptedToken)))
            ->line('La invitación caduca en 7 días y solo puede utilizarse una vez.');
    }
}
