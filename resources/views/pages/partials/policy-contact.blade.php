{{--
    The "Contact Us" list shared by the three policy pages. Pass the fields to show, in order:
        @include('pages.partials.policy-contact', ['fields' => ['name', 'email', 'website']])

    Edit the contact details here once and every policy picks them up. The email is the support
    email saved in Admin → Settings; the rest are still the [Insert …] placeholders from the
    policy text and need the shop's real handle / number.
--}}
@php
    $supportEmail = \App\Models\Setting::get('support_email');

    // field => [label, text, link (or null)]
    $details = [
        'name'         => ['Business Name', 'Ravellen', null],
        'email'        => ['Email', $supportEmail ?: '[Insert Business Email]', $supportEmail ? 'mailto:' . $supportEmail : null],
        'website'      => ['Website', url('/'), url('/')],
        'contact-form' => ['Website', url('/contact'), url('/contact')],
        'instagram'    => ['Instagram', '[Insert Instagram Handle]', null],
        'whatsapp'     => ['WhatsApp', '[Insert WhatsApp Number]', null],
    ];
@endphp
<ul class="policy-contact">
    @foreach($fields as $field)
        @php [$label, $text, $link] = $details[$field]; @endphp
        <li>
            <strong>{{ $label }}:</strong>
            @if($link)
                <a href="{{ $link }}">{{ $text }}</a>
            @else
                {{ $text }}
            @endif
        </li>
    @endforeach
</ul>
