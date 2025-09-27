<?php $__env->startSection('title', 'Edit ' . $property->name . ' - Rent Tracker'); ?>

<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Property: <?php echo e($property->name); ?></h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('properties.update', $property)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Property Name *</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="name" name="name" value="<?php echo e(old('name', $property->name)); ?>" required>
                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tenant_name" class="form-label">Tenant Name</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['tenant_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       id="tenant_name" name="tenant_name" value="<?php echo e(old('tenant_name', $property->tenant_name)); ?>">
                                <?php $__errorArgs = ['tenant_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address *</label>
                        <textarea class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                  id="address" name="address" rows="3" required><?php echo e(old('address', $property->address)); ?></textarea>
                        <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_amount" class="form-label">Rent Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control <?php $__errorArgs = ['rent_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="rent_amount" name="rent_amount" value="<?php echo e(old('rent_amount', $property->rent_amount)); ?>"
                                           step="0.01" min="0" required>
                                    <?php $__errorArgs = ['rent_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_frequency" class="form-label">Rent Frequency *</label>
                                <select class="form-control <?php $__errorArgs = ['rent_frequency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="rent_frequency" name="rent_frequency" required>
                                    <option value="">Select frequency</option>
                                    <option value="weekly" <?php echo e(old('rent_frequency', $property->rent_frequency) == 'weekly' ? 'selected' : ''); ?>>Weekly</option>
                                    <option value="fortnightly" <?php echo e(old('rent_frequency', $property->rent_frequency) == 'fortnightly' ? 'selected' : ''); ?>>Fortnightly</option>
                                    <option value="monthly" <?php echo e(old('rent_frequency', $property->rent_frequency) == 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                                </select>
                                <?php $__errorArgs = ['rent_frequency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="rent_due_day_of_week" class="form-label">Rent Due Day *</label>
                                <select class="form-control <?php $__errorArgs = ['rent_due_day_of_week'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="rent_due_day_of_week" name="rent_due_day_of_week" required>
                                    <option value="">Select day of week</option>
                                    <option value="0" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '0' ? 'selected' : ''); ?>>Sunday</option>
                                    <option value="1" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '1' ? 'selected' : ''); ?>>Monday</option>
                                    <option value="2" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '2' ? 'selected' : ''); ?>>Tuesday</option>
                                    <option value="3" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '3' ? 'selected' : ''); ?>>Wednesday</option>
                                    <option value="4" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '4' ? 'selected' : ''); ?>>Thursday</option>
                                    <option value="5" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '5' ? 'selected' : ''); ?>>Friday</option>
                                    <option value="6" <?php echo e(old('rent_due_day_of_week', $property->rent_due_day_of_week) == '6' ? 'selected' : ''); ?>>Saturday</option>
                                </select>
                                <?php $__errorArgs = ['rent_due_day_of_week'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_statement_keyword" class="form-label">Bank Statement Keyword *</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['bank_statement_keyword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                               id="bank_statement_keyword" name="bank_statement_keyword" value="<?php echo e(old('bank_statement_keyword', $property->bank_statement_keyword)); ?>" required
                               placeholder="e.g., RENT PAYMENT, JOHN SMITH, etc.">
                        <div class="form-text">
                            Enter a keyword or phrase that appears in bank statements to identify rent payments for this property.
                        </div>
                        <?php $__errorArgs = ['bank_statement_keyword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   <?php echo e(old('is_active', $property->is_active) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">
                                Property is active (rent checking enabled)
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="<?php echo e(route('properties.show', $property)); ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                        <div>
                            <form method="POST" action="<?php echo e(route('properties.destroy', $property)); ?>" class="d-inline me-2"
                                  onsubmit="return confirm('Are you sure you want to delete this property? This action cannot be undone.')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash"></i> Delete Property
                                </button>
                            </form>
                            <button type="submit" class="btn btn-primary">Update Property</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\DEdward\Projects3\rent\resources\views/properties/edit.blade.php ENDPATH**/ ?>