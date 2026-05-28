// Entry usado por las páginas Livewire (layout link-page-app).
// IMPORTANTE: no importar Alpine aquí — Livewire 4 ya trae su propia
// instancia integrada y arrancar otra rompe los wire:click.
import QRCodeStyling from 'qr-code-styling';
window.QRCodeStyling = QRCodeStyling;
