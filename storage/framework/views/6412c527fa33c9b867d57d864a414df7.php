<?php $__env->startSection('title', 'Dashboard - Rent Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Dashboard</h1>
            <?php if(!auth()->user()->akahuCredentials): ?>
                <a href="<?php echo e(route('akahu.connect')); ?>" class="btn btn-success">
                    <i class="fas fa-link"></i> Connect Akahu Account
                </a>
            <?php else: ?>
                <div class="btn-group">
                    <a href="<?php echo e(route('properties.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                    <form method="POST" action="<?php echo e(route('akahu.disconnect')); ?>" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-outline-danger"
                                onclick="return confirm('Are you sure you want to disconnect your Akahu account?')">
                            <i class="fas fa-unlink"></i> Disconnect Akahu
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <?php if(!auth()->user()->akahuCredentials): ?>
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle"></i> Getting Started</h5>
                <p>To start tracking your rent payments, you need to:</p>
                <ol>
                    <li>Connect your Akahu account by entering your App Token and User Token</li>
                    <li>Add your rental properties with rent amounts and due dates</li>
                    <li>The system will automatically check for rent payments on due dates</li>
                </ol>
                <p class="mb-0">Click "Connect Akahu Account" above to get started.</p>
            </div>
        <?php endif; ?>

        <?php if($overdueRent->count() > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i> Overdue Rent (<?php echo e($overdueRent->count()); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php $__currentLoopData = $overdueRent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rentCheck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong><?php echo e($rentCheck->property->name); ?></strong><br>
                                        <small class="text-muted"><?php echo e($rentCheck->property->address); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">$<?php echo e(number_format($rentCheck->expected_amount, 2)); ?></div>
                                        <small class="text-danger">
                                            Due <?php echo e($rentCheck->due_date->format('M j, Y')); ?>

                                            (<?php echo e($rentCheck->due_date->diffForHumans()); ?>)
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($upcomingRent->count() > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">
                                <i class="fas fa-clock"></i> Upcoming Rent (<?php echo e($upcomingRent->count()); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php $__currentLoopData = $upcomingRent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rentCheck): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong><?php echo e($rentCheck->property->name); ?></strong><br>
                                        <small class="text-muted"><?php echo e($rentCheck->property->address); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">$<?php echo e(number_format($rentCheck->expected_amount, 2)); ?></div>
                                        <small class="text-warning">
                                            Due <?php echo e($rentCheck->due_date->format('M j, Y')); ?>

                                            (<?php echo e($rentCheck->due_date->diffForHumans()); ?>)
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Properties Overview</h5>
                    </div>
                    <div class="card-body">
                        <?php if($properties->count() > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Rent Amount</th>
                                            <th>Due Day</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo e($property->name); ?></strong><br>
                                                    <small class="text-muted"><?php echo e($property->address); ?></small>
                                                </td>
                                                <td>$<?php echo e(number_format($property->rent_amount, 2)); ?></td>
                                                <td><?php echo e($property->rent_due_day); ?><?php echo e($property->rent_due_day == 1 ? 'st' : ($property->rent_due_day == 2 ? 'nd' : ($property->rent_due_day == 3 ? 'rd' : 'th'))); ?></td>
                                                <td>
                                                    <?php if($property->is_active): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo e(route('properties.show', $property)); ?>"
                                                       class="btn btn-sm btn-outline-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <h5>No Properties Added</h5>
                                <p class="text-muted">Start by adding your first rental property</p>
                                <?php if(auth()->user()->akahuCredentials): ?>
                                    <a href="<?php echo e(route('properties.create')); ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Property
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Account Status</h5>
                    </div>
                    <div class="card-body">
                        <?php if(auth()->user()->akahuCredentials): ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                <div>
                                    <strong>Akahu Connected</strong><br>
                                    <small class="text-muted">Bank account linked</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Connected Accounts:</strong><br>
                                <?php if(auth()->user()->akahuCredentials->accounts): ?>
                                    <?php $__currentLoopData = auth()->user()->akahuCredentials->accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <small class="d-block text-muted">
                                            <?php echo e($account['name'] ?? 'Account'); ?>

                                            (<?php echo e($account['type'] ?? 'Unknown'); ?>)
                                        </small>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                <div>
                                    <strong>Not Connected</strong><br>
                                    <small class="text-muted">Connect your bank account</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr>
                        <div class="stats">
                            <div class="stat-item mb-2">
                                <strong>Total Properties:</strong> <?php echo e($properties->count()); ?>

                            </div>
                            <div class="stat-item mb-2">
                                <strong>Active Properties:</strong> <?php echo e($properties->where('is_active', true)->count()); ?>

                            </div>
                            <div class="stat-item">
                                <strong>Monthly Rent:</strong> $<?php echo e(number_format($properties->where('is_active', true)->sum('rent_amount'), 2)); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\DEdward\Projects3\rent\resources\views/dashboard.blade.php ENDPATH**/ ?>