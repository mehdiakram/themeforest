<?php
/*-----------------------------------------------------------------------------------*/
/*	Single Campaign Featured - Style 1 (bar)
/*-----------------------------------------------------------------------------------*/

$custom_url = rwmb_meta('sd_custom_donate_button_url');

?>
<?php if ( !empty( $line_color ) ) : ?>
	<?php $rand = mt_rand( 10, 1000 ); ?>
	<style type = "text/css" scoped>
		.sd-funded<?php echo $rand; ?> {
			background-color: <?php echo $line_color; ?>;	
		}
		.sd-funded<?php echo $rand; ?>:after {
			border-top-color: <?php echo $line_color; ?>;
		}
	</style>
<?php endif; ?>
<h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
<div class="row">
	<div class="col-md-10 col-sm-10">
		<div class="sd-campaign-percent">
			<span class="sd-funded-line" style="width: <?php echo esc_attr( $sd_percent ); ?>; <?php echo esc_attr( $line_style ); ?> "><span class="sd-funded <?php echo ! empty( $line_color ) ? 'sd-funded' . $rand : ''; ?>"><?php printf( __( '%s', 'sd-framework' ), $sd_campaign->percent_completed() ); ?></span></span>
		</div>
		<!-- sd-campaign-percent -->
	</div>
	<!-- col-md-10 -->
	
	<div class="col-md-2 col-sm-2">
		<?php if ( ! empty( $custom_url ) ) : ?>
			<a class="sd-custom-url-donate sd-opacity-trans" href="<?php echo esc_url( $custom_url ); ?>" title="<?php _e( 'DONATE NOW', 'sd-framework' ); ?>"><?php _e( 'DONATE NOW', 'sd-framework' ); ?></a>
		<?php else : ?>
			<a class="sd-donate-button sd-opacity-trans" data-campaign-id="<?php echo esc_attr( $sd_campaign->ID ); ?>" <?php echo $btn_style; ?>><?php _e( 'DONATE NOW', 'sd-framework' ); ?></a>
		<?php endif; ?>
	</div>
	<!-- col-md-2 -->
</div>
<!-- row -->
<div class="row">
	<div class="col-md-10">
		<div class="row">
			<div class="col-md-4 col-sm-4">
				<span class="sd-raised">
					<span><?php _e( 'RAISED', 'sd-framework' ); ?></span> <?php echo $sd_raised; ?>
				</span>
			</div>
			<!-- col-md-4 -->
		
			<div class="col-md-4 col-sm-4 sd-center">
				<?php if ( ! $sd_campaign->is_endless() ) : ?>
					<span class="sd-days-left">
					<?php printf( '<span>' . __( 'DAYS LEFT', 'sd-framework' ) . '</span>' . '%s ', $sd_days ); ?></span>
				<?php endif; ?>
			</div>
			<!-- col-md-4 -->
		
			<div class="col-md-4 col-sm-4 sd-right">
				<span class="sd-goal"><span><?php _e( 'GOAL', 'sd-framework' ); ?></span> <?php echo $sd_goal; ?></span>
			</div>
			<!-- col-md-4 -->
		</div>
		<!-- row -->
	</div>
	<!-- col-md-10 -->
</div>
<!-- row -->