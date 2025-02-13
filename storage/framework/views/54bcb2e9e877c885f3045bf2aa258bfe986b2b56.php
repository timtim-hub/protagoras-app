

<?php $__env->startSection('menu'); ?>
    <?php echo $__env->make('layouts.secondary-menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid secondary-background">
        <div class="row text-center">
            <div class="col-md-12">
                <div class="section-title">
                    <!-- SECTION TITLE -->
                    <div class="text-center mb-9 mt-9 pt-7" id="contact-row">

                        <h6 class="fs-30 mt-6 font-weight-bold text-center"><?php echo e(__('Terms and Conditions')); ?></h6>
                        <p class="fs-12 text-center text-muted mb-5"><span><?php echo e(__('We guarantee your data privacy')); ?></p>

                    </div> <!-- END SECTION TITLE -->
                </div>
            </div>
        </div>
    </div>

    <section id="about-wrapper" class="secondary-background">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-12 policy">  
                    <div class="card border-0 p-4 pt-7 pb-7 mb-9 special-border-right special-border-left">              
                        <div class="card-body"> 

                            <div class="mb-7">
                                <?php echo $pages['terms']; ?>

                            </div>
        
                            <div class="form-group mt-6 text-center">                        
                                <a href="<?php echo e(route('register')); ?>" class="btn btn-primary mr-2"><?php echo e(__('I Agree, Sign me Up')); ?></a> 
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary mr-2"><?php echo e(__('I Agree, Let me Login')); ?></a>                               
                            </div>
                        
                        </div>     
                    </div>  
                </div>
            </div>
        </div>
    </section>
    <?php $__env->startSection('js'); ?>
        <script src="<?php echo e(URL::asset('js/minimize.js')); ?>"></script>
    <?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('curve'); ?>
    <div class="container-fluid" id="curve-container">
        <div class="curve-box">
            <div class="overflow-hidden">
                <svg class="curve secodary-curve" preserveAspectRatio="none" width="1440" height="86" viewBox="0 0 1440 86" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 85.662C240 29.1253 480 0.857 720 0.857C960 0.857 1200 29.1253 1440 85.662V0H0V85.662Z"></path>
                </svg>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.frontend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aihowgkq/protagoras.app/resources/views/service-terms.blade.php ENDPATH**/ ?>