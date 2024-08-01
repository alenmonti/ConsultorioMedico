<x-filament-panels::page>

{{ $this->infolist }}
@if(isset($this->infolist->record->documento_link))
<iframe src="{{ $this->infolist->record->documento_link }}" width="100%" height="800px" frameborder="0">
    Tu navegador no soporta iframes, por favor intenta con otro navegador.
</iframe>
@endif

</x-filament-panels::page>
