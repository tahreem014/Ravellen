
<?php
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
?>
<ul class="policy-contact">
    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php [$label, $text, $link] = $details[$field]; ?>
        <li>
            <strong><?php echo e($label); ?>:</strong>
            <?php if($link): ?>
                <a href="<?php echo e($link); ?>"><?php echo e($text); ?></a>
            <?php else: ?>
                <?php echo e($text); ?>

            <?php endif; ?>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/partials/policy-contact.blade.php ENDPATH**/ ?>