<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de certificado · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="min-h-screen flex flex-col items-center px-4 py-10">
        <div class="max-w-3xl w-full space-y-6">
            <div class="text-center space-y-2">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Aula Virtual LTS</p>
                <h1 class="text-3xl font-semibold">Verificación de certificado</h1>
                <p class="text-sm text-slate-500">Código consultado: <span class="font-mono text-slate-700">{{ strtoupper($code) }}</span></p>
            </div>

            @if($certificate)
                <div class="bg-white border border-slate-200 rounded-3xl shadow-xl shadow-slate-200/60 p-6 space-y-6">
                    <div class="flex flex-col gap-3 border-b border-slate-100 pb-4">
                        <p class="text-xs uppercase font-semibold text-slate-500">Estudiante</p>
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div>
                                <p class="text-2xl font-semibold">{{ $certificate->user?->name }}</p>
                                <p class="text-sm text-slate-500">{{ $certificate->user?->email }}</p>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 text-blue-700 px-3 py-1 text-xs font-semibold">
                                Código {{ strtoupper($certificate->code) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60">
                            <p class="text-xs uppercase font-semibold text-slate-500">Curso</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $certificate->course?->slug ?? 'Curso' }}</p>
                            <p class="text-xs text-slate-500 mt-1">Emitido el {{ $certificate->issued_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <div class="border border-slate-100 rounded-2xl p-4 bg-slate-50/60">
                            <p class="text-xs uppercase font-semibold text-slate-500">Verificaciones</p>
                            <p class="text-lg font-semibold text-slate-900">{{ $certificate->verified_count }}</p>
                            <p class="text-xs text-slate-500 mt-1">
                                Última: {{ $certificate->last_verified_at?->diffForHumans() ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2 items-center">
                        <div class="space-y-3">
                            <p class="text-xs uppercase font-semibold text-slate-500">Compartir</p>
                            <div class="flex items-center gap-3">
                                <input type="text" readonly value="{{ $shareUrl }}" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono text-slate-600">
                                <button type="button" id="copy-link" data-url="{{ $shareUrl }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 text-white text-xs font-semibold px-4 py-2">
                                    Copiar
                                </button>
                            </div>
                            <p class="text-xs text-slate-500">Comparte este enlace para que cualquiera valide la autenticidad del certificado.</p>
                        </div>
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ $qrUrl }}" alt="QR del certificado" class="w-40 h-40 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                            <p class="text-xs text-slate-500">Escanea para abrir este certificado</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white border border-rose-200 text-rose-700 rounded-3xl shadow-lg shadow-rose-100/60 p-6 space-y-3">
                    <p class="text-lg font-semibold">No encontramos un certificado con este código.</p>
                    <p class="text-sm text-rose-600">Verifica que el código fue escrito correctamente o solicita al estudiante que regenere el enlace desde su panel.</p>
                </div>
            @endif

            <div class="text-center text-xs text-slate-500">
                {{ config('app.name') }} · {{ now()->year }} &mdash; Seguridad verificable por código y enlace público
            </div>
        </div>
    </div>

    <script>
        const copyButton = document.getElementById('copy-link');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                const url = copyButton.dataset.url;
                try {
                    await navigator.clipboard.writeText(url);
                    copyButton.textContent = 'Copiado ✓';
                    setTimeout(() => copyButton.textContent = 'Copiar', 1800);
                } catch (error) {
                    console.error(error);
                }
            });
        }
    </script>
</body>
</html>


