<?php $__env->startSection('css'); ?>
<style>
    .policy-wrapper { background: #fff; min-height: 100vh; }

    /* ── Hero ── */
    .policy-hero {
        background: linear-gradient(135deg, #F88BC4 0%, #FB7185 100%);
        padding: 4.5rem 0 3.5rem;
        color: #fff;
    }
    .policy-hero h1 {
        font-size: clamp(2rem, 5vw, 3.1rem);
        font-weight: 800;
        color: #fde68a;
        margin-bottom: .75rem;
    }
    .policy-hero p { color: rgba(255,255,255,.92); font-size: 1.05rem; max-width: 38rem; margin-bottom: 0; }
    .policy-effective {
        display: inline-block; margin-top: 1.25rem;
        background: rgba(255,255,255,.2); color: #fff;
        font-size: .8rem; font-weight: 600;
        padding: .3rem .85rem; border-radius: 2rem;
    }

    /* ── Switcher between the three policies ── */
    .policy-tabs { display: flex; flex-wrap: wrap; gap: .5rem; }
    .policy-tab {
        padding: .45rem 1rem; border-radius: 2rem;
        border: 1px solid #fbcfe8; background: #fff; color: #be185d;
        font-size: .875rem; font-weight: 600; text-decoration: none;
        transition: background .2s, color .2s, border-color .2s;
    }
    .policy-tab:hover { background: #fdf2f8; color: #be185d; }
    .policy-tab.active { background: #ec4899; border-color: #ec4899; color: #fff; }

    /* ── "On this page" ── */
    .policy-toc { position: sticky; top: 6rem; }
    .policy-toc h6 {
        font-size: .75rem; font-weight: 700; letter-spacing: .06em;
        text-transform: uppercase; color: #9ca3af; margin-bottom: .75rem;
    }
    .policy-toc ol { list-style: none; margin: 0; padding: 0; max-height: calc(100vh - 10rem); overflow-y: auto; }
    .policy-toc a {
        display: block; padding: .3rem 0 .3rem .75rem;
        border-left: 2px solid #f3e8ff;
        color: #6b7280; font-size: .85rem; line-height: 1.4; text-decoration: none;
    }
    .policy-toc a:hover { color: #ec4899; border-left-color: #ec4899; }

    /* ── The policy text ── */
    .policy-card {
        background: #fff; border: 1px solid #fce7f3; border-radius: 1.25rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.05);
        padding: 2rem 1.5rem;
    }
    @media (min-width: 768px) { .policy-card { padding: 3rem 3.5rem; } }

    .policy-body { color: #4b5563; font-size: .98rem; line-height: 1.8; overflow-wrap: anywhere; }
    .policy-body h2 {
        font-size: 1.35rem; font-weight: 700; color: #be185d;
        margin: 2.5rem 0 .85rem; padding-top: 2rem; border-top: 1px solid #fce7f3;
        scroll-margin-top: 6rem;
    }
    .policy-body > h2:first-child,
    .policy-body > .policy-lead + h2 { margin-top: 2rem; }
    .policy-body h3 { font-size: 1.05rem; font-weight: 700; color: #9333ea; margin: 1.5rem 0 .5rem; }
    .policy-body p { margin-bottom: 1rem; }
    .policy-body ul, .policy-body ol { padding-left: 1.4rem; margin-bottom: 1rem; }
    .policy-body li { margin-bottom: .4rem; }
    .policy-body ul li::marker { color: #ec4899; }
    .policy-body ol li::marker { color: #ec4899; font-weight: 700; }
    .policy-body strong { color: #1f2937; }
    .policy-body a { color: #ec4899; }

    .policy-lead { font-size: 1.05rem; color: #374151; }
    .policy-callout {
        background: #fdf2f8; border-left: 4px solid #ec4899;
        border-radius: .5rem; padding: .85rem 1.1rem; margin: 1rem 0 1.25rem;
        color: #831843;
    }
    .policy-callout p:last-child { margin-bottom: 0; }

    .policy-contact { list-style: none; padding-left: 0 !important; }
    .policy-contact li { margin-bottom: .35rem; }

    .policy-closing {
        margin-top: 2.5rem; padding: 1.5rem; text-align: center;
        background: linear-gradient(to bottom right, #fff, #fdf2f8);
        border: 1px solid #fce7f3; border-radius: 1rem; color: #831843;
    }
    .policy-closing p:last-child { margin-bottom: 0; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php
    $policies = [
        'terms'    => 'Terms of Service',
        'privacy'  => 'Privacy Policy',
        'shipping' => 'Shipping & Returns',
    ];
?>
<div class="policy-wrapper">

    <section class="policy-hero">
        <div class="container px-4">
            <h1><?php echo $__env->yieldContent('policy-title'); ?></h1>
            <p><?php echo $__env->yieldContent('policy-tagline'); ?></p>
            <span class="policy-effective">Effective Date: <?php echo $__env->yieldContent('effective-date'); ?></span>
        </div>
    </section>

    <section class="py-5">
        <div class="container px-4">

            <nav class="policy-tabs mb-4" aria-label="Policies">
                <?php $__currentLoopData = $policies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route($name)); ?>"
                       class="policy-tab <?php echo e(request()->routeIs($name) ? 'active' : ''); ?>"
                       <?php if(request()->routeIs($name)): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="row g-4 g-lg-5">
                <aside class="col-lg-3 d-none d-lg-block">
                    <div class="policy-toc" id="policy-toc" hidden>
                        <h6>On this page</h6>
                        <ol></ol>
                    </div>
                </aside>

                <div class="col-lg-9">
                    <article class="policy-card">
                        <div class="policy-body">
                            <?php echo $__env->yieldContent('policy-body'); ?>
                        </div>
                    </article>
                </div>
            </div>

        </div>
    </section>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toc = document.getElementById('policy-toc');
        const list = toc && toc.querySelector('ol');
        if (!list) return;

        document.querySelectorAll('.policy-body h2').forEach(function (h, i) {
            h.id = h.id || 'section-' + (i + 1);
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#' + h.id;
            a.textContent = h.textContent;
            li.appendChild(a);
            list.appendChild(li);
        });

        toc.hidden = !list.children.length;
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/policy.blade.php ENDPATH**/ ?>