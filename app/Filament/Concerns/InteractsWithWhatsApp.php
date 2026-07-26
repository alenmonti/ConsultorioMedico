<?php

namespace App\Filament\Concerns;

trait InteractsWithWhatsApp
{
    /**
     * Dispara el deep link whatsapp:// (capturado por ZapZap u otro handler del
     * esquema) desde un iframe oculto, para no navegar la pestaña actual del panel
     * ni caer nunca a WhatsApp Web.
     */
    protected function abrirWhatsApp(string $telefono, ?string $mensaje = null): void
    {
        $phone = "549{$telefono}";
        $query = $mensaje !== null ? '&text=' . rawurlencode($mensaje) : '';

        $app = json_encode("whatsapp://send?phone={$phone}{$query}");

        $this->js(<<<JS
            (() => {
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = {$app};
                document.body.appendChild(iframe);
                setTimeout(() => iframe.remove(), 1000);
            })();
        JS);
    }
}
