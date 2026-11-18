<div style="font-family:Arial,sans-serif;color:#1e3e55;max-width:600px">
    <h2 style="margin:0 0 4px;font-size:20px">{{ $heading }}</h2>
    <p style="margin:0 0 16px;color:#56636e">Submitted from the &quot;ThOR&quot; BESS website. Reply to this email to respond directly.</p>
    <table cellpadding="8" style="border-collapse:collapse;width:100%;font-size:14px">
        @foreach ($rows as $label => $value)
            <tr>
                <td style="border-bottom:1px solid #e7ecf0;font-weight:bold;width:170px;vertical-align:top">{{ $label }}</td>
                <td style="border-bottom:1px solid #e7ecf0;white-space:pre-wrap">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
    <p style="margin:20px 0 0">
        <a href="{{ $adminUrl }}" style="display:inline-block;padding:10px 18px;border-radius:4px;background:#5db556;color:#fff;font-weight:bold;text-decoration:none">Open in admin panel</a>
    </p>
</div>
