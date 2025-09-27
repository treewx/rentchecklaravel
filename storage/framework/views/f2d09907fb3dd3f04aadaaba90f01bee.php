<?php $__env->startSection('title', 'Welcome - Rent Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="card-title">Welcome to Rent Tracker</h1>
                <p class="card-text lead">
                    Track your rental property payments automatically with Akahu integration
                </p>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-link fa-3x text-primary mb-3"></i>
                            <h5>Connect Your Bank</h5>
                            <p>Securely connect your bank account through Akahu</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-building fa-3x text-success mb-3"></i>
                            <h5>Manage Properties</h5>
                            <p>Add your rental properties and set rent due dates</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-box">
                            <i class="fas fa-check-circle fa-3x text-info mb-3"></i>
                            <h5>Auto-Check Payments</h5>
                            <p>Automatically verify rent payments on due dates</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <?php if(auth()->guard()->guest()): ?>
                        <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg me-2">Login</a>
                        <a href="<?php echo e(route('register')); ?>" class="btn btn-outline-primary btn-lg">Get Started</a>
                    <?php else: ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.feature-box {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\DEdward\Projects3\rent\resources\views/welcome.blade.php ENDPATH**/ ?>