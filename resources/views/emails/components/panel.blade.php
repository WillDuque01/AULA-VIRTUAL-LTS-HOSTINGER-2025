@php
    $palette = $emailPalette ?? [
        'panel_bg' => '#f8fafc',
        'border' => '#e2e8f0',
        'text' => '#334155',
        'muted' => '#64748b',
    ];
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 16px 0; border-collapse: separate;">
    <tr>
        <td style="background-color: {{ $palette['panel_bg'] }}; border: 1px solid {{ $palette['border'] }}; border-radius: 20px; padding: 24px;">
            {{ $slot }}
        </td>
    </tr>
</table>
