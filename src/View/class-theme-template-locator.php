<?php

namespace Metis\View;

class Theme_Template_Locator implements Template_Locator_Interface {
	public function locate( array $templates ) {
		return locate_template( $templates );
	}
}
