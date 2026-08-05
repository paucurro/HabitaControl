<?php

namespace App\Notifications;

use App\Models\Comunicado;
use App\Models\Propietario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComunicadoIndividualNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comunicado $comunicado, public Propietario $propietario) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->comunicado->asunto)
            ->greeting("Hola, {$this->propietario->nombre}")
            ->line($this->comunicado->contenido)
            ->salutation($this->comunicado->comunidad->nombre);
    }
}
