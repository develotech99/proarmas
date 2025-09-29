<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Mime\Email; // <-- importante

class NotificarpagoMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;
    protected ?UploadedFile $comprobante;

    public function __construct(array $payload, ?UploadedFile $comprobante = null)
    {
        $this->payload     = $payload;
        $this->comprobante = $comprobante;
    }

    public function build()
    {

        $mail = $this->subject('Pago enviado - Venta #' . $this->payload['venta_id'])
            ->view('emails.NotificarPago')
            ->with(array_merge($this->payload, [
                'logoCid' => 'cid:logo-proarmas',
            ]));

        if ($this->comprobante instanceof UploadedFile) {
            $mail->attach(
                $this->comprobante->getRealPath(),
                [
                    'as'   => 'comprobante_venta_' . $this->payload['venta_id'] . '.' . $this->comprobante->getClientOriginalExtension(),
                    'mime' => $this->comprobante->getClientMimeType(),
                ]
            );
        }

        $logoPath = public_path('images/pro_armas.png'); 

        if (is_file($logoPath)) {
            $this->withSymfonyMessage(function (Email $message) use ($logoPath) {
                $message->embedFromPath($logoPath, 'logo-proarmas');
            });
        }

        return $mail;
    }
}
