<?php


class Zeenshare_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'zeenshare_widget', // Base ID
			'Zeenshare_Widget', // Name
			array( 'description' => __( 'Zeenshare', 'text_domain' ), ) // Args
		);
	}

	public function form( $instance ) {
		// outputs the options form on admin
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
	}

	public function widget( $args ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		if(isset($_REQUEST["zs_sid"]) && $_REQUEST["zs_sid"] != null) {
			$zs_sid = $_REQUEST["zs_sid"];
			$zs_domain_name =  get_option('zs_domain_name');
			
			echo '<div class="zs_widget_link_container">';
			echo '<a href="https://'.$zs_domain_name.'.zeenshare.com/home?sid='.$zs_sid.'" target="_blanck" >My documents</a>';
			echo '</div>';
		}
		echo $after_widget;
	}

	function register(){
		register_sidebar_widget('Zeenshare_Widget', array('Zeenshare_Widget', 'widget'));
		register_widget_control('Zeenshare_Widget', array('Zeenshare_Widget', 'control'));
	}
}



?>
