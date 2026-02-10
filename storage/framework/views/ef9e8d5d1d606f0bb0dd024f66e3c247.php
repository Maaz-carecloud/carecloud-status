<div class="flex flex-col lg:flex-row gap-8">
    
    <div class="flex-1 space-y-8">
        <h1 class="text-2xl mb-2 font-bold">About us</h1>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aboutUs): ?>
            <div class="max-w-[90ch] whitespace-pre-line"><?php echo e($aboutUs); ?></div>
        <?php else: ?>
            <p class="mb-1 max-w-[90ch]">Welcome to CareCloud's home for real-time information on system performance. Here
                you'll find
                live and
                historical data on system performance. If there are any interruptions in service, a note will be posted
                here.</p>
            <p class="max-w-[90ch]">Please contact CareCloud's support team at <span class="text-blue-500">(866)
                    931-3832</span> or email us at <a href="mailto:support@carecloud.com"
                    class="text-blue-500">support@carecloud.com</a> for any
                additional questions or concerns.</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <div class="rounded-lg border-2 p-6 text-center" style="border-color: <?php echo e($overallStatus['color']); ?>;">
            <h1 class="text-3xl font-bold mb-2"><?php echo e($overallStatus['label']); ?></h1>
            <p class="text-gray-600"><?php echo e(now()->timezone('America/New_York')->format('F j, Y - g:i A')); ?></p>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeIncidents->count() > 0): ?>
        <section>
            <h2 class="text-2xl font-bold mb-4">Active Incidents</h2>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeIncidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-lg p-6 bg-white shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($incident->name); ?></h3>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid <?php echo e($incident->status->color()); ?>; color: <?php echo e($incident->status->color()); ?>;">
                                    <?php echo e($incident->status->label()); ?>

                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid <?php echo e($incident->impact->color()); ?>; color: <?php echo e($incident->impact->color()); ?>;">
                                    <?php echo e($incident->impact->label()); ?> Impact
                                </span>
                            </div>
                        </div>
                        <time class="text-sm text-gray-500">
                            <?php echo e($incident->created_at->timezone('America/New_York')->diffForHumans()); ?>

                        </time>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($incident->components->count() > 0): ?>
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">
                            <strong>Affected:</strong>
                            <?php echo e($incident->components->pluck('name')->join(', ')); ?>

                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($incident->updates->count() > 0): ?>
                    <div class="space-y-2 mt-4 border-t pt-4 max-h-96 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $incident->updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-sm">
                            <p class="text-gray-600">
                                <strong><?php echo e($update->created_at->timezone('America/New_York')->format('M d, H:i')); ?></strong>
                                -
                                <span class="font-semibold"><?php echo e($update->status->label()); ?></span>
                            </p>
                            <p class="mt-1"><?php echo nl2br(e($update->message)); ?></p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($scheduledMaintenance->count() > 0): ?>
        <section>
            <h2 class="text-2xl font-bold mb-4">Scheduled Maintenance</h2>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $scheduledMaintenance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-lg p-6 bg-blue-50 border-blue-200">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($maintenance->name); ?></h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Scheduled for: <strong><?php echo e($maintenance->scheduled_at->timezone('America/New_York')->format('F j, Y - g:i A')); ?></strong>
                            </p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($maintenance->components->count() > 0): ?>
                    <p class="text-sm text-gray-600">
                        <strong>Affected:</strong>
                        <?php echo e($maintenance->components->pluck('name')->join(', ')); ?>

                    </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($maintenance->message): ?>
                    <p class="mt-3"><?php echo e($maintenance->message); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <section>
            <h2 class="text-2xl font-bold mb-4">Components</h2>
            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $components; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $component): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border rounded-lg p-4 bg-white shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold"><?php echo e($component->name); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($component->description): ?>
                            <p class="text-sm text-gray-600 mt-1"><?php echo e($component->description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <span class="px-3 py-1 text-sm font-semibold rounded whitespace-nowrap"
                            style="border: 1px solid <?php echo e($component->status->color()); ?>; color: <?php echo e($component->status->color()); ?>;">
                            <?php echo e($component->status->label()); ?>

                        </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastResolvedIncident): ?>
        <section>
            <h2 class="text-2xl font-bold mb-4">Last Resolved Incident</h2>
            <div class="space-y-4">
                <div class="border rounded-lg p-6 bg-white shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo e($lastResolvedIncident->name); ?></h3>
                            <div class="flex gap-2 mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid <?php echo e($lastResolvedIncident->status->color()); ?>; color: <?php echo e($lastResolvedIncident->status->color()); ?>;">
                                    <?php echo e($lastResolvedIncident->status->label()); ?>

                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded"
                                    style="border: 1px solid <?php echo e($lastResolvedIncident->impact->color()); ?>; color: <?php echo e($lastResolvedIncident->impact->color()); ?>;">
                                    <?php echo e($lastResolvedIncident->impact->label()); ?> Impact
                                </span>
                            </div>
                        </div>
                        <time class="text-sm text-gray-500">
                            <?php echo e($lastResolvedIncident->resolved_at ?
                            $lastResolvedIncident->resolved_at->timezone('America/New_York')->diffForHumans() :
                            $lastResolvedIncident->created_at->timezone('America/New_York')->diffForHumans()); ?>

                        </time>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastResolvedIncident->components->count() > 0): ?>
                    <div class="mb-3">
                        <p class="text-sm text-gray-600">
                            <strong>Affected:</strong>
                            <?php echo e($lastResolvedIncident->components->pluck('name')->join(', ')); ?>

                        </p>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastResolvedIncident->updates->count() > 0): ?>
                    <div class="space-y-2 mt-4 border-t pt-4 max-h-96 overflow-y-auto">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $lastResolvedIncident->updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-sm">
                            <p class="text-gray-600">
                                <strong><?php echo e($update->created_at->timezone('America/New_York')->format('M d, H:i')); ?></strong>
                                -
                                <span class="font-semibold"><?php echo e($update->status->label()); ?></span>
                            </p>
                            <p class="mt-1"><?php echo nl2br(e($update->message)); ?></p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="text-center text-sm text-gray-500">
            <p>This page refreshes automatically every 60 seconds</p>
            <button wire:click="refresh" class="text-blue-600 hover:text-blue-800 underline mt-2">
                Refresh Now
            </button>
        </div>
    </div>

    
    <div class="w-full lg:w-80 flex-shrink-0">
        <div class="sticky top-8">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('public.subscribe-form', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4248250094-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </div>
</div>


    <?php
        $__scriptKey = '4248250094-0';
        ob_start();
    ?>
setInterval(() => {
$wire.$refresh();
}, 60000); // 60 seconds
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?><?php /**PATH C:\laragon\www\carecloudStatus\resources\views/livewire/public/status-page.blade.php ENDPATH**/ ?>