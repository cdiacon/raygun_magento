<?php
class Cdl_Raygun_Model_Observer

{
	public function actionPredispatch( $observer ) {

		$helper = Mage::helper('cdl_raygun');
		set_error_handler(array($helper, 'customRaygunErrorHandler'));

	}
}