<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Catalog Data Browser</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f3ff; padding: 2rem 0 4rem; }
        .cat-card { border: 0; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); margin-bottom: 1.5rem; }
        .cat-header { background: linear-gradient(135deg,#7c3aed,#ec4899); color: #fff; border-radius: 1rem 1rem 0 0; padding: 1rem 1.5rem; }
        .sub-header { font-weight: 700; color: #7c3aed; border-bottom: 2px solid #f3e8ff; padding-bottom: .3rem; margin: 1.25rem 0 .75rem; }
        .product-row { border: 1px solid #eee; border-radius: .6rem; padding: .85rem 1rem; margin-bottom: .75rem; }
        .product-thumb { width: 3.5rem; height: 3.5rem; object-fit: cover; border-radius: .5rem; background: #f3e8ff; }
        .variant-badge { display: inline-block; background: #f3e8ff; color: #6d28d9; border-radius: .4rem; padding: .15rem .55rem; font-size: .78rem; margin: .15rem .25rem .15rem 0; }
        .summary-bar { background: #111827; color: #fff; border-radius: .75rem; padding: 1rem 1.5rem; margin-bottom: 1.5rem; }
        .summary-bar b { font-size: 1.25rem; }
        .no-sub-badge { font-size: .7rem; background: #fef3c7; color: #92400e; border-radius: .4rem; padding: .1rem .5rem; margin-left: .5rem; }
    </style>
</head>
<body>
<div class="container">

    <h2 class="fw-bold mb-3">Catalog Data Browser</h2>
    <p class="text-muted">Internal read-only view of the seeded categories, subcategories, products, variants, and images.</p>

    <?php
        $subCategoryCount = $categories->sum(fn ($c) => $c->subCategories->count());
        $productCount = $categories->sum(fn ($c) => $c->subCategories->sum(fn ($s) => $s->products->count()) + $c->products->count());
        $variantCount = $categories->sum(function ($c) {
            $subProducts = $c->subCategories->flatMap->products;
            $allProducts = $subProducts->concat($c->products);
            return $allProducts->sum(fn ($p) => $p->variants->count());
        });
        $imageCount = $categories->sum(function ($c) {
            $subProducts = $c->subCategories->flatMap->products;
            $allProducts = $subProducts->concat($c->products);
            return $allProducts->sum(fn ($p) => $p->images->count());
        });
    ?>

    <div class="summary-bar d-flex flex-wrap gap-4">
        <div><b><?php echo e($categories->count()); ?></b> Categories</div>
        <div><b><?php echo e($subCategoryCount); ?></b> Subcategories</div>
        <div><b><?php echo e($productCount); ?></b> Products</div>
        <div><b><?php echo e($variantCount); ?></b> Variants</div>
        <div><b><?php echo e($imageCount); ?></b> Images</div>
    </div>

    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card cat-card">
            <div class="cat-header d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0"><?php echo e($category->name); ?></h4>
                <span class="badge bg-light text-dark">
                    <?php echo e($category->subCategories->count()); ?> subcategories ·
                    <?php echo e($category->subCategories->sum(fn ($s) => $s->products->count()) + $category->products->count()); ?> products
                </span>
            </div>
            <div class="card-body">

                <?php $__currentLoopData = $category->subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="sub-header"><?php echo e($subCategory->name); ?></div>

                    <?php $__empty_1 = true; $__currentLoopData = $subCategory->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('pages.partials.catalog-product-row', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted small">No products yet.</p>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if($category->products->isNotEmpty()): ?>
                    <?php if($category->subCategories->isNotEmpty()): ?>
                        <div class="sub-header">Uncategorized <span class="no-sub-badge">no subcategory</span></div>
                    <?php endif; ?>

                    <?php $__currentLoopData = $category->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('pages.partials.catalog-product-row', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>

                <?php if($category->subCategories->isEmpty() && $category->products->isEmpty()): ?>
                    <p class="text-muted">No subcategories or products yet.</p>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

</div>
</body>
</html>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/pages/catalog-browser.blade.php ENDPATH**/ ?>