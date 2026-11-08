{{--
    The "Contact Us" list shared by the three policy pages. Pass the fields to show, in order:
        @include('pages.partials.policy-contact', ['fields' => ['name', 'email', 'website']])

    Edit the contact details here once and every policy picks them up. The email and the WhatsApp
    number are the support email and phone saved in Admin → Settings (the same number the footer
    shows); the Instagram handle is the one the footer links to.
--}}
@php
    $supportEmail = \App\Models\Setting::get('support_email');
    $supportPhone = \App\Models\Setting::get('support_phone');

    // wa.me wants the number with its country code and digits only; a local 03xx… number is Pakistani
    $whatsappDigits = preg_replace('/\D/', '', (string) $supportPhone);
    if (str_starts_with($whatsappDigits, '0')) {
        $whatsappDigits = '92' . substr($whatsappDigits, 1);
    }

    // The live shop address. Fixed rather than url('/'), so the policies name the real site
    // wherever they are viewed (local, staging) instead of that environment's host.
    $site = 'https://ravellenraynae.store';

    // field => [label, text, link (or null), opens in a new tab]
    $details = [
        'name'         => ['Business Name', 'Ravellen', null, false],
        'email'        => ['Email', $supportEmail ?: '[Insert Business Email]', $supportEmail ? 'mailto:' . $supportEmail : null, false],
        'website'      => ['Website', $site, $site, false],
        'contact-form' => ['Website', $site . '/contact', $site . '/contact', false],
        'instagram'    => ['Instagram', '@ravellen_nm', 'https://www.instagram.com/ravellen_nm', true],
        'whatsapp'     => ['WhatsApp', $supportPhone ?: '[Insert WhatsApp Number]', $whatsappDigits ? 'https://wa.me/' . $whatsappDigits : null, true],
    ];
@endphp
<ul class="policy-contact">
    @foreach($fields as $field)
        @php [$label, $text, $link, $external] = $details[$field]; @endphp
        <li>
            <strong>{{ $label }}:</strong>
            @if($link)
                <a href="{{ $link }}" @if($external) target="_blank" rel="noopener noreferrer" @endif>{{ $text }}</a>
            @else
                {{ $text }}
            @endif
        </li>
    @endforeach
</ul>
