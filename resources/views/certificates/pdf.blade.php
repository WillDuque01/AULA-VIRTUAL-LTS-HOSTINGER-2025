<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 60px 40px; }
        body {
            font-family: 'Helvetica Neue', 'Inter', sans-serif;
            color: #0f172a;
        }
        .frame {
            border: 6px double #e2e8f0;
            padding: 40px;
            min-height: 500px;
            position: relative;
        }
        h1 {
            font-size: 28px;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 22px;
            text-align: center;
            margin: 0;
        }
        .meta {
            margin-top: 30px;
            font-size: 14px;
            text-align: center;
            color: #475569;
        }
        .signature {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        .signature span {
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            display: inline-block;
            width: 200px;
            text-align: center;
        }
        .badge {
            position: absolute;
            top: 30px;
            right: 30px;
            font-size: 12px;
            background: #0ea5e9;
            color: #fff;
            padding: 6px 12px;
            border-radius: 999px;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="frame">
        <div class="badge">{{ $code }}</div>
        <h1>Certificado</h1>
        <h2>de {{ $course->slug }}</h2>

        <div style="margin-top: 50px; text-align: center;">
            <p style="font-size: 15px; color: #475569;">Otorgado a</p>
            <p style="font-size: 30px; font-weight: 600; margin: 0;">{{ $user->name }}</p>
        </div>

        <p style="margin-top: 30px; font-size: 16px; text-align: center; line-height: 1.6; color: #334155;">
            Por haber completado satisfactoriamente el curso<br>
            <strong>{{ $course->slug }}</strong> con una dedicación destacada.
        </p>

        <div class="meta">
            Emitido el {{ $issued_at->translatedFormat('d \\d\\e F \\d\\e Y') }}<br>
            Progreso final: {{ $percent ?? 'N/A' }}%
        </div>

        <div class="signature">
            <span>Dirección académica</span>
            <span>Coordinación Aula Virtual</span>
        </div>
    </div>
</body>
</html>


