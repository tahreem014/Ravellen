<!DOCTYPE html>
<html lang="en" data-theme="corporate" class="bg-white">
<head>
    <meta charset="utf-8" />
    <?php echo $__env->make('layouts.title-meta', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Your destination for mystery scoops, BTS merch, and curated gift boxes." name="description" />
    <meta content="Ravellen" name="author" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php echo $__env->make('layouts.head-css', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class=" overflow-x-hidden font-poppins">

  
    
    

    <div class=" flex flex-col">
        
        <?php echo $__env->make('layouts.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      
      <main class="overflow-y-hidden overflow-x-hidden">
        <?php echo $__env->yieldContent('content'); ?>
        <?php echo $__env->yieldContent('script'); ?>
      </main>


      
        <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    </div>

    <?php echo $__env->make('layouts.toastify', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.live-updates', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
  

</body>
</html>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/layouts/landing-page-layout.blade.php ENDPATH**/ ?>