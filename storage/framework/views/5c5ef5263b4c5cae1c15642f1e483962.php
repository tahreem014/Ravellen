
<?php $__env->startSection('title'); ?>
<?php echo app('translator')->get('translation.signup'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<style>
    body,
    html { background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important; }

    .rv-register-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #fde8ee 0%, #f3eafc 50%, #eaf3fc 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem;
        font-family: 'Inter', sans-serif;
    }

    .rv-register-card {
        background: #ffffff !important;
        border-radius: .75rem !important;
        box-shadow: 0 8px 48px rgba(124,58,237,.10) !important;
        padding: 3rem 2.5rem !important;
        width: 100% !important;
        max-width: 32rem !important;
        border: none !important;
    }

    .rv-register-card .rv-input {
        background: #ebebeb !important;
        border: none !important;
        border-radius: .5rem !important;
        padding: .6rem 1.1rem !important;
        font-size: .95rem !important;
        color: #374151 !important;
        width: 100% !important;
        box-shadow: none !important;
        outline: none !important;
        display: block !important;
        box-sizing: border-box !important;
    }

    .rv-register-card .rv-input:focus {
        background: #e2e2e2 !important;
        box-shadow: none !important;
        border: none !important;
        outline: none !important;
    }

    .rv-label {
        font-size: .875rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
        margin-bottom: .45rem !important;
        display: block !important;
    }

    .rv-btn-register {
        background: linear-gradient(to right, #f472b6, #ec4899) !important;
        border: none !important;
        border-radius: .5rem !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 1rem !important;
        padding: .5rem !important;
        width: 100% !important;
        cursor: pointer !important;
        transition: opacity .2s !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: .5rem !important;
    }
    .rv-btn-register:hover { opacity: .88 !important; }
    .rv-btn-register:disabled { opacity: .7 !important; cursor: not-allowed !important; }

    .rv-btn-register .btn-loader {
        display: none;
        width: 1.1rem; height: 1.1rem;
        border: 2px solid rgba(255,255,255,.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    .rv-btn-register.loading .btn-loader { display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .register-progress {
        height: 3px;
        background: linear-gradient(to right, #f472b6, #a855f7);
        border-radius: 0 0 .5rem .5rem;
        width: 0; transition: width .8s ease; display: none;
    }
    .register-progress.active { display: block; }

    .rv-eye-btn {
        position: absolute !important;
        right: .85rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        background: none !important;
        border: none !important;
        color: #9ca3af !important;
        cursor: pointer !important;
        padding: 0 !important;
        line-height: 1 !important;
    }
</style>

<div class="rv-register-page">
    <div class="rv-register-card">

        
        <div style="text-align:center; margin-bottom:2rem;">
            <a href="<?php echo e(url('/')); ?>" class="rv-logo text-decoration-none">
            <img src="<?php echo e(asset('images/ravellen-logo.png')); ?>" alt="Ravellen" height="56" class="mb-3">
            </a>
            <h4 style="
                font-weight:800;
                font-size:1.65rem;
                margin-bottom:.3rem;
                background: linear-gradient(to right, #6517AB, #9A1A8B);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            ">Create Account</h4>
            <p style="color:#6b7280;font-size:.875rem;margin:0;">Join Ravellen and start your mystery adventure</p>
        </div>

        <?php if (isset($component)) { $__componentOriginal8acbd4837bbd25b9ec0fd8dc636511f4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8acbd4837bbd25b9ec0fd8dc636511f4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.checkout-login-notice','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('checkout-login-notice'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8acbd4837bbd25b9ec0fd8dc636511f4)): ?>
<?php $attributes = $__attributesOriginal8acbd4837bbd25b9ec0fd8dc636511f4; ?>
<?php unset($__attributesOriginal8acbd4837bbd25b9ec0fd8dc636511f4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8acbd4837bbd25b9ec0fd8dc636511f4)): ?>
<?php $component = $__componentOriginal8acbd4837bbd25b9ec0fd8dc636511f4; ?>
<?php unset($__componentOriginal8acbd4837bbd25b9ec0fd8dc636511f4); ?>
<?php endif; ?>

        <form action="<?php echo e(route('register')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-name">Full Name</label>
                <input type="text"
                       placeholder="John Doe"
                       class="rv-input <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       id="rv-name" name="name"
                       value="<?php echo e(old('name')); ?>"
                       autocomplete="name">
                <?php $__errorArgs = ['name'];
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
                <label class="rv-label" for="rv-email">Email</label>
                <input type="email"
                       placeholder="john@example.com"
                       class="rv-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       id="rv-email" name="email"
                       value="<?php echo e(old('email')); ?>"
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

            
            <div style="margin-bottom:1.5rem;">
                <label class="rv-label" for="rv-password">Password</label>
                <div style="position:relative;">
                    <input type="password"
                           placeholder="Min. 8 characters"
                           class="rv-input password-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="rv-password" name="password"
                           autocomplete="new-password"
                           style="padding-right:2.75rem !important;">
                    <button type="button" class="rv-eye-btn password-addon">
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
                <label class="rv-label" for="rv-password-confirm">Confirm Password</label>
                <div style="position:relative;">
                    <input type="password"
                           placeholder="Re-enter password"
                           class="rv-input <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                           id="rv-password-confirm" name="password_confirmation"
                           autocomplete="new-password"
                           style="padding-right:2.75rem !important;">
                </div>
                <?php $__errorArgs = ['password_confirmation'];
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
                <label class="rv-label" for="rv-phone">Phone Number</label>
                <input type="tel"
                       placeholder="+923001234567"
                       class="rv-input <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       id="rv-phone" name="phone_number"
                       value="<?php echo e(old('phone_number')); ?>"
                       autocomplete="tel">
                <?php $__errorArgs = ['phone_number'];
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

            
            <button type="submit" class="rv-btn-register" id="register-btn">
                <span class="btn-loader"></span>
                <span class="btn-text">Create Account</span>
            </button>
            <div class="register-progress" id="register-progress"></div>

        </form>

        
        <p style="text-align:center;color:#6b7280;font-size:.9rem;margin-top:1.5rem;margin-bottom:0;">
            Already have an account?
            <a href="<?php echo e(route('login')); ?>"
               style="font-weight:700;color:#9333ea;text-decoration:none;">Sign In here</a>
        </p>

    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
<script>
// ── Password toggle ──────────────────────────────────────────────────────
document.querySelector('.password-addon').addEventListener('click', function () {
    const input = document.getElementById('rv-password');
    const icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('ri-eye-fill', 'ri-eye-off-fill');
    } else {
        input.type = 'password';
        icon.classList.replace('ri-eye-off-fill', 'ri-eye-fill');
    }
});

// ── Register loading bar ─────────────────────────────────────────────────
document.querySelector('form').addEventListener('submit', function () {
    const btn      = document.getElementById('register-btn');
    const progress = document.getElementById('register-progress');
    btn.classList.add('loading');
    btn.disabled = true;
    progress.classList.add('active');
    setTimeout(() => { progress.style.width = '90%'; }, 50);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/auth/register.blade.php ENDPATH**/ ?>