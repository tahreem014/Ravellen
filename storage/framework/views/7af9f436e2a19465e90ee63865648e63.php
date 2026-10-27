
<?php if(request()->path() !== '/'): ?>
    <div class="rv-back-anchor">
        <div class="container">
            <a href="<?php echo e(request()->is('product-item/*', 'product/*', 'gift-box') ? url('/shop') : url('/')); ?>"
               class="rv-back-btn" id="rv-back-btn" aria-label="Go back" title="Back"
               onclick="return rvGoBack(event)">
                <i class="ri-arrow-left-line"></i>
            </a>
        </div>
    </div>

    <style>
        .rv-back-anchor { position: relative; height: 0; z-index: 30; }
        .rv-back-anchor .container { position: relative; }
        .rv-back-btn {
            position: absolute; top: .65rem; left: calc(var(--bs-gutter-x, 1.5rem) * .5);
            width: 2.35rem; height: 2.35rem; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #fff; color: #374151; font-size: 1.2rem; line-height: 1;
            box-shadow: 0 2px 10px rgba(0,0,0,.14);
            text-decoration: none !important; transition: color .2s, transform .2s;
        }
        .rv-back-btn:hover { color: #a855f7; transform: translateX(-2px); }

        /* A page whose first content sits at the very top (a title, a card) leaves this much room for the arrow */
        .rv-pt-back { padding-top: 3.6rem !important; }
    </style>

    <script>
        function rvGoBack(e) {
            let cameFromThisSite = false;
            try { cameFromThisSite = !!document.referrer && new URL(document.referrer).origin === location.origin; } catch (err) {}

            if (cameFromThisSite && history.length > 1) {
                e.preventDefault();
                history.back();
                return false;
            }
            return true;
        }
    </script>
<?php endif; ?>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/back-button.blade.php ENDPATH**/ ?>