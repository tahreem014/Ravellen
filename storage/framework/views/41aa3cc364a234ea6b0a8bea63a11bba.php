
<?php
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
?>
<ul class="policy-contact">
    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php [$label, $text, $link, $external] = $details[$field]; ?>
        <li>
            <strong><?php echo e($label); ?>:</strong>
            <?php if($link): ?>
                <a href="<?php echo e($link); ?>" <?php if($external): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>><?php echo e($text); ?></a>
            <?php else: ?>
                <?php echo e($text); ?>

            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/partials/policy-contact.blade.php ENDPATH**/ ?>