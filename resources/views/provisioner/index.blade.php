<!doctype html>
<html>
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Provisioner</title>
  <style>label{display:block;margin:.5rem 0 .25rem}input,select{width:100%;padding:.5rem} .grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))} .card{border:1px solid #ddd;padding:1rem;border-radius:12px}</style>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css'])
</head>
<body class="p-6">
  <h1 class="text-2xl font-semibold mb-4">Provisionar Integraciones</h1>

  @php($integrationStatus = config('integrations.status', []))
  @if(!empty($integrationStatus))
    <section class="grid mb-6">
      @foreach($integrationStatus as $block)
        <article class="card bg-slate-50">
          <h3 class="font-semibold text-lg mb-2">{{ $block['label'] }}</h3>
          <p class="text-sm mb-1"><strong>Estado:</strong> {{ $block['status'] }}</p>
          <p class="text-xs text-gray-500">Driver: <code>{{ $block['driver'] }}</code></p>
          @if($block['forced'])
            <p class="text-xs text-amber-600 font-semibold mt-2">Modo forzado</p>
          @elseif(!$block['has_credentials'])
            <p class="text-xs text-gray-500 mt-2">Sin credenciales detectadas (usando fallback)</p>
          @endif
        </article>
      @endforeach
    </section>
  @endif

  <form id="prov" class="grid">
    <div class="card"><h3 class="font-medium mb-2">Google OAuth</h3>
      <label>CLIENT ID</label><input name="GOOGLE_CLIENT_ID" value="{{ config('services.google.client_id') }}">
      <label>CLIENT SECRET</label><input name="GOOGLE_CLIENT_SECRET" value="{{ config('services.google.client_secret') }}">
      <label>REDIRECT URI</label><input name="GOOGLE_REDIRECT_URI" value="{{ config('services.google.redirect', url('/auth/google/callback')) }}">
    </div>
    <div class="card"><h3 class="font-medium mb-2">Pusher</h3>
      <label>PUSHER_APP_ID</label><input name="PUSHER_APP_ID" value="{{ config('services.pusher.app_id') }}">
      <label>PUSHER_APP_KEY</label><input name="PUSHER_APP_KEY" value="{{ config('services.pusher.key') }}">
      <label>PUSHER_APP_SECRET</label><input name="PUSHER_APP_SECRET" value="{{ config('services.pusher.secret') }}">
      <label>PUSHER_APP_CLUSTER</label><input name="PUSHER_APP_CLUSTER" value="{{ config('services.pusher.cluster', 'mt1') }}">
    </div>
    <div class="card"><h3 class="font-medium mb-2">S3/R2</h3>
      <label>AWS_ACCESS_KEY_ID</label><input name="AWS_ACCESS_KEY_ID" value="{{ config('services.s3.key') }}">
      <label>AWS_SECRET_ACCESS_KEY</label><input name="AWS_SECRET_ACCESS_KEY" value="{{ config('services.s3.secret') }}">
      <label>AWS_BUCKET</label><input name="AWS_BUCKET" value="{{ config('services.s3.bucket') }}">
      <label>AWS_ENDPOINT</label><input name="AWS_ENDPOINT" placeholder="https://... (R2/Wasabi)" value="{{ config('services.s3.endpoint') }}">
      <label>AWS_DEFAULT_REGION</label><input name="AWS_DEFAULT_REGION" placeholder="auto" value="{{ config('services.s3.region') }}">
      <label>AWS_USE_PATH_STYLE_ENDPOINT</label><input name="AWS_USE_PATH_STYLE_ENDPOINT" value="{{ config('services.s3.use_path_style_endpoint') ? 'true' : 'false' }}">
    </div>
    <div class="card"><h3 class="font-medium mb-2">Video</h3>
      <label>VIMEO_TOKEN</label><input name="VIMEO_TOKEN" value="{{ config('services.vimeo.token') }}">
      <label>CLOUDFLARE_STREAM_TOKEN</label><input name="CLOUDFLARE_STREAM_TOKEN" value="{{ config('services.cf.token') }}">
      <label>CLOUDFLARE_ACCOUNT_ID</label><input name="CLOUDFLARE_ACCOUNT_ID" value="{{ config('services.cf.account_id') }}">
      <label>YOUTUBE_ORIGIN</label><input name="YOUTUBE_ORIGIN" value="{{ env('YOUTUBE_ORIGIN', config('app.url')) }}">
    </div>
    <div class="card"><h3 class="font-medium mb-2">SMTP</h3>
      <label>MAIL_MAILER</label><input name="MAIL_MAILER" value="{{ config('mail.default', 'smtp') }}">
      <label>MAIL_HOST</label><input name="MAIL_HOST" value="{{ config('mail.mailers.smtp.host') }}">
      <label>MAIL_PORT</label><input name="MAIL_PORT" value="{{ config('mail.mailers.smtp.port') }}">
      <label>MAIL_USERNAME</label><input name="MAIL_USERNAME" value="{{ config('mail.mailers.smtp.username') }}">
      <label>MAIL_PASSWORD</label><input name="MAIL_PASSWORD" type="password" value="{{ config('mail.mailers.smtp.password') }}">
      <label>MAIL_ENCRYPTION</label><input name="MAIL_ENCRYPTION" value="{{ config('mail.mailers.smtp.encryption', 'ssl') }}">
      <label>MAIL_FROM_ADDRESS</label><input name="MAIL_FROM_ADDRESS" value="{{ config('mail.from.address') }}">
      <label>MAIL_FROM_NAME</label><input name="MAIL_FROM_NAME" value="{{ config('mail.from.name') }}">
    </div>
    <div class="card"><h3 class="font-medium mb-2">Make/Discord/Sheets</h3>
      <label>WEBHOOKS_MAKE_SECRET</label><input name="WEBHOOKS_MAKE_SECRET" value="{{ config('services.make.secret') }}">
      <label>MAKE_WEBHOOK_URL</label><input name="MAKE_WEBHOOK_URL" value="{{ config('services.make.webhook_url') }}">
      <label>DISCORD_WEBHOOK_URL</label><input name="DISCORD_WEBHOOK_URL" value="{{ config('services.discord.webhook_url') }}">
      <label>DISCORD_WEBHOOK_USERNAME</label><input name="DISCORD_WEBHOOK_USERNAME" placeholder="LMS Alerts" value="{{ config('services.discord.username') }}">
      <label>DISCORD_WEBHOOK_AVATAR</label><input name="DISCORD_WEBHOOK_AVATAR" placeholder="https://cdn..." value="{{ config('services.discord.avatar') }}">
      <label>DISCORD_WEBHOOK_THREAD_ID</label><input name="DISCORD_WEBHOOK_THREAD_ID" placeholder="Opcional: ID de hilo" value="{{ config('services.discord.thread_id') }}">
      <label>GOOGLE_SERVICE_ACCOUNT_JSON_PATH</label><input name="GOOGLE_SERVICE_ACCOUNT_JSON_PATH" value="{{ config('services.google.service_account_json', 'storage/app/keys/google.json') }}">
      <label>SHEET_ID</label><input name="SHEET_ID" value="{{ config('services.google.sheet_id') }}">
      <label class="flex items-center gap-2 mt-2"><input type="checkbox" name="GOOGLE_SHEETS_ENABLED" value="1" {{ config('services.google.enabled') ? 'checked' : '' }}> Habilitar Google Sheets</label>
      <label class="mt-3 text-xs text-slate-500 uppercase font-semibold">CERTIFICATES_VERIFY_SECRET</label>
      <div class="flex items-center gap-2">
        <input name="CERTIFICATES_VERIFY_SECRET" value="{{ config('services.certificates.verify_secret') }}" class="flex-1" type="password">
        <button type="button" id="generate-certificate-secret" class="px-3 py-2 text-xs font-semibold rounded bg-slate-900 text-white">Rotar</button>
      </div>
      <p class="text-[11px] text-slate-500 mt-1">Se usa para firmar el endpoint `/api/certificates/verify`.</p>
    </div>
    <div class="card"><h3 class="font-medium mb-2">WhatsApp / Alertas</h3>
      <label class="flex items-center gap-2"><input type="checkbox" name="WHATSAPP_ENABLED" value="1" {{ config('services.whatsapp.enabled') ? 'checked' : '' }}> Activar Cloud API</label>
      <label>WHATSAPP_TOKEN</label><input name="WHATSAPP_TOKEN" type="password" value="{{ config('services.whatsapp.token') }}">
      <label>WHATSAPP_PHONE_ID</label><input name="WHATSAPP_PHONE_ID" value="{{ config('services.whatsapp.phone_number_id') }}">
      <label>WHATSAPP_DEFAULT_TO</label><input name="WHATSAPP_DEFAULT_TO" placeholder="+57300..." value="{{ config('services.whatsapp.default_to') }}">
      <label>WHATSAPP_DEEPLINK</label><input name="WHATSAPP_DEEPLINK" placeholder="https://wa.me/57..." value="{{ config('services.whatsapp.deeplink') }}">
      <p class="text-[11px] text-slate-500 mt-1">Usa el deeplink si solo necesitas compartir un enlace de contacto rápido. Con token y phone ID se habilitan alertas automáticas.</p>
    </div>
    <div class="card"><h3 class="font-medium mb-2">Modos gratuitos</h3>
      <label class="flex items-center gap-2"><input type="checkbox" name="FORCE_FREE_STORAGE" value="1" {{ config('integrations.force_free_storage') ? 'checked' : '' }}> Forzar almacenamiento local</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="FORCE_FREE_REALTIME" value="1" {{ config('integrations.force_free_realtime') ? 'checked' : '' }}> Forzar realtime local</label>
      <label class="flex items-center gap-2"><input type="checkbox" name="FORCE_YOUTUBE_ONLY" value="1" {{ config('integrations.force_youtube_only') ? 'checked' : '' }}> Forzar modo YouTube</label>
    </div>
    <div>
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
    </div>
  </form>
  <script>
    const secretInput = document.querySelector('input[name="CERTIFICATES_VERIFY_SECRET"]');
    const generateSecret = () => {
      const random = crypto.getRandomValues(new Uint8Array(32));
      return Array.from(random, (b) => ('0' + b.toString(16)).slice(-2)).join('');
    };
    document.getElementById('generate-certificate-secret')?.addEventListener('click', () => {
      if (secretInput) {
        secretInput.value = generateSecret();
      }
    });
    document.getElementById('prov').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const data = new FormData(e.target);
      const params = new URLSearchParams();
      for (const [key, value] of data.entries()) {
        params.append(key, value);
      }
      const res = await fetch('{{ url('/provisioner/save') }}',{method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content}, body:params});
      let message = res.ok ? 'Guardado' : 'Error';
      try {
        const json = await res.json();
        if (json?.message) {
          message = json.message;
        }
      } catch (error) {
        // Ignorar error de parseo, usar mensaje por defecto
      }

      alert(message);
      if (res.ok) {
        window.location.reload();
      }
    });
  </script>
</body>
</html>


