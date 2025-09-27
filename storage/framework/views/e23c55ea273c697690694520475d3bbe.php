<?php $__env->startSection('title', 'Properties - Rent Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Properties</h1>
            <?php if(auth()->user()->akahuCredentials): ?>
                <a href="<?php echo e(route('properties.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Property
                </a>
            <?php endif; ?>
        </div>

        <?php if($properties->count() > 0): ?>
            <div class="row">
                <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><?php echo e($property->name); ?></h5>
                                <?php if($property->is_active): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo e($property->address); ?>

                                </p>

                                <?php if($property->tenant_name): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-user"></i> <strong>Tenant:</strong> <?php echo e($property->tenant_name); ?>

                                    </p>
                                <?php endif; ?>

                                <p class="mb-2">
                                    <i class="fas fa-dollar-sign"></i> <strong>Rent:</strong> $<?php echo e(number_format($property->rent_amount, 2)); ?>

                                </p>

                                <p class="mb-3">
                                    <i class="fas fa-calendar"></i> <strong>Due:</strong> <?php echo e($property->rent_due_day); ?><?php echo e($property->rent_due_day == 1 ? 'st' : ($property->rent_due_day == 2 ? 'nd' : ($property->rent_due_day == 3 ? 'rd' : 'th'))); ?> of each month
                                </p>

                                <div class="mt-auto">
                                    <div class="btn-group w-100">
                                        <a href="<?php echo e(route('properties.show', $property)); ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?php echo e(route('properties.edit', $property)); ?>" class="btn btn-outline-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-4x text-muted mb-4"></i>
                <h3>No Properties Yet</h3>
                <p class="text-muted mb-4">Start managing your rental properties by adding your first property.</p>

                <?php if(auth()->user()->akahuCredentials): ?>
                    <a href="<?php echo e(route('properties.create')); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                <?php else: ?>
                    <div class="alert alert-info d-inline-block">
                        <p class="mb-2">You need to connect your Akahu account first before adding properties.</p>
                        <form method="POST" action="<?php echo e(route('akahu.connect')); ?>" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-link"></i> Connect Akahu Account
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\DEdward\Projects3\rent\resources\views/properties/index.blade.php ENDPATH**/ ?>