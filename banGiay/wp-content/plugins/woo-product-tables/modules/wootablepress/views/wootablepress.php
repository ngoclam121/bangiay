<?php
class WootablepressViewWtbp extends ViewWtbp {
	public $orderColumns = array();
	public $columnNiceNames = array();
	public $loopProductType = '';

	/**
	 * Adnin view table preview mod current tab
	 *
	 * @var string
	 */
	public $prewiewTab = '';

	public function getTabContent() {
		FrameWtbp::_()->addStyle('wtbp.admin.css', $this->getModule()->getModPath() . 'css/admin.tables.css');
		FrameWtbp::_()->addScript('wtbp.admin.list.js', $this->getModule()->getModPath() . 'js/admin.list.js');
		FrameWtbp::_()->addJSVar('wtbp.admin.list.js', 'wtbpTblDataUrl', UriWtbp::mod('wootablepress', 'getListForTbl', array('reqType' => 'ajax')));
		FrameWtbp::_()->addJSVar('wtbp.admin.list.js', 'url', admin_url('admin-ajax.php'));
		FrameWtbp::_()->addScript('adminCreateTableWtbp', $this->getModule()->getModPath() . 'js/create-table.js');
		FrameWtbp::_()->addScript('wtbp.dataTables.js', $this->getModule()->getModPath() . 'js/dt/jquery.dataTables.min.js');
		FrameWtbp::_()->addStyle('wtbp.dataTables.css', $this->getModule()->getModPath() . 'css/dt/jquery.dataTables.min.css');
		FrameWtbp::_()->addScript('wtbp.buttons', $this->getModule()->getModPath() . 'js/dt/dataTables.buttons.min.js');

		FrameWtbp::_()->getModule('templates')->loadJqGrid();
		FrameWtbp::_()->getModule('templates')->loadFontAwesome();
		FrameWtbp::_()->getModule('templates')->loadBootstrap();

		$this->assign('addNewLink', FrameWtbp::_()->getModule('options')->getTabUrl('wootablepress#wtbpadd'));

		return parent::getContent('wootablepressAdmin');
	}

	public function getEditTabContent( $idIn ) {
		$isWooCommercePluginActivated = $this->getModule()->isWooCommercePluginActivated();
		if (!$isWooCommercePluginActivated) {
			return;
		}

		FrameWtbp::_()->getModule('templates')->loadBootstrap();
		FrameWtbp::_()->getModule('templates')->loadJqueryUi();
		FrameWtbp::_()->getModule('templates')->loadCodemirror();
		FrameWtbp::_()->getModule('templates')->loadSlimscroll();

		$this->loadAssets();

		FrameWtbp::_()->addScript('wtbp.admin.tables.js', $this->getModule()->getModPath() . 'js/tables.admin.js');
		FrameWtbp::_()->addStyle('wtbp.admin.tables.css', $this->getModule()->getModPath() . 'css/admin.tables.css');
		FrameWtbp::_()->addStyle('wtbp.frontend.tables.css', $this->getModule()->getModPath() . 'css/frontend.tables.css');
		FrameWtbp::_()->addScript('adminCreateTableWtbp', $this->getModule()->getModPath() . 'js/create-table.js');

		DispatcherWtbp::doAction('addScriptsContent', true);

		$idIn = isset($idIn) ? (int) $idIn : 0;
		$table = $this->getModel('wootablepress')->getById($idIn);
		$tableColumns = $this->getModel('columns')->getFullColumnList();
		$settings = $this->getModule()->unserialize($table['setting_data']);
		$link = FrameWtbp::_()->getModule('options')->getTabUrl( $this->getCode() );
		$languages = FrameWtbp::_()->getModule('wootablepress')->getModel('languages')->getLanguageBackend();

		$this->assign('languages', $languages);
		$this->assign('link', $link);
		$this->assign('settings', $settings);
		$this->assign('table', $table);
		$this->assign('table_columns', $tableColumns);
		$this->assign('authors_html', $this->getAuthorsHtml());
		$this->assign('categories_html', $this->getTaxonomyHierarchyHtml());
		$this->assign('products_has_variations_html', $this->getProductsWithVariationsHtml());
		$this->assign('tags_html', $this->getTaxonomyHierarchyHtml(0, '', 'product_tag'));
		$this->assign('attributes_html', $this->getAttributesHierarchy());
		$this->assign('search_table', $this->getLeerSearchTable());
		$this->assign('is_pro', FrameWtbp::_()->isPro());

		return parent::getContent('wootablepressEditAdmin');
	}

	public function renderHtml( $params ) {
		$isWooCommercePluginActivated = $this->getModule()->isWooCommercePluginActivated();
		if (!$isWooCommercePluginActivated) {
			return;
		}

		$this->loadAssets();

		FrameWtbp::_()->addScript('wtpb.frontend.tables.js', $this->getModule()->getModPath() . 'js/tables.frontend.js');
		FrameWtbp::_()->addStyle('wtpb.frontend.tables.css', $this->getModule()->getModPath() . 'css/frontend.tables.css');
		FrameWtbp::_()->addJSVar('wtpb.frontend.tables.js', 'url', admin_url('admin-ajax.php'));
		FrameWtbp::_()->addScript('wtpb-common-js', WTBP_JS_PATH . 'common.js', array(), false, true);
		FrameWtbp::_()->addScript('wtpb-lightbox-js', $this->getModule()->getModPath() . 'js/lightbox.js');
		FrameWtbp::_()->addStyle('wtpb-lightbox-css', $this->getModule()->getModPath() . 'css/lightbox.css');

		DispatcherWtbp::doAction('addScriptsContent', false);

		$id = isset($params['id']) ? (int) $params['id'] : 0;
		if (!$id) {
			return false;
		}
		$table = $this->getModel('wootablepress')->getById($id);
		$tableSettings = $this->getModule()->unserialize($table['setting_data']);
		$settings = $this->getTableSetting($tableSettings, 'settings', array());
		$html = $this->getProductContentFrontend($id, $tableSettings);
		$filter = DispatcherWtbp::applyFilters('getTableFilters', '', $id, $tableSettings);

		$tableSettings['settings']['order'] = json_encode($this->orderColumns);

		if (!empty($tableSettings['settings']['custom_js'])) {
			$tableSettings['settings']['custom_js'] = stripslashes(base64_decode($tableSettings['settings']['custom_js']));
		}

		$viewId = $id . '_' . mt_rand(0, 999999);
		$this->assign('tableId', $id);
		$this->assign('viewId', $viewId);
		$this->assign('html', $html);
		$this->assign('filter', $filter);
		$this->assign('settings', $tableSettings);
		$this->assign('custom_css', $this->getCustomCss($tableSettings, 'wtbp-table-' . $viewId));
		$this->assign('loader', $this->getLoaderHtml($tableSettings));


		return parent::getContent('wootablepressHtml');
	}

	public function loadAssets() {
		FrameWtbp::_()->addScript('wtbp.dataTables.js', $this->getModule()->getModPath() . 'js/dt/jquery.dataTables.min.js');
		FrameWtbp::_()->addScript('wtbp.buttons', $this->getModule()->getModPath() . 'js/dt/dataTables.buttons.min.js');
		FrameWtbp::_()->addScript('wtbp.colReorder', $this->getModule()->getModPath() . 'js/dt/dataTables.colReorder.min.js');
		FrameWtbp::_()->addScript('wtbp.fixedColumns', $this->getModule()->getModPath() . 'js/dt/dataTables.fixedColumns.min.js');
		FrameWtbp::_()->addScript('wtbp.print', $this->getModule()->getModPath() . 'js/dt/buttons.print.min.js');
		FrameWtbp::_()->addScript('wtbp.fixedHeader', $this->getModule()->getModPath() . 'js/dt/dataTables.fixedHeader.min.js');
		FrameWtbp::_()->addScript('wtbp.scroller', $this->getModule()->getModPath() . 'js/dt/dataTables.scroller.min.js');
		FrameWtbp::_()->addScript('wtbp.responsive', $this->getModule()->getModPath() . 'js/dt/dataTables.responsive.min.js');
		FrameWtbp::_()->addStyle('wtbp.responsive', $this->getModule()->getModPath() . 'css/dt/responsive.dataTables.min.css');
		FrameWtbp::_()->addStyle('wtbp.dataTables.css', $this->getModule()->getModPath() . 'css/dt/jquery.dataTables.min.css');
		FrameWtbp::_()->addStyle('wtbp.fixedHeader.css', $this->getModule()->getModPath() . 'css/dt/fixedHeader.dataTables.min.css');
		FrameWtbp::_()->addScript('wtbp.core.tables.js', $this->getModule()->getModPath() . 'js/core.tables.js');
		FrameWtbp::_()->addJSVar('wtbp.core.tables.js', 'url', admin_url('admin-ajax.php'));
		FrameWtbp::_()->addStyle('wtbp.loaders.css', $this->getModule()->getModPath() . 'css/loaders.css');
		FrameWtbp::_()->addScript('wtbp.notify.js', WTBP_JS_PATH . 'notify.js', array(), false, true);
		$options = FrameWtbp::_()->getModule('options')->getModel('options')->getAll();
		if (isset($options['accent_neutralise']) && isset($options['accent_neutralise']['value']) && !empty($options['accent_neutralise']['value'])) {
			FrameWtbp::_()->addScript('wtbp.removeAccents', $this->getModule()->getModPath() . 'js/dt/dataTables.removeAccents.min.js');
		}
		if (!empty($options['google_api_map_key']['value'])) {
			FrameWtbp::_()->addScript('wtbp.google.map', 'https://maps.googleapis.com/maps/api/js?key=' . esc_html($options['google_api_map_key']['value']) . '&callback=wtbpInitMap');
		}
	}

	public function getCustomCss( &$tableSettings, $viewId, $raw = true ) {
		if (isset($tableSettings['settings']['custom_css']) && !empty($tableSettings['settings']['custom_css'])) {
			$customCss = $raw ? base64_decode($tableSettings['settings']['custom_css']) : $tableSettings['settings']['custom_css'];
			unset($tableSettings['settings']['custom_css']);
		} else {
			$customCss = '';
		}
		return DispatcherWtbp::applyFilters('getCustomStyles', $customCss, $viewId, $tableSettings['settings']);
	}

