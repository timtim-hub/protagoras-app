

<?php $__env->startSection('page-header'); ?>
	<!-- PAGE HEADER -->
	<div class="page-header mt-5-7 justify-content-center">
		<div class="page-leftheader text-center">
			<h4 class="page-title mb-0"><?php echo e(__('View Plan')); ?></h4>
			<ol class="breadcrumb mb-2">
				<ol class="breadcrumb mb-2">
					<li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><i class="fa-solid fa-sack-dollar mr-2 fs-12"></i><?php echo e(__('Admin')); ?></a></li>
					<li class="breadcrumb-item" aria-current="page"><a href="<?php echo e(route('admin.finance.dashboard')); ?>"> <?php echo e(__('Finance Management')); ?></a></li>
					<li class="breadcrumb-item" aria-current="page"><a href="<?php echo e(route('admin.finance.plans')); ?>"> <?php echo e(__('Subscription Plans')); ?></a></li>
					<li class="breadcrumb-item active" aria-current="page"><a href="<?php echo e(url('#')); ?>"> <?php echo e(__('Plan Details')); ?></a></li>
				</ol>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>						
	<div class="row justify-content-center">
		<div class="col-lg-6 col-md-6 col-xm-12">
			<div class="card border-0">
				<div class="card-header">
					<h3 class="card-title"><?php echo e(__('Subscription Plan Name')); ?>: <span class="text-info"><?php echo e($id->plan_name); ?></span> </h3>
				</div>
				<div class="card-body pt-5">
					<div class="row">
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Plan Type')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(ucfirst($id->payment_frequency)); ?></span>
							</div>
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Plan Name')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(ucfirst($id->plan_name)); ?></span>
							</div>	
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Plan Status')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(ucfirst($id->status)); ?></span>
							</div>	
						</div>										
					
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Price')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->price); ?></span>
							</div>	
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Currency')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->currency); ?></span>
							</div>	
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Created Date')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(date_format($id->created_at, 'M d Y, H:i:s')); ?></span>
							</div>							
						</div>	
					</div>

					<h6 class="fs-12 font-weight-bold text-center mb-5 mt-2"><?php echo e(__('Payment Gateways')); ?></h6>

					<div class="row">
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Paypal Plan ID')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->paypal_gateway_plan_id); ?></span>
							</div>							
						</div>	
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Stripe Plan ID')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->stripe_gateway_plan_id); ?></span>
							</div>							
						</div>	
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Razorpay Plan ID')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->razorpay_gateway_plan_id); ?></span>
							</div>							
						</div>	
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Paystack Plan ID')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->paystack_gateway_plan_id); ?></span>
							</div>							
						</div>												
					</div>

					<h6 class="fs-12 font-weight-bold text-center mb-5 mt-2"><i class="fa-solid fa-box-circle-check text-info fs-14 mr-1 fw-2"></i><?php echo e(__('Included AI Credits')); ?></h6>

					<div class="row">
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('GPT 4 Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->gpt_4_credits)); ?></span>
							</div>							
						</div>	
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('GPT 4 Turbo Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->gpt_4_turbo_credits)); ?></span>
							</div>							
						</div>	
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('GPT 3 Turbo Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->gpt_3_turbo_credits)); ?></span>
							</div>							
						</div>												

						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Fine Tune Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->fine_tune_credits)); ?></span>
							</div>							
						</div>	
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Claude 3 Opus Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->claude_3_opus_credits)); ?></span>
							</div>							
						</div>	
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Claude 3 Sonnet Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->claude_3_sonnet_credits)); ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Claude 3 Haiku Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->claude_3_haiku_credits)); ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Gemini Pro Model Credits')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->gemini_pro_credits)); ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Characters Included')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->characters)); ?></span>
							</div>							
						</div>												

						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Dalle Images Included')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->dalle_images)); ?></span>
							</div>	
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Stable Diffusion Images Included')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->sd_images)); ?></span>
							</div>	
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('Minutes Included')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e(number_format($id->minutes)); ?></span>
							</div>
						</div>
					</div>

					<h6 class="fs-12 font-weight-bold text-center mb-5 mt-2"><i class="fa-solid fa-box-circle-check text-info fs-14 mr-1 fw-2"></i><?php echo e(__('Included AI Models')); ?></h6>

					<div class="row">
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Models for Templates')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->model); ?></span>
							</div>							
						</div>	
						<div class="col-md-6 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Model for Chatbots')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php echo e($id->model_chat); ?></span>
							</div>							
						</div>	
					</div>

					<h6 class="fs-12 font-weight-bold text-center mb-5 mt-2"><i class="fa-solid fa-box-circle-check text-info fs-14 mr-1 fw-2"></i><?php echo e(__('Included Features')); ?></h6>

					<div class="row">
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Image Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->image_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Voiceover Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->voiceover_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Speech to Text Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->transcribe_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Chat Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->chat_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Code Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->code_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
						<div class="col-lg-4 col-md-4 col-12">
							<div class="prepaid-view-box text-center">
								<h6 class="text-muted fs-12 mb-1"><?php echo e(__('AI Article Wizard Feature')); ?> </h6>
								<span class="fs-14 font-weight-semibold"><?php if($id->wizard_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></span>
							</div>							
						</div>
					</div>
					<table class="table" id="database-backup">
						<tbody>
							
							
							
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Total Text Result Length')); ?></span><span><?php echo e(number_format($id->max_tokens)); ?> <?php echo e(__(' tokens')); ?></span></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Supported Template Package')); ?></span><?php echo e(ucfirst($id->templates)); ?></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Supported Chat Package')); ?></span><?php echo e(ucfirst($id->chats)); ?></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Team Members')); ?></span><?php echo e($id->team_members); ?></td></tr>

							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('AI Vision Feature')); ?></span><?php if($id->vision_feature): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Free Plan')); ?></span><?php if($id->free): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Featured Plan')); ?></span><?php if($id->featured): ?><i class="fa-solid fa-circle-check table-info-button green fs-20"></i><?php else: ?> <i class="fa-solid fa-circle-xmark red table-info-button fs-20"></i> <?php endif; ?></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Primary Heading')); ?></span><span><?php echo e(ucfirst($id->primary_heading)); ?></span></td></tr>
							<tr><td class="justify-content-between d-flex align-items-center pl-5 pr-5 pt-4 pb-4"><span class="font-weight-bold"><?php echo e(__('Plan Features')); ?></span><span><?php echo e(ucfirst($id->plan_features)); ?></span></td></tr>
							
						</tbody>
					</table>		

					<!-- SAVE CHANGES ACTION BUTTON -->
					<div class="border-0 text-center mb-4 mr-4">
						<a href="<?php echo e(route('admin.finance.plans')); ?>" class="btn btn-cancel mr-2 pl-7 pr-7 ripple"><?php echo e(__('Return')); ?></a>
						<a href="<?php echo e(route('admin.finance.plan.edit', $id)); ?>" class="btn btn-primary pl-7 pr-7 ripple"><?php echo e(__('Edit Plan')); ?></a>						
					</div>
				</div>
			</div>
		</div>
	</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/aihowgkq/protagoras.app/resources/views/admin/finance/plans/show.blade.php ENDPATH**/ ?>