<?php
class WootablepressWtbp extends ModuleWtbp {
	public function init() {
		if (is_admin()) {
			add_action('admin_notices', array($this, 'showAdminErrors'));
		}
		DispatcherWtbp::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_shortcode(WTBP_SHORTCODE, array($this, 'render'));
	}
	public function addAdminTab( $tabs ) {
		$tabs[ $this->getCode() . '#wtbpadd' ] = array(
			'label' => esc_html__('Add New Table', 'woo-product-tables'), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-plus-circle', 'sort_order' => 10, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() . '_edit' ] = array(
			'label' => esc_html__('Edit', 'woo-product-tables'), 'callback' => array($this, 'getEditTabContent'), 'sort_order' => 20, 'child_of' => $this->getCode(), 'hidden' => 1, 'add_bread' => $this->getCode(),
		);
		$tabs[ $this->getCode() ] = array(
			'label' => esc_html__('Show All Tables', 'woo-product-tables'), 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-table', 'sort_order' => 20,
		);
		return $tabs;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getEditTabContent() {
		$id = ReqWtbp::getVar('id', 'get');
		return $this->getView()->getEditTabContent( $id );
	}
	public function getEditLink( $id, $tableTab = '' ) {
		$link = FrameWtbp::_()->getModule('options')->getTabUrl( $this->getCode() . '_edit' );
		$link .= '&id=' . $id;
		if (!empty($tableTab)) {
			$link .= '#' . $tableTab;
		}
		return $link;
	}
	public function render( $params ) {
		return $this->getView()->renderHtml($params);
	}
	public function showAdminErrors() {
		// check WooCommerce is installed and activated
		if (!$this->isWooCommercePluginActivated()) {
			// WooCommerce install url
			$wooCommerceInstallUrl = add_query_arg(
				array(
					's' => 'WooCommerce',
					'tab' => 'search',
					'type' => 'term',
				),
				admin_url( 'plugin-install.php' )
			);
			$tableView = $this->getView();
			/* translators: %s: module name */
			$error = sprintf(esc_html__('For work with "%s" plugin, You need to install and activate', 'woo-product-tables'), WTBP_WP_PLUGIN_NAME) .
				' <a target="_blank" href="' . esc_url($wooCommerceInstallUrl) . '">WooCommerce</a> ' . esc_html__('plugin', 'woo-product-tables');

			$tableView->assign('errorMsg', $error);
			// check current module
			if (isset($_GET['page']) && WTBP_SHORTCODE == $_GET['page']) {
				// show message
				HtmlWtbp::echoEscapedHtml($tableView->getContent('showAdminNotice'));
			}
		}
	}
	public function isWooCommercePluginActivated() {
		return class_exists('WooCommerce');
	}

	public function unserialize( $data, $isReplaceCallback = true ) {
		if ($isReplaceCallback) {
			$data = preg_replace_callback ( '!s:(\d+):"(.*?)";!', function( $match ) {
				return ( strlen($match[2]) == $match[1] ) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
			}, $data );
		}

		if ( @unserialize(base64_decode($data)) !== false ) {
			return unserialize(base64_decode($data));
		} else {
			return unserialize($data);
		}
	}
}
