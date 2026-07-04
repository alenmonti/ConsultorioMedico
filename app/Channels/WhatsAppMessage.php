<?php

namespace App\Channels;

class WhatsAppMessage
{
    public string $phone;
    public string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function to(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }
}
