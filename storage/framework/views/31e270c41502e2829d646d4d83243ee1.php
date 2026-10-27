<?php $__env->startSection('title', 'Users'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" class="admin-toolbar-form">
            <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="form-control form-control-sm" placeholder="Search users...">
            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-search-line"></i></button>
        </form>
        <button class="btn btn-admin-primary btn-sm admin-toolbar-btn" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="ri-add-line"></i> Add User
        </button>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="stack-title"><?php echo e($user->name); ?></td>
                            <td data-label="Email"><?php echo e($user->email); ?></td>
                            <td data-label="Phone"><?php echo e($user->phone_number ?? '—'); ?></td>
                            <td data-label="Orders"><?php echo e($user->orders_count); ?></td>
                            <td data-label="Role">
                                <?php if($user->is_admin): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if($user->is_blocked): ?>
                                    <span class="badge bg-danger">Blocked</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap stack-actions">
                                <?php if($user->id !== Auth::id()): ?>
                                    <form action="<?php echo e(route('admin.users.toggle-block', $user)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-sm btn-outline-warning"><?php echo e($user->is_blocked ? 'Unblock' : 'Block'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3"><?php echo e($users->links()); ?></div>

    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('admin.users.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_admin" value="1" class="form-check-input" id="newUserIsAdmin">
                            <label class="form-check-label" for="newUserIsAdmin">Admin access</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-admin-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/users/index.blade.php ENDPATH**/ ?>