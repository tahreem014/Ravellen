

<?php $__env->startSection('content'); ?>
<style>
    body, html { background: linear-gradient(135deg,#fde8ee 0%,#f3eafc 50%,#eaf3fc 100%) !important; }
    .rv-rp-page {
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        padding: 2rem 1rem; font-family: 'Inter', sans-serif;
    }
    .rv-rp-card {
        background: #fff; border-radius: .75rem;
        box-shadow: 0 8px 48px rgba(124,58,237,.10);
        padding: 3rem 2.5rem; width: 100%; max-width: 30rem;
    }
    .rv-input {
        background: #ebebeb; border: none; border-radius: .5rem;
        padding: .65rem 1.1rem; font-size: .95rem; color: #374151;
        width: 100%; outline: none; display: block; box-sizing: border-box;
    }
    .rv-input:focus { background: #e2e2e2; }
    .rv-input.is-invalid { outline: 2px solid #ef4444; }
    .rv-label { font-size: .875rem; font-weight: 700; color: #111827; margin-bottom: .45rem; display: block; }
    .rv-eye-btn {
        position: absolute; right: .85rem; top: 50%; transform: translateY(-50%);
        background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0; line-height: 1;
    }
    .rv-btn {
        background: linear-gradient(to right,#f472b6,#ec4899);
        border: none; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: 1rem; padding: .65rem;
        width: 100%; cursor: pointer; transition: opacity .2s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .rv-btn:hover { opacity: .88; }
    .rv-btn:disabled { opacity: .7; cursor: not-allowed; }
    .rv-btn .btn-loader {
        display: none; width: 1.1rem; height: 1.1rem;
        border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
        border-radius: 50%; animation: spin .7s linear infinite;
    }
    .rv-btn.loading .btn-loader { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="rv-rp-page">
    <div class="rv-rp-card">

        <div class="text-center mb-4">
            <a href="<?php echo e(url('/')); ?>" class="rv-logo text-decoration-none">
            <img src="<?php echo e(asset('images/ravellen-logo.png')); ?>" alt="Ravellen" height="60" style="display:block;margin:0 auto 1.25rem;">
            </a>
            <h4 style="font-weight:800;font-size:1.5rem;margin-bottom:.3rem;background:linear-gradient(to right,#6517AB,#9A1A8B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                Reset Password
            </h4>
            <p style="color:#6b7280;font-size:.85rem;margin:0;">Enter your new password below</p>
        </div>

        <form method="POST" action="<?php echo e(route('password.update')); ?>" id="rp-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="token" value="<?php echo e($token); ?>">

            
            <div style="margin-top:1.5rem;margin-bottom:1.5rem;">
                <label class="rv-label" for="rp-email">Email Address</label>
                <input type="email" class="rv-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       id="rp-email" name="email"
                       value="<?php echo e($email ?? old('email')); ?>"
                       placeholder="john@example.com"
                       autocomplete="email">
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div style="margin-bottom:1.1rem;">
                <label class="rv-label" for="rp-password">New Password</label>
                <div style="position:relative;">
                    <input type="password" class="rv-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="rp-password" name="password"
                           placeholder="Min. 8 characters"
                           autocomplete="new-password"
                           style="padding-right:2.75rem;">
                    <button type="button" class="rv-eye-btn" onclick="togglePwd('rp-password', this)">
                        <i class="ri-eye-fill" style="font-size:1.1rem;"></i>
                    </button>
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div style="color:#ef4444;font-size:.8rem;margin-top:.3rem;"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rp-confirm">Confirm New Password</label>
                <div style="position:relative;">
                    <input type="password" class="rv-input"
                           id="rp-confirm" name="password_confirmation"
                           placeholder="Re-enter new password"
                           autocomplete="new-password"
                           style="padding-right:2.75rem;">
                    <button type="button" class="rv-eye-btn" onclick="togglePwd('rp-confirm', this)">
                        <i class="ri-eye-fill" style="font-size:1.1rem;"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="rv-btn" id="rp-btn">
                <span class="btn-loader"></span>
                <span>Reset Password</span>
            </button>
        </form>

        <p style="text-align:center;color:#6b7280;font-size:.875rem;margin-top:1.5rem;margin-bottom:0;">
            Remember your password?
            <a href="<?php echo e(route('login')); ?>" style="font-weight:700;color:#9333ea;text-decoration:none;">Sign In</a>
        </p>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('ri-eye-fill', 'ri-eye-off-fill');
    } else {
        input.type = 'password';
        icon.classList.replace('ri-eye-off-fill', 'ri-eye-fill');
    }
}
document.getElementById('rp-form').addEventListener('submit', function () {
    const btn = document.getElementById('rp-btn');
    btn.classList.add('loading');
    btn.disabled = true;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/auth/passwords/reset.blade.php ENDPATH**/ ?>