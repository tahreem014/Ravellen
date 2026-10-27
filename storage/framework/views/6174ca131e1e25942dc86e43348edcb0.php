
<?php $__env->startSection('title', 'BTS Merch'); ?>

<?php $__env->startSection('css'); ?>
<style>
    .bts-wrapper { background: #fff; min-height: 100vh; }

    /* ── Hero ── */
    .bts-hero {
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);
        color: #fff;
        padding: 4rem 0 0;
        position: relative;
    }
    .bts-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 2rem; padding: .3rem .9rem;
        font-size: .8rem; font-weight: 500; margin-bottom: 1.25rem;
    }
    .bts-badge i { color: #f9a8d4; }

    /* ── Product Cards ── */
    .bts-card {
        border: 0; border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        background: #fff;
    }
    .bts-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }
    .bts-card-img { width: 100%; height: 16rem; object-fit: cover; display: block; }

    .bts-explore-btn {
        border: 0; color: #fff; font-weight: 600; font-size: .85rem;
        border-radius: .5rem; padding: .65rem 1.25rem;
        white-space: nowrap;   /* "Order Mystery Scoop" stays on one line */
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        transition: opacity .2s; display: inline-flex; align-items: center; gap: .4rem;
    }
    .bts-explore-btn:hover { opacity: .85; color: #fff; }

    /* ── Bias Selector ── */
    
    .bias-title { font-size: 1.4rem; font-weight: 800; color: #7c3aed; letter-spacing: -.01em; }

    .bias-grid-row { display: flex; gap: .85rem; justify-content: center; flex-wrap: nowrap; }

    .bias-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .45rem; padding: 1.1rem .75rem; min-height: 120px; flex: 1; max-width: 190px;
        border: 2px solid white; border-radius: .75rem;
        cursor: pointer; transition: all .22s; user-select: none; position: relative;
    }
    /* Phones: all nine members in one 3 × 3 grid instead of two rows that run off the screen */
    @media (max-width: 575.98px) {
        .bias-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .65rem; }
        .bias-grid-row { display: contents; }
        .bias-btn { max-width: none; min-height: 6rem; padding: .9rem .4rem; }
        .bias-btn .bias-name { font-size: .75rem; }
    }
    .bias-btn:hover  { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(168,85,247,.22); }
    .bias-btn.active { border-color: #7c3aed !important; box-shadow: 0 6px 20px rgba(124,58,237,.28); }

    /* per-member soft gradients */
    .bias-btn[data-bias="jungkook"] { background: linear-gradient(145deg,#e8e0f7,#ddd6f3); }
    .bias-btn[data-bias="taehyung"] { background: linear-gradient(145deg,#f5d6e8,#eec9df); }
    .bias-btn[data-bias="jimin"]    { background: linear-gradient(145deg,#fadadd,#f5c6cb); }
    .bias-btn[data-bias="namjoon"]  { background: linear-gradient(145deg,#d8d4e8,#ccc8de); }
    .bias-btn[data-bias="jin"]      { background: linear-gradient(145deg,#f5d6e8,#eec9df); }
    .bias-btn[data-bias="jhope"]    { background: linear-gradient(145deg,#e8e0f7,#ddd6f3); }
    .bias-btn[data-bias="suga"]     { background: linear-gradient(145deg,#d8d4e8,#ccc8de); }
    .bias-btn[data-bias="ot7"]      { background: linear-gradient(145deg,#c4b5fd,#a78bfa); }
    .bias-btn[data-bias="bt21"]     { background: linear-gradient(145deg,#ddd6fe,#c4b5fd); }

    .bias-emoji { font-size: 2rem; line-height: 1; }
    .bias-name  { font-size: .8rem; font-weight: 700; color: #5b21b6; }
    
    .bias-btn.active .bias-heart { color: #fff; }
    .bias-btn[data-bias="ot7"] .bias-name,
    .bias-btn[data-bias="bt21"] .bias-name { color: #fff; }
    .bias-btn[data-bias="ot7"] .bias-heart,
    .bias-btn[data-bias="bt21"] .bias-heart { color: rgba(255,255,255,.5); }
    .bias-btn[data-bias="ot7"].active .bias-heart,
    .bias-btn[data-bias="bt21"].active .bias-heart { color: #fff; }

    /* ── Card badge ── */
    .prod-badge {
        position: absolute; top: .75rem; right: .75rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: .35rem; color: #fff;
    }
    .badge-army { background: #7c3aed; }
    .badge-min  { background: #ec4899; }

    .borahae-banner {
        background: linear-gradient(to right, #ede9fe, #fce7f3);
        border-radius: 3rem;
        padding: 1rem 2.5rem;
        font-size: 1.15rem; font-weight: 700;
        display: inline-block;
        background-clip: padding-box;
    }
    .borahae-banner span {
        background: linear-gradient(to right, #9333ea, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="bts-wrapper">

    
    <div class="bts-hero text-center">
        <div class="container px-4 position-relative" style="z-index:1;">
            <div class="bts-badge">
                <i class="ri-heart-fill"></i> ARMY Exclusive Collection
            </div>
            <h1 class="fw-bold mb-3 text-white" style="font-size:clamp(2rem,5vw,3rem);">BTS Merch</h1>
            <p class="mb-4 mx-auto" style="max-width:34rem;color:rgba(255,255,255,.9);font-size:1.05rem;">
                Exclusive mystery scoops and balls for ARMY! Featuring all members. Borahae! 💜
            </p>
            <p style="color:rgba(255,255,255,.75);font-size:.9rem;">
                <i class="ri-sparkling-line me-1"></i> Special themed items for each member
            </p>
        </div>

        
        <div style="line-height:0;margin-top:3rem;margin-bottom:-2px;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;display:block;">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="#fff"/>
            </svg>
        </div>
    </div>

    
    <div class="container px-4 pt-5">

        
        <div class="bias-section mb-4">
            <div class="text-center bias-title mb-4">Choose Your Bias 💜</div>
            <?php
            $row1 = [
                ['id'=>'jungkook','name'=>'Jungkook','emoji'=>'🐰'],
                ['id'=>'taehyung','name'=>'Taehyung','emoji'=>'🐯'],
                ['id'=>'jimin',   'name'=>'Jimin',   'emoji'=>'🐥'],
                ['id'=>'namjoon', 'name'=>'Namjoon', 'emoji'=>'🐨'],
                ['id'=>'jin',     'name'=>'Jin',     'emoji'=>'🐹'],
            ];
            $row2 = [
                ['id'=>'jhope','name'=>'J-Hope','emoji'=>'🐿️'],
                ['id'=>'suga', 'name'=>'Suga',  'emoji'=>'🐱'],
                ['id'=>'ot7',  'name'=>'OT7',   'emoji'=>'💜'],
                ['id'=>'bt21', 'name'=>'BT21',  'emoji'=>'✨'],
            ];
            ?>
            <div class="bias-grid">
            
            <div class="bias-grid-row mb-3">
                <?php $__currentLoopData = $row1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bias-btn" data-bias="<?php echo e($m['id']); ?>" onclick="selectBias('<?php echo e($m['id']); ?>')">
                    
                    <span class="bias-emoji"><?php echo e($m['emoji']); ?></span>
                    <span class="bias-name"><?php echo e($m['name']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            
            <div class="bias-grid-row">
                <?php $__currentLoopData = $row2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bias-btn" data-bias="<?php echo e($m['id']); ?>" onclick="selectBias('<?php echo e($m['id']); ?>')">
                    
                    <span class="bias-emoji"><?php echo e($m['emoji']); ?></span>
                    <span class="bias-name"><?php echo e($m['name']); ?></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            </div>
        </div>

        
        <div class="row g-4 justify-content-center mb-2" id="dynamicCards" style="display:none!important;">

            
            <div class="col-sm-10 col-md-6 col-lg-5">
                <div class="bts-card card h-100">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=600&q=80"
                             alt="Mystery Scoop" class="bts-card-img">
                        <span class="prod-badge badge-army">ARMY Special</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-1" id="dynScoopTitle">Mystery Scoop 💜</h5>
                        <p class="text-muted small mb-3">Exclusive themed mystery items for ARMY! Available in Classic, Standard, and Premium sizes.</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Starting from</span>
                            <span class="fw-bold" id="dynScoopPrice" style="color:#7c3aed;">PKR 2,500</span>
                        </div>
                        <button class="bts-explore-btn w-100 justify-content-center mt-auto" onclick="goToProduct('scoop')">Order Mystery Scoop</button>
                    </div>
                </div>
            </div>

            
            <div class="col-sm-10 col-md-6 col-lg-5">
                <div class="bts-card card h-100">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=600&q=80"
                             alt="Mystery Balls" class="bts-card-img">
                        <span class="prod-badge badge-min">Min 5 pcs</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold mb-1" id="dynBallsTitle">Mystery Balls 💜</h5>
                        <p class="text-muted small mb-3">Surprise mystery balls with ARMY-themed goodies! Minimum 5 balls, add by multiples of 5.</p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small" id="dynBallsQty">5 balls for</span>
                            <span class="fw-bold" id="dynBallsPrice" style="color:#7c3aed;">PKR 1,000</span>
                        </div>
                        <button class="bts-explore-btn w-100 justify-content-center mt-auto" onclick="goToProduct('balls')">Order Mystery Balls</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="container px-4 pb-5">
        <div class="text-center mt-4 pt-2">
            <span class="borahae-banner"><span>보라해 Borahae 💜 I Purple You</span></span>
        </div>
    </div>

</div>


<script>
    let selectedBias = null;
    const memberLabels = {
        jungkook:'Jungkook 🐰', taehyung:'Taehyung 🐯', jimin:'Jimin 🐥',
        namjoon:'Namjoon 🐨', jin:'Jin 🐹', jhope:'J-Hope 🐿️',
        suga:'Suga 🐱', ot7:'OT7 💜', bt21:'BT21 ✨'
    };

    function selectBias(id) {
        selectedBias = id;
        document.querySelectorAll('.bias-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.bias-btn[data-bias="${id}"]`).classList.add('active');
        updateCards();
        // kept in the URL so Back from a product page returns here with this member still chosen
        history.replaceState(null, '', location.pathname + '?bias=' + encodeURIComponent(id));
    }

    // Each member's cheapest scoop and smallest balls pack, sale prices applied
    const biasPrices = <?php echo json_encode($biasPrices ?? [], 15, 512) ?>;

    function fmtPkr(n) { return 'PKR ' + Math.round(n).toLocaleString(); }
    function priceHtml(amount, original) {
        return fmtPkr(amount) + (original && original > amount
            ? ' <s style="color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">' + fmtPkr(original) + '</s>'
            : '');
    }

    function updateCards() {
        if (!selectedBias) return;
        const label = memberLabels[selectedBias];
        document.getElementById('dynScoopTitle').textContent = label + ' Mystery Scoop 💜';
        document.getElementById('dynBallsTitle').textContent = label + ' Mystery Balls 💜';
        const prices = biasPrices[selectedBias] || {};
        if (prices.scoop) document.getElementById('dynScoopPrice').innerHTML = priceHtml(prices.scoop.price, prices.scoop.original);
        if (prices.balls) {
            document.getElementById('dynBallsQty').textContent   = prices.balls.qty + ' balls for';
            document.getElementById('dynBallsPrice').innerHTML = priceHtml(prices.balls.price, prices.balls.original);
        }
        document.getElementById('dynamicCards').style.setProperty('display', 'flex', 'important');
    }

    function goToProduct(type) {
        if (!selectedBias) return;
        const params = new URLSearchParams({ bias: selectedBias });
        const base = type === 'scoop' ? '/mystery-scoops' : '/mystery-balls';
        window.location.href = base + '?' + params.toString();
    }

    // Coming back (or opening a shared link) with ?bias=… shows that member's cards straight away
    const savedBias = new URLSearchParams(location.search).get('bias');
    if (savedBias && document.querySelector(`.bias-btn[data-bias="${CSS.escape(savedBias)}"]`)) selectBias(savedBias);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.landing-page-layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/bts-merch.blade.php ENDPATH**/ ?>