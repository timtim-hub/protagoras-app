<div class="secondary-navbar">
    <div class="row no-gutters">
        <nav class="navbar navbar-expand-lg navbar-light w-100" id="navbar-responsive">
            <a class="navbar-brand" href="<?php echo e(url('/')); ?>"><img id="brand-img"  src="<?php echo e(URL::asset('img/brand/logo.png')); ?>" alt=""></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse section-links" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link scroll active" href="<?php echo e(url('/')); ?>/#main-wrapper"><?php echo e(__('Home')); ?> <span class="sr-only">(current)</span></a>
                    </li>	
                    <?php if(config('frontend.features_section') == 'on'): ?>
                        <li class="nav-item">
                            <a class="nav-link scroll" href="<?php echo e(url('/')); ?>/#features-wrapper"><?php echo e(__('Features')); ?></a>
                        </li>
                    <?php endif; ?>	
                    <?php if(config('frontend.pricing_section') == 'on'): ?>
                        <li class="nav-item">
                            <a class="nav-link scroll" href="<?php echo e(url('/')); ?>/#prices-wrapper"><?php echo e(__('Pricing')); ?></a>
                        </li>
                    <?php endif; ?>							
                    <?php if(config('frontend.faq_section') == 'on'): ?>
                        <li class="nav-item">
                            <a class="nav-link scroll" href="<?php echo e(url('/')); ?>/#faq-wrapper"><?php echo e(__('FAQs')); ?></a>
                        </li>
                    <?php endif; ?>	
                    <?php if(config('frontend.blogs_section') == 'on'): ?>
                        <li class="nav-item">
                            <a class="nav-link scroll" href="<?php echo e(url('/')); ?>/#blog-wrapper"><?php echo e(__('Blogs')); ?></a>
                        </li>
                    <?php endif; ?>										
                </ul>                    
            </div>
            <?php if(Route::has('login')): ?>
                    <div id="login-buttons">
                        <div class="dropdown header-languages" id="frontend-local">
                            <a class="icon" data-bs-toggle="dropdown">
                                <span class="header-icon fa-solid fa-globe mr-4 fs-15"></span>
                            </a>
                            <div class="dropdown-menu animated">
                                <div class="local-menu">
                                    <?php $__currentLoopData = LaravelLocalization::getSupportedLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localeCode => $properties): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(in_array($localeCode, explode(',', $settings->languages))): ?>
                                            <a href="<?php echo e(LaravelLocalization::getLocalizedURL($localeCode, null, [], true)); ?>" class="dropdown-item d-flex pl-4" hreflang="<?php echo e($localeCode); ?>">
                                                <div>
                                                    <span class="font-weight-normal fs-12"><?php echo e(ucfirst($properties['native'])); ?></span> <span class="fs-10 text-muted"><?php echo e($localeCode); ?></span>
                                                </div>
                                            </a>   
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>

                        <?php if(auth()->guard()->check()): ?>
                            <a href="<?php echo e(route('user.templates')); ?>" class="action-button dashboard-button pl-5 pr-5"><?php echo e(__('Dashboard')); ?></a>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="" id="login-button"><?php echo e(__('Sign In')); ?></a>

                            <?php if(config('settings.registration') == 'enabled'): ?>
                                <?php if(Route::has('register')): ?>
                                    <a href="<?php echo e(route('register')); ?>" class="ml-2 action-button register-button pl-5 pr-5"><?php echo e(__('Sign Up')); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
        </nav>
    </div>
</div><?php /**PATH /home/aihowgkq/protagoras.app/resources/views/layouts/secondary-menu.blade.php ENDPATH**/ ?>