	public function getLoaderHtml( $settings ) {
		$html = '';
		if (!$this->getTableSetting($settings['settings'], 'hide_table_loader', false)) {
			$html = '<div class="woobewoo-table-loader wtbpLogoLoader"></div>';
			$html = DispatcherWtbp::applyFilters('getLoaderHtml', $html, $settings['settings']);
			$html = '<div class="wtbpLoader">' . $html . '</div>';
		}
		return $html;
	}

	public function getSearchProductsFilters( $args, $params ) {
		$filterAuthor = isset($params['filter_author']) ? $params['filter_author'] : 0;
		$filterCategory = isset($params['filter_category']) ? $params['filter_category'] : 0;
		$filterTag = isset($params['filter_tag']) ? $params['filter_tag'] : 0;
		$filterAttribute = isset($params['filter_attribute']) ? $params['filter_attribute'] : 0;

		if (!empty($filterAuthor)) {
			$args['author'] = $filterAuthor;
		}

		if (!empty($filterCategory)) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'id',
				'terms'    => $filterCategory,
				'include_children' => true
			);
		}

		if (!empty($filterTag)) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_tag',
				'field'    => 'id',
				'terms'    => $filterTag,
				'include_children' => true
			);
		}

		if (!empty($filterAttribute)) {
			if ( empty(wc_get_attribute($filterAttribute)->slug) ) {
				$term = get_term( $filterAttribute );
				$taxonomy = $term->taxonomy;
				$args['tax_query'][] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => $filterAttribute,
					'operator' => 'IN'
				);
			} else {
				$term = get_term( $filterAttribute );
				$taxonomy = $term->taxonomy;
				$args['tax_query'][] = array(
					'taxonomy' => wc_get_attribute($filterAttribute)->slug,
					'operator' => 'EXISTS',
				);
			}
		}

		if (!empty($params['search']['value'])) {
			if (FrameWtbp::_()->isPro()) {
				global $wpdb;
				$sku = '%' . $wpdb->esc_like($params['search']['value']) . '%';
				$postIds = $wpdb->get_col( $wpdb->prepare("SELECT p.ID FROM $wpdb->posts as p INNER JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id
						   WHERE p.post_title LIKE %s OR p.post_content LIKE %s OR p.post_excerpt LIKE %s OR (pm.meta_key = '_sku' AND pm.meta_value LIKE %s)", $sku, $sku, $sku, $sku) );
				
				if (!empty($postIds)) {
					$args['post__in'] = $postIds;
				} else {
					$args['s'] = $params['search']['value'];
				}
			} else {
				$args['s'] = $params['search']['value'];
			}
		}
		if (isset($params['filter_private']) && 1 == $params['filter_private']) {
			$args['post_status'] = array('publish', 'private');
		}
		if (isset($params['show_variations']) && 1 == $params['show_variations']) {
			$args['post_type'] = array('product', 'product_variation');
			if (!empty($filterCategory)) {
				$parents = new WP_Query(array(
					'posts_per_page' => -1,
					'post_type'   => 'product',
					'suppress_filters' => true,
					'post_status' => array('publish'),
					'fields' => 'ids',
					'tax_query' => array(array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => $filterCategory,
						'include_children' => true
					))
				));
				$existsPost = $parents->have_posts();
				if (!empty($existsPost)) {
					$list = implode(',', $parents->posts);
					$args['suppress_filters'] = false;
					add_filter('posts_where', function ( $where, $query ) use( $filterCategory, $list ) {
						remove_filter( current_filter(), __FUNCTION__ );
						global $wpdb;
						$old = $wpdb->prefix . 'term_relationships.term_taxonomy_id IN (' . $filterCategory . ')';
						$new = '(' . $wpdb->prefix . 'term_relationships.term_taxonomy_id IN (' . $filterCategory . ') OR ' . $wpdb->prefix . 'posts.post_parent IN (' . $list . '))';
						$where = str_replace($old, $new, $where);
						return $where;
					}, 10, 2);
				}
			}
		}

		return $args;
	}

	public function getSearchProducts( $params ) {
		$dataArr = array();
		$args = array(
			'posts_per_page' => 10,
			'post_type'   => 'product',
			'order'       => 'DESC',
			'suppress_filters' => true,
			'post_status' => array('publish'),
			'offset' => !empty($params['start']) ? $params['start'] : '0'
		);
		$filterInTable = isset($params['filter_in_table']) ? $params['filter_in_table'] : '';
		$ids = isset($params['productids']) ? explode(',', $params['productids']) : array();
		if (count($ids) > 0 && !empty($filterInTable)) {
			$args['no' == $filterInTable ? 'post__not_in' : 'post__in'] = $ids;
		}
		$args = $this->getSearchProductsFilters($args, $params);

		if (!empty($params['order']['0']['column']) && !empty($params['order']['0']['dir'])) {
			switch ($params['order']['0']['column']) {
				//3 - title column
				case 3:
					$args['orderby'] = 'title';
					break;
				//6 - sku
				case 6:
					$args['meta_key'] = '_sku';
					$args['orderby'] = 'meta_value';
					break;
				//7 - stock column
				case 7:
					$args['meta_key'] = '_stock_status';
					$args['orderby'] = 'meta_value';
					break;
				//8 - price column
				case 8:
					$args['meta_key'] = '_price';
					$args['orderby'] = 'meta_value_num';
					break;
				//9 - date column
				case 9:
					$args['orderby'] = 'date';
					break;
			}
			$args['order'] = $params['order']['0']['dir'];
		}
		$stockNames = wc_get_product_stock_status_options();

		$products = new WP_Query( $args );

		$filterAttribute = isset($params['filter_attribute']) ? $params['filter_attribute'] : 0;
		$filterAttributeExactly = isset($params['filter_attribute_exactly']) ? $params['filter_attribute_exactly'] : '';

		if (empty($filterAttribute)) {
			$filterAttributeExactly = '';
		} else {
			$slug = wc_get_attribute($filterAttribute)->slug;
			if (empty($slug) ) {
				$term = get_term( $filterAttribute );
				$attributeSlug = $term->taxonomy;
			} else {
				$attributeExactlyParent = true;
			}
		}

		$filtered = false;

		foreach ($products->posts as $product) {
			$id = $product->ID;
			$thumbnailSrc = get_the_post_thumbnail($id, array(50,50));
			$continue = true;
			$_product = wc_get_product($id);
			if ( !empty($filterAttributeExactly) ) {
				$continue = true;
				$attributesList = $_product->get_attributes();
				foreach ($attributesList as $attribute) {
					if ( ( $attribute['name'] == $attributeSlug ) && ( count($attribute['options']) > 1 ) ) {
						$continue = false;
					}
				}
				if ( ( !$continue ) || ( !empty($attributeExactlyParent) && $attributeExactlyParent && count($attributesList) > 1 ) ) {
					$filtered = true;
					continue;
				}
			}

			$attributes = '';
			$attributesList2 = $_product->get_attributes();
			foreach ($attributesList2 as $attribute) {
				if (!empty($attribute['id'])) {
					$attr = wc_get_attribute($attribute['id']);
					$title = is_null($attr) ? $attribute['name'] : $attr->name;
					$terms = is_null($attr) ? $attribute->get_options() : wc_get_product_terms($id, $attribute['name'], array('fields' => 'names'));
					if (is_array($terms) && count($terms) > 0) {
						$title .= ' : ';
						foreach ($terms as $key => $term) {
							$title .= $term;
							if (!empty($terms[$key + 1])) {
								$title .= ', ';
							}
						}
						$attributes .= $title;
						$attributes .= '<br>';
					}
				}
			}

			$price = $_product->get_price_html();
			$date = $product->post_date;
			if ('product_variation' == $_product->post_type) {
				$existVariations = true;
				$parentId = $_product->get_parent_id();
				if (!isset($parents[$parentId])) {
					$parents[$parentId] = array(
						'thumbnail' => get_the_post_thumbnail($parentId, array(50, 50)),
						'categories' => get_the_term_list($parentId, 'product_cat', '', ', ', '')
					);
				}
				if (empty($thumbnailSrc)) {
					$thumbnailSrc = $parents[$parentId]['thumbnail'];
				}
				$categories = $parents[$parentId]['categories'];
				$variation = implode(', ', $_product->get_attributes());
			} else {
				$categories = get_the_term_list($id, 'product_cat', '', ', ', '');
				$variation = '';
			}
			$categories = is_admin() ? str_ireplace('<a', '<a target="_blank"', $categories) : $categories;

			$dataArr[] = array(
				'id' => $id,
				'in_table' => in_array($id, $ids),
				'product_title' => $product->post_title,
				'thumbnail' => $thumbnailSrc,
				'categories' => $categories,
				'sku' => $_product->get_sku(),
				'stock' => $stockNames[$_product->get_stock_status()],
				'price' => $price,
				'date' => $date,
				'variation' => $variation,
				'attributes' => $attributes,
			);
		}

		$filtered = $filtered ? count($dataArr) : $products->found_posts;

		$data = $this->generateTableSearchData($dataArr);
		$return = array(
			'draw' => 0,
			'recordsTotal' => $products->found_posts,
			'recordsFiltered' => $filtered,
			'data' => $data
		);
		return $return;
	}

	public function setOrderColumns( $orders, $backend = true ) {
		$columns = array();

		if (!$backend) {
			//If we use stripslashes, then we remove the special characters of the encoding - as a result,
			//sometimes on frontend we get incorrect text for json_decode (and as a result, an incorrect column name send to frontend data-settings) if we use Cyrillic or diacritical symbols,
			//therefore we need to make json_decode without stripslashes and take the correct column name from there
			$ordersPrepare = json_decode($orders, true);
			$nameList = array();
			if (!empty($ordersPrepare)) {
				foreach ($ordersPrepare as $key => $ord) {
					$nameList[$key]['display_name'] = !empty($ord['display_name']) ? $ord['display_name'] : '';
					$nameList[$key]['original_name'] = !empty($ord['original_name']) ? $ord['original_name'] : '';
				};
			}
		}

		if (false !== $orders && !empty($orders)) {
			$orders = json_decode(stripslashes($orders), true);
			$enabledColumns = $this->getModel('columns')->enabledColumns;
			foreach ($orders as $key => $column) {
				$fullSlug = $column['slug'];
				$subDelim = strpos($fullSlug, '-');
				if (!$backend) {
					//Insert the correct column name
					$column['display_name'] = $nameList[$key]['display_name'];
					$column['original_name'] = $nameList[$key]['original_name'];
				}
				if ($subDelim > 0) {
					$column['main_slug'] = substr($fullSlug, 0, $subDelim);
					$sub_slug = substr($fullSlug, $subDelim + 1);
					$column['sub_slug'] = 'attribute' == $column['main_slug'] ? wc_attribute_taxonomy_name_by_id((int) $sub_slug) : $sub_slug;
				} else {
					$column['main_slug'] = $fullSlug;
				}
				if (in_array($column['main_slug'], $enabledColumns)) {
					$columns[] = $column;
				}
			}
		}
		$this->orderColumns = $columns;
	}

	public function addHiddenFilterQuery( $query ) {
		$hidden_term = get_term_by('name', 'exclude-from-catalog', 'product_visibility');
		if ($hidden_term) {
			$query[] = array(
				'taxonomy' => 'product_visibility',
				'field' => 'term_taxonomy_id',
				'terms' => array($hidden_term->term_taxonomy_id),
				'operator' => 'NOT IN'
			);
		}
		return $query;
	}

	public function getProductContentFrontend( $id, $tableSettings ) {
		if (empty($id)) {
			return false;
		}

		$settings = $this->getTableSetting($tableSettings, 'settings', array());

		$order = DispatcherWtbp::applyFilters('addHiddenColumns', $this->getTableSetting($settings, 'order', false), $settings);
		$this->setOrderColumns($order, false);

		$dataArr = array();
		if (!( FrameWtbp::_()->isPro() && $this->getTableSetting($settings, 'pagination_ssp', false) )) {
			if ($this->getTableSetting($settings, 'auto_categories_enable', false) && $this->getTableSetting($settings, 'auto_categories_list', '') == 'all') {
				$productIds = false;
			} else {
				$productIds = $this->getTableSetting($settings, 'productids', false);
				$productIds = explode(',', $productIds);
				if (!empty($productIds) && !is_array($productIds)) {
					$productIds = array($productIds);
				}
			}
			$dataArr = $this->getProductContent(array('in' => $productIds, 'not' => false), $tableSettings, true);

			wp_reset_postdata();
		}
		$html = $this->generateTableHtml($dataArr, true, $settings);

		return $html;
	}

	public function getProductPage( $params ) {
		if (empty($params['id'])) {
			return false;
		}
		$tableId = $params['id'];
		$frontend = empty($params['admin']);

		$settings = false;
		if (!empty($params['settings'])) {
			parse_str($params['settings'], $settings);
			unset($params['settings']);
		}
		if (false == $settings) {
			$table = $this->getModel('wootablepress')->getById($tableId);
			$tableSettings = $this->getModule()->unserialize($table['setting_data']);
			$settings = $this->getTableSetting($tableSettings, 'settings', array());
		} else {
			$tableSettings = $settings;
		}
		$settings = $this->getTableSetting($tableSettings, 'settings', array());

		$order = isset($params['orders']) ? $params['orders'] : $this->getTableSetting($settings, 'order', false);
		$this->setOrderColumns($order, true);
		if (!$frontend) {
			$orders = $this->orderColumns;
			if (!empty($params['sortCol']) && !empty($params['order']['0']['dir'])) {
				$slug = $params['sortCol'];
				foreach ($orders as $column) {
					if ($column['slug'] == $slug) {
						$params['sortCol'] = $column;
						break;
					}
				}
			}
		}
		if ($this->getTableSetting($settings, 'auto_categories_enable', false) && $this->getTableSetting($settings, 'auto_categories_list', '') == 'all') {
			$productIds = false;
		} else {
			$productIds = isset($params['productids']) ? $params['productids'] : $this->getTableSetting($settings, 'productids', false);
			$productIds = explode(',', $productIds);
			if (!empty($productIds) && !is_array($productIds)) {
				$productIds = array($productIds);
			}
		}
		if (isset($params['sortCustom'])) {
			$tableSettings['settings']['sorting_custom'] = $params['sortCustom'];
		}

		$dataArr = $this->getProductContent(array('in' => $productIds, 'not' => false), $tableSettings, $frontend, $params);

		$html = $this->generateTableHtml($dataArr, $frontend, $settings, false);

		$result = array('html' => $html, 'total' => $params['total'], 'filtered' => $params['filtered']);
		if (isset($params['idsExist'])) {
			$result['ids'] = $params['idsExist'];
		}

		return $result;
	}

	public function calcProductIds( $params, $getList = false ) {
		$productIdsExits = !empty($params['productIdExist']) ? $params['productIdExist'] : array();
		if (is_string($productIdsExits)) {
			$productIdsExits = explode(',', $productIdsExits);
		}
		$productIdsSelected = !empty($params['productIdSelected']) ? $params['productIdSelected'] : array();
		$productIdExcluded = !empty($params['productIdExcluded']) ? $params['productIdExcluded'] : array();
		$productFilters = !empty($params['filters']) ? $params['filters'] : array();

		$filter = $this->getSearchProductsFilters(array(), $productFilters);
		$isAll = 'all' == $productIdsSelected;

		$productIds = array();
		if (count($filter) > 0) {
			$args = array_merge(array(
				'post_type' => 'product',
				'ignore_sticky_posts' => true,
				'post_status' => array('publish'),
				'posts_per_page' => -1
			), $filter);

			if ($isAll) {
				if (count($productIdExcluded) > 0) {
					$args['post__not_in'] = $productIdExcluded;
				}
			} else {
				$args['post__in'] = $productIdsSelected;
			}
			$postExist = new WP_Query($args);

			$products = $productIdsExits;
			foreach ($postExist->posts as $post) {
				$products[] = $post->ID;
			}
			$productIds = array('in' => array_unique($products), 'not' => false);
		} else {
			if ($isAll) {
				$filtered = array_filter($productIdExcluded,
					function ( $value ) use ( $productIdsExits ) {
						return !in_array($value, $productIdsExits);
					}
				);
				$productIds = array('in' => false, 'not' => $filtered);
			} else {
				$productIdsExits = DispatcherWtbp::applyFilters('filterProductIds', $productIdsExits, $params);
				$productIds = array('in' => array_unique(array_merge($productIdsExits, $productIdsSelected)), 'not' => false);
			}
		}
		if ($getList) {
			if (false == $productIds['not']) {
				$ids = $productIds['in'];
			} else {
				$args = array(
					'post_type' => 'product',
					'ignore_sticky_posts' => true,
					'post_status' => array('publish'),
					'posts_per_page' => -1,
					'post__not_in' => $productIds['not'],
					'fields' => 'ids',
				);

				if (is_array($productIds['in'])) {
					$args['post__in'] = $productIds['in'];
				}
				$result = new WP_Query($args);
				$ids = $result->posts;
			}
			wp_reset_postdata();
			return is_array($ids) ? implode(',', $ids) : '';
		}
		return $productIds;
	}

	public function getProductContentBackend( $params, $preview = false, $settings = false) {
		$this->prewiewTab = !empty($params['prewiewTab']) ? $params['prewiewTab'] : '';
		$productIds = $this->calcProductIds($params);
		if (empty($params['tableid']) || empty($productIds) ) {
			return false;
		}
		$tableId = $params['tableid'];

		if (false == $settings) {
			$table = $this->getModel('wootablepress')->getById($tableId);
			$tableSettings = $this->getModule()->unserialize($table['setting_data']);
		} else {
			$settings['settings']['productids'] = $productIds['in'];
			$tableSettings = $settings;
		}
		$tableSettings['start'] = isset($params['start']) ? $params['start'] : 0;
		$tableSettings['length'] = isset($params['length']) ? $params['length'] : -1;
		$returnIds = isset($params['returnIds']) && 1 == $params['returnIds'];

		$settings = $this->getTableSetting($tableSettings, 'settings', array());
		$order = isset($params['order']) ? $params['order'] : false;
		if ($preview) {
			$order = DispatcherWtbp::applyFilters('addHiddenColumns', $order, $settings);
		}
		$this->setOrderColumns($order, true);
		$tableSettings['settings']['order'] = json_encode($this->orderColumns);

		if (isset($params['sortCustom'])) {
			$tableSettings['settings']['sorting_custom'] = $params['sortCustom'];
		}

		if (!$preview || !( FrameWtbp::_()->isPro() && $this->getTableSetting($settings, 'pagination_ssp', false) )) {
			if ($this->getTableSetting($settings, 'auto_categories_enable', false) && $this->getTableSetting($settings, 'auto_categories_list', '') == 'all') {
				$productIds = array('in' => false, 'not' => false);
			}
			$dataArr = $this->getProductContent($productIds, $tableSettings, $preview);
		} else {
			$dataArr = array();
		}

		$total = isset($dataArr['total']) ? $dataArr['total'] : 0;
		$idsExist = isset($dataArr['idsExist']) && !empty($dataArr['idsExist']) ? $dataArr['idsExist'] : '';
		unset($dataArr['total']);
		unset($dataArr['idsExist']);
		$html = $this->generateTableHtml($dataArr, $preview, $settings);

		$return = array();
		$return['html'] = $html;
		$return['filter'] = DispatcherWtbp::applyFilters('getTableFilters', '', $tableId, $tableSettings);
		$return['settings'] = $tableSettings;
		$return['css'] = $preview ? $this->getCustomCss($tableSettings, 'wtbpPreviewTable', false) : '';
		if ($returnIds) {
			$return['ids'] = $idsExist;
		}
		$return['total'] = $total;

		if ($total > 1000 && !$this->getTableSetting($settings, 'pagination_ssp', false)) {
			$return['notices'][] = '<div class="error notice">' . esc_html__('You need to enable pagination and server-side processing options or reduce the number of selected products, because the products table may not work at the front!', 'woo-product-tables') . '</div>';
		}

		return $return;
	}

	/**
	 * Get product thumbnail link
	 *
	 * @param int $id
	 * @param string $imgSize
	 * @param string $add
	 *
	 * @return string
	 */
	public function getProductThumbnailLink( $id, $imgSize, $add = 'class="wtbpMainImage"', $useProductLink = false ) {
		$link = '';
		$postThumbnailId = get_post_thumbnail_id($id);
		if ($postThumbnailId) {
			$link = $this->getThumbnailLinkHtml($id, $postThumbnailId, $imgSize, $add, $useProductLink);
		}
		return $link;
	}

	/**
	 * Get product second thumbnail with link wrapper from gallery first image
	 *
	 * @param object $_product
	 * @param string $imgSize
	 * @param string $add
	 *
	 * @return string
	 */
	public function getProductSecondThumbnailLink( $product, $imgSize, $add = 'class="wtbpMainImage"', $useProductLink = false ) {
		$link = '';

		$gallaryFirstImgId = $this->getProductFirstGalleryImageId($product);

		if ($gallaryFirstImgId) {
			$link = $this->getThumbnailLinkHtml($product->get_id(), $gallaryFirstImgId, $imgSize, $add, $useProductLink);
		}

		return $link;
	}

	/**
	 * Get product second thumbnail with link wrapper from gallery first image
	 *
	 * @param object $_product
	 * @param string $imgSize
	 * @param string $mobileStyles
	 *
	 * @return string
	 */
	public function getProductSecondThumbnail( $product, $imgSize, $mobileStyles ) {
		$thumbnail = '';

		$gallaryFirstImgId = $this->getProductFirstGalleryImageId($product);

		if ($gallaryFirstImgId) {
			$thumbnail = wp_get_attachment_image($gallaryFirstImgId, $imgSize, false, $mobileStyles);
		}

		return $thumbnail;
	}

	/**
	 * Get gellary first image id
	 *
	 * @param object $product
	 *
	 * @return int|false
	 */
	public function getProductFirstGalleryImageId( $product ) {
		$firstImageId = false;
		$gallaryIds = $product->get_gallery_image_ids();

		if (!empty($gallaryIds[0])) {
			$firstImageId = $gallaryIds[0];
		}

		return $firstImageId;
	}

	/**
	 * Get thumbnail link html
	 *
	 * @param int $postId
	 * @param int $imgId
	 * @param string $imgSize
	 * @param string $add
	 *
	 * @return string
	 */
	public function getThumbnailLinkHtml( $postId, $imgId, $imgSize, $add, $useProductLink = false ) {
		$link = '';

		$postImg = wp_get_attachment_image($imgId, $imgSize);
		if ($useProductLink) {
			$postImgSrc = array(
				get_permalink($postId)
			);
			$dataLightbox = '';
		} else {
			$postImgSrc = wp_get_attachment_image_src($imgId, 'full');
			$dataLightbox = 'data-lightbox="' . esc_attr($postId) . '" ';
		}

		if (!empty($postImg) && !empty($postImgSrc[0])) {
			$link = '<a href="' . esc_url($postImgSrc[0]) . '" ' . $dataLightbox . $add . '>' . $postImg . '</a>';
		}

		return $link;
	}

	public function getProductContent( $productIds, $tableSettings, $preview = true, &$page = array() ) {
		set_time_limit(300);
		$frontend = !is_admin() || $preview;
		$orders = $this->orderColumns;
		$settings = isset($tableSettings['settings']) ? $tableSettings['settings'] : array();
		$isPage = !empty($page);

		$postStatuses = array('publish');
		if ($this->getTableSetting($settings, 'show_private', false) || !$frontend) {
			$postStatuses[] = 'private';
		}
		$postTypes = array('product');
		if ( false !== $productIds['in'] || false !== $productIds['not'] ) {
			$postTypes[] = 'product_variation';
		}
		$args = array(
			'post_type' => $postTypes,
			'ignore_sticky_posts' => true,
			'post_status' => $postStatuses,
			'posts_per_page' => -1,
			'tax_query' => array()
		);

		if (!empty($settings['hide_out_of_stock'])) {
			$args['meta_query'][] = array(
				'key'     => '_stock_status',
				'value'   => 'outofstock',
				'compare' => 'NOT LIKE'
			);
		}
		if (is_array($productIds['in'])) {
			$args['post__in'] = $productIds['in'];
		} else if (is_array($productIds['not'])) {
			$args['post__not_in'] = $productIds['not'];
			$args['post_type'] = 'product';
			$args['post_status'] = 'publish';
		}

		if (!empty($settings['sorting_custom'])) {
			if (empty($settings['pre_sorting'])) {
				$args['orderby'] = 'post__in';
			} else {
				$desc = empty($settings['pre_sorting_desc']) ? 'ASC' : 'DESC';
				switch ($settings['pre_sorting']) {
					case 'title':
						$args['orderby'] = 'title';
						$args['order'] = $desc;
						break;
					case 'rand':
						$args['orderby'] = 'rand';
						break;
					case 'date':
						$args['orderby'] = 'date ID';
						$args['order'] = $desc;
						break;
					case 'price':
						$args['meta_key'] = '_price';
						$args['orderby'] = 'meta_value_num';
						$args['order'] = $desc;
						break;
					case 'popularity':
						$args['meta_key'] = 'total_sales';
						$args['orderby'] = 'meta_value_num';
						$args['order'] = $desc;
						break;
					case 'rating':
						$args['meta_key'] = '_wc_average_rating';
						$args['orderby']  = array(
							'meta_value_num' => $desc,
							'ID'             => 'ASC',
						);
						break;
					case 'menu_order':
						$args['orderby'] = 'menu_order';
						$args['order']   = $desc;
						break;
				}
			}
		}

		$multyAddToCart = $this->getTableSetting($settings, 'multiple_add_cart', false);
		$multyAddPosition = $this->getTableSetting($settings, 'multiple_add_cart_position', 'first');
		$showVarImages = $this->getTableSetting($settings, 'show_variation_image', false);
		$isPro = FrameWtbp::_()->isPro();

		if (!$isPage && !$frontend) {
			$args['fields'] = 'ids';
			$idsExist = new WP_Query($args);
			$idsTmp = !empty($idsExist->posts) ? implode(',', $idsExist->posts) : '';
		}
		
		if ($isPro) {
			$args = DispatcherWtbp::applyFilters('setLazyLoadQueryFilters', $args, $settings, $page);
		}

		if ($isPage) {
			if ($isPro) {
				$args = DispatcherWtbp::applyFilters('setSSPQueryFilters', $settings, $args, $page);
			} else {
				$args = $this->setAdminSSPQueryFilters($settings, $args, $page);
			}
			if (!empty($page['returnIds'])) {
				$l = $args['posts_per_page'];
				$o = $args['offset'];
				unset($args['posts_per_page'], $args['offset']);
				$args['fields'] = 'ids';
				$args['posts_per_page'] = -1;
				$args['offset'] = 0;
				$idsExist = new WP_Query($args);
				$page['idsExist'] = !empty($idsExist->posts) ? implode(',', $idsExist->posts) : '';
				unset($idsExist);
				$args['posts_per_page'] = $l;
				$args['offset'] = $o;
			}
		} elseif (!$frontend && isset($tableSettings['start']) && isset($tableSettings['length'])) {
			$args['posts_per_page'] = $tableSettings['length'];
			$args['offset'] = $tableSettings['start'];
		}
		$args['fields'] = 'all';

		$dataExist = new WP_Query($args);
		DispatcherWtbp::doAction('removeSSPQueryFilters');
		$postExist = $dataExist->posts;
		$imgSize = !empty($settings['thumbnail_size']) ? $settings['thumbnail_size'] : 'thumbnail';

		if ($frontend) {
			if ('set_size' == $imgSize) {
				$imgSize = array(
					( !empty($settings['thumbnail_width']) ? $settings['thumbnail_width'] : 0 ),
					( !empty($settings['thumbnail_height']) ? $settings['thumbnail_height'] : 0 )
				);
			}
			DispatcherWtbp::applyFilters('customizeCartButton', $settings);
		}

		$hideQuantityInput = !empty($settings['hide_quantity_input']) ? $settings['hide_quantity_input'] : false;

		$stockNames = wc_get_product_stock_status_options();
		$dataArr = array();
		if (!$isPage && !$frontend) {
			$dataArr['total'] = $dataExist->found_posts;
			$dataArr['idsExist'] = $idsTmp;
		}

		foreach ($orders as $i => $column) {
			switch ($column['main_slug']) {
				case 'thumbnail':
					$mobileStyles = '';
					if (isset($settings['responsive_mode']) && 'disable' !== $settings['responsive_mode']) {
						if ( wp_is_mobile() || ! empty( $this->prewiewTab ) && 'mobile' == $this->prewiewTab ) {
							$mobileThubmnailWidth = $this->getTableSetting($column, 'mobile_thumbnail_size_width', 0);
							$mobileThubmnailHeight = $this->getTableSetting($column, 'mobile_thumbnail_size_height', 0);

							if (!empty($mobileThubmnailWidth) || !empty($mobileThubmnailHeight)) {
								if (empty($mobileThubmnailWidth)) {
									$mobileThubmnailWidth = $mobileThubmnailHeight;
								} else if (empty($mobileThubmnailHeight)) {
									$mobileThubmnailHeight = $mobileThubmnailWidth;
								}

								$mobileStyles = array('class' => esc_attr('attachment-' . $mobileThubmnailWidth . 'x' . $mobileThubmnailHeight));
								$imgSize = array(
									$mobileThubmnailWidth,
									$mobileThubmnailHeight
								);
							}
						}
					}

					if ( $isPro ) {
						$thumbnailCartButton = $this->getTableSetting($column, 'add_cart_button', false);
						$secondthumbnailActive = $this->getTableSetting($column, 'display_secont_thumbnail', false);
						$useProductLink = $this->getTableSetting($column, 'use_product_link', false);
					}
					break;
				case 'product_title':
					$prodTitleLink = !isset($column['product_title_link']) || !empty($column['product_title_link']);
					$prodTitleLinkBlank = $this->getTableSetting($column, 'product_title_link_blank', false);
					$prodTitleQuickView = $prodTitleLink && $isPro && $this->getTableSetting($column, 'product_title_link_to', '') == 'quick';
					$stripTitle = isset($column['cut_product_title_text']) ? $column['cut_product_title_text'] : true;
					$stripTitleSize = !empty($column['cut_product_title_text_size']) ? $column['cut_product_title_text_size'] : 100;
					break;
				case 'categories':
					$prodCategoryLink = !isset($column['product_category_link']) || !empty($column['product_category_link']);
					$prodCategoryLinkBlank = $this->getTableSetting($column, 'product_category_link_blank', false);
					$categoriesSeparator = $this->getTableSetting($column, 'product_category_new_line', false) ? '<br />' : ', ';
					
					$prodCategoryExclude = $frontend && $isPro && !$isPage ? $this->getTableSetting($column, 'product_category_exclude', false) : false;
					if (!empty($prodCategoryExclude)) {
						$prodCategoryExcludeList = explode(',', $prodCategoryExclude);
						$prodCategoryExclude = empty(!$prodCategoryExcludeList);
					}
					break;
				case 'product_link':
					$prodLinkUserString = esc_html(isset($column['product_link_text']) ? $column['product_link_text'] : 'More');
					break;
				case 'stock':
					$stockMaxQuantity = $isPro ? $this->getTableSetting($column, 'stock_max_quantity', false, true) : false;
					$showIcons = $this->getTableSetting($column, 'stock_show_icons', false);
					$showText = esc_html($this->getTableSetting($column, 'stock_show_text', false));
					$showQuantity = $this->getTableSetting($column, 'stock_item_counts', false);
					$showVariationQuantity = $isPro && $this->getTableSetting($column, 'stock_item_variation_counts', false);
					$showVariationQuantityAttrNames = $isPro && $this->getTableSetting($column, 'stock_item_variation_attr_names', false);
					if ($showQuantity || $showVariationQuantity) {
						$stockQuantityText = esc_html($this->getTableSetting($settings, 'stock_quantity_text', false));
					}
					if ($showIcons) {
						$stockIcons = array('instock' => 'smile-o', 'outofstock' => 'frown-o', 'onbackorder' => 'meh-o');
					}
					if (!$showIcons && !$showQuantity && !$showVariationQuantity) {
						$showText = true;
					}
					break;
				case 'description':
					$stripDescription = isset($column['cut_description_text']) ? $column['cut_description_text'] : true;
					$stripDescriptionSize = !empty($column['cut_description_text_size']) ? $column['cut_description_text_size'] : 100;
					$displayDescriptionPopup = $isPro ? $this->getTableSetting($column, 'description_popup', false) : false;
					break;
				case 'short_description':
					$stripDescriptionShort = isset($column['cut_short_description_text']) ? $column['cut_short_description_text'] : false;
					$stripSizeShort = !empty($column['cut_short_description_text_size']) ? $column['cut_short_description_text_size'] : 100;
					$displayShortDescriptionPopup = $isPro ? $this->getTableSetting($column, 'short_description_popup', false) : false;
					$isDoShortcodes = $this->getTableSetting($column, 'is_do_shortcodes', false) ? true : false;
					break;
				case 'downloads':
					$prodDownloadsLinkBlank = $this->getTableSetting($column, 'product_downloads_link_blank', false);
					break;
				case 'add_to_cart':
					$hideVariationAttr = $this->getTableSetting($column, 'add_to_cart_hide_variation_attribute', false);
					break;
				case 'sku':
					$changeSkuForVariation = $isPro && $this->getTableSetting($column, 'change_sku_for_variation', false) ? true : false;
					break;
				default:
			}
		}
		$isFilterPrices = $frontend && $isPro && $this->getTableSetting($settings, 'filter_price', false);
		$isFilterCatChildren = $frontend && $isPro && !$isPage && $this->getTableSetting($settings, 'filter_category', false) && $this->getTableSetting($settings, 'filter_category_children', false);
		if ($isFilterCatChildren) {
			$catParents = array();
			$catNames = get_terms('product_cat', array('fields' => 'id=>name'));
		}

		$dateFormat = $this->getTableSetting($settings, 'date_formats', 'Y-d-m');
		$varPriceColumn = $this->getTableSetting($settings, 'var_price_column', false);
		if ($varPriceColumn) {
			$priceFound = false;
			foreach ($orders as $column) {
				if ('price' == $column['main_slug']) {
					$priceFound = true;
					break;
				}
			}
			if (!$priceFound) {
				$varPriceColumn = false;
			}
		}

		$parents = array();
		$taxonomies = array();
		$existMB = function_exists('mb_strimwidth');

		foreach ($postExist as $post) {
			$id = $post->ID;
			$postTitle = $post->post_title;
			$postContent = $post->post_content;
			$postDate = $post->post_date;
			$post = null;
			$_product = wc_get_product($id);
			$productType = $_product->get_type();
			$isVariable = 'variable' == $productType || 'variable-subscription' == $productType;
			$this->loopProductType = $productType;
			$isVariation = 'product_variation' == $_product->post_type;
			$parentId = $isVariation ? $_product->get_parent_id() : 0;
			if (!empty($parentId)) {
				$parents[$parentId] = array();
			}
			$mainId = empty($parentId) ? $id : $parentId;

			$sku = $_product->get_sku();
			$data = array('id' => $id);
			foreach ($orders as $column) {
				switch ($column['main_slug']) {
					case 'thumbnail':
						$useProductLink = $isPro && $useProductLink;
						$value = $frontend ? $this->getProductThumbnailLink($id, $imgSize, 'class="wtbpMainImage"', $useProductLink) : get_the_post_thumbnail($id, 'thumbnail', $mobileStyles);
						
						// case when variation does not has image than replace with product parent image
						if ($isVariation && empty($value)) {
							$value = $frontend ? $this->getProductThumbnailLink($parentId, $imgSize, 'class="wtbpMainImage"', $useProductLink) : get_the_post_thumbnail($parentId, 'thumbnail', $mobileStyles);
							$parent_image_active = true;
						}

						if ( $isPro ) {
							if ( $secondthumbnailActive ) {
								// case when variation does not has image than add second image to parent product image
								if ($isVariation && !empty($parent_image_active) ) {
									$paretn_product = wc_get_product($parentId);
									$value .= $frontend ? $this->getProductSecondThumbnailLink($paretn_product, $imgSize, 'class="wtbpMainImage"', $useProductLink) : $this->getProductSecondThumbnail($paretn_product, 'thumbnail', $mobileStyles);
									// all cases exept variation with image
								} else {
									$value .= $frontend ? $this->getProductSecondThumbnailLink($_product, $imgSize, 'class="wtbpMainImage"', $useProductLink) : $this->getProductSecondThumbnail($_product, 'thumbnail', $mobileStyles);
								}
								$value = '<div class="wtbpTableThumbnailWrapper">' . $value . '</div>';
							}


							if ($thumbnailCartButton) {
								if ($_product->get_stock_status() == 'outofstock') {
									if ($frontend && $multyAddToCart) {
										$data['check_multy'] = '';
									}
									$value .= '<div class="wtbpOutOfStockCart">' . $stockNames['outofstock'] . '</div>';
								} else {
									if ($multyAddToCart) {
										$data['check_multy'] = '<input type="checkbox" class="wtbpAddMulty" value="' . esc_attr($id) . '" data-quantity="1" data-variation_id="0" data-position="' . esc_attr($multyAddPosition) . '">';
									}
									$addToCartButton = do_shortcode('[add_to_cart id="' . $id . '" class="" style="" show_price="false" sku ="' . $sku . '"]');
									$value .= '<div class="wtbpAddToCartWrapper">' . $addToCartButton . '</div>';
								}
							}
						}

						$data['thumbnail'] = $value;
						break;
					case 'product_title':
						if ($stripTitle) {
							$postTitle = strip_tags($existMB ? mb_strimwidth($postTitle, 0, $stripTitleSize, '...') : $this->truncateWordwrap($postTitle, $stripTitleSize));
						}
						if ($prodTitleLink) {
							if ($prodTitleQuickView) {
								$data['product_title'] = '<a href="#" class="yith-wcqv-button" data-product_id="' . $id . '">' . $postTitle . '</a>';
							} else {
								$url = get_permalink($id);
								$data['product_title'] = '<a href="' . esc_url($url) . '"' . ( !$frontend || $prodTitleLinkBlank ? ' target="_blank"' : '' ) . '>' . esc_html($postTitle) . '</a>';
							}
						} else {
							$data['product_title'] = $postTitle;
						}
						break;
					case 'featured':
						$featured = '';
						if ($_product->get_featured()) {
							$showAs = $isPro ? $this->getTableSetting($column, 'featured_show_as', 'text') : 'text';
							if ('icon' == $showAs) {
								$featured = '<i class="fa fa-fw fa-star"></i>';
							} else if ('image' == $showAs) {
								$featured = '<img class="wtbpFeaturedImage" src="' . esc_url($this->getTableSetting($column, 'featured_image_path', WTBP_IMG_PATH . 'default.png')) . '">';
							} else {
								$featured = esc_html__('Featured', 'woocommerce');
							}
						}
						$data['featured'] = $featured;
						break;
					case 'sku':
						$variations = $isVariable && $changeSkuForVariation ? $_product->get_available_variations() : array();
						if (!empty($variations)) {
							$sku = '<span data-default>' . $sku . '</span>';
							foreach ($variations as $variationIterator => $variation) {
								$variationObj = new WC_Product_variation($variation['variation_id']);
								$sku .= '<span class="wtbpHidden" data-variation-id="' . esc_attr($variation['variation_id']) . '">' . esc_attr($variationObj->get_sku()) . '</span>';
							}
						}
						$data['sku'] = $sku;
						break;
					case 'categories':
						$terms = false;
						if ($prodCategoryLink && !$prodCategoryExclude) {
							$categories = get_the_term_list($mainId, 'product_cat', '', $categoriesSeparator, '');
							if (!$frontend || $prodCategoryLinkBlank) {
								$categories = str_ireplace('<a', '<a target="_blank"', $categories);
							}
						} else {
							$terms = get_the_terms($mainId, 'product_cat');
							$categories = '';
							if (!empty($terms)) {
								$first = true;
								foreach ($terms as $term) {
									if ( $prodCategoryExclude && in_array($term->term_id, $prodCategoryExcludeList) ) {
										continue;
									}
									if ($first) {
										$first = false;
									} else {
										$categories .= $categoriesSeparator;
									}
									$categories .= $prodCategoryLink ? '<a href="' . get_category_link($term->term_id) . '">' . $term->name . '</a>' : $term->name;
								}
							}
						}
						if ( $prodCategoryLink && ( !$frontend || $prodCategoryLinkBlank ) ) {
							$categories = str_ireplace('<a', '<a target="_blank"', $categories);
						}
						if ($isFilterCatChildren) {
							if (false === $terms) {
								$terms = get_the_terms($mainId, 'product_cat');
							}
							$list = array();
							if (!empty($terms)) {
								foreach ($terms as $term) {
									$termId = $term->term_id;
									if (!isset($catParents[$termId])) {

										$catParents[$termId] = get_ancestors($termId, 'product_cat');
										$catParents[$termId][] = $termId;
									}
									$list = array_merge($list, $catParents[$termId]);
								}
							}
							$strNames = '';
							$list = array_unique($list);
							foreach ($list as $termId) {
								if (isset($catNames[$termId])) {
									$strNames .= $catNames[$termId] . ',';
								}
							}
							$data['categories'] = array($categories, 2 => $strNames);
						} else {
							$data['categories'] = $categories;
						}
						break;
					case 'description':
						if ($isVariation) {
							$varDescription = $_product->get_description();
							if (!empty($varDescription)) {
								$postContent = $varDescription;
							}
						}

						if ($displayDescriptionPopup && ( $frontend || $preview )) {
							$popupContent = '<div class="wtbpModalContentFull">' . $postContent . '</div>';
						}
						if ($stripDescription) {
							$postContent = strip_tags($existMB ? mb_strimwidth($postContent, 0, $stripDescriptionSize, '...') : $this->truncateWordwrap($postContent, $stripDescriptionSize));
						}
						if ($displayDescriptionPopup && ( $frontend || $preview )) {
							$postContent = '<div class="wtbpOpenModal">' . $postContent . $popupContent . '</div>';
						}

						$data['description'] = $postContent;
						break;
					case 'short_description':
						$postShortDescr = $_product->get_short_description();
						if (isset($isDoShortcodes) && $isDoShortcodes) {
							$postShortDescr = apply_filters( 'the_content', $postShortDescr );
						}
						if ($displayShortDescriptionPopup && ( $frontend || $preview )) {
							$popupContent = '<div class="wtbpModalContentFull">' . $postShortDescr . '</div>';
						}
						if ($stripDescriptionShort) {
							$postShortDescr = strip_tags($existMB ? mb_strimwidth($postShortDescr, 0, $stripSizeShort, '...') : $this->truncateWordwrap($postShortDescr, $stripSizeShort));
						}
						if ($displayShortDescriptionPopup && ( $frontend || $preview )) {
							$postShortDescr = '<div class="wtbpOpenModal">' . $postShortDescr . $popupContent . '</div>';
						}
						$data['short_description'] = $postShortDescr;
						break;
					case 'product_link':
						$url = get_permalink($id);
						if ($prodLinkUserString) {
							$productLinkStr = '<div class="product woocommerce"><a class="product-details-button button btn single-product-link" href="' . esc_url($url) . '" target="_blank">' . $prodLinkUserString . '</a></div>';
							$data['product_link'] = $productLinkStr;
						}
						break;
					case 'reviews':
						$reviews = '';
						$average = $_product->get_average_rating();
						if ($average) {
							/* translators: %s: average rating */
							$reviews .= '<div class="star-rating" title="' . esc_attr(sprintf(__( 'Rated %s out of 5', 'woocommerce' ), $average)) . '"><span class="star-rating-width" data-width="' . esc_attr( ( $average / 5 ) * 100 ) . '%"><strong itemprop="ratingValue" class="rating">' . $average . '</strong> ' . esc_html__('out of 5', 'woocommerce') . '</span></div>';
						}
						$data['reviews'] = $reviews;
						break;
					case 'stock':
						$value = '';
						$status = $_product->get_stock_status();
						$name = $stockNames[$status];
						$colored = $showIcons && isset($stockIcons[$status]);
						if ($colored) {
							$value = '<span class="wtbp-stock-' . esc_attr($status) . '"><i class="fa fa-' . $stockIcons[$status] . ' wtbp-stock-icon" aria-hidden="true" title="' . esc_attr($name) . '"></i>';
						}
						if ($showText) {
							$value .= $name;
						}
						if ($showQuantity) {
							$variations = $isVariable ? $_product->get_available_variations() : array();
							if ($isVariable && $showVariationQuantity && !empty($variations)) {
								foreach ($variations as $variationIterator => $variation) {
									$variationObj = new WC_Product_variation($variation['variation_id']);
									$quantity = $variationObj->get_stock_quantity();
									if ($quantity) {
										$variationName = array();
										foreach ($variation['attributes'] as $attrName => $attrValue) {
											if (empty($attrValue)) {
												continue;
											}
											if ($showVariationQuantityAttrNames) {
												$attrName = str_replace('attribute_', '', $attrName);
												if (taxonomy_exists($attrName)) {
													$attrName = get_taxonomy($attrName)->labels->singular_name;
												}
												$attrName = $attrName . ': ';
											} else {
												$attrName = '';
											}
											array_push($variationName, strtoupper($attrName . $attrValue));
										}
										if ($stockMaxQuantity && $quantity > $stockMaxQuantity) {
											$quantity = $stockMaxQuantity . '+';
										}
										$quantityTxt = $stockQuantityText
											? sprintf('<span class="stock-count" data-quantity="%1$d">%2$s - %3$s</span> %4$s',
												$quantity, implode(', ', $variationName), $quantity, $stockQuantityText)
											: sprintf('<span class="stock-count" data-quantity="%1$d">%2$s - %3$s</span> item(s)',
												$quantity, implode(', ', $variationName), $quantity);
									} else {
										$quantityTxt = $stockQuantityText
											? sprintf('<span class="stock-count"></span> %1$s', $stockQuantityText)
											: '<span class="stock-count"></span> item(s)';
									}
									$value .= ( $showText || $variationIterator > 0 ? '<br>' : '' ) . '<span class="stock-item-counts' . ( $quantity ? ''
											: ' wtbpHidden' ) . '">' . $quantityTxt . '</span>';
								}
							} else {
								$quantity = $_product->get_stock_quantity();
								if ($quantity) {
									if ($stockMaxQuantity && $quantity > $stockMaxQuantity) {
										$quantity = $stockMaxQuantity . '+';
									}
									$quantityTxt = $stockQuantityText
										? sprintf('<span class="stock-count" data-quantity="%1$d">%2$s</span> %3$s',
											$quantity, $quantity, $stockQuantityText)
										: sprintf('<span class="stock-count" data-quantity="%1$d">%2$s</span> item(s)',
											$quantity, $quantity);
								} else {
									$quantityTxt = $stockQuantityText
										? sprintf('<span class="stock-count"></span> %1$s', $stockQuantityText)
										: '<span class="stock-count"></span> item(s)';
								}
								$value .= ( $showText ? '<br>' : '' ) . '<span class="stock-item-counts' . ( $quantity ? ''
										: ' wtbpHidden' ) . '">' . $quantityTxt . '</span>';
							}
						}
						if ($colored) {
							$value .= '</span>';
						}
						$data['stock'] = $value;
						break;
					case 'date':
						$data['date'] = $postDate;
						break;
					case 'sale_dates':
						$period = '';
						if ($_product->is_on_sale()) {
							$saleFrom = get_post_meta($id, '_sale_price_dates_from', true);
							$saleTo = get_post_meta($id, '_sale_price_dates_to', true);
							if (!empty($saleFrom)) {
								$period = '<span class="wtbpSaleDates">' . gmdate($dateFormat, $saleFrom) . '</span>';
							}
							if (!empty($saleTo)) {
								$period .= ( empty($period) ? '' : ' ' ) . '<span class="wtbpSaleDates">' . gmdate($dateFormat, $saleTo) . '</span>';
							}
						}
						$data['sale_dates'] = $period;
						break;
					case 'downloads':
						$downloads = '';
						$files = $_product->get_downloads();
						if (count($files) > 0) {
							$showAs = $this->getTableSetting($column, 'downloads_show_as', 'icon');
							foreach ($files as $download) {
								$path = esc_url($download->get_file());
								$name = esc_html($download->get_name());

								if ('audio' == $showAs) {
									$downloads .= '<audio controls class="wtbpDownloadsControl"><source src="' . $path . '">Your browser does not support the <code>audio</code> element.</audio>';
								} elseif ('video' == $showAs) {
									$downloads .= '<video controls class="wtbpDownloadsControl"><source src="' . $path . '">Sorry, your browser does not support embedded videos.</video>';
								} else {
									$downloads .= '<a' . ( 'button' == $showAs ? ' class="button wtbpDownloadsButton"' : '' ) . ' href="' . $path . '"' . ( !$frontend || $prodDownloadsLinkBlank ? ' target="_blank"' : '' ) . '>';
									if ('icon' == $showAs) {
										$downloads .= '<i class="fa fa-fw fa-download"></i>';
									} else {
										$downloads .= $name;
									}
									$downloads .= '</a>';
								}
							}
						}
						$data['downloads'] = $downloads;
						break;
					case 'price':
						$price = $_product->get_price_html();
						if ($varPriceColumn) {
							$price = '<span class="wtbpPrice">' . $price . '</span>';
						}
						$rawPrice = apply_filters('raw_woocommerce_price', $_product->get_price());
						$prices = array($price, $rawPrice);
						if ($isFilterPrices) {
							if ($isVariable) {
								$varPrices = $_product->get_variation_prices();
								if (isset($varPrices['price']) && is_array($varPrices['price'])) {
									$rawPrice = implode(',', $varPrices['price']);
								}
							}
							$prices[] = $rawPrice;
						}
						$data['price'] = $prices;
						break;
					case 'add_to_cart':
						$varInStock = '';

						if ($_product->get_stock_status() == 'outofstock') {
							if ($frontend && $multyAddToCart) {
								$data['check_multy'] = '';
							}
							$data['add_to_cart'] = '<div class="wtbpOutOfStockCart">' . $stockNames['outofstock'] . '</div>';
						} else {
							if ($frontend) {
								$variablesHtml = '';
								$varPricesHtml = '';
								$varImagesHtml = '';

								$view = FrameWtbp::_()->getModule('wootablepress')->getView();

								if ( $isVariable && ! $hideVariationAttr ) {
									$variations = array();
									$attributes = array();
									$defaultId = 0;
									foreach ($_product->get_available_variations() as $variation) {
										if ($variation['variation_is_visible']) {
											$varId = $variation['variation_id'];
											$inStock = $variation['is_in_stock'];
											if ($inStock) {
												$varInStock = $varId;
											}
											$varAttributes = array();
											foreach ($variation['attributes'] as $key => $value) {
												$taxonomy = str_replace('attribute_', '', $key);
												if (taxonomy_exists($taxonomy)) {
													if (!isset($taxonomies[$taxonomy])) {
														$terms = get_terms($taxonomy);
														foreach ($terms as $term) {
															$taxonomies[$taxonomy]['terms'][$term->slug] = $term->name;
														}
														$taxonomies[$taxonomy]['label'] = get_taxonomy($taxonomy)->labels->singular_name;
													}
												} else {
													if (!isset($taxonomies[$taxonomy])) {
														$taxonomies[$taxonomy] = array('label' => $taxonomy, 'terms' => array());
													}
													if (!empty($value)) {
														$taxonomies[$taxonomy]['terms'][$value] = $value;
													}
												}

												if (!isset($taxonomies[$taxonomy])) {
													break;
												}
												if (empty($value)) {
													$attributes[$taxonomy] = $taxonomies[$taxonomy]['terms'];
													$varAttributes[$taxonomy] = '';
												} else {
													$attributes[$taxonomy][$value] = $taxonomies[$taxonomy]['terms'][$value];
													$varAttributes[$taxonomy] = $value;
												}
											}

											$variationQuantity =
												isset($variation['max_qty']) ? ' data-quantity="' . esc_attr($variation['max_qty']) . '"' : ' data-quantity="' . esc_attr(__('n/a', 'woo-product-tables')) . '"';

											$variations[$varId] = $varAttributes;
											$maxQty = isset($variation['max_qty']) ? $variation['max_qty'] : '';
											$minQty = isset($variation['min_qty']) ? $variation['min_qty'] : '';
											$varPricesHtml .=
												'<div class="wtbpVarPrice wtbpHidden" data-variation_id="' . esc_attr($varId) .
													'" data-instock="' . ( $inStock ? '1' : '0' ) . '"' . $variationQuantity .
													'" data-max-qty="' . $maxQty . '"' .
													'" data-min-qty="' . $minQty . '"' .
													'>' .
														$variation['price_html'] .
													( $inStock ? '' : '<div class="wtbpVarOutofstock">' . esc_html($stockNames['outofstock']) . '</div>' ) .
												'</div>';
											if ($showVarImages) {
												$varImagesHtml .= $view->getProductThumbnailLink($varId, $imgSize, 'class="wtbpVarImage wtbpHidden" data-variation_id="' . esc_attr($varId) . '"');
											}
											if (empty($defaultId)) {
												$defaultId = $varId;
											}
										}
									}
									if (!empty($varPricesHtml)) {
										$varPricesHtml = '<div class="wtbpVarPrices' . ( $varPriceColumn ? ' wtbpHidden' : '' ) . '">' . $varPricesHtml . '</div>';
									}
									if (!empty($varImagesHtml)) {
										$varImagesHtml = '<div class="wtbpVarImages">' . $varImagesHtml . '</div>';
									}
									if (count($attributes) > 0) {
										$variablesHtml = '<div class="wtbpVarAttributes" data-default-id="' . esc_attr($defaultId) . 
											'" data-variations="' . htmlspecialchars(json_encode($variations), ENT_QUOTES, 'UTF-8') . '">';

										foreach ($attributes as $taxonomy => $terms) {
											$variablesHtml .=
												'<select class="wtbpVarAttribute" data-attribute="' . esc_attr($taxonomy) . 
												'"><option value="">' .
													esc_html($taxonomies[$taxonomy]['label']) .
												'</option>';
											foreach ($terms as $slug => $value) {
												$variablesHtml .= '<option value="' . esc_attr($slug) . '">' . esc_html($value) . '</option>';
											}
											$variablesHtml .= '</select>';
										}
										$variablesHtml .= '</div>';
									}
								}

								if ($isVariable) {
									$quantityHtml = !$hideQuantityInput && !$hideVariationAttr ? woocommerce_quantity_input(array(), $_product, false) : '';
								} else {
									$quantityHtml = !$hideQuantityInput ? woocommerce_quantity_input(array(), $_product, false) : '';
								}

								$cartUrl = wc_get_cart_url();
								$addToCartUrl = do_shortcode('[add_to_cart_url id="' . $id . '"]'); // dont delete this row!
								if ($multyAddToCart) {
									$data['check_multy'] =
										'<input type="checkbox" class="wtbpAddMulty" value="' . esc_attr($id) .
										'" data-quantity="1" data-variation_id="0" data-position="' . esc_attr($multyAddPosition) . '"' .
										( empty($variablesHtml) ? '' : ' disabled' ) .
										'>';
								}

								if ( $isVariable && $hideVariationAttr ) {
									$data['check_multy'] = '';
								}
								$prId = !empty($varInStock) ? $varInStock : $id;
								if ( get_post_meta($prId, '_wc_measurement_price_calculator_min_price', false) ) {
									ob_start();
									woocommerce_template_single_add_to_cart();
									$data['add_to_cart'] = ob_get_contents();
									ob_end_clean();

									$shortcode =
										'<div class="mpc_add_to_cart_shortcode">' .
										do_shortcode('[add_to_cart id="' . $prId . '" style="" class="product_button_mpc" show_price="false" sku ="' . $sku . '"]') .
										'</div>';
									$shortcode = DispatcherWtbp::applyFilters('customizeCartButtonMPC', $shortcode);

									$addToCartClass = 'add_to_cart_button ajax_add_to_cart product_mpc';
									$shortcode =  str_replace('add_to_cart_button', $addToCartClass, $shortcode);
									$data['add_to_cart'] =  str_replace('<form class="cart"', '<form class="cart form_product_mpc"', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('</form>', $shortcode . '</form>', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('<table', '<div', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('</table>', '</div>', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('<tbody>', '<div>', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('</tbody>', '</div>', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('<tr', '<div', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('</tr>', '</div>', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('<td', '<div', $data['add_to_cart']);
									$data['add_to_cart'] =  str_replace('</td>', '</div>', $data['add_to_cart']);
								} else {
									$data['add_to_cart'] =
										$variablesHtml .
										'<div class="wtbpAddToCartWrapper' . ( empty($variablesHtml) ? '' : ' wtbpDisabledLink' ) . '" data-product_id="' . $id . '">' .
											$quantityHtml .
											do_shortcode('[add_to_cart id="' . $prId . '" class="" style="" show_price="false" sku ="' . $sku . '"]') .
										'</div>' .
										$varPricesHtml .
										$varImagesHtml;
								}
								$varId = '';
							} else {
								$data['add_to_cart'] = do_shortcode('[add_to_cart id="' . $id . '" class="" style="" show_price="false" sku ="' . $sku . '"]');
							}
						}
						break;
					default:
						$data = DispatcherWtbp::applyFilters(
							'getColumnContent',
							$data,
							array(
								'column'     => $column,
								'product'    => $_product,
								'frontend'   => $frontend,
								'settings'   => $settings,
								'stockNames' => $stockNames,
								'imgSize'    => $imgSize,
								'mainId'     => $mainId,
							)
						);
						break;
				}
			}
			$dataArr[] = $data;
		}
		if ($isPage || !$frontend) {
			$page['total'] = $dataExist->found_posts;
			$page['filtered'] = count($dataArr);
		}

		return $dataArr;
	}

	public function setAdminSSPQueryFilters( $settings, $args, $page ) {
		$args['posts_per_page'] = $page['length'];
		$args['offset'] = $page['start'];
		if (!empty($page['search']['value'])) {
			$args['s'] = $page['search']['value'];
		}
		return $args;
	}

	public function truncateWordwrap( $str, $len, $etc = '.. . ' ) {
		if (strlen($str) <= $len) {
			return $str;
		}
		$len = $len - strlen($etc);
		$cut = substr($str, 0, $len);
		if (substr($str, $len, 1) != ' ') {
			$end = strrpos($cut, ' ');
			if ($end > 0) {
				$cut = substr($cut, 0, $end);
			}
		}
		return $cut . $etc;
	}

	public function getColumnNiceName( $slug ) {
		if (empty($this->columnNiceNames)) {
			$orders = $this->orderColumns;
			$names = array();
			if (empty($orders)) {
				$tableColumns = $this->getModel('columns')->getFromTbl();
				foreach ($tableColumns as $columns) {
					$names[$columns['columns_name']] = $columns['columns_nice_name'];
				}
			} else {
				foreach ($orders as $order) {
					$name = ( !empty($order['show_display_name']) && '1' === $order['show_display_name'] ) ? $order['display_name'] : $order['original_name'];
					$names[$order['slug']] = $name;
				}
			}
			$this->columnNiceNames = $names;
		}
		return array_key_exists($slug, $this->columnNiceNames) ? $this->columnNiceNames[$slug] : $slug;
	}

	public function sortProductColumns() {
		$orders = $this->orderColumns;
		$sortArray = array();
		if (!empty($orders)) {
			foreach ($orders as $order) {
				$sortArray[] = $order['slug'];
			}
		} else {
			$orders = array('product_title', 'thumbnail', 'categories', 'price', 'date');
			foreach ($orders as $order) {
				$sortArray[] = $order;
			}
		}
		return $sortArray;
	}

	public function generateTableHtml( $listPost, $frontend, $settings, $withHeader = true ) {
		$dateAndTimeFormat = $this->getDateTimeFormat($settings);
		$columns = $this->sortProductColumns();
		if ($frontend && $this->getTableSetting($settings, 'multiple_add_cart', false)) {
			$mode = $this->getTableSetting($settings, 'responsive_mode', '');
			$multyAddPosition = $this->getTableSetting($settings, 'multiple_add_cart_position', 'first');
			if ( 'first' == $multyAddPosition || 'responsive' == $mode || 'hiding' == $mode ) {
				array_unshift($columns, 'check_multy');
			} else {
				array_push($columns, 'check_multy');
			}
		}
		if ($withHeader) {
			$noSortColumns = array('thumbnail', 'add_to_cart', 'description', 'short_description', 'attribute', 'sale_dates', 'check_multy');
			$this->columnNiceNames = array();
			$tableHeader = '<tr>';
			if (!$frontend) {
				$tableHeader .= '<th class="no-sort"><input class="wtbpCheckAll" type="checkbox"/></th>';
			}
			foreach ($columns as $key) {
				$noSort = in_array($key, $noSortColumns) ? ' class="no-sort"' : '';
				$tableHeader .= '<th data-key="' . esc_attr($key) . '"' . $noSort . '>' . ( 'check_multy' == $key ? '<input type="checkbox" class="wtbpAddMultyAll" data-position="' . esc_attr($multyAddPosition) . '">': esc_html($this->getColumnNiceName($key)) ) . '</th>';
			}
			$tableHeader .= '</tr>';
		}
		$tableBody = '';
		for ($i = 0; $i < count($listPost); $i++) {
			$tableBody .=  $frontend ? '<tr>' : '<tr><td><input type="checkbox" data-id="' . esc_attr($listPost[$i]['id']) . '"></td>';
			$product = $listPost[$i];
			foreach ($columns as $key) {
				$data = isset($product[$key]) ? $product[$key] : '';

				if (empty($data)) {
					$data = '';
				}

				if (is_array($data)) {
					if (isset($data[1])) { 
						$order = ' data-order="' . esc_attr($data[1]) . '" data-search="' . esc_attr($data[1]) . '"';
					}
					if (isset($data[2])) {
						$order .= ' data-custom-filter="' . esc_attr($data[2]) . '"';
					}
					$data = $data[0];
				} else {
					$order = '';
				}
				if ('date' === $key && $dateAndTimeFormat) {
					$date = $data;
					$dateTimestamp = strtotime($date);
					$outputDate = gmdate($dateAndTimeFormat, $dateTimestamp);

					$tableBody .=  '<td' . ( $frontend ? ' data-order="' . esc_attr($dateTimestamp) . '"' : '' ) . ' class="' . esc_attr($key) . '"><div class="wtbpNoBreak">' . $outputDate . '</div></td>';
				} else if ('product_title' === $key) {
					$tableBody .=  '<td class="' . esc_attr($key) . '">' . $data . '</td>';
				} else {
					$tableBody .=  '<td class="' . esc_attr($key) . '"' . ( $frontend ? $order : '' ) . '>' . $data . '</td>';
				}
			}
			$tableBody .=  '</tr>';
		}

		$table = '';
		if ($withHeader) {
			$table = '<thead>' . $tableHeader . '</thead>';
			if ($this->getTableSetting($settings, 'footer_show', false)) {
				$table .= '<tfoot>' . $tableHeader . '</tfoot>';
			}
		}
		$table .= '<tbody>' . $tableBody . '</tbody>';

		$isPro = FrameWtbp::_()->isPro();
		if ( $isPro ) {
			foreach ( $this->orderColumns as $column ) {
				if ( array_key_exists( 'description_popup', $column ) || array_key_exists( 'short_description_popup', $column ) ) {
					$table .= ViewWtbp::getContent('wootablepressDescriptionPopup');
				}
			}
		}

		return $table;
	}

	public function generateTableSearchData( $listPost ) {
		$table = array();
		$yes = esc_html__('yes', 'woo-product-tables');
		$no = esc_html__('no', 'woo-product-tables');
		foreach ($listPost as $post) {
			$table[] = array(
				'0' => '<input type="checkbox" data-id="' . $post['id'] . '">',
				'1' => ( $post['in_table'] ? '<label class="wtbpPropuctInTable">' . $yes . '</label>' : $no ),
				'2' => $post['thumbnail'],
				'3' => $post['product_title'],
				'4' => $post['variation'],
				'5' => $post['categories'],
				'6' => $post['sku'],
				'7' => $post['stock'],
				'8' => $post['price'],
				'9' => $post['date'],
				'10' => $post['attributes'],
			);
		}
		return $table;
	}

	public function getDateTimeFormat( $settings ) {

		$dateFormat = $this->getTableSetting($settings, 'date_formats', false);
		$timeFormat = $this->getTableSetting($settings, 'time_formats', false);
		$dateAndTimeFormat = false;
		if ($timeFormat && $dateFormat) {
			$dateAndTimeFormat = $dateFormat . ' ' . $timeFormat;
		} else if ($dateFormat) {
			$dateAndTimeFormat = $dateFormat;
		} else if ($timeFormat) {
			$dateAndTimeFormat = $timeFormat;
		}
		return $dateAndTimeFormat;
	}

	public function getTaxonomyHierarchyHtml( $parent = 0, $pre = '', $tax = 'product_cat' ) {
		$args = array(
			'hide_empty' => true,
			'parent' => $parent
		);
		$terms = get_terms($tax, $args);
		$options = '';
		foreach ($terms as $term) {
			if (!empty($term->term_id)) {
				$options .= '<option data-parent="' . esc_attr($parent) . '" value="' . esc_attr($term->term_id) . '">' . $pre . esc_html($term->name) . '</option>';
				$options .= $this->getTaxonomyHierarchyHtml($term->term_id, $pre . '&nbsp;&nbsp;&nbsp;', $tax);
			}
		}
		return $options;
	}
	public function getProductsWithVariationsHtml() {
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'fields' => array('ID', 'post_title'),
			'ignore_sticky_posts' => true,
			'tax_query' => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'variable',
				),
			),
		);
		$products = new WP_Query($args);
		$options = '';
		foreach ($products->posts as $product) {
			if (!empty($product->ID)) {
				$options .= '<option value="' . esc_attr($product->ID) . '">' . esc_html($product->post_title) . '</option>';
			}
		}
		return $options;
	}
	public function getAuthorsHtml() {
		$options = '';
		foreach (get_users() as $user) {
			$options .= '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
		}
		return $options;
	}

	public function getChildrenAttributesHierarchy( $parent = 0, $slugname = '', $pre = '' ) {
		$terms = get_terms($slugname, array(
			'hide_empty' => true,
			'parent' => 0
		));
		$options = '';
		foreach ($terms as $term) {
			if (!empty($term->term_id)) {
				$options .= '<option data-parent="' . esc_attr($parent) . '" value="' . esc_attr($term->term_id) . '">' . $pre . esc_html($term->name) . '</option>';
			}
		}
		return $options;
	}
	public function getAttributesHierarchy( $parent = 0, $pre = '' ) {
		$listOfProducts = wc_get_products( array( 'return' => 'ids', 'limit' => -1 ) );
		$producstListArray = array();
		$attributesListArray = array();
		$options = '';

		foreach ($listOfProducts as $product) {
			$productId = $product;
			$product = wc_get_product( $product );
			$attributesList = $product->get_attributes();
			if (!empty($attributesList)) {
				foreach ($attributesList as $attribute) {
					$id = $attribute['id'];
					if (!empty($id) && !isset($attributesListArray[$id])) {
						$data = wc_get_attribute($id);
						$attributesListArray[$id] = array('name' => $attribute['name'], 'label' => is_null($data) ? $attribute['name'] : $data->name);
					}
				}
			}
		}
		foreach ($attributesListArray as $attributeId => $attribute) {
			$options .= '<option data-parent="' . esc_attr($parent) . '" value="' . esc_attr($attributeId) . '">' . esc_html($attribute['label']) . '</option>';
			$options .= self::getChildrenAttributesHierarchy($attributeId, $attribute['name'], '&nbsp;&nbsp;&nbsp;');
		}

		return $options;
	}
	public function getLeerSearchTable() {
		$th = '<th class="no-sort"><input class="wtbpCheckAll" type="checkbox"/></th>' . 
			'<th class="no-sort">' . esc_html__('In table', 'woo-product-tables') . '</th>' . 
			'<th class="no-sort">' . esc_html__('Thumbnail', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('Name', 'woo-product-tables') . '</th>' . 
			'<th class="no-sort">' . esc_html__('Variation', 'woo-product-tables') . '</th>' . 
			'<th class="no-sort">' . esc_html__('Categories', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('SKU', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('Stock status', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('Price', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('Date', 'woo-product-tables') . '</th>' . 
			'<th>' . esc_html__('Attributes', 'woo-product-tables') . '</th>';
		return '<thead><tr>' . $th . '</tr></thead>';
	}

}
